<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarouselPost;
use App\Models\FacebookPost;
use App\Models\Post;
use App\Models\SocialStory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SocialCalendarController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        // Validate format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = now()->format('Y-m');
        }

        [$year, $mon] = explode('-', $month);
        $start = Carbon::createFromDate((int)$year, (int)$mon, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        // ── Recopilar contenido del mes ───────────────────────────────────────

        $fbPosts = FacebookPost::where(function ($q) use ($start, $end) {
            $q->whereBetween('scheduled_at', [$start, $end])
              ->orWhereBetween('published_at', [$start, $end]);
        })->get()->map(fn($p) => [
            'id'         => $p->id,
            'type'       => 'facebook_post',
            'type_label' => 'Post FB',
            'type_color' => 'blue',
            'title'      => $p->title,
            'status'     => $p->status,
            'date'       => ($p->scheduled_at ?? $p->published_at ?? $p->created_at)?->toDateTimeString(),
            'url'        => route('admin.facebook.show', $p),
        ]);

        $carousels = CarouselPost::where(function ($q) use ($start, $end) {
            $q->whereBetween('scheduled_at', [$start, $end])
              ->orWhereBetween('published_at', [$start, $end]);
        })->get()->map(fn($c) => [
            'id'         => $c->id,
            'type'       => 'carousel',
            'type_label' => 'Carrusel IG',
            'type_color' => 'pink',
            'title'      => $c->title,
            'status'     => $c->status,
            'date'       => ($c->scheduled_at ?? $c->published_at ?? $c->created_at)?->toDateTimeString(),
            'url'        => route('admin.carousels.show', $c),
        ]);

        $blogPosts = Post::whereBetween('published_at', [$start, $end])
            ->whereIn('status', ['published', 'scheduled'])
            ->get()
            ->map(fn($p) => [
                'id'         => $p->id,
                'type'       => 'blog_post',
                'type_label' => 'Blog',
                'type_color' => 'green',
                'title'      => $p->title,
                'status'     => $p->status,
                'date'       => $p->published_at?->toDateTimeString(),
                'url'        => route('admin.posts.edit', $p),
            ]);

        $stories = SocialStory::where(function ($q) use ($start, $end) {
            $q->whereBetween('scheduled_at', [$start, $end])
              ->orWhereBetween('published_at', [$start, $end]);
        })->get()->map(fn($s) => [
            'id'         => $s->id,
            'type'       => 'story',
            'type_label' => 'Historia',
            'type_color' => 'purple',
            'title'      => $s->headline ?? 'Historia sin título',
            'status'     => $s->status,
            'date'       => ($s->scheduled_at ?? $s->published_at ?? $s->created_at)?->toDateTimeString(),
            'url'        => route('admin.social.stories.show', $s),
        ]);

        // ── Unificar y agrupar por día ────────────────────────────────────────

        $allContent = $fbPosts->concat($carousels)->concat($blogPosts)->concat($stories)
            ->sortBy('date')
            ->values();

        $calendarDays = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $dayStr = $d->format('Y-m-d');
            $calendarDays[$dayStr] = $allContent->filter(
                fn($item) => isset($item['date']) && Carbon::parse($item['date'])->format('Y-m-d') === $dayStr
            )->values()->all();
        }

        // ── Stats del mes ─────────────────────────────────────────────────────

        $stats = [
            'total'     => $allContent->count(),
            'published' => $allContent->where('status', 'published')->count(),
            'scheduled' => $allContent->whereIn('status', ['scheduled', 'approved'])->count(),
            'draft'     => $allContent->whereIn('status', ['draft', 'review'])->count(),
        ];

        // ── Próximos 5 items ──────────────────────────────────────────────────

        $upcomingItems = $allContent
            ->filter(fn($item) => isset($item['date']) && Carbon::parse($item['date'])->isFuture())
            ->sortBy('date')
            ->take(5)
            ->values()
            ->all();

        return view('admin.social.calendar', compact(
            'calendarDays', 'month', 'start', 'stats', 'upcomingItems'
        ));
    }

    public function quickSchedule(Request $request)
    {
        $validated = $request->validate([
            'type'         => 'required|in:facebook_post,carousel,blog_post,story',
            'id'           => 'required|integer',
            'scheduled_at' => 'required|date',
        ]);

        $item = match($validated['type']) {
            'facebook_post' => FacebookPost::findOrFail($validated['id']),
            'carousel'      => CarouselPost::findOrFail($validated['id']),
            'blog_post'     => Post::findOrFail($validated['id']),
            'story'         => SocialStory::findOrFail($validated['id']),
        };

        $dateField = $validated['type'] === 'blog_post' ? 'published_at' : 'scheduled_at';

        $newStatus = match($validated['type']) {
            'blog_post'     => 'scheduled',
            'facebook_post' => 'scheduled',
            'story'         => 'scheduled',
            'carousel'      => $item->status, // keep existing (approved / draft)
        };

        $item->update([
            $dateField => $validated['scheduled_at'],
            'status'   => $newStatus,
        ]);

        return response()->json(['success' => true]);
    }

    public function upcoming(Request $request)
    {
        $fbPosts = FacebookPost::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->take(20)->get()
            ->map(fn($p) => [
                'type'       => 'facebook_post',
                'type_label' => 'Post FB',
                'type_color' => 'blue',
                'title'      => $p->title,
                'status'     => $p->status,
                'date'       => $p->scheduled_at,
                'url'        => route('admin.facebook.show', $p),
            ]);

        $carousels = CarouselPost::where('status', 'approved')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->take(20)->get()
            ->map(fn($c) => [
                'type'       => 'carousel',
                'type_label' => 'Carrusel IG',
                'type_color' => 'pink',
                'title'      => $c->title,
                'status'     => $c->status,
                'date'       => $c->scheduled_at,
                'url'        => route('admin.carousels.show', $c),
            ]);

        $blog = Post::where('status', 'scheduled')
            ->where('published_at', '>=', now())
            ->orderBy('published_at')
            ->take(20)->get()
            ->map(fn($p) => [
                'type'       => 'blog_post',
                'type_label' => 'Blog',
                'type_color' => 'green',
                'title'      => $p->title,
                'status'     => $p->status,
                'date'       => $p->published_at,
                'url'        => route('admin.posts.edit', $p),
            ]);

        $stories = SocialStory::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->take(20)->get()
            ->map(fn($s) => [
                'type'       => 'story',
                'type_label' => 'Historia',
                'type_color' => 'purple',
                'title'      => $s->headline ?? 'Historia',
                'status'     => $s->status,
                'date'       => $s->scheduled_at,
                'url'        => route('admin.social.stories.show', $s),
            ]);

        $items = $fbPosts->concat($carousels)->concat($blog)->concat($stories)
            ->sortBy('date')
            ->values();

        return view('admin.social.upcoming', compact('items'));
    }
}
