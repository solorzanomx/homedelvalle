<?php

namespace App\Http\Controllers;

use App\Models\CatastroPredio;
use App\Models\ZonificacionPredio;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Buscador independiente sobre las bases públicas de SEDUVI (zonificación) y
 * catastro de Benito Juárez — sin relación con Property. Sirve hoy para
 * armar listas de predios candidatos a campaña de correo (ej. H4 + más de
 * 300m²); más adelante puede alimentar altas de propiedad y valuaciones.
 */
class CatastroBjController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'zonificacion');

        $zonificacion = null;
        $catastro = null;

        if ($tab === 'catastro') {
            $query = CatastroPredio::query();

            if ($colonia = $request->get('colonia')) {
                $query->where('colonia', 'like', "%{$colonia}%");
            }
            if ($calle = $request->get('calle')) {
                $query->where('calle', 'like', "%{$calle}%");
            }
            if ($supMin = $request->get('sup_terreno_min')) {
                $query->where('sup_terreno', '>=', (float) $supMin);
            }

            $catastro = $query->orderByDesc('sup_terreno')->paginate(30)->withQueryString();
        } else {
            $query = ZonificacionPredio::query();

            if ($colonia = $request->get('colonia')) {
                $query->where('colonia', 'like', "%{$colonia}%");
            }
            if ($calle = $request->get('calle')) {
                $query->where('calle', 'like', "%{$calle}%");
            }
            if ($niveles = $request->get('niveles')) {
                $query->where('niveles', $niveles);
            }
            if ($supMin = $request->get('superficie_min')) {
                $query->where('superficie', '>=', (float) $supMin);
            }

            $zonificacion = $query->orderByDesc('superficie')->paginate(30)->withQueryString();
        }

        $colonias = ZonificacionPredio::whereNotNull('colonia')->distinct()->orderBy('colonia')->pluck('colonia');
        $nivelesDisponibles = ZonificacionPredio::whereNotNull('niveles')->distinct()->orderBy('niveles')->pluck('niveles')
            ->filter(fn ($n) => is_numeric($n))->sortBy(fn ($n) => (int) $n)->values();

        $stats = [
            'total_zonificacion' => ZonificacionPredio::count(),
            'total_catastro'     => CatastroPredio::count(),
        ];

        return view('catastro-bj.index', compact('tab', 'zonificacion', 'catastro', 'colonias', 'nivelesDisponibles', 'stats'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = ZonificacionPredio::query();

        if ($colonia = $request->get('colonia')) {
            $query->where('colonia', 'like', "%{$colonia}%");
        }
        if ($calle = $request->get('calle')) {
            $query->where('calle', 'like', "%{$calle}%");
        }
        if ($niveles = $request->get('niveles')) {
            $query->where('niveles', $niveles);
        }
        if ($supMin = $request->get('superficie_min')) {
            $query->where('superficie', '>=', (float) $supMin);
        }

        $filename = 'predios-candidatos-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Calle', 'Numero', 'Colonia', 'Codigo Postal', 'Superficie m2', 'Niveles Permitidos', 'Uso de Suelo', 'Latitud', 'Longitud']);

            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [$r->calle, $r->no_externo, $r->colonia, $r->codigo_postal, $r->superficie, $r->niveles, $r->uso_descri, $r->latitud, $r->longitud]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
