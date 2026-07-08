<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Page;

class BlogController extends Controller
{
    public function index()
    {
        $query = Post::published()->with(['author', 'category'])->orderByDesc('published_at');

        if (request()->filled('category')) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', request('category'));
            });
        }

        if (request()->filled('tag')) {
            $query->whereHas('tags', function ($q) {
                $q->where('slug', request('tag'));
            });
        }

        $posts = $query->paginate(12)->withQueryString();

        // Solo categorías con contenido — una píldora que filtra a una
        // lista vacía es un callejón sin salida para el lector.
        $categories = PostCategory::withCount(['posts' => fn($q) => $q->published()])
            ->orderBy('name')
            ->get()
            ->filter(fn ($c) => $c->posts_count > 0)
            ->values();

        return view('blog.index', compact('posts', 'categories'));
    }

    public function show(string $slug)
    {
        $post = Post::published()->where('slug', $slug)->with(['author', 'category', 'tags'])->firstOrFail();
        $post->recordView(request());

        $related = Post::published()
            ->where('id', '!=', $post->id)
            ->when($post->category_id, fn($q) => $q->where('category_id', $post->category_id))
            ->with(['author', 'category'])
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'related'));
    }

    public function page(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        $layout = $page->is_landing ? 'layouts.landing' : 'layouts.public';

        return view('blog.page', compact('page', 'layout'));
    }
}
