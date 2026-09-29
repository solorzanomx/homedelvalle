<?php

namespace App\Http\Controllers;

use App\Models\BlogRedirect;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Page;

class BlogController extends Controller
{
    // Levenshtein normalizado: 1 - distancia/longitud. 0.85 = tolera 1-2 dedazos en un slug típico,
    // sin confundir dos artículos distintos que casualmente se parecen (fase 1 del prompt de leads).
    private const FUZZY_THRESHOLD = 0.85;
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
        $post = Post::published()->where('slug', $slug)->with(['author', 'category', 'tags'])->first();

        if (! $post) {
            return $this->notFoundOrFuzzyRedirect($slug);
        }

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

    /**
     * Slug que no existe: busca el post publicado más parecido (Levenshtein normalizado) y, si pasa
     * el umbral, redirige 301 dejando el redirect registrado como "auto" (así la próxima visita ya
     * no recalcula nada). Si no hay candidato razonable, 404 útil con artículos relacionados.
     */
    private function notFoundOrFuzzyRedirect(string $slug)
    {
        $best = null;
        $bestScore = 0.0;

        foreach (Post::published()->pluck('slug') as $candidate) {
            $len = max(strlen($slug), strlen($candidate), 1);
            $score = 1 - (levenshtein($slug, $candidate) / $len);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidate;
            }
        }

        if ($best && $bestScore >= self::FUZZY_THRESHOLD) {
            $path = BlogRedirect::normalize($slug);
            BlogRedirect::firstOrCreate(
                ['from_path' => $path],
                ['to_path' => '/blog/' . $best, 'status' => 301, 'active' => true, 'notes' => 'auto (similitud ' . round($bestScore * 100) . '%)']
            );

            return redirect('/blog/' . $best, 301);
        }

        // Fase 7 del prompt de leads del blog: sin match ni siquiera difuso — registrado para el
        // panel "los 404 más frecuentes" (antes esto no dejaba ningún rastro).
        \App\Models\BlogNotFoundHit::register($slug);

        $related = Post::published()->latest('published_at')->take(4)->get();

        return response()->view('blog.not-found', compact('related', 'slug'), 404);
    }

    public function page(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();

        $layout = $page->is_landing ? 'layouts.landing' : 'layouts.public';

        return view('blog.page', compact('page', 'layout'));
    }
}
