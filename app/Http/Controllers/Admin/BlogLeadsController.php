<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogNotFoundHit;
use App\Models\BlogRedirect;
use App\Models\FormSubmission;
use App\Models\Post;
use App\Support\BlogCluster;
use Illuminate\Http\Request;

/**
 * Fase 7 del prompt de leads del blog: panel "Blog → Leads" — leads por post/cluster/cta_variant,
 * conversión por post (si hay vistas), hits de redirects y los 404 más frecuentes. Complementa a
 * /admin/atribucion (que es general, para todo el sitio) con el detalle específico del blog.
 */
class BlogLeadsController extends Controller
{
    public function index(Request $request)
    {
        $rangeDays = in_array((int) $request->input('range', 30), [7, 30, 90], true) ? (int) $request->input('range', 30) : 30;
        $since = now()->subDays($rangeDays)->startOfDay();

        $leads = FormSubmission::whereNotNull('landing_post_id')
            ->where('created_at', '>=', $since)
            ->get(['id', 'landing_post_id', 'payload', 'created_at']);

        $posts = Post::whereIn('id', $leads->pluck('landing_post_id')->unique())
            ->get(['id', 'title', 'slug', 'views_count', 'category_id', 'cluster'])
            ->keyBy('id');

        $enriched = $leads->map(function ($lead) use ($posts) {
            $post = $posts->get($lead->landing_post_id);
            $payload = $lead->payload ?? [];

            return [
                'post' => $post,
                'cluster' => $payload['cluster'] ?? BlogCluster::forPost($post),
                'cta_variant' => $payload['cta_variant'] ?? null,
            ];
        });

        $byPost = $enriched
            ->filter(fn($l) => $l['post'])
            ->groupBy(fn($l) => $l['post']->id)
            ->map(function ($group) {
                $post = $group->first()['post'];
                $conversions = $group->count();

                return [
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'views_count' => $post->views_count,
                    'conversions' => $conversions,
                    // Conversión aproximada: leads del rango sobre vistas TOTALES (histórico) del post —
                    // no es una tasa periodo-a-periodo estrictamente comparable, misma advertencia que
                    // ya usa /admin/atribucion con este mismo dato.
                    'rate' => $post->views_count > 0 ? round($conversions / $post->views_count * 100, 1) : null,
                ];
            })
            ->sortByDesc('conversions')
            ->values();

        $byCluster = $enriched
            ->filter(fn($l) => $l['cluster'])
            ->groupBy('cluster')
            ->map(fn($group, $cluster) => ['cluster' => $cluster, 'label' => BlogCluster::LABELS[$cluster] ?? $cluster, 'count' => $group->count()])
            ->sortByDesc('count')
            ->values();

        $byCtaVariant = $enriched
            ->filter(fn($l) => $l['cta_variant'])
            ->groupBy('cta_variant')
            ->map(fn($group, $variant) => ['variant' => $variant, 'count' => $group->count()])
            ->sortByDesc('count')
            ->values();

        $redirectHits = BlogRedirect::orderByDesc('hits')->take(20)->get();
        $notFoundHits = BlogNotFoundHit::orderByDesc('hits')->take(20)->get();

        return view('admin.blog-leads.index', compact(
            'rangeDays', 'byPost', 'byCluster', 'byCtaVariant', 'redirectHits', 'notFoundHits'
        ) + ['totalLeads' => $leads->count()]);
    }
}
