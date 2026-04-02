<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::orderBy('sort_order')->paginate(20);

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages',
            'body' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'use_sections' => 'boolean',
            'sections_json' => 'nullable|string',
            'is_landing' => 'boolean',
            'landing_hide_header' => 'boolean',
            'landing_hide_footer' => 'boolean',
            'landing_custom_css' => 'nullable|string|max:5000',
            'is_published' => 'boolean',
            'sort_order' => 'nullable|integer',
            'show_in_nav' => 'boolean',
            'nav_order' => 'nullable|integer',
            'nav_label' => 'nullable|string|max:50',
            'nav_url' => 'nullable|string|max:255',
            'nav_route' => 'nullable|string|max:255',
            'nav_style' => 'nullable|string|in:link,button,muted',
        ]);

        $validated['is_published'] = $request->boolean('is_published');
        $validated['show_in_nav'] = $request->boolean('show_in_nav');
        $validated['use_sections'] = $request->boolean('use_sections');
        $validated['is_landing'] = $request->boolean('is_landing');

        $validated['landing_settings'] = [
            'hide_header' => $request->boolean('landing_hide_header'),
            'hide_footer' => $request->boolean('landing_hide_footer'),
            'custom_css' => $request->input('landing_custom_css', ''),
        ];
        unset($validated['landing_hide_header'], $validated['landing_hide_footer'], $validated['landing_custom_css']);

        if ($validated['use_sections'] && !empty($validated['sections_json'])) {
            $validated['sections'] = json_decode($validated['sections_json'], true);
        }
        unset($validated['sections_json']);

        if (!$validated['use_sections'] && empty($validated['body'])) {
            $validated['body'] = '';
        }

        Page::create($validated);

        cache()->forget('nav_items');

        return redirect()->route('admin.pages.index')->with('success', 'Pagina creada correctamente.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug,' . $page->id,
            'body' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
            'use_sections' => 'boolean',
            'sections_json' => 'nullable|string',
            'is_landing' => 'boolean',
            'landing_hide_header' => 'boolean',
            'landing_hide_footer' => 'boolean',
            'landing_custom_css' => 'nullable|string|max:5000',
            'is_published' => 'boolean',
            'sort_order' => 'nullable|integer',
            'show_in_nav' => 'boolean',
            'nav_order' => 'nullable|integer',
            'nav_label' => 'nullable|string|max:50',
            'nav_url' => 'nullable|string|max:255',
            'nav_route' => 'nullable|string|max:255',
            'nav_style' => 'nullable|string|in:link,button,muted',
        ]);

        $validated['is_published'] = $request->boolean('is_published');
        $validated['show_in_nav'] = $request->boolean('show_in_nav');
        $validated['use_sections'] = $request->boolean('use_sections');
        $validated['is_landing'] = $request->boolean('is_landing');

        $validated['landing_settings'] = [
            'hide_header' => $request->boolean('landing_hide_header'),
            'hide_footer' => $request->boolean('landing_hide_footer'),
            'custom_css' => $request->input('landing_custom_css', ''),
        ];
        unset($validated['landing_hide_header'], $validated['landing_hide_footer'], $validated['landing_custom_css']);

        if ($validated['use_sections'] && !empty($validated['sections_json'])) {
            $validated['sections'] = json_decode($validated['sections_json'], true);
        } elseif (!$validated['use_sections']) {
            $validated['sections'] = null;
        }
        unset($validated['sections_json']);

        $page->update($validated);

        cache()->forget('nav_items');

        return redirect()->route('admin.pages.index')->with('success', 'Pagina actualizada correctamente.');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        cache()->forget('nav_items');

        return redirect()->route('admin.pages.index')->with('success', 'Pagina eliminada correctamente.');
    }
}
