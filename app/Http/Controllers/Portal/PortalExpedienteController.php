<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Notification;
use App\Models\RentalAval;
use App\Models\RentalProcess;
use App\Services\ClientPortalService;
use App\Services\EmailService;
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

        $interestTypes = $client->interest_types ?? [];
        $isArrendador  = in_array('renta_propietario', $interestTypes);
        $isArrendatario= in_array('renta_inquilino',   $interestTypes);
        $isComprador   = in_array('compra',            $interestTypes);
        $isVendedor    = in_array('venta',             $interestTypes);

        // Active rental process (arrendatario side)
        $rentalAsInquilino = null;
        if ($isArrendatario) {
            $rentalAsInquilino = RentalProcess::where('tenant_client_id', $client->id)
                ->whereNotIn('status', ['cancelled'])
                ->with(['avales', 'pagares'])
                ->latest()
                ->first();
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

        // Calcular completitud por sección
        $sections = $this->calcSections($client, $isArrendador, $isArrendatario, $isComprador, $isVendedor, $aval, $rentalAsInquilino, $documents);

        return view('portal.expediente', compact(
            'client', 'sections',
            'isArrendador', 'isArrendatario', 'isComprador', 'isVendedor',
            'rentalAsInquilino', 'rentalAsOwner',
            'documents', 'aval',
        ));
    }

    /** Guardar datos personales / legales */
    public function saveDatos(Request $request)
    {
        $user   = Auth::user();
        $client = $this->portalService->getClientForUser($user);
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
            'id_expiry'         => 'nullable|date',
            'address_street'       => 'nullable|string|max:200',
            'address_colony'       => 'nullable|string|max:100',
            'address_municipality' => 'nullable|string|max:100',
            'address_state'        => 'nullable|string|max:60',
            'address_zip'          => 'nullable|string|max:5',
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

        return back()->with('success', 'Datos personales actualizados correctamente.');
    }

    /** Guardar ingresos (arrendatario) */
    public function saveIngresos(Request $request)
    {
        $user   = Auth::user();
        $client = $this->portalService->getClientForUser($user);
        if (!$client) abort(403);

        $validated = $request->validate([
            'income_type'   => 'nullable|in:empleado,independiente,empresario,otro',
            'income_amount' => 'nullable|numeric|min:0',
        ]);

        $client->update($validated);
        return back()->with('success', 'Información de ingresos guardada.');
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

        return back()->with('success', 'Datos del aval guardados correctamente.');
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
        $path  = $file->store('expediente/client-' . $client->id, 'public');

        $document = Document::create([
            'client_id'         => $client->id,
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
    private function calcSections($client, $isArrendador, $isArrendatario, $isComprador, $isVendedor, $aval, $rental, $documents): array
    {
        $sections = [];

        // Datos personales (todos)
        $personalFields = ['first_name','last_name_paterno','last_name_materno','birth_date','birth_state','gender','nationality','marital_status','curp','rfc'];
        $personalFilled = collect($personalFields)->filter(fn($f) => !empty($client->$f))->count();
        $sections['datos'] = ['filled' => $personalFilled, 'total' => count($personalFields), 'pct' => round($personalFilled / count($personalFields) * 100)];

        // Identificación (todos)
        $idFields = ['id_type','id_number','address_street','address_colony','address_municipality','address_state','address_zip'];
        $idFilled = collect($idFields)->filter(fn($f) => !empty($client->$f))->count();
        $sections['identificacion'] = ['filled' => $idFilled, 'total' => count($idFields), 'pct' => round($idFilled / count($idFields) * 100)];

        // Ingresos (arrendatario)
        if ($isArrendatario) {
            $incomeFields = ['income_type','income_amount'];
            $incomeFilled = collect($incomeFields)->filter(fn($f) => !empty($client->$f))->count();
            // + comprobante documento
            $hasIncomeDoc = $documents->has('proof_of_income') || $documents->has('nomina') || $documents->has('estado_cuenta') || $documents->has('cfdi_honorarios');
            $incomeFilled += $hasIncomeDoc ? 1 : 0;
            $sections['ingresos'] = ['filled' => $incomeFilled, 'total' => 3, 'pct' => round($incomeFilled / 3 * 100)];
        }

        // Garantía (arrendatario)
        if ($isArrendatario) {
            $guaranteeType = $rental?->guarantee_type;
            $hasAval = $guaranteeType && in_array($guaranteeType, ['aval','aval_pagares']);
            $hasPagares = $guaranteeType && in_array($guaranteeType, ['pagares','aval_pagares']);
            $hasPoliza = $guaranteeType === 'poliza_juridica';

            $gFilled = 0; $gTotal = 1;
            if ($hasAval && $aval) {
                $avalFields = ['name','curp','phone','property_address','property_state'];
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

        // Documentos del inmueble (arrendador / vendedor)
        if ($isArrendador || $isVendedor) {
            $docKeys = ['escritura','predial','agua','libertad_gravamen'];
            $docFilled = collect($docKeys)->filter(fn($k) => $documents->has($k))->count();
            $sections['documentos_inmueble'] = ['filled' => $docFilled, 'total' => count($docKeys), 'pct' => round($docFilled / count($docKeys) * 100)];
        }

        return $sections;
    }
}
