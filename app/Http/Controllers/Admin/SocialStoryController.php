<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Social\PublishStoryAction;
use App\Http\Controllers\Controller;
use App\Models\SocialStory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Browsershot\Browsershot;

class SocialStoryController extends Controller
{
    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = SocialStory::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('platform')) {
            $query->where('platform', $request->platform);
        }

        $stories = $query->paginate(20)->withQueryString();

        return view('admin.social.stories.index', compact('stories'));
    }

    public function create()
    {
        return view('admin.social.stories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform'         => 'required|in:instagram,facebook',
            'media_type'       => 'required|in:image,video',
            'headline'         => 'nullable|string|max:100',
            'sticker_hashtags' => 'nullable|string',
            'sticker_location' => 'nullable|string|max:100',
            'sticker_link'     => 'nullable|url|max:255',
            'scheduled_at'     => 'nullable|date',
        ]);

        // Parse hashtags from comma-separated string
        $hashtags = null;
        if (!empty($validated['sticker_hashtags'])) {
            $hashtags = array_values(array_filter(
                array_map('trim', explode(',', $validated['sticker_hashtags']))
            ));
        }

        $story = SocialStory::create([
            'user_id'          => Auth::id(),
            'platform'         => $validated['platform'],
            'media_type'       => $validated['media_type'],
            'headline'         => $validated['headline'] ?? null,
            'sticker_hashtags' => $hashtags,
            'sticker_location' => $validated['sticker_location'] ?? null,
            'sticker_link'     => $validated['sticker_link'] ?? null,
            'scheduled_at'     => $validated['scheduled_at'] ?? null,
            'status'           => !empty($validated['scheduled_at']) ? 'scheduled' : 'draft',
        ]);

        return redirect()->route('admin.social.stories.show', $story)
            ->with('success', 'Historia creada correctamente.');
    }

    public function show(SocialStory $story)
    {
        $imageUrl = $story->rendered_image_path
            ? Storage::disk('public')->url($story->rendered_image_path) . '?t=' . $story->updated_at->timestamp
            : null;

        $bgUrl = $story->background_image_path
            ? Storage::disk('public')->url($story->background_image_path) . '?t=' . $story->updated_at->timestamp
            : null;

        return view('admin.social.stories.show', compact('story', 'imageUrl', 'bgUrl'));
    }

    public function update(Request $request, SocialStory $story)
    {
        $validated = $request->validate([
            'platform'         => 'sometimes|in:instagram,facebook',
            'media_type'       => 'sometimes|in:image,video',
            'headline'         => 'nullable|string|max:100',
            'caption'          => 'nullable|string|max:2200',
            'sticker_hashtags' => 'nullable|array',
            'sticker_hashtags.*' => 'string|max:50',
            'sticker_location' => 'nullable|string|max:100',
            'sticker_link'     => 'nullable|url|max:255',
            'scheduled_at'     => 'nullable|date',
            'status'           => 'sometimes|in:draft,scheduled,published,failed',
        ]);

        $story->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Historia actualizada.');
    }

    public function destroy(SocialStory $story)
    {
        $dir = "stories/{$story->id}";
        Storage::disk('public')->deleteDirectory($dir);

        $story->delete();

        return redirect()->route('admin.social.stories.index')
            ->with('success', 'Historia eliminada.');
    }

    // ── AJAX Actions ──────────────────────────────────────────────────────────

    /** POST /{story}/upload-bg — upload background image, resize to 1080x1920 */
    public function uploadBackground(Request $request, SocialStory $story)
    {
        $request->validate(['image' => 'required|image|max:15360']);

        $dir  = "stories/{$story->id}";
        $path = "{$dir}/background.jpg";
        Storage::disk('public')->makeDirectory($dir);

        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        $resized  = $manager->read($request->file('image')->getContent())
            ->cover(1080, 1920)
            ->toJpeg(90);

        Storage::disk('public')->put($path, (string) $resized);

        $story->update([
            'background_image_path' => $path,
            'render_status'         => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'url'     => Storage::disk('public')->url($path) . '?t=' . time(),
        ]);
    }

    /** POST /{story}/render — Browsershot render → 1080x1920 PNG */
    public function renderStory(SocialStory $story)
    {
        $story->update(['render_status' => 'rendering', 'render_error' => null]);

        try {
            $html = view('admin.social.stories.template', compact('story'))->render();

            $dir  = "stories/{$story->id}";
            $file = "{$dir}/rendered.png";
            Storage::disk('public')->makeDirectory($dir);
            $absolutePath = Storage::disk('public')->path($file);

            Browsershot::html($html)
                ->windowSize(1080, 1920)
                ->setChromePath(config('browsershot.chrome_path'))
                ->setNodeBinary(config('browsershot.node_path'))
                ->setNpmBinary(config('browsershot.npm_path'))
                ->addChromiumArguments(['no-sandbox', 'disable-setuid-sandbox', 'disable-dev-shm-usage'])
                ->waitUntilNetworkIdle()
                ->save($absolutePath);

            $story->update([
                'rendered_image_path' => $file,
                'render_status'       => 'done',
                'render_error'        => null,
            ]);

            return response()->json([
                'success' => true,
                'url'     => Storage::disk('public')->url($file) . '?t=' . time(),
            ]);
        } catch (\Throwable $e) {
            $story->update([
                'render_status' => 'failed',
                'render_error'  => substr($e->getMessage(), 0, 500),
            ]);

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /** POST /{story}/publish — send to n8n via PublishStoryAction */
    public function publishStory(SocialStory $story)
    {
        try {
            $action = new PublishStoryAction();
            $result = $action->execute($story);

            return response()->json([
                'success'      => true,
                'webhook_sent' => $result['webhook_sent'],
                'published_at' => $story->fresh()->published_at?->format('d M Y H:i'),
            ]);
        } catch (\Throwable $e) {
            Log::error('SocialStoryController: error publicando historia', [
                'story_id' => $story->id,
                'error'    => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /** GET /{story}/download — download rendered PNG */
    public function download(SocialStory $story)
    {
        if (!$story->rendered_image_path) {
            return back()->with('error', 'Primero renderiza la historia.');
        }

        $absolutePath = Storage::disk('public')->path($story->rendered_image_path);
        $filename     = 'hdv-story-' . $story->id . '.png';

        return response()->download($absolutePath, $filename);
    }
}
