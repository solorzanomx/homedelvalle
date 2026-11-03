<?php

namespace App\Services;

use App\Models\DocumentClause;
use App\Models\RentalProcess;
use App\Support\NumeroALetras;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

/**
 * Pagarés (renta) — serie de documentos, uno por cada mes de la renta, que el inquilino (y su
 * co-arrendatario si lo hay) entrega al propietario como garantía cuando `guarantee_type` incluye
 * pagarés ('pagares' o 'aval_pagares'). Caso real que lo originó: serie "PAGARÉS INDIANA 2026-2027"
 * (12 pagarés, Calle Indiana 53-301-A, Colonia Nápoles — Daphne Presenda Barrios y Raúl Alejandro
 * Flores Luna como suscriptores mancomunados, a favor del propietario Marcelino Rodríguez León).
 *
 * Un pagaré por mes, vencimiento el `payment_day` del trato (o el día del mes de
 * `lease_start_date` si no está capturado) + 5 días naturales de prórroga (ver PRORROGA_DIAS —
 * instrucción de Alejandro, 2026-11-02: si la renta se paga el día 1, el pagaré vence el día 6,
 * no el día 1), por el monto de `monthly_rent`. Se generan TODOS los de la serie de una vez, en
 * un solo PDF a tamaño media carta (uno por página) listo para imprimir y firmar — así se hizo
 * siempre en papel, no se generan de uno en uno.
 */
class PagaresGeneratorService
{
    const DEFAULT_CLAUSES = [
        'serie' => 'EL PRESENTE PAGARÉ FORMA PARTE DE UNA SERIE DE {{total_letras}} ({{total_numero}}) PAGARÉS.',
        'aceleracion' => 'LAS PARTES ACUERDAN QUE LA FALTA EN EL CUMPLIMIENTO DEL PRESENTE PAGARÉ DARÁ LUGAR AL COBRO ANTICIPADO DEL RESTO DE LOS PAGARÉS QUE FORMAN PARTE DE LA SERIE. DEVENGARÁ ADEMÁS INTERESES MORATORIOS MENSUALES, A PARTIR DE LA FECHA DE SU VENCIMIENTO Y HASTA SU TOTAL LIQUIDACIÓN, A RAZÓN DEL 5% (CINCO POR CIENTO) SOBRE LA CANTIDAD ADEUDADA, COMPUTADOS MENSUALMENTE HASTA EL DÍA EN QUE SE REALICE EL PAGO TOTAL DE LA CANTIDAD ADEUDADA, JUNTO CON LOS INTERESES MORATORIOS.',
        'jurisdiccion' => 'PARA LA INTERPRETACIÓN Y CUMPLIMIENTO DEL PRESENTE PAGARÉ, LAS PARTES ACUERDAN EXPRESAMENTE EN SOMETERSE A LA JURISDICCIÓN Y COMPETENCIA DE LOS TRIBUNALES DEL FUERO COMÚN DE LA CIUDAD DE MÉXICO, RENUNCIANDO EXPRESAMENTE A CUALQUIER OTRA QUE LES PUDIERA CORRESPONDER.',
        'protesto' => 'EL PRESENTE PAGARÉ NO REQUERIRÁ DE PROTESTO.',
    ];

    const CLAUSE_LABELS = [
        'serie' => 'Serie de pagarés',
        'aceleracion' => 'Aceleración e intereses moratorios',
        'jurisdiccion' => 'Jurisdicción',
        'protesto' => 'Sin protesto',
    ];

    /**
     * Prórroga de 5 días naturales sobre el día de pago pactado (instrucción de Alejandro,
     * 2026-11-02): si la renta se paga el día 1, el pagaré vence el día 6, no el día 1.
     */
    const PRORROGA_DIAS = 5;

    public static function clause(string $clauseKey, array $tokens = []): string
    {
        return DocumentClause::text('pagares', $clauseKey, self::DEFAULT_CLAUSES[$clauseKey], $tokens);
    }

    /** Números del 1 al 12 en mayúsculas, para "SERIE DE DOCE PAGARÉS" — no hace falta NumeroALetras (es cardinal corto, no monto). */
    const NUMEROS_LETRAS = [
        1 => 'UN', 2 => 'DOS', 3 => 'TRES', 4 => 'CUATRO', 5 => 'CINCO', 6 => 'SEIS',
        7 => 'SIETE', 8 => 'OCHO', 9 => 'NUEVE', 10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE',
        13 => 'TRECE', 14 => 'CATORCE', 15 => 'QUINCE', 16 => 'DIECISÉIS', 17 => 'DIECISIETE',
        18 => 'DIECIOCHO', 19 => 'DIECINUEVE', 20 => 'VEINTE', 21 => 'VEINTIUNO', 22 => 'VEINTIDÓS',
        23 => 'VEINTITRÉS', 24 => 'VEINTICUATRO',
    ];

