<?php

namespace App\Services;

use App\Models\DocumentClause;
use App\Models\RentalProcess;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

/**
 * Inventario y Estado del Inmueble al momento de la entrega (renta) — "Anexo A" que ya usaban en
 * Word, copiado y editado a mano por cada propiedad (ej. "INVENTARIO MELBOURNE 1201 2026-2027").
 * Caso real que lo originó (2026-10-09): Carlos Sánchez Fernández, entrega del mismo día — el Word
 * traía pegado el nombre del inquilino anterior, sin firma de quien entrega, sin lecturas de
 * medidores, sin fotos de respaldo, y los renglones (cristales, pisos, instalaciones...) estaban
 * fijos para OTRO inmueble (Melbourne) en vez del que realmente se entregaba.
 *
 * A diferencia del Acta de Entrega (venta) o el Recibo de Pago Parcial, aquí el contenido real
 * (qué hay, en qué estado) CAMBIA por completo según el inmueble — no son cláusulas legales fijas
 * con tokens, son observaciones de un recorrido físico. Por eso el detalle del inventario es un
 * solo bloque de texto editable: se precarga con una lista genérica (vía DocumentClause, editable
 * desde /admin/documentos sin tocar código) y el asesor la ajusta a mano según lo que encuentre.
 *
 * Firman arrendador y arrendatario directamente — a diferencia del Acta de Entrega de venta, aquí
 * Home del Valle NO firma como intermediaria (decisión explícita de Alejandro 2026-10-09).
 */
class InventarioEntregaGeneratorService
{
    /** Lista genérica de partida — el asesor la edita según lo que encuentre en el recorrido. */
    const DEFAULT_ITEMS = <<<'TXT'
Cristales de todo el departamento sin fracturas y completos.
Herrajes en puertas de closets y puertas de acceso a recámaras y baños completos y funcionando.
Tubos de colgar ropa y tablas para almacenamiento en closets en buen estado y completos.
Herrajes en ventanas de todo el departamento completos y funcionando.
Muros de todo el departamento en buen estado sin rayones ni grietas.
Piso de laminado en recámaras y estancia en buen estado.
Pisos de loseta cerámica en baños y cocina en buen estado sin grietas ni fisuras.
Zoclo en todo el departamento en buen estado.
Instalación eléctrica funcionando.
Instalación hidráulica funcionando.
Mezcladoras en lavabos de baños y cocina funcionando sin goteras.
Regaderas y mezcladoras de las mismas funcionando y sin goteras.
WC funcionando sin goteras.
Muebles de baño con herrajes completos y funcionando.
Muebles de cocina integral completos con herrajes completos sin rayones.
Fregadero en buen estado sin fracturas y sin goteras.
Instalación hidráulica para lavadora en buen estado sin goteras.
Persianas instaladas en todo el departamento, funcionando.
Plafones de luz en todo el departamento con luminarias y funcionando.
TXT;

    public static function defaultItemsText(): string
    {
        return DocumentClause::text('inventario_entrega', 'items_default', self::DEFAULT_ITEMS);
    }

    /**
     * @param  string  $itemsDetalle  lista de renglones del inventario, ya editada por el asesor
     *         según el recorrido real del inmueble (una línea por renglón).
     * @param  \Illuminate\Support\Carbon|string|null  $fechaEntrega  fecha real de la entrega.
     */
    public function renderHtml(
        RentalProcess $rental,
        string $itemsDetalle,
        ?string $lecturaLuz = null,
        ?string $lecturaGas = null,
        ?string $lecturaAgua = null,
        ?string $llavesRecamaras = null,
        ?string $llavesEntrada = null,
        ?string $chipsAcceso = null,
        ?string $controlesEstacionamiento = null,
        ?string $observaciones = null,
        $fechaEntrega = null
    ): string {
        $rental->loadMissing('tenantClient', 'ownerClient', 'property');

        $folio = 'INV-' . str_pad((string) $rental->id, 5, '0', STR_PAD_LEFT);
        $fecha = ($fechaEntrega ? \Illuminate\Support\Carbon::parse($fechaEntrega) : now())->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        ['buyerName' => $arrendatario] = PurchaseOfferGeneratorService::buyerInfo($rental->tenantClient);
        ['buyerName' => $arrendador] = PurchaseOfferGeneratorService::buyerInfo($rental->ownerClient);
        ['propertyFull' => $inmueble] = PurchaseOfferGeneratorService::propertyInfo($rental->property);

        $arrendatario = $arrendatario ?: '—';
        $arrendador = $arrendador ?: '—';
        $inmueble = $inmueble ?: ($rental->property?->title ?? '—');

        $items = collect(preg_split('/\r\n|\r|\n/', trim($itemsDetalle)))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();

        return view('pdf.inventario-entrega', compact(
            'rental', 'folio', 'fecha', 'arrendatario', 'arrendador', 'inmueble', 'items',
            'lecturaLuz', 'lecturaGas', 'lecturaAgua',
            'llavesRecamaras', 'llavesEntrada', 'chipsAcceso', 'controlesEstacionamiento',
            'observaciones'
        ))->render();
    }

    public function generatePdf(
        RentalProcess $rental,
        string $itemsDetalle,
        ?string $lecturaLuz = null,
        ?string $lecturaGas = null,
        ?string $lecturaAgua = null,
        ?string $llavesRecamaras = null,
        ?string $llavesEntrada = null,
        ?string $chipsAcceso = null,
        ?string $controlesEstacionamiento = null,
        ?string $observaciones = null,
        $fechaEntrega = null
    ): string {
        set_time_limit(120);

        $html = $this->renderHtml(
            $rental, $itemsDetalle, $lecturaLuz, $lecturaGas, $lecturaAgua,
            $llavesRecamaras, $llavesEntrada, $chipsAcceso, $controlesEstacionamiento,
            $observaciones, $fechaEntrega
        );

        $dir  = storage_path('app/inventarios-entrega/' . $rental->id);
        File::ensureDirectoryExists($dir);
        $path = $dir . '/inventario-entrega-' . time() . '.pdf';

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
