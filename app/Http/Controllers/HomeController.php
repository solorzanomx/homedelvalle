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
        if (Auth::check() && !$request->has('preview')) {
            return redirect()->route('admin.dashboard');
        }

        // publiclyVisible (no available): una destacada que se reservó o
        // vendió sigue en el home CON su letrero — pedido explícito; se
        // quita marcándola como no destacada o archivándola.
        $featuredProperties = Property::publiclyVisible()->featured()->latest()->take(6)->get();

        // Fallback: si no hay destacadas, mostrar las más recientes disponibles
        if ($featuredProperties->isEmpty()) {
            $featuredProperties = Property::available()->latest()->take(6)->get();
        }
        $latestPosts = Post::published()->latest('published_at')->take(3)->get();
        $homeTestimonials = Testimonial::active()->inRandomOrder()->take(3)->get();

        return view('public.home', compact('featuredProperties', 'latestPosts', 'homeTestimonials'));
    }
}
