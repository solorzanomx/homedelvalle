<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Client;
use App\Models\Broker;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        $featuredProperties = Property::available()->latest()->take(6)->get();
        $latestPosts = Post::published()->latest('published_at')->take(3)->get();

        return view('public.home', compact('featuredProperties', 'latestPosts'));
    }
}
