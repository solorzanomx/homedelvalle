<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarouselPost;
use App\Models\CarouselTemplate;
use Illuminate\Http\Request;

class CarouselController extends Controller
{
    public function index(Request $request)
    {
        $query = CarouselPost::with(['template', 'user'])
            ->withCount('slides')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $carousels = $query->paginate(20)->withQueryString();

        return view('admin.carousels.index', compact('carousels'));
    }

    public function create()
    {
        $templates = CarouselTemplate::active()->get();
        return view('admin.carousels.create', compact('templates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:commercial,educational,capture,informative,branding',
            'source_type' => 'nullable|in:property,blog_post,free',
            'source_id'   => 'nullable|integer',
            'template_id' => 'nullable|exists:carousel_templates,id',
            'caption_short' => 'nullable|string|max:280',
            'caption_long'  => 'nullable|string',
            'cta'           => 'nullable|string|max:255',
        ]);

        $data['user_id'] = auth()->id();
        $data['status']  = 'draft';

        $carousel = CarouselPost::create($data);

        return redirect()
            ->route('admin.carousels.show', $carousel)
            ->with('success', 'Carrusel creado correctamente.');
    }

    public function show(CarouselPost $carousel)
    {
        $carousel->load(['template', 'user', 'approvedBy', 'slides', 'versions', 'publications']);
        return view('admin.carousels.show', compact('carousel'));
    }

    public function edit(CarouselPost $carousel)
    {
        $templates = CarouselTemplate::active()->get();
        $carousel->load('slides');
        return view('admin.carousels.edit', compact('carousel', 'templates'));
    }

    public function update(Request $request, CarouselPost $carousel)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'type'          => 'required|in:commercial,educational,capture,informative,branding',
            'source_type'   => 'nullable|in:property,blog_post,free',
            'source_id'     => 'nullable|integer',
            'template_id'   => 'nullable|exists:carousel_templates,id',
            'caption_short' => 'nullable|string|max:280',
            'caption_long'  => 'nullable|string',
            'cta'           => 'nullable|string|max:255',
            'status'        => 'sometimes|in:draft,review,approved,archived',
        ]);

        $carousel->update($data);

        return redirect()
            ->route('admin.carousels.show', $carousel)
            ->with('success', 'Carrusel actualizado correctamente.');
    }

    public function destroy(CarouselPost $carousel)
    {
        $carousel->delete();

        return redirect()
            ->route('admin.carousels.index')
            ->with('success', 'Carrusel eliminado.');
    }
}
