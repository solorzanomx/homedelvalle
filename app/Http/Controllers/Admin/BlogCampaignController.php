<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCampaign;
use App\Models\Post;
use Illuminate\Http\Request;

class BlogCampaignController extends Controller
{
    public function index()
    {
        $campaigns = BlogCampaign::withCount('posts')->orderByDesc('id')->get();

        return view('admin.blog-campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('admin.blog-campaigns.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:160',
            'objetivo'       => 'nullable|string|max:2000',
            'posts_per_week' => 'required|integer|min:1|max:7',
            'topic_count'    => 'required|integer|min:5|max:40',
            'mezcla'         => 'nullable|string|max:500',
            'publish_hour'   => 'nullable|date_format:H:i',
        ]);

        $campaign = BlogCampaign::create([
            'name'           => $validated['name'],
            'objetivo'       => $validated['objetivo'] ?? null,
            'posts_per_week' => $validated['posts_per_week'],
            'mezcla'         => $validated['mezcla'] ?? null,
            'publish_hour'   => $validated['publish_hour'] ?? '08:00',
            'status'         => 'draft',
            'topics'         => [],
        ]);

        return redirect()
            ->route('admin.blog-campaigns.show', $campaign)
            ->with('success', 'Campaña creada. Genera el mapa de temas para arrancar.')
            ->with('generate_map_count', $validated['topic_count']);
    }

    public function show(BlogCampaign $blogCampaign)
    {
        $posts = $blogCampaign->posts()->orderByRaw("CASE status WHEN 'draft' THEN 0 WHEN 'scheduled' THEN 1 ELSE 2 END")->orderBy('published_at')->get();

        // Leads atribuidos por post (el reporte "qué sí y qué no")
        $leadsPorPost = \App\Models\FormSubmission::whereIn('landing_post_id', $posts->pluck('id'))
            ->selectRaw('landing_post_id, count(*) as total')
            ->groupBy('landing_post_id')
            ->pluck('total', 'landing_post_id');

        return view('admin.blog-campaigns.show', [
            'campaign'     => $blogCampaign,
            'posts'        => $posts,
            'leadsPorPost' => $leadsPorPost,
        ]);
    }

    /**
     * Deja la orden de generar el mapa; blog:campaign-work la ejecuta por
     * cron (~2 min con 30 temas — un request web no la aguanta, Cloudflare
     * corta a los 100s) y notifica al terminar.
     */
    public function generateMap(Request $request, BlogCampaign $blogCampaign)
    {
        $blogCampaign->update([
            'map_requested_at'    => now(),
            'map_requested_count' => min(40, max(5, (int) $request->input('count', 30))),
        ]);

        return back()->with('success', 'Generando el mapa de temas en segundo plano (~2-3 min). Te llegará una notificación 🔔 cuando esté listo — esta página se recarga sola.');
    }

    public function activate(BlogCampaign $blogCampaign)
    {
        if (empty($blogCampaign->pendingTopics())) {
            return back()->with('error', 'La campaña no tiene temas pendientes — genera el mapa primero.');
        }

        $blogCampaign->update(['status' => 'active', 'started_at' => $blogCampaign->started_at ?? now()]);

        return back()->with('success', 'Campaña activa: el productor generará borradores automáticamente (o usa "Producir siguiente" ahora).');
    }

    public function pause(BlogCampaign $blogCampaign)
    {
        $blogCampaign->update(['status' => $blogCampaign->status === 'paused' ? 'active' : 'paused']);

        return back()->with('success', 'Estado de la campaña actualizado.');
    }

    /** Descarta un tema del mapa (el motivo alimenta la bitácora editorial). */
    public function discardTopic(Request $request, BlogCampaign $blogCampaign)
    {
        $index  = (int) $request->input('index');
        $motivo = trim((string) $request->input('motivo'));
        $topics = $blogCampaign->topics ?? [];

        if (! isset($topics[$index])) {
            return back()->with('error', 'Tema no encontrado.');
        }

        $topics[$index]['status'] = 'discarded';
        $blogCampaign->update(['topics' => $topics]);

        if ($motivo !== '') {
            $blogCampaign->addLeccion("Tema descartado: \"{$topics[$index]['title']}\" — {$motivo}");
        }

        return back()->with('success', 'Tema descartado' . ($motivo ? ' (motivo guardado para futuras generaciones)' : '') . '.');
    }

    /** Deja la orden de producir el siguiente borrador; el worker por cron la ejecuta (~3-5 min). */
    public function produceNext(BlogCampaign $blogCampaign)
    {
        if (empty($blogCampaign->pendingTopics())) {
            return back()->with('error', 'No hay temas pendientes en el mapa.');
        }

        $blogCampaign->update(['produce_requested_at' => now()]);

        return back()->with('success', 'Produciendo el siguiente borrador en segundo plano (~3-5 min, texto + imágenes). Te llegará una notificación 🔔 cuando espere tu OK.');
    }

    /** OK del editor: programa el post en el siguiente slot del calendario. */
    public function approvePost(BlogCampaign $blogCampaign, Post $post)
    {
        abort_unless($post->blog_campaign_id === $blogCampaign->id, 404);

        $fecha = $blogCampaign->nextPublishDate();
        $post->update(['status' => 'scheduled', 'published_at' => $fecha]);

        return back()->with('success', "«{$post->title}» programado para el {$fecha->translatedFormat('d \d\e F H:i')}.");
    }

    /** Descarta un borrador (motivo → bitácora editorial). */
    public function discardPost(Request $request, BlogCampaign $blogCampaign, Post $post)
    {
        abort_unless($post->blog_campaign_id === $blogCampaign->id, 404);

        $motivo = trim((string) $request->input('motivo'));
        if ($motivo !== '') {
            $blogCampaign->addLeccion("Borrador descartado: \"{$post->title}\" — {$motivo}");
        }

        // Liberar el tema como descartado en el mapa
        $topics = collect($blogCampaign->topics ?? [])->map(function ($t) use ($post) {
            if (($t['post_id'] ?? null) === $post->id) {
                $t['status'] = 'discarded';
            }
            return $t;
        })->all();
        $blogCampaign->update(['topics' => $topics]);

        $post->delete();

        return back()->with('success', 'Borrador descartado' . ($motivo ? ' — el motivo entra a la memoria editorial' : '') . '.');
    }
}
