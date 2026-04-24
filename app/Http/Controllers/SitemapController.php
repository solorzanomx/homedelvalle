<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Property;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $posts = Post::where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderBy('updated_at', 'desc')
            ->get(['slug', 'updated_at']);

        $properties = Property::available()
            ->orderBy('updated_at', 'desc')
            ->get(['id', 'updated_at']);

        $staticPages = [
            ['url' => url('/'),                    'priority' => '1.0',  'changefreq' => 'weekly'],
            ['url' => url('/blog'),                'priority' => '0.9',  'changefreq' => 'daily'],
            ['url' => url('/propiedades'),         'priority' => '0.9',  'changefreq' => 'daily'],
            ['url' => url('/vende-tu-propiedad'),  'priority' => '0.8',  'changefreq' => 'monthly'],
            ['url' => url('/servicios'),           'priority' => '0.7',  'changefreq' => 'monthly'],
            ['url' => url('/nosotros'),            'priority' => '0.6',  'changefreq' => 'monthly'],
            ['url' => url('/contacto'),            'priority' => '0.6',  'changefreq' => 'monthly'],
        ];

        $content = view('sitemap', compact('staticPages', 'posts', 'properties'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
