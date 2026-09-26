<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Captacion;
use App\Models\Client;
use App\Models\Document;
use App\Models\Notification;
use App\Models\Property;
use App\Models\RentalAval;
use App\Models\RentalProcess;
use App\Services\ClientPortalService;
use App\Services\EmailService;
use App\Support\SellerDocumentChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PortalExpedienteController extends Controller
{
    public function __construct(protected ClientPortalService $portalService) {}

    public function show()
    {
        $user   = Auth::user();
        $client = $this->portalService->getClientForUser($user);
        if (!$client) abort(404);

        // ?para=obligado: el INQUILINO captura los datos de su obligado solidario (el obligado no tiene Portal). Todo el
        // resto de la página trabaja con `$client` = el obligado; solo el inquilino de esa renta puede entrar.
        $obligadoRental = null;
        if (request('para') === 'obligado') {
            $obligadoRental = app(\App\Services\ObligadoSolidarioService::class)->tenantMayActFor($client, $this->portalService->activeTenantRental($client)?->obligado_client_id);
            abort_unless($obligadoRental, 404);
            $client = $obligadoRental->obligado;
        }

        $interestTypes = $client->interest_types ?? [];
        $isArrendador  = in_array('renta_propietario', $interestTypes);
        // Inquilino = tiene una renta activa como arrendatario (misma fuente que "Mi camino"); el interés capturado
        // ya no es requisito (si faltaba, el asistente y las pestañas de inquilino no salían).
        $tenantRental  = $obligadoRental ? null : $this->portalService->activeTenantRental($client);
        $isArrendatario= in_array('renta_inquilino',   $interestTypes) || (bool) $tenantRental || (bool) $obligadoRental;
        $isComprador   = in_array('compra',            $interestTypes);
        $isVendedor    = in_array('venta',             $interestTypes);

        // Active rental process (arrendatario side)
        $rentalAsInquilino = null;
        if ($isArrendatario) {
            $rentalAsInquilino = RentalProcess::where('tenant_client_id', $client->id)
                ->whereNotIn('status', ['cancelled'])
                ->with(['avales', 'pagares', 'property'])
                ->latest()
                ->first();
        }

        if (! $rentalAsInquilino && $obligadoRental) {
            $rentalAsInquilino = $obligadoRental->load(['avales', 'pagares', 'property']);
        }

        // Active rental process (arrendador side)
        $rentalAsOwner = null;
        if ($isArrendador) {
            $rentalAsOwner = RentalProcess::where('owner_client_id', $client->id)
                ->whereNotIn('status', ['cancelled'])
                ->with(['property'])
                ->latest()
                ->first();
        }

        // Documents already uploaded by client
        $documents = Document::where('client_id', $client->id)
            ->orderBy('category')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('category');

        // Aval del cliente (si es arrendatario)
        $aval = $rentalAsInquilino
            ? $rentalAsInquilino->avales->first()
            : RentalAval::where('client_id', $client->id)->latest()->first();

        // Referencias personales ya capturadas (arrendatario)
        $references = $client->references;

        // Calcular completitud por sección
        $sections = $this->calcSections($client, $isArrendador, $isArrendatario, $isComprador, $isVendedor, $aval, $rentalAsInquilino, $documents, $references, $obligadoRental !== null);

        // Asistente "Tus datos" del inquilino: pasos cortos, uno por pantalla.
        $wizard = null;
        $prefilled = [];
        $wizardRental = $tenantRental ?? $obligadoRental;
        if ($isArrendatario && $rentalAsInquilino && $wizardRental && $wizardRental->id === $rentalAsInquilino->id) {
            $wizard = $this->wizardSteps($sections, $rentalAsInquilino, (string) request('paso'), $obligadoRental !== null);
            // Después de calcular el avance (para no inflarlo): prellena, SOLO en pantalla, lo que la IA leyó de sus documentos.
            $prefilled = $this->prefillFromDocuments($client, $documents);
        }

        return view('portal.expediente', compact(
            'client', 'sections',
            'isArrendador', 'isArrendatario', 'isComprador', 'isVendedor',
            'rentalAsInquilino', 'rentalAsOwner',
            'documents', 'aval', 'references', 'wizard', 'prefilled',
        ) + ['obligadoMode' => $obligadoRental !== null]);
    }

    /** Pasos del asistente (orden fijo). El aval solo aparece si la garantía del inquilino es aval. */
    private function wizardSteps(array $sections, RentalProcess $rental, string $requested, bool $obligado = false): array
    {
        $defs = [
            'datos' => ['Datos personales', 'datos', 'datos'],
            'identificacion' => ['Identificación y domicilio', 'identificacion', 'identificacion'],
            'hogar' => ['Tu hogar', 'hogar', 'hogar'],
            'referencias' => ['Referencias personales', 'hogar', 'referencias'],
            'ingresos' => ['Trabajo e ingresos', 'ingresos', 'ingresos'],
        ];
        if ($obligado) {
            // El obligado solidario lleva el mismo cuestionario (datos, identificación, referencias, trabajo/arrendador
            // anterior); NO "información del hogar" (es de quien va a vivir en el inmueble) ni aval.
            unset($defs['hogar']);
        } elseif (\App\Support\TenantRoadmap::route($rental) === \App\Support\TenantRoadmap::ROUTE_AVAL) {
            $defs['garantia'] = ['Tu aval', 'garantia', 'garantia'];
        }

        $keys = array_keys($defs);
        $steps = [];
        foreach ($defs as $key => [$title, $section, $sectionKey]) {
            $steps[$key] = ['key' => $key, 'title' => $title, 'section' => $section, 'pct' => (int) ($sections[$sectionKey]['pct'] ?? 0)];
        }

        // Paso actual: el pedido; si no, el primero incompleto; si todo está completo, el primero.
        $current = isset($steps[$requested]) ? $requested : (collect($steps)->first(fn($st) => $st['pct'] < 100)['key'] ?? $keys[0]);
        $i = array_search($current, $keys, true);

        return [
            'steps' => $steps,
            'current' => $current,
            'index' => $i + 1,
            'total' => count($keys),
            'prev' => $i > 0 ? $keys[$i - 1] : null,
            'next' => $keys[$i + 1] ?? 'fin',
        ];
    }

    /**
     * Rellena EN MEMORIA (no guarda) los campos vacíos con lo que la IA leyó en sus documentos: CURP y vigencia de la
     * INE, y la dirección del recibo. Así lo que subió en "Mis documentos" no se vuelve a teclear; lo confirma y guarda.
     *
     * @return string[] etiquetas de lo prellenado (para avisar al cliente)
     */
    private function prefillFromDocuments(Client $client, $documents): array
    {
        $filled = [];
        $ai = fn(string $cat) => ($documents->get($cat)?->sortByDesc('created_at')->first(fn($d) => is_array($d->ai_extracted_data) && ! empty($d->ai_extracted_data['legible'])))?->ai_extracted_data;

        $id = $ai('ine_frente') ?? $ai('pasaporte');
        if ($id) {
            if (! $client->curp && ! empty($id['curp'])) { $client->curp = strtoupper($id['curp']); $filled[] = 'CURP'; }
            if (! $client->id_expiry_month && ! empty($id['vigencia_mes'])) { $client->id_expiry_month = $id['vigencia_mes']; $client->id_expiry_year = $id['vigencia_anio'] ?? null; $filled[] = 'vigencia de tu identificación'; }
            if (! $client->id_type) { $client->id_type = $documents->has('pasaporte') && ! $documents->has('ine_frente') ? 'pasaporte' : 'INE'; }
        }

        $addr = $ai('luz') ?? $ai('agua') ?? $ai('gas');
        if ($addr) {
            $map = ['address_street' => 'calle_numero', 'address_colony' => 'colonia', 'address_municipality' => 'alcaldia_municipio', 'address_state' => 'estado', 'address_zip' => 'codigo_postal'];
            $any = false;
            foreach ($map as $field => $key) {
                if (! $client->{$field} && ! empty($addr[$key])) { $client->{$field} = $addr[$key]; $any = true; }
            }
            $any && $filled[] = 'tu domicilio';
        }

        return $filled;
    }

    /** Tras guardar un paso: si vino `next`, sigue al siguiente paso (o de regreso a Mi camino al terminar). */
    private function saved(Request $request, string $message)
    {
        $next = (string) $request->input('next');
        if ($next === 'fin') {
            return redirect()->route('portal.journey')->with('success', $message . ' ¡Terminaste tus datos!');
        }
        if (in_array($next, ['datos', 'identificacion', 'hogar', 'referencias', 'ingresos', 'garantia'], true)) {
            return redirect()->route('portal.expediente', ['paso' => $next] + ($request->input('para') === 'obligado' ? ['para' => 'obligado'] : []))->with('success', $message);
        }

        return back()->with('success', $message);
    }

    /**
     * A quién se guarda: el cliente del Portal, o —con `para=obligado`— su obligado solidario (solo el inquilino de esa
     * renta, mientras el trato lo exija). Toda escritura a nombre del obligado pasa por aquí.
     */
    private function subject(Request $request): Client
    {
        $me = $this->portalService->getClientForUser(Auth::user());
        abort_unless($me, 403);
        if ($request->input('para') !== 'obligado') {
            return $me;
        }
        $rental = app(\App\Services\ObligadoSolidarioService::class)->tenantMayActFor($me, $this->portalService->activeTenantRental($me)?->obligado_client_id);
        abort_unless($rental, 403);

        return $rental->obligado;
    }

    /** Guardar datos personales / legales */
    public function saveDatos(Request $request)
    {
        $user   = Auth::user();
        $client = $this->subject($request);
        if (!$client) abort(403);

        $validated = $request->validate([
            'first_name'        => 'nullable|string|max:100',
            'last_name_paterno' => 'nullable|string|max:100',
            'last_name_materno' => 'nullable|string|max:100',
            'birth_date'        => 'nullable|date',
            'birth_state'       => 'nullable|string|max:50',
            'gender'            => 'nullable|in:H,M',
            'nationality'       => 'nullable|string|max:50',
            'marital_status'    => 'nullable|in:soltero,casado,divorciado,viudo,union_libre',
            'occupation'        => 'nullable|string|max:120',
            'marital_regime'    => 'nullable|in:separacion_bienes,sociedad_conyugal',
            'spouse_name'       => 'nullable|string|max:200',
            'spouse_curp'       => 'nullable|string|max:18',
            'curp'              => 'nullable|string|max:18',
            'rfc'               => 'nullable|string|max:13',
            'id_type'           => 'nullable|in:INE,pasaporte,cedula_profesional,otro',
            'id_number'         => 'nullable|string|max:60',
            'id_expiry_month'   => 'nullable|integer|min:1|max:12',
            'id_expiry_year'    => 'nullable|integer|min:2000|max:2100',
            'address_street'       => 'nullable|string|max:200',
            'address_colony'       => 'nullable|string|max:100',
            'address_municipality' => 'nullable|string|max:100',
            'address_state'        => 'nullable|string|max:60',
            'address_zip'          => 'nullable|string|max:5',
            'domicilio_proof_type' => 'nullable|in:' . implode(',', array_keys(\App\Models\Client::DOMICILIO_PROOF_TYPES)),
        ]);

        // Sync name field if full name parts provided
        if (!empty($validated['first_name']) && !empty($validated['last_name_paterno'])) {
            $validated['name'] = trim(
                ($validated['first_name'] ?? '') . ' ' .
                ($validated['last_name_paterno'] ?? '') . ' ' .
                ($validated['last_name_materno'] ?? '')
            );
        }

        $client->update($validated);

        return $this->saved($request, 'Datos personales actualizados correctamente.');
    }

    /** Guardar ingresos + datos laborales + arrendador anterior (arrendatario) */
    public function saveIngresos(Request $request)
    {
        $user   = Auth::user();
        $client = $this->subject($request);
        if (!$client) abort(403);

        $validated = $request->validate([
            'income_type'       => 'nullable|in:empleado,independiente,empresario,otro',
            'income_amount'     => 'nullable|numeric|min:0',
            'income_proof_type' => 'nullable|in:' . implode(',', array_keys(\App\Models\Client::INCOME_PROOF_TYPES)),
            // Cuestionario — datos laborales
            'employer_name'            => 'nullable|string|max:150',
            'employer_address'         => 'nullable|string|max:200',
            'employer_phone'           => 'nullable|string|max:30',
            'job_seniority'            => 'nullable|string|max:60',
            'other_income_amount'      => 'nullable|numeric|min:0',
            'other_income_description' => 'nullable|string|max:200',
            // Cuestionario — arrendador anterior
            'previous_landlord_name'   => 'nullable|string|max:150',
            'previous_landlord_phone'  => 'nullable|string|max:30',
            'previous_landlord_mobile' => 'nullable|string|max:30',
            'previous_landlord_email'  => 'nullable|email|max:150',
            'previous_landlord_years'  => 'nullable|string|max:60',
        ]);

        $client->update($validated);
        return $this->saved($request, 'Información de ingresos guardada.');
    }

    /** Guardar información del hogar (arrendatario) — ocupantes y mascotas */
    public function saveHogar(Request $request)
    {
        $user   = Auth::user();
        $client = $this->portalService->getClientForUser($user);
        if (!$client) abort(403);

        $validated = $request->validate([
            'occupants_count' => 'nullable|integer|min:0|max:20',
            'pets'                => 'nullable|array',
            'pets.*.type'         => 'nullable|in:' . implode(',', array_keys(\App\Models\Client::PET_TYPES)),
            'pets.*.size'         => 'nullable|in:' . implode(',', array_keys(\App\Models\Client::PET_SIZES)),
        ]);

        $pets = collect($validated['pets'] ?? [])
            ->filter(fn($p) => !empty($p['type']))
            ->values()
            ->all();

        $client->update([
            'occupants_count' => $validated['occupants_count'] ?? null,
            'pets'            => $pets,
        ]);

        return $this->saved($request, 'Información del hogar guardada.');
    }

    /** Guardar referencias personales (arrendatario) — hasta 3, estructuradas */
    public function saveReferencias(Request $request)
    {
        $user   = Auth::user();
        $client = $this->subject($request);
        if (!$client) abort(403);

        $validated = $request->validate([
            'references'                  => 'nullable|array|max:3',
            'references.*.name'           => 'nullable|string|max:150',
            'references.*.address'        => 'nullable|string|max:200',
            'references.*.mobile_phone'   => 'nullable|string|max:30',
            'references.*.landline_phone' => 'nullable|string|max:30',
            'references.*.email'          => 'nullable|email|max:150',
        ]);

        foreach ($validated['references'] ?? [] as $i => $ref) {
            if (empty($ref['name'])) continue;

            $client->references()->updateOrCreate(
                ['sort_order' => $i + 1],
                [
                    'name'            => $ref['name'],
                    'address'         => $ref['address'] ?? null,
                    'mobile_phone'    => $ref['mobile_phone'] ?? null,
                    'landline_phone'  => $ref['landline_phone'] ?? null,
                    'email'           => $ref['email'] ?? null,
                ]
            );
        }

        return $this->saved($request, 'Referencias personales guardadas.');
    }

    /** Guardar financiamiento (comprador) */
    public function saveFinanciamiento(Request $request)
    {
        $user   = Auth::user();
        $client = $this->portalService->getClientForUser($user);
        if (!$client) abort(403);

        $validated = $request->validate([
            'financing_type'          => 'nullable|in:contado,hipotecario,infonavit,fovissste,cofinanciamiento',
            'financing_preauth_amount'=> 'nullable|numeric|min:0',
            'nss'                     => 'nullable|string|max:11',
            'infonavit_balance'       => 'nullable|numeric|min:0',
        ]);

        $client->update($validated);
        return back()->with('success', 'Información de financiamiento guardada.');
    }

    /** Guardar datos del aval (arrendatario) */
    public function saveAval(Request $request)
    {
        $user   = Auth::user();
        $client = $this->portalService->getClientForUser($user);
        if (!$client) abort(403);

        $validated = $request->validate([
            'name'                  => 'required|string|max:200',
            'curp'                  => 'nullable|string|max:18',
            'rfc'                   => 'nullable|string|max:13',
            'phone'                 => 'nullable|string|max:20',
            'email'                 => 'nullable|email|max:150',
            'relationship'          => 'nullable|string|max:80',
            'id_type'               => 'nullable|string|max:30',
            'id_number'             => 'nullable|string|max:60',
            'id_expiry'             => 'nullable|date',
            'property_address'      => 'nullable|string|max:200',
            'property_colony'       => 'nullable|string|max:100',
            'property_municipality' => 'nullable|string|max:100',
            'property_state'        => 'nullable|string|max:60',
            'property_zip'          => 'nullable|string|max:5',
            'property_folio_real'   => 'nullable|string|max:80',
            'escritura_numero'      => 'nullable|string|max:80',
            'property_value'        => 'nullable|numeric|min:0',
            'property_has_mortgage' => 'nullable|boolean',
            'property_free_of_liens'=> 'nullable|boolean',
            'notes'                 => 'nullable|string|max:500',
        ]);

        $validated['client_id'] = $client->id;

        // Attach to active rental process if exists
        $rental = RentalProcess::where('tenant_client_id', $client->id)
            ->whereNotIn('status', ['cancelled'])
            ->latest()->first();
        if ($rental) $validated['rental_process_id'] = $rental->id;

        $existing = RentalAval::where('client_id', $client->id)->latest()->first();
        if ($existing) {
            $existing->update($validated);
        } else {
            RentalAval::create($validated);
        }

        return $this->saved($request, 'Datos del aval guardados correctamente.');
    }

    /** Subir documento al expediente */
    public function uploadDocument(Request $request)
    {
        $user   = Auth::user();
        $client = $this->portalService->getClientForUser($user);
        if (!$client) abort(403);

        $request->validate([
            'category' => 'required|string',
            'file'     => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png',
            'rental_process_id' => 'nullable|integer',
        ]);

        $file  = $request->file('file');

        $quality = app(\App\Services\DocumentQualityService::class);
        $gate = $quality->gate($file, $request->category, $client->id);
        if ($gate['block']) {
            return back()->with('error', $gate['block']);
        }

        $path  = \App\Support\SecureFiles::store($file, 'expediente/client-' . $client->id);

        // property_id era fillable pero nunca se poblaba aquí — sin esto,
        // el widget de expediente en la ficha de propiedad (admin) no podía
        // filtrar por property_id, solo por client_id (auditoria 2026-07-07).
        // Misma resolución que PortalPropertyController::show(): la
        // Captación más reciente del cliente y su property, o la property
        // más reciente asignada directo a él.
        $captacion = Captacion::where('client_id', $client->id)->with('property')->latest()->first();
        $property = $captacion?->property ?? Property::where('client_id', $client->id)->latest()->first();

        $document = Document::create([
            'client_id'         => $client->id,
            'property_id'       => $property?->id,
            'rental_process_id' => $request->rental_process_id,
            'uploaded_by'       => Auth::id(),
            'category'          => $request->category,
            'label'             => Document::CATEGORIES[$request->category] ?? $file->getClientOriginalName(),
            'file_path'         => $path,
            'file_name'         => $file->getClientOriginalName(),
            'mime_type'         => $file->getMimeType(),
            'file_size'         => $file->getSize(),
            'status'            => 'received',
        ]);

        $quality->record($document, $gate);

        // Antes nada avisaba al broker de un documento nuevo — se enteraba
        // solo si entraba manualmente a revisar (auditoria 2026-07-06).
        $assignedUserId = $client->assigned_user_id;
        if ($assignedUserId) {
            Notification::create([
                'user_id' => $assignedUserId,
                'type'    => 'system',
                'title'   => 'Documento subido en expediente',
                'body'    => "{$client->name} subio un documento a su expediente: {$document->label}.",
                'data'    => ['url' => route('clients.show', $client->id), 'client_id' => $client->id, 'document_id' => $document->id],
            ]);
            try {
                $asesorUser = \App\Models\User::find($assignedUserId);
                if ($asesorUser?->email) {
                    app(EmailService::class)->send(
                        $asesorUser->email,
                        'Documento subido en expediente — ' . $client->name,
                        "<p>{$client->name} subio un documento a su expediente: <strong>{$document->label}</strong>.</p>"
                        . '<p><a href="' . route('clients.show', $client->id) . '">Ver expediente</a></p>',
                        $asesorUser->name
                    );
                }
            } catch (\Exception $e) {
                Log::warning('PortalExpedienteController: no se pudo notificar por correo al asesor', [
                    'client_id' => $client->id,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', 'Documento subido correctamente.');
    }

    /** Calcular completitud por sección */
    private function calcSections($client, $isArrendador, $isArrendatario, $isComprador, $isVendedor, $aval, $rental, $documents, $references = null, bool $obligado = false): array
    {
        $sections = [];
        $references = $references ?? collect();

        // Datos personales (todos) — curp/rfc NO van aquí: se llenan en la
        // pestaña de Identificación, no en esta. Antes contaban para el % de
        // "Datos personales" sin tener un campo visible ahí para llenarlos,
        // así que un cliente que llenara TODO lo que sí ve en esta pestaña
        // se quedaba atorado sin poder llegar a 100% (hallazgo 2026-09-24,
        // reportado por un cliente real vía WhatsApp).
        $personalFields = \App\Support\ExpedienteFields::PERSONAL;
        $personalFilled = collect($personalFields)->filter(fn($f) => !empty($client->$f))->count();
        $sections['datos'] = ['filled' => $personalFilled, 'total' => count($personalFields), 'pct' => round($personalFilled / count($personalFields) * 100)];

        // Identificación (todos) — incluye curp/rfc, que sí se llenan aquí.
        $idFields = \App\Support\ExpedienteFields::IDENTIFICATION;
        $idFilled = collect($idFields)->filter(fn($f) => !empty($client->$f))->count();
        $sections['identificacion'] = ['filled' => $idFilled, 'total' => count($idFields), 'pct' => round($idFilled / count($idFields) * 100)];

        // Información del hogar (arrendatario) — ocupantes + mascotas (las
        // mascotas no suman "obligatorio", solo cuentan si se declararon).
        if ($isArrendatario) {
            $hogarFilled = !empty($client->occupants_count) ? 1 : 0;
            $sections['hogar'] = ['filled' => $hogarFilled, 'total' => 1, 'pct' => round($hogarFilled / 1 * 100)];
        }

        // Ingresos (arrendatario) — incluye datos laborales, otros ingresos,
        // arrendador anterior, comprobante de ingresos (según el tipo que
        // elija) y buró de crédito (checklist real del cuestionario en
        // papel, ver App\Support\TenantDocumentChecklist).
        if ($isArrendatario) {
            $incomeFields = $obligado ? \App\Support\ExpedienteFields::INCOME_OBLIGADO : \App\Support\ExpedienteFields::INCOME_TENANT;
            $incomeFilled = collect($incomeFields)->filter(fn($f) => !empty($client->$f))->count();
            $incomeProofCat = \App\Models\Client::INCOME_PROOF_CATEGORY[$client->income_proof_type] ?? null;
            $hasIncomeDoc = $incomeProofCat && $documents->has($incomeProofCat);
            // El obligado no cuenta el comprobante aquí (va en Mis documentos, con la regla de los últimos 3).
            $incomeFilled += (! $obligado && $hasIncomeDoc) ? 1 : 0;
            $incomeTotal = count($incomeFields) + ($obligado ? 0 : 1);
            $sections['ingresos'] = ['filled' => $incomeFilled, 'total' => $incomeTotal, 'pct' => round($incomeFilled / $incomeTotal * 100)];

            // Referencias personales — 3 contactos estructurados
            $refFilled = min($references->count(), 3);
            $sections['referencias'] = ['filled' => $refFilled, 'total' => 3, 'pct' => round($refFilled / 3 * 100)];
        }

        // Garantía (arrendatario)
        if ($isArrendatario) {
            $guaranteeType = $rental?->guarantee_type;
            $hasAval = $guaranteeType && in_array($guaranteeType, ['aval','aval_pagares']);
            $hasPagares = $guaranteeType && in_array($guaranteeType, ['pagares','aval_pagares']);
            $hasPoliza = $guaranteeType === 'poliza_juridica';

            $gFilled = 0; $gTotal = 1;
            if ($hasAval && $aval) {
                $avalFields = ['name','curp','phone','property_address','property_state','escritura_numero'];
                $avalFilled = collect($avalFields)->filter(fn($f) => !empty($aval->$f))->count();
                $gFilled = $avalFilled; $gTotal = count($avalFields);
            } elseif ($hasPagares && $rental?->pagares->count()) {
                $gFilled = 1; $gTotal = 1;
            } elseif ($hasPoliza && $rental?->poliza_number) {
                $gFilled = 1; $gTotal = 1;
            }
            $sections['garantia'] = ['filled' => $gFilled, 'total' => $gTotal, 'pct' => $gTotal > 0 ? round($gFilled / $gTotal * 100) : 0, 'type' => $guaranteeType];
        }

        // Financiamiento (comprador)
        if ($isComprador) {
            $finFields = ['financing_type'];
            $finFilled = collect($finFields)->filter(fn($f) => !empty($client->$f))->count();
            if ($client->financing_type === 'infonavit') {
                $finFields = ['financing_type','nss','infonavit_balance'];
            } elseif (in_array($client->financing_type, ['hipotecario','cofinanciamiento'])) {
                $finFields = ['financing_type','financing_preauth_amount'];
                $hasPreauth = $documents->has('carta_preautorizacion');
                $finFilled = collect($finFields)->filter(fn($f) => !empty($client->$f))->count() + ($hasPreauth ? 1 : 0);
                $finFields[] = 'carta_preautorizacion_doc';
            }
            $finFilled = collect($finFields)->filter(fn($f) => !empty($client->$f))->count();
            $sections['financiamiento'] = ['filled' => $finFilled, 'total' => count($finFields), 'pct' => count($finFields) > 0 ? round($finFilled / count($finFields) * 100) : 0];
        }

        // Documentos del inmueble (arrendador / vendedor) — antes una lista
        // fija de 4 que además nunca contaba reglamento_condominio pese a
        // poder subirse en el blade; ahora usa el checklist real del
        // vendedor (SellerDocumentChecklist::INMUEBLE), 7 documentos.
        if ($isArrendador || $isVendedor) {
            $docKeys = array_keys(SellerDocumentChecklist::INMUEBLE);
            $docFilled = collect($docKeys)->filter(fn($k) => $documents->has($k))->count();
            $sections['documentos_inmueble'] = ['filled' => $docFilled, 'total' => count($docKeys), 'pct' => round($docFilled / count($docKeys) * 100)];
        }

        // Documentación notarial (solo vendedor) — la tramita la notaría,
        // no el cliente; se muestra en el Portal como estado, no cuenta
        // como "pendiente del cliente".
        if ($isVendedor) {
            $notarialKeys = array_keys(SellerDocumentChecklist::NOTARIAL);
            $notarialFilled = collect($notarialKeys)->filter(fn($k) => $documents->has($k))->count();
            $sections['documentos_notariales'] = ['filled' => $notarialFilled, 'total' => count($notarialKeys), 'pct' => round($notarialFilled / count($notarialKeys) * 100)];
        }

        return $sections;
    }
}
