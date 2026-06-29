<?php

namespace App\Http\Controllers;

use App\Models\ColoniaPage;
use App\Models\MarketColonia;
use App\Models\MarketZone;
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

        $colonias = ColoniaPage::published()
            ->orderBy('sort_order')
            ->get(['slug', 'updated_at']);

        $marketZones = MarketZone::published()->with('publishedColonias')->orderBy('sort_order')->get(['id', 'slug', 'updated_at']);

        $staticPages = [
            ['url' => url('/'),                                        'priority' => '1.0', 'changefreq' => 'weekly'],
            ['url' => url('/propiedades'),                             'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => url('/blog'),                                    'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => url('/precios'),                                 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['url' => url('/vende-tu-propiedad'),                      'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => url('/comprar'),                                 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => url('/rentar'),                                  'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => url('/renta-tu-propiedad'),                      'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => url('/desarrolladores-e-inversionistas'),        'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => url('/servicios'),                               'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => url('/nosotros'),                                'priority' => '0.6', 'changefreq' => 'monthly'],
            ['url' => url('/testimonios'),                             'priority' => '0.6', 'changefreq' => 'monthly'],
            ['url' => url('/contacto'),                                'priority' => '0.6', 'changefreq' => 'monthly'],
        ];

        $content = view('sitemap', compact('staticPages', 'posts', 'properties', 'colonias', 'marketZones'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }
}
