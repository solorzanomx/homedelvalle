<?php

namespace App\Services;

use App\Models\Client;
use App\Models\DocumentClause;
use App\Models\Operation;
use App\Models\Property;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

/**
 * Acuerdo de Representación (Renta) — se firma en la etapa 'exclusiva' del
 * pipeline de Colocación (Operation type='renta'). Alcance limitado a
 * promoción y colocación del arrendatario; la administración continua de la
 * renta, si el propietario la contrata, es un adéndum aparte (ver
 * ContratoExclusivaGeneratorService para el equivalente de venta — mismo
 * patrón, distinto objeto legal).
 */
class AcuerdoRepresentacionRentaGeneratorService
{
    /**
     * Texto por defecto de las cláusulas legales — editable desde
     * /admin/documentos/contrato-exclusiva-renta/clausulas (App\Models\DocumentClause).
     * 'declaracion_propiedad' no se numera como cláusula: se muestra en su
     * propio bloque de Declaraciones, antes de las cláusulas contractuales.
     */
    const DEFAULT_CLAUSES = [
        'declaracion_propiedad' => 'El propietario declara, bajo protesta de decir verdad, ser legítimo propietario del inmueble ubicado en {{property_address}}, según consta en la escritura pública número {{escritura_numero}}, de fecha {{escritura_fecha}}, otorgada ante la fe del Notario Público número {{notario_numero}} de {{notario_plaza}}, licenciado(a) {{notario_nombre}}, inscrita en el Registro Público de la Propiedad y de Comercio de la Ciudad de México bajo el Folio Real Electrónico número {{folio_real}}; que dicho inmueble se encuentra libre de arrendamiento vigente con terceros y libre de todo litigio, embargo o limitación de dominio que impida su arrendamiento; y que cuenta con facultades suficientes para celebrar el presente Acuerdo y, en su momento, el contrato de arrendamiento respectivo.',
        'objeto' => '<strong>Objeto y representación.</strong> El propietario designa a Home del Valle Bienes Raíces como su representante para la promoción y colocación en arrendamiento del inmueble descrito en este documento. Durante la vigencia de este Acuerdo, el propietario se compromete a no promoverlo en renta por ningún otro medio ni con otro intermediario, lo que nos permite invertir en su promoción con la certeza de representarlo activamente hasta encontrar al arrendatario adecuado. El presente Acuerdo comprende exclusivamente la promoción y colocación del inmueble; cualquier servicio de administración continua de la renta (cobro mensual, seguimiento de pagos, renovaciones) será materia de un adéndum aparte.',
        'vigencia' => '<strong>Vigencia.</strong> El presente Acuerdo tiene una vigencia de {{vigencia_dias}} días naturales contados a partir de la fecha de firma, es decir, hasta el {{vigencia_hasta}}, pudiendo renovarse por acuerdo expreso entre las partes.',
        'comision' => '<strong>Comisión.</strong> Home del Valle Bienes Raíces percibirá como comisión por colocación el equivalente a un mes de renta ({{renta_mensual}}), calculado sobre la renta mensual pactada con el arrendatario, pagadera al momento de la firma del contrato de arrendamiento. Esta comisión se causará también si, dentro de los {{cola_dias}} días naturales posteriores a la terminación del presente Acuerdo, se renta el inmueble a un candidato presentado por Home del Valle durante la vigencia de este Acuerdo.',
        'obligaciones_hdv' => '<strong>Obligaciones de Home del Valle.</strong> Home del Valle se compromete a realizar la promoción activa del inmueble en renta, incluyendo su publicación en portales inmobiliarios y redes sociales, la gestión de visitas con candidatos interesados, la investigación de solvencia de los candidatos propuestos, y la entrega de reportes periódicos de actividad al propietario.',
        'obligaciones_propietario' => '<strong>Obligaciones del propietario.</strong> El propietario se compromete a proporcionar acceso al inmueble para su promoción y visitas, mantenerlo en condiciones adecuadas para su exhibición y con los servicios necesarios vigentes, entregar la documentación necesaria para la operación, e informar con veracidad cualquier dato relevante sobre la situación legal o física del inmueble.',
        'manifestaciones_garantias' => '<strong>Manifestaciones, garantías y responsabilidad.</strong> El propietario garantiza la veracidad de todo lo declarado en este Acuerdo, incluyendo su Declaración de Propiedad. En caso de que alguna declaración resulte falsa o inexacta, el propietario será responsable de los daños y perjuicios que se causen a Home del Valle o a terceros derivados de dicha falsedad, y Home del Valle quedará liberada de responsabilidad frente a terceros por haber actuado de buena fe con base en la documentación e identidad proporcionadas por el propietario. La falsedad de la Declaración de Propiedad será causal de terminación inmediata del presente Acuerdo, sin responsabilidad para Home del Valle.',
        'privacidad' => '<strong>Aviso de Privacidad.</strong> Los datos personales y documentos proporcionados en este documento, incluyendo identificación oficial, CURP, RFC y datos registrales del inmueble, serán tratados por Home del Valle Bienes Raíces conforme a lo dispuesto por la Ley Federal de Protección de Datos Personales en Posesión de los Particulares, únicamente para acreditar la propiedad y facultad para arrendar, y para los fines relacionados con la comercialización del inmueble. El Aviso de Privacidad completo está disponible en el sitio web de Home del Valle.',
    ];

