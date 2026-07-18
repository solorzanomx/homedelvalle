<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCampaign;
use App\Models\Post;
use App\Services\BlogAIService;
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

    /** Genera (o re-genera) el mapa de temas de la campaña con IA. */
    public function generateMap(Request $request, BlogCampaign $blogCampaign, BlogAIService $blogAI)
    {
        set_time_limit(180);
        $count = min(40, max(5, (int) $request->input('count', 30)));

        $topics = $blogAI->discoverTopics('', [
            'count'     => $count,
            'objetivo'  => $blogCampaign->objetivo,
            'mezcla'    => $blogCampaign->mezcla ?: null,
            'lecciones' => $blogCampaign->lecciones ?: null,
        ]);

        if (empty($topics)) {
            return back()->with('error', 'La IA no devolvió temas — intenta de nuevo.');
        }

        // Conservar temas ya trabajados (generados/descartados); agregar nuevos como pending
        $existentes = collect($blogCampaign->topics ?? [])->where('status', '!=', 'pending')->values();
        $nuevos = collect($topics)->map(fn ($t) => [
            'title'       => $t['title'] ?? '',
            'description' => $t['description'] ?? '',
            'keywords'    => $t['suggested_keywords'] ?? [],
            'categoria'   => $t['categoria'] ?? null,
            'score'       => $t['relevance_score'] ?? null,
            'status'      => 'pending',
            'post_id'     => null,
        ]);

        $blogCampaign->update(['topics' => $existentes->concat($nuevos)->values()->all()]);

        return back()->with('success', count($topics) . ' temas generados. Revisa el mapa, descarta los que no y activa la campaña.');
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

    /** Produce el siguiente borrador de la campaña ahora mismo (sin esperar al productor). */
    public function produceNext(BlogCampaign $blogCampaign)
    {
        set_time_limit(600);

        $result = app(\App\Services\BlogCampaignProducer::class)->produceNext($blogCampaign);

        return $result
            ? back()->with('success', "Borrador generado: «{$result->title}» — revísalo y aprueba.")
            : back()->with('error', 'No hay temas pendientes o la generación falló (revisa el log).');
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
