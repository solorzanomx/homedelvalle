<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HelpArticle;
use App\Models\HelpCategory;
use App\Models\HelpTip;
use App\Models\HelpOnboardingProgress;
use Illuminate\Http\Request;

class HelpCenterController extends Controller
{
    // ── User-facing ───────────────────────────────────

    public function index()
    {
        $categories = HelpCategory::with('publishedArticles')
            ->orderBy('sort_order')
            ->get();

        $onboarding = HelpOnboardingProgress::firstOrCreate(
            ['user_id' => auth()->id()],
            ['completed_steps' => []]
        );

        return view('admin.help.index', compact('categories', 'onboarding'));
    }

    public function show(HelpArticle $article)
    {
        $article->recordView();
        $article->load('category');

        $relatedArticles = HelpArticle::published()
            ->where('help_category_id', $article->help_category_id)
            ->where('id', '!=', $article->id)
            ->orderBy('sort_order')
            ->limit(5)
            ->get();

        return view('admin.help.article', compact('article', 'relatedArticles'));
    }

    public function tips(string $context)
    {
        $tips = HelpTip::forContext($context)->get();
        return response()->json($tips);
    }

    public function completeStep(Request $request)
    {
        $step = $request->input('step');
        $onboarding = HelpOnboardingProgress::firstOrCreate(
            ['user_id' => auth()->id()],
            ['completed_steps' => []]
        );

        $onboarding->completeStep($step);

        return response()->json([
            'progress' => $onboarding->getProgressPercent(),
            'completed' => $onboarding->is_completed,
        ]);
    }

    // ── Admin management ──────────────────────────────

    public function adminIndex()
    {
        $categories = HelpCategory::withCount('articles')->orderBy('sort_order')->get();
        $articles = HelpArticle::with('category')->orderBy('help_category_id')->orderBy('sort_order')->get();
        $tips = HelpTip::orderBy('context')->get();

        return view('admin.help.manage', compact('categories', 'articles', 'tips'));
    }

    public function storeArticle(Request $request)
    {
        $validated = $request->validate([
            'help_category_id' => 'required|exists:help_categories,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ]);

        $validated['slug'] = \Str::slug($validated['title']);
        $validated['is_published'] = $request->boolean('is_published', true);

        HelpArticle::create($validated);

        return back()->with('success', 'Articulo creado');
    }

    public function updateArticle(Request $request, HelpArticle $article)
    {
        $validated = $request->validate([
            'help_category_id' => 'required|exists:help_categories,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ]);

        $validated['is_published'] = $request->boolean('is_published', true);
        $article->update($validated);

        return back()->with('success', 'Articulo actualizado');
    }

    public function destroyArticle(HelpArticle $article)
    {
        $article->delete();
        return back()->with('success', 'Articulo eliminado');
    }

    public function storeTip(Request $request)
    {
        $validated = $request->validate([
            'context' => 'required|string|max:80',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'in:tip,warning,pro_tip',
        ]);

        HelpTip::create($validated);

        return back()->with('success', 'Tip creado');
    }

    public function destroyTip(HelpTip $tip)
    {
        $tip->delete();
        return back()->with('success', 'Tip eliminado');
    }
}