    const CLAUSE_LABELS = [
        'declaracion_propiedad' => 'Declaración de Propiedad',
        'objeto' => 'Objeto y representación',
        'vigencia' => 'Vigencia',
        'comision' => 'Comisión',
        'obligaciones_hdv' => 'Obligaciones de Home del Valle',
        'obligaciones_propietario' => 'Obligaciones del propietario',
        'manifestaciones_garantias' => 'Manifestaciones, garantías y responsabilidad',
        'privacidad' => 'Aviso de Privacidad',
    ];

    /** Cláusulas numeradas del cuerpo del contrato — declaracion_propiedad vive aparte, en el bloque de Declaraciones. */
    const NUMBERED_CLAUSES = ['objeto', 'vigencia', 'comision', 'obligaciones_hdv', 'obligaciones_propietario', 'manifestaciones_garantias', 'privacidad'];

    /** Campos de Property que deben estar capturados antes de poder generar el Acuerdo — es la garantía real de que quien firma es el dueño. */
    const REQUIRED_PROPERTY_FIELDS = ['folio_real', 'escritura_numero', 'escritura_fecha', 'notario_nombre', 'notario_numero'];

    public static function clause(string $clauseKey, array $tokens = []): string
    {
        return DocumentClause::text('contrato_exclusiva_renta', $clauseKey, self::DEFAULT_CLAUSES[$clauseKey], $tokens);
    }

    /** @return string[] Etiquetas de los campos de escritura faltantes en $property, vacío si está completa. */
    public static function missingOwnershipFields(?Property $property): array
    {
        $labels = [
            'folio_real' => 'Folio Real',
            'escritura_numero' => 'Número de escritura',
            'escritura_fecha' => 'Fecha de escritura',
            'notario_nombre' => 'Nombre del notario',
            'notario_numero' => 'Número de notaría',
        ];

        return collect(self::REQUIRED_PROPERTY_FIELDS)
            ->filter(fn ($field) => empty($property?->{$field}))
            ->map(fn ($field) => $labels[$field])
            ->values()
            ->all();
    }

