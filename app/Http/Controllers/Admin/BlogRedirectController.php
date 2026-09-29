<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogRedirect;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlogRedirectController extends Controller
{
    public function index()
    {
        $redirects = BlogRedirect::orderByDesc('hits')->orderByDesc('created_at')->paginate(50);

        return view('admin.blog-redirects.index', compact('redirects'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        BlogRedirect::create($validated + ['active' => true]);

        return back()->with('success', 'Redirect creado.');
    }

    public function update(Request $request, BlogRedirect $blogRedirect)
    {
        $validated = $this->validated($request, $blogRedirect->id);

        $blogRedirect->update($validated);

        return back()->with('success', 'Redirect actualizado.');
    }

    public function toggle(BlogRedirect $blogRedirect)
    {
        $blogRedirect->update(['active' => ! $blogRedirect->active]);

        return back()->with('success', $blogRedirect->active ? 'Redirect activado.' : 'Redirect desactivado.');
    }

    public function destroy(BlogRedirect $blogRedirect)
    {
        $blogRedirect->delete();

        return back()->with('success', 'Redirect eliminado.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:255', Rule::unique('blog_redirects', 'from_path')->ignore($ignoreId)],
            'to_path'   => ['required', 'string', 'max:255'],
            'status'    => ['required', 'in:301,410'],
            'notes'     => ['nullable', 'string', 'max:255'],
        ]);

        $data['from_path'] = \App\Models\BlogRedirect::normalize($data['from_path']);
        // to_path puede apuntar fuera de /blog (p. ej. a una landing) — solo normalizamos si es de blog.
        if (str_starts_with(ltrim($data['to_path'], '/'), 'blog/')) {
            $data['to_path'] = \App\Models\BlogRedirect::normalize($data['to_path']);
        }

        return $data;
    }
}
