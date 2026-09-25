<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Support\DocumentReviewInbox;
use Illuminate\Http\Request;

class DocumentReviewController extends Controller
{
    public function index(Request $request)
    {
        $scope = $request->query('scope', 'all');
        $q = trim((string) $request->query('q', ''));

        $docs = DocumentReviewInbox::query()
            ->with(['client', 'uploader', 'rentalProcess', 'operation', 'captacion'])
            ->when($q !== '', fn($query) => $query->whereHas('client', fn($c) => $c->where('name', 'like', "%{$q}%")))
            ->orderBy('created_at') // los más viejos primero: son los que llevan más tiempo esperando
            ->limit(300)
            ->get()
            ->each(function (Document $d) {
                // Los de captación viven en captacion_status; para la fila/visor se comportan como "recibido".
                if ($d->status !== 'received') {
                    $d->status = 'received';
                }
            });

        $items = $docs->map(fn(Document $d) => [
            'doc' => $d,
            'ctx' => DocumentReviewInbox::context($d),
            'late' => DocumentReviewInbox::isLate($d),
        ]);

        $counts = [
            'all' => $items->count(),
            'late' => $items->where('late', true)->count(),
            'renta' => $items->filter(fn($i) => $i['ctx']['kind'] === 'renta')->count(),
            'venta' => $items->filter(fn($i) => in_array($i['ctx']['kind'], ['venta', 'expediente'], true))->count(),
            'captacion' => $items->filter(fn($i) => $i['ctx']['kind'] === 'captacion')->count(),
        ];

        $items = match ($scope) {
            'late' => $items->where('late', true),
            'renta' => $items->filter(fn($i) => $i['ctx']['kind'] === 'renta'),
            'venta' => $items->filter(fn($i) => in_array($i['ctx']['kind'], ['venta', 'expediente'], true)),
            'captacion' => $items->filter(fn($i) => $i['ctx']['kind'] === 'captacion'),
            default => $items,
        };

        // Agrupado por cliente: un solo vistazo por persona.
        $groups = $items->groupBy(fn($i) => $i['doc']->client_id ?? 0);

        return view('documents.inbox', compact('groups', 'counts', 'scope', 'q'));
    }
}