    private static function tituloCase(?string $s): ?string
    {
        return $s ? mb_convert_case(mb_strtolower($s, 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : $s;
    }

    /** Datos del propietario (nombre, identificación, CURP/RFC, domicilio, estado civil), en formato título. */
    private static function ownerInfo(?Client $client): array
    {
        $ownerName = self::tituloCase($client?->name) ?: self::tituloCase(trim(implode(' ', array_filter([
            $client?->first_name,
            $client?->last_name_paterno,
            $client?->last_name_materno,
        ])))) ?: '—';

        $ownerId = $client?->id_type && $client?->id_number
            ? "{$client->id_type} {$client->id_number}"
            : null;

        $ownerCurpRfc = collect([
            $client?->curp ? "CURP: {$client->curp}" : null,
            $client?->rfc ? "RFC: {$client->rfc}" : null,
        ])->filter()->implode(' · ') ?: null;

        $ownerAddress = collect([
            $client?->address_street,
            $client?->address_colony,
            $client?->address_municipality,
            $client?->address_state,
            $client?->address_zip,
        ])->filter()->implode(', ') ?: null;

        // Sociedad conyugal implica copropiedad del cónyuge aunque la escritura
        // solo tenga un nombre — se muestra explícito para que el propietario
        // confirme si necesita el consentimiento/firma del cónyuge.
        $ownerMarital = null;
        if ($client?->marital_status === 'casado') {
            $regimen = $client->marital_regime === 'sociedad_conyugal' ? 'Sociedad Conyugal' : ($client->marital_regime === 'separacion_bienes' ? 'Separación de Bienes' : null);
            $ownerMarital = collect([
                'Casado(a)' . ($regimen ? " — {$regimen}" : ''),
                $client->spouse_name ? "Cónyuge: " . self::tituloCase($client->spouse_name) : null,
            ])->filter()->implode(' · ');
        }

        return compact('ownerName', 'ownerId', 'ownerCurpRfc', 'ownerAddress', 'ownerMarital');
    }

    /** Dirección + colonia + datos de escritura del inmueble, en formato título. */
    private static function propertyInfo(?Property $property): array
    {
        $propertyAddress = self::tituloCase($property?->address ?: ($property ? ($property->colony . ', ' . $property->city) : null));
        $propertyColony  = self::tituloCase($property?->colony);

        $propertyColonyLabel = $propertyColony && !str_contains(mb_strtolower($propertyColony), 'colonia')
            ? "Colonia {$propertyColony}"
            : $propertyColony;

        $propertyFull = collect([
            $propertyAddress,
            $propertyColonyLabel,
        ])->filter()->implode(', ') ?: null;

        $escrituraFecha = $property?->escritura_fecha
            ? \Illuminate\Support\Carbon::parse($property->escritura_fecha)->locale('es')->isoFormat('D [de] MMMM [de] YYYY')
            : '—';

        return [
            'propertyAddress' => $propertyAddress,
            'propertyColonyLabel' => $propertyColonyLabel,
            'propertyFull' => $propertyFull,
            'folioReal' => $property?->folio_real ?: '—',
            'escrituraNumero' => $property?->escritura_numero ?: '—',
            'escrituraFecha' => $escrituraFecha,
            'notarioNombre' => $property?->notario_nombre ?: '—',
            'notarioNumero' => $property?->notario_numero ?: '—',
            'notarioPlaza' => $property?->notario_plaza ?: 'Ciudad de México',
        ];
    }

    public function renderHtml(Operation $operation, int $vigenciaDias = 90): string
    {
        $operation->loadMissing('client', 'property');
        $client   = $operation->client;
        $property = $operation->property;

        $folio = 'ARR-' . str_pad((string) $operation->id, 5, '0', STR_PAD_LEFT);
        $fecha = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $vigenciaHasta = now()->addDays($vigenciaDias)->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        $owner = self::ownerInfo($client);
        $propertyData = self::propertyInfo($property);

        $rentaMensual = $operation->monthly_rent
            ? '$' . number_format((float) $operation->monthly_rent, 2) . ' ' . ($operation->currency ?: 'MXN')
            : '—';

        $colaDias = 60;

        $declaracionPropiedad = self::clause('declaracion_propiedad', [
            'property_address' => $propertyData['propertyFull'] ?? $propertyData['propertyAddress'],
            'folio_real' => $propertyData['folioReal'],
            'escritura_numero' => $propertyData['escrituraNumero'],
            'escritura_fecha' => $propertyData['escrituraFecha'],
            'notario_numero' => $propertyData['notarioNumero'],
            'notario_plaza' => $propertyData['notarioPlaza'],
            'notario_nombre' => $propertyData['notarioNombre'],
        ]);

        return view('pdf.acuerdo-representacion-renta', array_merge(
            compact('operation', 'client', 'property', 'folio', 'fecha', 'vigenciaDias', 'vigenciaHasta', 'rentaMensual', 'colaDias', 'declaracionPropiedad'),
            [
                'ownerName' => $owner['ownerName'], 'ownerId' => $owner['ownerId'], 'ownerCurpRfc' => $owner['ownerCurpRfc'],
                'ownerAddress' => $owner['ownerAddress'], 'ownerMarital' => $owner['ownerMarital'],
                'propertyAddress' => $propertyData['propertyAddress'], 'propertyColonyLabel' => $propertyData['propertyColonyLabel'],
                'propertyFull' => $propertyData['propertyFull'], 'folioReal' => $propertyData['folioReal'],
                'escrituraNumero' => $propertyData['escrituraNumero'], 'escrituraFecha' => $propertyData['escrituraFecha'],
                'notarioNombre' => $propertyData['notarioNombre'], 'notarioNumero' => $propertyData['notarioNumero'],
                'notarioPlaza' => $propertyData['notarioPlaza'],
            ]
        ))->render();
    }

    public function generatePdf(Operation $operation, int $vigenciaDias = 90): string
    {
        set_time_limit(120);

        $html = $this->renderHtml($operation, $vigenciaDias);

        $dir  = storage_path('app/contratos-exclusiva-renta/' . $operation->id);
        File::ensureDirectoryExists($dir);
        $path = $dir . '/acuerdo-representacion-renta-' . time() . '.pdf';

        Browsershot::html($html)
            ->setNodeBinary(config('browsershot.node_path', '/usr/bin/node'))
            ->setChromePath(config('browsershot.chrome_path', '/usr/bin/google-chrome'))
            ->noSandbox()
            ->addChromiumArguments(['--disable-gpu', '--disable-dev-shm-usage', '--disable-extensions'])
            ->windowSize(816, 1056)
            ->paperSize(215.9, 279.4)
            ->landscape(false)
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->emulateMedia('screen')
            ->timeout(90)
            ->savePdf($path);

        return $path;
    }
}