    /**
     * Arma los datos de cada pagaré de la serie (uno por mes de la renta). No persiste nada —
     * lectura pura a partir del trato.
     *
     * @return array{total:int, monto:float, montoLetras:string, fechaFirma:string, acreedorNombre:string,
     *     acreedorDomicilio:string, deudorNombre:string, deudorDomicilio:string, deudorEsPlural:bool,
     *     inmuebleDireccion:string, firmantes:array<int,array{nombre:string}>, pagares:array<int,array{numero:int,fechaVencimiento:string}>}
     */
    public function buildSeries(RentalProcess $rental): array
    {
        $rental->loadMissing(['property', 'ownerClient', 'tenantClient', 'coTenant']);

        $total = (int) ($rental->lease_duration_months ?: 12);
        $monto = (float) ($rental->monthly_rent ?? 0);

        $start = $rental->lease_start_date ? Carbon::parse($rental->lease_start_date) : now();
        $day = $rental->payment_day ?: $start->day;

        $pagares = [];
        for ($i = 1; $i <= $total; $i++) {
            $due = $start->copy()->startOfMonth()->addMonthsNoOverflow($i - 1);
            $due->day(min($day, $due->daysInMonth));
            // addDays(), no se fija el día directo — así corre bien al mes siguiente si el día
            // de pago + la prórroga ya no cabe en el mes (ej. día 28 + 5 → 2 del mes siguiente).
            $due->addDays(self::PRORROGA_DIAS);
            $pagares[] = [
                'numero' => $i,
                'fechaVencimiento' => $due->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            ];
        }

        ['buyerName' => $acreedorNombre] = PurchaseOfferGeneratorService::buyerInfo($rental->ownerClient);
        ['buyerName' => $deudorTitular] = PurchaseOfferGeneratorService::buyerInfo($rental->tenantClient);
        ['propertyFull' => $inmuebleDireccion] = PurchaseOfferGeneratorService::propertyInfo($rental->property);

        $firmantes = [['nombre' => $deudorTitular ?: '—']];
        if ($rental->coTenant?->name) {
            $firmantes[] = ['nombre' => $rental->coTenant->name];
        }
        $esPlural = count($firmantes) > 1;
        $deudorNombre = collect($firmantes)->pluck('nombre')->implode(' y ');

        return [
            'total' => $total,
            'totalLetras' => self::NUMEROS_LETRAS[$total] ?? (string) $total,
            'monto' => $monto,
            'montoNumero' => '$' . number_format($monto, 2),
            'montoLetras' => NumeroALetras::pesosMonedaNacional($monto),
            'fechaFirma' => $start->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            'acreedorNombre' => $acreedorNombre ?: '—',
            'acreedorDomicilio' => $rental->ownerClient?->legal_address ?: '—',
            'deudorNombre' => $deudorNombre ?: '—',
            'deudorEsPlural' => $esPlural,
            'inmuebleDireccion' => $inmuebleDireccion ?: ($rental->property?->title ?? '—'),
            'firmantes' => $firmantes,
            // Aval (2026-11-03) — texto libre, rental_processes.aval_nombre, distinto del
            // obligado solidario (ese es solo para la ruta de póliza). Solo se muestra la línea
            // de firma si hay nombre capturado.
            'avalNombre' => $rental->aval_nombre ?: null,
            'pagares' => $pagares,
        ];
    }

    public function renderHtml(RentalProcess $rental): string
    {
        $data = $this->buildSeries($rental);

        $tokens = [
            'total_numero' => (string) $data['total'],
            'total_letras' => $data['totalLetras'],
        ];

        $clauses = collect(['serie', 'aceleracion', 'jurisdiccion', 'protesto'])->map(fn ($key) => [
            'key' => $key,
            'body' => self::clause($key, $tokens),
        ])->values();

        $folio = 'PAG-' . str_pad((string) $rental->id, 5, '0', STR_PAD_LEFT);

        return view('pdf.pagares', $data + compact('rental', 'clauses', 'folio'))->render();
    }

    public function generatePdf(RentalProcess $rental): string
    {
        set_time_limit(180);

        $html = $this->renderHtml($rental);

        $dir = storage_path('app/pagares/' . $rental->id);
        File::ensureDirectoryExists($dir);
        $path = $dir . '/pagares-' . time() . '.pdf';

        // Media carta: 215.9 x 139.7 mm (una carta cortada a la mitad) — un pagaré por página.
        Browsershot::html($html)
            ->setNodeBinary(config('browsershot.node_path', '/usr/bin/node'))
            ->setChromePath(config('browsershot.chrome_path', '/usr/bin/google-chrome'))
            ->noSandbox()
            ->addChromiumArguments(['--disable-gpu', '--disable-dev-shm-usage', '--disable-extensions'])
            ->paperSize(215.9, 139.7)
            ->landscape(false)
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->emulateMedia('screen')
            ->timeout(150)
            ->savePdf($path);

        return $path;
    }
}
