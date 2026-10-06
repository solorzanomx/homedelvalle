<?php

namespace App\Services;

use App\Models\Captacion;
use App\Models\Client;
use App\Models\DocumentClause;
use App\Models\Property;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

/**
 * Acuerdo de Representación (Venta). Hasta 2026-10-06 era un documento mucho más corto que su
 * hermano de renta (AcuerdoRepresentacionRentaGeneratorService) — sin Declaración de Propiedad
 * (folio real/escritura/notario), sin la cláusula de "Precio de Referencia y Recepción de Ofertas",
 * y sin "Manifestaciones, garantías y responsabilidad". Para una venta (montos más altos, más
 * riesgo de título) esas protecciones son si acaso más necesarias que en una renta, no menos —
 * hallazgo real al comparar un Acuerdo real de renta con uno de venta recién generado. Igualado al
 * mismo nivel de protección que el de renta; mismo patrón, mismo representante legal.
 */
class ContratoExclusivaGeneratorService
{
    /** Quien firma el Acuerdo a nombre de Home del Valle — la persona con facultades para representar legalmente a la empresa, no el broker asignado al trato. Mismo valor que AcuerdoRepresentacionRentaGeneratorService. */
    const REPRESENTANTE_NOMBRE = 'Ana Laura Monsivais Flores';
    const REPRESENTANTE_CARGO = 'Directora General';

    /**
     * Texto por defecto de las cláusulas legales — editable desde
     * /admin/documentos/contrato-exclusiva/clausulas (App\Models\DocumentClause).
     * 'declaracion_propiedad' no se numera como cláusula: se muestra en su propio bloque de
     * Declaraciones, antes de las cláusulas contractuales (igual que en el Acuerdo de renta).
     */
    const DEFAULT_CLAUSES = [
        'declaracion_propiedad' => 'El propietario declara, bajo protesta de decir verdad, ser legítimo propietario del inmueble ubicado en {{property_address}}, según consta en la escritura pública número {{escritura_numero}}, de fecha {{escritura_fecha}}, otorgada ante la fe del Notario Público número {{notario_numero}} de {{notario_plaza}}, licenciado(a) {{notario_nombre}}, inscrita en el Registro Público de la Propiedad y de Comercio de la Ciudad de México bajo el Folio Real Electrónico número {{folio_real}}; que dicho inmueble se encuentra libre de todo litigio, embargo o limitación de dominio que impida su venta; y que cuenta con facultades suficientes para celebrar el presente Acuerdo y, en su momento, el contrato de compraventa respectivo.',
        'objeto' => '<strong>Objeto y representación.</strong> El propietario designa a Home del Valle Bienes Raíces como su representante para la comercialización del inmueble descrito en este documento. Durante la vigencia de este Acuerdo, el propietario se compromete a trabajar únicamente con Home del Valle para la venta del inmueble, lo que nos permite invertir en su promoción con la certeza de representarlo activamente hasta encontrar al comprador adecuado.',
        'vigencia' => '<strong>Vigencia.</strong> El presente Acuerdo tiene una vigencia de {{vigencia_dias}} días naturales contados a partir de la fecha de firma, es decir, hasta el {{vigencia_hasta}}, pudiendo renovarse por acuerdo expreso entre las partes.',
        'ofertas_referencia' => '<strong>Precio de Referencia y Recepción de Ofertas.</strong> Las partes reconocen que el precio de lista señalado en el presente Acuerdo constituye un precio de referencia para efectos de la comercialización del inmueble, y no representa una condición fija, mínima ni inamovible. En consecuencia, Home del Valle queda expresamente facultado para recibir, evaluar y presentar al propietario cualquier oferta de compra formulada por candidatos interesados, ya sea igual, superior o inferior al monto de referencia pactado. Corresponderá exclusivamente al propietario, en cada caso, la decisión de aceptar, rechazar o contraofertar dicha propuesta, sin que la sola presentación de una oferta por parte de Home del Valle genere obligación alguna de aceptarla ni responsabilidad para Home del Valle derivada de la decisión del propietario. Ninguna oferta se entenderá aceptada hasta que medie manifestación expresa de voluntad del propietario, formalizada en el contrato de compraventa respectivo.',
        'comision' => '<strong>Comisión.</strong> Home del Valle Bienes Raíces percibirá una comisión del {{comision_pct}}% sobre el valor final de la operación, pagadera al momento de la firma del contrato de compraventa o de la escrituración correspondiente. Esta comisión se causará también si, dentro de los 90 días naturales posteriores a la terminación del presente Acuerdo, se concreta la venta del inmueble con un comprador presentado por Home del Valle durante la vigencia de este Acuerdo.',
        'obligaciones_hdv' => '<strong>Obligaciones de Home del Valle.</strong> Home del Valle se compromete a realizar la promoción activa del inmueble, incluyendo su publicación en portales inmobiliarios y redes sociales, la gestión de visitas con candidatos interesados, y la entrega de reportes periódicos de actividad al propietario.',
        'obligaciones_propietario' => '<strong>Obligaciones del propietario.</strong> El propietario se compromete a proporcionar acceso al inmueble para su promoción y visitas. Para tal efecto, el propietario podrá entregar a Home del Valle un juego de llaves del inmueble, con el fin de agilizar las visitas con candidatos interesados sin necesidad de coordinar su presencia en cada ocasión; dicho juego de llaves será resguardado por Home del Valle con la debida diligencia, utilizado exclusivamente para la exhibición del inmueble en el marco de este Acuerdo, y devuelto al propietario a la terminación del presente Acuerdo o en cuanto éste lo solicite. Asimismo, el propietario se compromete a mantener el inmueble en condiciones adecuadas para su exhibición y con los servicios necesarios vigentes, entregar la documentación necesaria para la operación, e informar con veracidad cualquier dato relevante sobre la situación legal o física del inmueble.',
        'manifestaciones_garantias' => '<strong>Manifestaciones, garantías y responsabilidad.</strong> El propietario garantiza la veracidad de todo lo declarado en este Acuerdo, incluyendo su Declaración de Propiedad. En caso de que alguna declaración resulte falsa o inexacta, el propietario será responsable de los daños y perjuicios que se causen a Home del Valle o a terceros derivados de dicha falsedad, y Home del Valle quedará liberada de responsabilidad frente a terceros por haber actuado de buena fe con base en la documentación e identidad proporcionadas por el propietario. La falsedad de la Declaración de Propiedad será causal de terminación inmediata del presente Acuerdo, sin responsabilidad para Home del Valle.',
        'privacidad' => '<strong>Aviso de Privacidad.</strong> Los datos personales y documentos proporcionados en este documento, incluyendo identificación oficial, CURP, RFC y datos registrales del inmueble, serán tratados por Home del Valle Bienes Raíces conforme a lo dispuesto por la Ley Federal de Protección de Datos Personales en Posesión de los Particulares, únicamente para acreditar la propiedad y facultad para vender, y para los fines relacionados con la comercialización del inmueble. El Aviso de Privacidad completo está disponible en el sitio web de Home del Valle.',
    ];

