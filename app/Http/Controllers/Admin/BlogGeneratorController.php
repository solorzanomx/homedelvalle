<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Blog\GenerateBlogImagesAction;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateBlogPostJob;
use App\Models\BlogTopicSuggestion;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use App\Services\BlogAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogGeneratorController extends Controller
{
    public function __construct(
        private readonly BlogAIService $blogAI,
        private readonly GenerateBlogImagesAction $imageAction,
    ) {}

    // ── STEP 1 ────────────────────────────────────────────────────────

    /** GET /admin/blog/generar */
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

        $categories = PostCategory::orderBy('name')->get(['id','name']);
        $tags       = Tag::orderBy('name')->get(['id','name']);

        return view('admin.posts.generate', compact('suggestions', 'sessionId', 'recentPosts', 'categories', 'tags'));
    }

    /** POST /admin/blog/descubrir */
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

    /** POST /admin/blog/generar — async job */
    public function generate(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:200',
            'keywords'      => 'required|string|max:500',
            'suggestion_id' => 'nullable|exists:blog_topic_suggestions,id',
            'market_data'   => 'nullable|string|max:5000',
        ]);

        $post = $this->createPlaceholder($request);

        if ($sid = $request->input('suggestion_id')) {
            BlogTopicSuggestion::find($sid)?->update(['status' => 'selected']);
        }

        GenerateBlogPostJob::dispatch(
            $post,
            $request->input('title'),
            array_values(array_filter(array_map('trim', explode(',', $request->input('keywords'))))),
            (string) $request->input('market_data', ''),
            $request->input('suggestion_id'),
        );

        return redirect()
            ->route('admin.posts.show', $post)
            ->with('info', 'Generación iniciada. El artículo estará listo en 1-2 minutos.');
    }

    /** POST /admin/blog/generar-sync — synchronous, then go to Step 2 */
    public function generateSync(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:200',
            'keywords'      => 'required|string|max:500',
            'market_data'   => 'nullable|string|max:5000',
            'suggestion_id' => 'nullable|exists:blog_topic_suggestions,id',
            'category_id'   => 'nullable|exists:post_categories,id',
            'tags'          => 'nullable|array',
            'tags.*'        => 'exists:tags,id',
        ]);

        $title       = $request->input('title');
        $keywords    = array_values(array_filter(array_map('trim', explode(',', $request->input('keywords')))));
        $marketData  = (string) $request->input('market_data', '');
        $suggestionId = $request->input('suggestion_id');

        $post = $this->createPlaceholder($request);
        $post->tags()->sync($request->input('tags', []));

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

        // Go to Step 2 — image generation
        return redirect()
            ->route('admin.blog.images', $post)
            ->with('success', 'Contenido generado. Ahora genera las imágenes.');
    }

    // ── STEP 2 ────────────────────────────────────────────────────────

    /** GET /admin/blog/{post}/imagenes */
    public function images(Post $post)
    {
        $prompts  = $post->image_prompts ?? [];
        $imageData = [];

        foreach (GenerateBlogImagesAction::KEYS as $key) {
            $storedPath = $prompts["path_{$key}"] ?? null;
            $imageData[$key] = [
                'label'  => GenerateBlogImagesAction::LABELS[$key],
                'prompt' => $prompts[$key] ?? null,
                'url'    => $storedPath ? Storage::disk('public')->url($storedPath) : null,
            ];
        }

        return view('admin.posts.images', compact('post', 'imageData'));
    }

    /** POST /admin/blog/{post}/generar-imagenes — AJAX: generate all */
    public function generateAllImages(Request $request, Post $post)
    {
        try {
            // Apply any custom prompts sent from the UI before generating
            $customPrompts = $request->input('prompts', []);
            if (!empty($customPrompts)) {
                $prompts = $post->image_prompts ?? [];
                foreach (GenerateBlogImagesAction::KEYS as $key) {
                    if (!empty($customPrompts[$key])) {
                        $prompts[$key] = $customPrompts[$key];
                    }
                }
                $post->update(['image_prompts' => $prompts]);
                $post->refresh();
            }

            $this->imageAction->generateAll($post);

            $post->refresh();
            $prompts = $post->image_prompts ?? [];
            $urls = [];

            foreach (GenerateBlogImagesAction::KEYS as $key) {
                $path      = $prompts["path_{$key}"] ?? null;
                $urls[$key] = $path
                    ? Storage::disk('public')->url($path) . '?t=' . time()
                    : null;
            }

            return response()->json(['success' => true, 'urls' => $urls]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /** POST /admin/blog/{post}/re-imagen — AJAX: regenerate single */
    public function regenerateImage(Request $request, Post $post)
    {
        $request->validate(['key' => 'required|in:featured,interior_1,interior_2,interior_3']);

        // Save custom prompt if provided
        $customPrompt = $request->input('prompt');
        if ($customPrompt) {
            $prompts = $post->image_prompts ?? [];
            $prompts[$request->input('key')] = $customPrompt;
            $post->update(['image_prompts' => $prompts]);
            $post->refresh();
        }

        try {
            $url = $this->imageAction->generateSingle($post, $request->input('key'));
            return response()->json(['success' => true, 'url' => $url]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /** POST /admin/blog/{post}/finalizar — inject images into body, go to Step 3 */
    public function finalizeImages(Post $post)
    {
        $this->imageAction->injectIntoBody($post);

        return redirect()
            ->route('admin.posts.edit', $post)
            ->with('success', 'Imágenes insertadas. Revisa y publica el artículo.');
    }

    // ── POLLING ───────────────────────────────────────────────────────

    /** GET /admin/blog/status/{post} */
    public function status(Post $post)
    {
        return response()->json([
            'status'      => $post->ai_generation_status,
            'done'        => $post->ai_generation_status === 'done',
            'failed'      => $post->ai_generation_status === 'failed',
            'images_url'  => route('admin.blog.images', $post),
            'edit_url'    => route('admin.posts.edit', $post),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────

    private function createPlaceholder(Request $request): Post
    {
        return Post::create([
            'title'                => $request->input('title'),
            'slug'                 => Str::slug($request->input('title')) . '-' . substr((string) Str::uuid(), 0, 8),
            'body'                 => '',
            'status'               => 'draft',
            'user_id'              => Auth::id(),
            'category_id'          => $request->input('category_id') ?: null,
            'ai_generated'         => true,
            'ai_generation_status' => 'pending',
        ]);
    }
}
