<?php

namespace App\Http\Controllers;

use App\Models\ColoniaPage;
use App\Models\Post;
use App\Models\Property;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ColoniaController extends Controller
{
    public function show(string $slug): View|Response
    {
        $colonia = ColoniaPage::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Properties available in this colonia
        $terms = $colonia->getSearchTermsArray();
        $properties = Property::available()
            ->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->orWhere('colony', 'like', "%{$term}%");
                }
            })
            ->latest()
            ->take(6)
            ->get();

        // Related blog posts (by title or meta_description — avoids full-text scan on body)
        $posts = Post::published()
            ->where(function ($q) use ($colonia) {
                $name = $colonia->name;
                $q->where('title', 'like', "%{$name}%")
                  ->orWhere('meta_description', 'like', "%{$name}%")
                  ->orWhere('focus_keyword', 'like', "%{$name}%");
            })
            ->latest()
            ->take(3)
            ->get();

        return view('public.colonia', compact('colonia', 'properties', 'posts'));
    }
}
