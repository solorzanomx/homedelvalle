<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Client;
use App\Models\Broker;
use App\Models\Post;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Nota histórica: aquí vivía un redirect que expulsaba a los usuarios
        // logueados hacia /admin (parche de cuando sitio y CRM compartían
        // dominio; el hack '?preview' lo esquivaba). Con el CRM separado en
        // admin.homedelvalle.mx quedó obsoleto — y con cookie de sesión
        // compartida entre subdominios impedía ver el sitio público estando
        // logueado en el admin (bug real reportado 2026-07-17).

        // publiclyVisible (no available): una destacada que se reservó o
        // vendió sigue en el home CON su letrero — pedido explícito; se
        // quita marcándola como no destacada o archivándola.
        $featuredProperties = Property::publiclyVisible()->featured()->latest()->take(6)->get();

        // Fallback: si no hay destacadas, mostrar las más recientes disponibles
        if ($featuredProperties->isEmpty()) {
            $featuredProperties = Property::available()->latest()->take(6)->get();
        }
        // Jerarquía constructor-primero (docs/posicionamiento-marca.md): entre
        // los 3 destacados siempre va al menos un post del funnel
        // predio→desarrolladora; si los 3 más recientes no lo traen, el más
        // nuevo de zonificacion-desarrollo reemplaza al tercero.
        $latestPosts = Post::published()->with('category')->latest('published_at')->take(3)->get();
        if (! $latestPosts->contains(fn ($p) => $p->category?->slug === 'zonificacion-desarrollo')) {
            $predioPost = Post::published()
                ->whereHas('category', fn ($q) => $q->where('slug', 'zonificacion-desarrollo'))
                ->latest('published_at')
                ->first();
            if ($predioPost) {
                $latestPosts = $latestPosts->take(2)->push($predioPost);
            }
        }
        $homeTestimonials = Testimonial::active()->inRandomOrder()->take(3)->get();

        return view('public.home', compact('featuredProperties', 'latestPosts', 'homeTestimonials'));
    }
}
