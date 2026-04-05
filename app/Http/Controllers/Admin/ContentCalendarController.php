<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ContentCalendarController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $currentDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $start = $currentDate->copy()->startOfWeek(Carbon::MONDAY);
        $end = $currentDate->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $posts = Post::whereBetween('published_at', [$start, $end])
            ->whereIn('status', ['scheduled', 'published'])
            ->with('category')
            ->orderBy('published_at')
            ->get();

        $drafts = Post::where('status', 'draft')
            ->whereNull('published_at')
            ->with('category')
            ->latest()
            ->get();

        $postsJson = $posts->map(function ($p) {
            return [
                'id' => $p->id,
                'title' => $p->title,
                'date' => $p->published_at->format('Y-m-d'),
                'time' => $p->published_at->format('H:i'),
                'status' => $p->status,
                'category' => $p->category ? $p->category->name : null,
                'url' => route('admin.posts.edit', $p),
            ];
        });

        $draftsJson = $drafts->map(function ($p) {
            return [
                'id' => $p->id,
                'title' => $p->title,
                'url' => route('admin.posts.edit', $p),
            ];
        });

        return view('admin.posts.calendar', compact('posts', 'drafts', 'currentDate', 'postsJson', 'draftsJson'));
    }

    public function events(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $posts = Post::whereBetween('published_at', [$request->start, $request->end])
            ->whereIn('status', ['scheduled', 'published'])
            ->with('category')
            ->orderBy('published_at')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'date' => $p->published_at->format('Y-m-d'),
                'time' => $p->published_at->format('H:i'),
                'status' => $p->status,
                'category' => $p->category?->name,
                'url' => route('admin.posts.edit', $p),
            ]);

        $drafts = Post::where('status', 'draft')
            ->whereNull('published_at')
            ->latest()
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'status' => 'draft',
                'url' => route('admin.posts.edit', $p),
            ]);

        return response()->json(['posts' => $posts, 'drafts' => $drafts]);
    }

    public function updateDate(Request $request, Post $post)
    {
        $request->validate(['published_at' => 'required|date']);

        $date = Carbon::parse($request->published_at);

        $post->published_at = $date;

        if ($date->isFuture()) {
            $post->status = 'scheduled';
        } else {
            $post->status = 'published';
        }

        $post->save();

        return response()->json([
            'success' => true,
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'date' => $post->published_at->format('Y-m-d'),
                'time' => $post->published_at->format('H:i'),
                'status' => $post->status,
            ],
        ]);
    }
}