    const CLAUSE_LABELS = [
        'declaracion_propiedad' => 'Declaración de Propiedad',
        'objeto' => 'Objeto y representación',
        'vigencia' => 'Vigencia',
        'ofertas_referencia' => 'Precio de Referencia y Recepción de Ofertas',
        'comision' => 'Comisión',
        'obligaciones_hdv' => 'Obligaciones de Home del Valle',
        'obligaciones_propietario' => 'Obligaciones del propietario',
        'manifestaciones_garantias' => 'Manifestaciones, garantías y responsabilidad',
        'privacidad' => 'Aviso de Privacidad',
    ];

    /** Cláusulas numeradas del cuerpo del contrato — declaracion_propiedad vive aparte, en el bloque de Declaraciones. */
    const NUMBERED_CLAUSES = ['objeto', 'vigencia', 'ofertas_referencia', 'comision', 'obligaciones_hdv', 'obligaciones_propietario', 'manifestaciones_garantias', 'privacidad'];

    /** Campos de Property que deben estar capturados antes de poder generar el Acuerdo — es la garantía real de que quien firma es el dueño. Mismos campos que renta (genéricos de Property, no específicos de operación). */
    const REQUIRED_PROPERTY_FIELDS = ['folio_real', 'escritura_numero', 'escritura_fecha', 'notario_nombre', 'notario_numero'];

