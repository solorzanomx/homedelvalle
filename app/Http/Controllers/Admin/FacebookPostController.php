<?php

namespace App\Http\Controllers\Admin;

use App\Actions\FacebookPost\GenerateFacebookPostAction;
use App\Actions\FacebookPost\RenderFacebookPostAction;
use App\Http\Controllers\Controller;
use App\Models\FacebookPost;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FacebookPostController extends Controller
{
    public function __construct(
        private readonly GenerateFacebookPostAction $generateAction,
        private readonly RenderFacebookPostAction   $renderAction,
    ) {}

    // ── CRUD ─────────────────────────────────────────────────────────

    public function index()
    {
        $posts = FacebookPost::with('user')
            ->latest()
            ->paginate(20);

        return view('admin.facebook-posts.index', compact('posts'));
    }

    public function create()
    {
        $blogPosts = Post::where('status', 'published')
            ->latest()
            ->limit(50)
            ->get(['id', 'title']);

        return view('admin.facebook-posts.create', compact('blogPosts'));
    }

    public function store(Request $request)
    {
        $request->validate(['title' => 'required|string|max:200']);

        $post = FacebookPost::create([
            'user_id'     => Auth::id(),
            'title'       => $request->input('title'),
            'source_type' => $request->input('source_type', 'manual'),
            'source_id'   => $request->input('source_id') ?: null,
            'template'    => $request->input('template', 'fb-dark'),
        ]);

        return redirect()->route('admin.facebook.show', $post);
    }

    public function show(FacebookPost $post)
    {
        $blogPosts = Post::where('status', 'published')
            ->latest()
            ->limit(50)
            ->get(['id', 'title']);

        $imageUrl = $post->rendered_image_path
            ? Storage::disk('public')->url($post->rendered_image_path) . '?t=' . $post->updated_at->timestamp
            : null;

        return view('admin.facebook-posts.show', compact('post', 'blogPosts', 'imageUrl'));
    }

    public function update(Request $request, FacebookPost $post)
    {
        $request->validate([
            'title'       => 'sometimes|string|max:200',
            'source_type' => 'sometimes|in:blog_post,perplexity,manual',
            'source_id'   => 'nullable|exists:posts,id',
            'template'    => 'sometimes|in:fb-dark,fb-light,fb-foto,fb-gradient',
            'headline'    => 'nullable|string|max:200',
            'subheadline' => 'nullable|string|max:300',
            'body_text'   => 'nullable|string|max:500',
            'caption'     => 'nullable|string|max:2000',
            'hashtags'    => 'nullable|array',
            'hashtags.*'  => 'string|max:50',
        ]);

        $post->update($request->only([
            'title', 'source_type', 'source_id', 'template',
            'headline', 'subheadline', 'body_text', 'caption', 'hashtags',
        ]));

        // If template changed and image was already rendered, mark as stale
        if ($request->has('template') && $post->rendered_image_path) {
            $post->update(['render_status' => 'pending']);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Guardado.');
    }

    public function destroy(FacebookPost $post)
    {
        // Clean up stored files
        $dir = "facebook/{$post->id}";
        Storage::disk('public')->deleteDirectory($dir);

        $post->delete();

        return redirect()->route('admin.facebook.index')->with('success', 'Post eliminado.');
    }

    // ── AJAX ─────────────────────────────────────────────────────────

    /** POST /{post}/generate — generate copy with Claude */
    public function generateContent(Request $request, FacebookPost $post)
    {
        $request->validate(['content' => 'required|string|max:10000']);

        try {
            $bgPrompt = $this->generateAction->execute($post, $request->input('content'));
            $post->refresh();

            return response()->json([
                'success'     => true,
                'headline'    => $post->headline,
                'subheadline' => $post->subheadline,
                'body_text'   => $post->body_text,
                'caption'     => $post->caption,
                'hashtags'    => $post->hashtags ?? [],
                'bg_prompt'   => $bgPrompt,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /** POST /{post}/generate-bg — generate background with Gemini */
    public function generateBackground(Request $request, FacebookPost $post)
    {
        $request->validate(['prompt' => 'required|string|max:1000']);

        $apiKey = config('services.google_ai.api_key');
        if (!$apiKey) {
            return response()->json(['success' => false, 'error' => 'GOOGLE_AI_STUDIO_KEY no configurada.'], 500);
        }

        $promptSuffix = 'Ultra photorealistic, shot on full-frame DSLR, natural lighting, sharp focus, high detail, 4K resolution, aspect ratio 16:9, no text, no logos, no watermarks, no overlays, no UI elements, no borders, no artificial filters — if any signage, street signs, real estate signs or commercial text appears in the scene, render it exclusively in Spanish, Mexico City context.';

        $fullPrompt = rtrim($request->input('prompt'), '. ') . '. ' . $promptSuffix;

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
                ->timeout(120)
                ->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-image-preview:generateContent', [
                    'contents' => [[
                        'role'  => 'user',
                        'parts' => [['text' => $fullPrompt]],
                    ]],
                    'generationConfig' => [
                        'responseModalities' => ['image', 'text'],
                    ],
                ]);

            if (!$response->successful()) {
                $error = $response->json('error.message') ?? $response->body();
                throw new \RuntimeException("Gemini error ({$response->status()}): {$error}");
            }

            $b64 = null;
            foreach ($response->json('candidates.0.content.parts', []) as $part) {
                if (!empty($part['inlineData']['data'])) {
                    $b64 = $part['inlineData']['data'];
                    break;
                }
            }

            if (!$b64) {
                throw new \RuntimeException('Gemini no devolvió datos de imagen.');
            }

            $dir  = "facebook/{$post->id}";
            $path = "{$dir}/background.png";
            Storage::disk('public')->makeDirectory($dir);

            // Resize to 1200×628 via Intervention Image
            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $resized  = $manager->read(base64_decode($b64))
                ->cover(1200, 628)
                ->toPng();

            Storage::disk('public')->put($path, (string) $resized);

            $post->update([
                'background_image_path' => $path,
                'render_status'         => 'pending', // force re-render
            ]);

            Log::info('FacebookPostController: background generated', ['post_id' => $post->id]);

            return response()->json([
                'success' => true,
                'url'     => Storage::disk('public')->url($path) . '?t=' . time(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /** POST /{post}/upload-bg — manual background upload */
    public function uploadBackground(Request $request, FacebookPost $post)
    {
        $request->validate(['image' => 'required|image|max:10240']);

        $dir  = "facebook/{$post->id}";
        $path = "{$dir}/background.png";
        Storage::disk('public')->makeDirectory($dir);

        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        $resized  = $manager->read($request->file('image')->getContent())
            ->cover(1200, 628)
            ->toPng();

        Storage::disk('public')->put($path, (string) $resized);

        $post->update([
            'background_image_path' => $path,
            'render_status'         => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'url'     => Storage::disk('public')->url($path) . '?t=' . time(),
        ]);
    }

    /** POST /{post}/render — render image via Browsershot */
    public function renderImage(FacebookPost $post)
    {
        try {
            $this->renderAction->execute($post);
            $post->refresh();

            return response()->json([
                'success' => true,
                'url'     => Storage::disk('public')->url($post->rendered_image_path) . '?t=' . time(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /** GET /{post}/download — download rendered PNG */
    public function download(FacebookPost $post)
    {
        if (!$post->rendered_image_path) {
            return back()->with('error', 'Primero renderiza la imagen.');
        }

        $absolutePath = Storage::disk('public')->path($post->rendered_image_path);
        $filename     = 'hdv-facebook-' . $post->id . '.png';

        return response()->download($absolutePath, $filename);
    }
}
