<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateBlogPostJob;
use App\Models\BlogTopicSuggestion;
use App\Models\Post;
use App\Services\BlogAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlogGeneratorController extends Controller
{
    public function __construct(private readonly BlogAIService $blogAI) {}

    /**
     * GET /admin/blog/generar — Show the generator UI
     */
    public function index(Request $request)
    {
        $sessionId   = $request->get('session', null);
        $suggestions = $sessionId
            ? BlogTopicSuggestion::forSession($sessionId)->orderByDesc('relevance_score')->get()
            : collect();

        $recentPosts = Post::where('ai_generated', true)
            ->latest()
            ->limit(10)
            ->get(['id','title','status','ai_generation_status','seo_score','created_at']);

        return view('admin.posts.generate', compact('suggestions', 'sessionId', 'recentPosts'));
    }

    /**
     * POST /admin/blog/descubrir — Run Perplexity + Claude topic discovery
     */
    public function discover(Request $request)
    {
        $request->validate(['topic' => 'nullable|string|max:200']);

        $freeText  = (string) $request->input('topic', '');
        $sessionId = (string) Str::uuid();

        try {
            $topics = $this->blogAI->discoverTopics($freeText);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error en la búsqueda de temas: ' . $e->getMessage());
        }

        // Persist suggestions for this session
        foreach ($topics as $topic) {
            BlogTopicSuggestion::create([
                'session_id'         => $sessionId,
                'title'              => $topic['title'],
                'description'        => $topic['description'],
                'reasoning'          => $topic['reasoning'],
                'suggested_keywords' => $topic['suggested_keywords'],
                'relevance_score'    => $topic['relevance_score'],
                'status'             => 'pending',
            ]);
        }

        return redirect()->route('admin.blog.generator', ['session' => $sessionId]);
    }

    /**
     * POST /admin/blog/generar — Create a Post placeholder and dispatch generation job
     */
    public function generate(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:200',
            'keywords'   => 'required|string|max:500',
            'suggestion_id' => 'nullable|exists:blog_topic_suggestions,id',
            'market_data'   => 'nullable|string|max:5000',
        ]);

        $title       = $request->input('title');
        $keywords    = array_values(array_filter(array_map('trim', explode(',', $request->input('keywords')))));
        $marketData  = $request->input('market_data', '');
        $suggestionId = $request->input('suggestion_id');

        // Create a draft Post placeholder
        $post = Post::create([
            'title'                => $title,
            'slug'                 => Str::slug($title) . '-' . substr((string) Str::uuid(), 0, 8),
            'body'                 => '',
            'status'               => 'draft',
            'user_id'              => Auth::id(),
            'ai_generated'         => true,
            'ai_generation_status' => 'pending',
        ]);

        if ($suggestionId) {
            BlogTopicSuggestion::find($suggestionId)?->update(['status' => 'selected']);
        }

        GenerateBlogPostJob::dispatch($post, $title, $keywords, $marketData, $suggestionId);

        return redirect()
            ->route('admin.posts.show', $post)
            ->with('info', 'Generación iniciada. El artículo estará listo en 1-2 minutos.');
    }

    /**
     * POST /admin/blog/generar-sync — Synchronous generation (for small queues / local)
     */
    public function generateSync(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:200',
            'keywords' => 'required|string|max:500',
            'market_data'   => 'nullable|string|max:5000',
            'suggestion_id' => 'nullable|exists:blog_topic_suggestions,id',
        ]);

        $title       = $request->input('title');
        $keywords    = array_values(array_filter(array_map('trim', explode(',', $request->input('keywords')))));
        $marketData  = $request->input('market_data', '');
        $suggestionId = $request->input('suggestion_id');

        $post = Post::create([
            'title'                => $title,
            'slug'                 => Str::slug($title) . '-' . substr((string) Str::uuid(), 0, 8),
            'body'                 => '',
            'status'               => 'draft',
            'user_id'              => Auth::id(),
            'ai_generated'         => true,
            'ai_generation_status' => 'pending',
        ]);

        if ($suggestionId) {
            BlogTopicSuggestion::find($suggestionId)?->update(['status' => 'selected']);
        }

        try {
            $action = app(\App\Actions\Blog\GenerateBlogPostAction::class);
            $action->execute($post, $title, $keywords, $marketData);

            if ($suggestionId) {
                BlogTopicSuggestion::find($suggestionId)?->update([
                    'status'            => 'converted',
                    'converted_post_id' => $post->id,
                ]);
            }
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.blog.generator')
                ->with('error', 'Error al generar el artículo: ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('success', 'Artículo generado y listo para editar.');
    }

    /**
     * GET /admin/blog/status/{post} — JSON status for polling
     */
    public function status(Post $post)
    {
        return response()->json([
            'status'     => $post->ai_generation_status,
            'done'       => $post->ai_generation_status === 'done',
            'failed'     => $post->ai_generation_status === 'failed',
            'edit_url'   => route('admin.posts.edit', $post),
        ]);
    }
}
