<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostCategoryController extends Controller
{
    public function index()
    {
        $categories = PostCategory::withCount('posts')->orderBy('name')->get();

        return view('admin.posts.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:post_categories,name',
            'slug' => 'nullable|string|max:100|unique:post_categories,slug',
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        PostCategory::create($validated);

        return back()->with('success', 'Categoria creada correctamente.');
    }

    public function update(Request $request, PostCategory $postCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:post_categories,name,' . $postCategory->id,
            'slug' => 'nullable|string|max:100|unique:post_categories,slug,' . $postCategory->id,
            'description' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $postCategory->update($validated);

        return back()->with('success', 'Categoria actualizada correctamente.');
    }

    public function destroy(PostCategory $postCategory)
    {
        $postCategory->delete();

        return back()->with('success', 'Categoria eliminada correctamente.');
    }
}
