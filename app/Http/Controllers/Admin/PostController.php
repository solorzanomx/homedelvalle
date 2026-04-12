<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Tag;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with(['author', 'category'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $posts = $query->paginate(20)->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        $categories = PostCategory::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();

        return view('admin.posts.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:posts',
            'body' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|image|max:2048',
            'category_id' => 'nullable|exists:post_categories,id',
            'status' => 'required|in:draft,scheduled,published,archived',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'ctas' => 'nullable|array',
            'ctas.*.title' => 'nullable|string|max:255',
            'ctas.*.description' => 'nullable|string|max:500',
            'ctas.*.button_text' => 'nullable|string|max:100',
            'ctas.*.link' => 'nullable|string|max:500',
        ]);

        if ($validated['status'] === 'scheduled') {
            $request->validate(['published_at' => 'required|date|after:now']);
            $validated['published_at'] = Carbon::parse($request->published_at);
        } elseif ($validated['status'] === 'published') {
            $validated['published_at'] = !empty($validated['published_at'])
                ? Carbon::parse($validated['published_at'])->min(now())
                : now();
        } elseif ($validated['status'] === 'draft') {
            $validated['published_at'] = null;
        }

        if ($request->hasFile('featured_image')) {
            $optimizer = new ImageOptimizer();
            $imageData = $optimizer->process($request->file('featured_image'), 'posts', $validated['slug'] ?? '');
            $validated['featured_image'] = $imageData['original'];
            $validated['featured_image_data'] = $imageData;
        }

        $validated['user_id'] = auth()->id();
        $validated['ctas'] = $this->filterCtas($request->input('ctas', []));
        unset($validated['tags']);

        $post = Post::create($validated);

        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        return redirect()->route('admin.posts.index')->with('success', 'Post creado correctamente.');
    }

    public function edit(Post $post)
    {
        $post->load('tags');
        $categories = PostCategory::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();

        return view('admin.posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:posts,slug,' . $post->id,
            'body' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|image|max:2048',
            'category_id' => 'nullable|exists:post_categories,id',
            'status' => 'required|in:draft,scheduled,published,archived',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'ctas' => 'nullable|array',
            'ctas.*.title' => 'nullable|string|max:255',
            'ctas.*.description' => 'nullable|string|max:500',
            'ctas.*.button_text' => 'nullable|string|max:100',
            'ctas.*.link' => 'nullable|string|max:500',
        ]);

        if ($validated['status'] === 'scheduled') {
            $request->validate(['published_at' => 'required|date|after:now']);
            $validated['published_at'] = Carbon::parse($request->published_at);
        } elseif ($validated['status'] === 'published') {
            $validated['published_at'] = !empty($validated['published_at'])
                ? Carbon::parse($validated['published_at'])->min(now())
                : ($post->published_at ?? now());
        } elseif ($validated['status'] === 'draft') {
            $validated['published_at'] = null;
        }

        if ($request->hasFile('featured_image')) {
            $optimizer = new ImageOptimizer();
            $optimizer->cleanup($post->featured_image_data ?: $post->featured_image);
            $imageData = $optimizer->process($request->file('featured_image'), 'posts', $validated['slug'] ?? $post->slug);
            $validated['featured_image'] = $imageData['original'];
            $validated['featured_image_data'] = $imageData;
        }

        $validated['ctas'] = $this->filterCtas($request->input('ctas', []));
        unset($validated['tags']);

        $post->update($validated);
        $post->tags()->sync($request->tags ?? []);

        return redirect()->route('admin.posts.index')->with('success', 'Post actualizado correctamente.');
    }

    public function destroy(Post $post)
    {
        if ($post->featured_image) {
            $optimizer = new ImageOptimizer();
            $optimizer->cleanup($post->featured_image_data ?: $post->featured_image);
        }

        $post->tags()->detach();
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Post eliminado correctamente.');
    }

    /**
     * Upload image from TinyMCE editor (shared by posts and pages).
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $optimizer = new ImageOptimizer();
        $result = $optimizer->processInline($request->file('image'), 'cms-images');

        return response()->json([
            'url' => $result['url'],
        ]);
    }

    private function filterCtas(array $ctas): array
    {
        return array_values(array_map(function ($cta) {
            return [
                'title' => trim($cta['title'] ?? ''),
                'description' => trim($cta['description'] ?? ''),
                'button_text' => trim($cta['button_text'] ?? ''),
                'link' => trim($cta['link'] ?? ''),
            ];
        }, $ctas));
    }
}
