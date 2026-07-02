<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use App\Models\FormSubmission;
use App\Models\NewsletterSubscriber;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Reporte de atribución de origen: de qué página (blog, testimonios, landing,
 * etc.) vinieron los leads/conversiones — ver App\Support\Attribution.
 */
class AttributionController extends Controller
{
    public function index(Request $request)
    {
        $rangeDays = in_array((int) $request->input('range', 30), [7, 30, 90]) ? (int) $request->input('range', 30) : 30;
        $since = now()->subDays($rangeDays)->startOfDay();

        $conversions = $this->conversionsInRange($since);

        $byLabel = $conversions
            ->groupBy(fn ($c) => $c['landing_label'] ?: 'Sin atribuir')
            ->map(fn ($group) => [
                'label' => $group->first()['landing_label'] ?: 'Sin atribuir',
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values();

        $bySource = $conversions
            ->groupBy(fn ($c) => $this->trafficSource($c))
            ->map(fn ($group, $key) => ['source' => $key, 'count' => $group->count()])
            ->sortByDesc('count')
            ->values();

        // Blog específicamente: qué artículos convierten, no solo cuáles tienen más vistas.
        $blogConversions = $conversions->filter(fn ($c) => $c['landing_post_id']);
        $postIds = $blogConversions->pluck('landing_post_id')->unique();
        $posts = Post::whereIn('id', $postIds)->get(['id', 'title', 'views_count'])->keyBy('id');

        $byPost = $blogConversions
            ->groupBy('landing_post_id')
            ->map(function ($group, $postId) use ($posts) {
                $post = $posts->get($postId);
                return [
                    'title'       => $post?->title ?? 'Artículo eliminado',
                    'views_count' => $post?->views_count ?? 0,
                    'conversions' => $group->count(),
                ];
            })
            ->sortByDesc('conversions')
            ->values();

        $totalConversions = $conversions->count();
        $attributedConversions = $conversions->where('landing_label', '!=', null)->count();

        return view('admin.attribution.index', compact(
            'rangeDays', 'totalConversions', 'attributedConversions', 'byLabel', 'bySource', 'byPost'
        ));
    }

    /** Une los 3 modelos de conversión en una sola colección homogénea para el rango dado. */
    private function conversionsInRange($since): Collection
    {
        $contact = ContactSubmission::where('created_at', '>=', $since)
            ->get(['landing_label', 'landing_post_id', 'utm_source', 'created_at']);

        $forms = FormSubmission::where('created_at', '>=', $since)
            ->get(['landing_label', 'landing_post_id', 'utm_source', 'referrer', 'created_at']);

        $newsletter = NewsletterSubscriber::where('created_at', '>=', $since)
            ->get(['landing_label', 'landing_post_id', 'utm_source', 'referrer', 'created_at']);

        return $contact->concat($forms)->concat($newsletter)->map(fn ($c) => [
            'landing_label'   => $c->landing_label,
            'landing_post_id' => $c->landing_post_id,
            'utm_source'      => $c->utm_source,
            'referrer'        => $c->referrer ?? null,
        ]);
    }

    /** Fuente de tráfico aproximada cuando no hay UTM: dominio del referrer, o "Directo". */
    private function trafficSource(array $conversion): string
    {
        if ($conversion['utm_source']) {
            return ucfirst($conversion['utm_source']);
        }

        if (!empty($conversion['referrer'])) {
            $host = parse_url($conversion['referrer'], PHP_URL_HOST);
            if ($host && !str_contains($host, 'homedelvalle.mx')) {
                return ucfirst(preg_replace('/^www\./', '', explode('.', $host)[0] ?? $host));
            }
        }

        return 'Directo';
    }
}
