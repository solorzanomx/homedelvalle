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
            ->with(['client', 'uploader', 'rentalProcess', 'operation', 'captacion', 'events.user'])
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

    /** Métricas de calidad y revisión de los últimos N días (para afinar guías y umbrales). */
    public function metrics(Request $request)
    {
        $days = in_array((int) $request->query('days'), [7, 30, 90], true) ? (int) $request->query('days') : 30;
        $since = now()->subDays($days);

        $events = \App\Models\DocumentEvent::with('document:id,category,rejection_reason')
            ->where('created_at', '>=', $since)->get()->filter(fn($e) => $e->document);

        $uploads = $events->where('type', 'uploaded');
        $verified = $events->where('type', 'verified');
        $rejected = $events->where('type', 'rejected');
        $warned = $events->where('type', 'quality_warn');

        // Tiempo hasta la primera decisión, por documento subido en el periodo.
        $byDoc = \App\Models\DocumentEvent::whereIn('document_id', $uploads->pluck('document_id'))->whereIn('type', ['uploaded', 'verified', 'rejected'])
            ->orderBy('created_at')->get()->groupBy('document_id');
        $hours = $byDoc->map(function ($evs) {
            $up = $evs->firstWhere('type', 'uploaded');
            $dec = $evs->first(fn($e) => in_array($e->type, ['verified', 'rejected'], true));
            return ($up && $dec) ? $up->created_at->diffInMinutes($dec->created_at) / 60 : null;
        })->filter(fn($h) => $h !== null)->sort()->values();
        $median = $hours->isEmpty() ? null : $hours[(int) floor(($hours->count() - 1) / 2)];

        $cats = \App\Models\Document::CATEGORIES;
        $perCategory = $uploads->groupBy(fn($e) => $e->document->category)->map(function ($ups, $cat) use ($rejected, $warned, $cats) {
            $n = $ups->count();
            $rej = $rejected->filter(fn($e) => $e->document->category === $cat)->count();
            $warn = $warned->filter(fn($e) => $e->document->category === $cat)->count();
            return ['label' => $cats[$cat] ?? $cat, 'uploads' => $n, 'rejected' => $rej, 'warned' => $warn, 'rate' => $n ? round($rej / $n * 100) : 0];
        })->sortByDesc('rejected')->values();

        $reasons = $rejected->map(fn($e) => trim((string) $e->note))->filter()->countBy()->sortDesc()->take(8);

        $blocks = \DB::table('document_quality_blocks')->where('created_at', '>=', $since)->get();
        $blocksByCat = $blocks->groupBy('category')->map->count()->sortDesc()->take(8);
        $blockReasons = $blocks->groupBy(fn($b) => mb_substr($b->reason, 0, 60))->map->count()->sortDesc()->take(6);

        return view('documents.metrics', [
            'days' => $days,
            'kpis' => [
                'uploads' => $uploads->count(),
                'verified' => $verified->count(),
                'rejected' => $rejected->count(),
                'rate' => $uploads->count() ? round($rejected->count() / $uploads->count() * 100) : 0,
                'median_hours' => $median !== null ? round($median, 1) : null,
                'blocks' => $blocks->where('bypassed', false)->count(),
                'bypassed' => $blocks->where('bypassed', true)->count(),
            ],
            'perCategory' => $perCategory,
            'reasons' => $reasons,
            'blocksByCat' => $blocksByCat->mapWithKeys(fn($n, $c) => [($cats[$c] ?? ($c ?: 'Sin categoría')) => $n]),
            'blockReasons' => $blockReasons,
        ]);
    }
}