    public static function clause(string $clauseKey, array $tokens = []): string
    {
        return DocumentClause::text('contrato_exclusiva', $clauseKey, self::DEFAULT_CLAUSES[$clauseKey], $tokens);
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

    /** Primera letra de cada palabra en mayúscula (los datos suelen venir en minúsculas del formulario de captación). */
    private static function tituloCase(?string $s): ?string
    {
        return $s ? mb_convert_case(mb_strtolower($s, 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : $s;
    }

    /** Datos del propietario (nombre, identificación, CURP/RFC, domicilio), en formato título. */
    private static function ownerInfo(?Client $client): array
    {
        // full_name (accessor de Client) prioriza los campos de Datos Legales
        // cuando están completos (first_name + last_name_paterno) y solo cae a
        // name si la ficha legal aún no se ha llenado — evita tanto firmar con
        // el nombre de trato en vez del legal, como truncar el nombre si solo
        // hay first_name capturado sin apellidos divididos.
        $ownerName = self::tituloCase($client?->full_name) ?: '—';

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

        return compact('ownerName', 'ownerId', 'ownerCurpRfc', 'ownerAddress');
    }

    /** Dirección + colonia + datos de escritura del inmueble, en formato título. */
    private static function propertyInfo(?Property $property): array
    {
        $propertyAddress = self::tituloCase($property?->address ?: ($property ? ($property->colony . ', ' . $property->city) : null));
        $propertyColony  = self::tituloCase($property?->colony);

        // Solo la colonia (sin repetir la dirección) — para el párrafo inicial.
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

    public function renderHtml(Captacion $captacion, int $vigenciaDias = 180): string
    {
        $captacion->loadMissing('client', 'property');
        $client   = $captacion->client;
        $property = $captacion->property;

        $folio = 'AR-' . str_pad((string) $captacion->id, 5, '0', STR_PAD_LEFT);
        $fecha = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $vigenciaHasta = now()->addDays($vigenciaDias)->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        $owner = self::ownerInfo($client);
        $propertyData = self::propertyInfo($property);

        $comisionPct = $captacion->commission_pct ?? 5.00;

        $precioLista = $captacion->precio_acordado
            ? '$' . number_format((float) $captacion->precio_acordado, 2) . ' MXN'
            : '—';

        $declaracionPropiedad = self::clause('declaracion_propiedad', [
            'property_address' => $propertyData['propertyFull'] ?? $propertyData['propertyAddress'],
            'folio_real' => $propertyData['folioReal'],
            'escritura_numero' => $propertyData['escrituraNumero'],
            'escritura_fecha' => $propertyData['escrituraFecha'],
            'notario_numero' => $propertyData['notarioNumero'],
            'notario_plaza' => $propertyData['notarioPlaza'],
            'notario_nombre' => $propertyData['notarioNombre'],
        ]);

        return view('pdf.contrato-exclusiva', array_merge(
            compact('captacion', 'client', 'property', 'folio', 'fecha', 'vigenciaDias', 'vigenciaHasta', 'comisionPct', 'precioLista', 'declaracionPropiedad'),
            [
                'ownerName' => $owner['ownerName'], 'ownerId' => $owner['ownerId'], 'ownerCurpRfc' => $owner['ownerCurpRfc'],
                'ownerAddress' => $owner['ownerAddress'],
                'propertyAddress' => $propertyData['propertyAddress'], 'propertyColonyLabel' => $propertyData['propertyColonyLabel'],
                'propertyFull' => $propertyData['propertyFull'], 'folioReal' => $propertyData['folioReal'],
                'escrituraNumero' => $propertyData['escrituraNumero'], 'escrituraFecha' => $propertyData['escrituraFecha'],
                'notarioNombre' => $propertyData['notarioNombre'], 'notarioNumero' => $propertyData['notarioNumero'],
                'notarioPlaza' => $propertyData['notarioPlaza'],
            ]
        ))->render();
    }

    public function generatePdf(Captacion $captacion, int $vigenciaDias = 180): string
    {
        set_time_limit(120);

        $html = $this->renderHtml($captacion, $vigenciaDias);

        $dir  = storage_path('app/contratos-exclusiva/' . $captacion->id);
        File::ensureDirectoryExists($dir);
        $path = $dir . '/contrato-exclusiva-' . time() . '.pdf';

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
