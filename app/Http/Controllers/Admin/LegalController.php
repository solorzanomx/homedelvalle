<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalAcceptance;
use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LegalController extends Controller
{
    /**
     * List all legal documents with stats.
     */
    public function index()
    {
        $documents = LegalDocument::with('currentVersion')
            ->withCount(['versions', 'acceptances'])
            ->latest()
            ->paginate(20);

        $totalDocuments = LegalDocument::count();
        $publishedCount = LegalDocument::where('status', 'published')->count();
        $totalVersions = \App\Models\LegalDocumentVersion::count();
        $totalAcceptances = LegalAcceptance::count();

        return view('admin.legal.index', compact('documents', 'totalDocuments', 'publishedCount', 'totalVersions', 'totalAcceptances'));
    }

    /**
     * Show the form to create a new legal document.
     */
    public function create()
    {
        $types = LegalDocument::TYPES;

        return view('admin.legal.create', compact('types'));
    }

    /**
     * Store a newly created legal document with its first version.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_keys(LegalDocument::TYPES)),
            'content' => 'required|string',
            'is_public' => 'boolean',
            'meta_description' => 'nullable|string',
        ]);

        // Auto-generate unique slug from the title
        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 1;
        while (LegalDocument::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $document = LegalDocument::create([
            'title' => $validated['title'],
            'slug' => Str::limit($slug, 120, ''),
            'type' => $validated['type'],
            'is_public' => $request->boolean('is_public', true),
            'status' => 'published',
            'meta_description' => $validated['meta_description'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Create the first version
        $document->createNewVersion(
            $validated['content'],
            'Versión inicial',
            auth()->id()
        );

        return redirect()
            ->route('admin.legal.edit', $document)
            ->with('success', 'Documento legal creado correctamente.');
    }

    /**
     * Show a legal document with its versions and acceptance stats.
     */
    public function show(LegalDocument $document)
    {
        $document->load([
            'versions' => fn($q) => $q->latest('version_number'),
            'versions.creator',
            'creator',
        ]);

        $acceptanceStats = [
            'total' => $document->acceptances()->count(),
            'last_30_days' => $document->acceptances()
                ->where('accepted_at', '>=', now()->subDays(30))
                ->count(),
            'unique_emails' => $document->acceptances()
                ->distinct('email')
                ->count('email'),
        ];

        $recentAcceptances = $document->acceptances()
            ->with('version')
            ->latest('accepted_at')
            ->limit(20)
            ->get();

        return view('admin.legal.show', compact('document', 'acceptanceStats', 'recentAcceptances'));
    }

    /**
     * Show the form to edit a legal document.
     */
    public function edit(LegalDocument $document)
    {
        $document->load('currentVersion');
        $types = LegalDocument::TYPES;

        return view('admin.legal.edit', compact('document', 'types'));
    }

    /**
     * Update a legal document. Creates a new version if content changed.
     */
    public function update(Request $request, LegalDocument $document)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', array_keys(LegalDocument::TYPES)),
            'content' => 'required|string',
            'is_public' => 'boolean',
            'meta_description' => 'nullable|string',
            'change_notes' => 'nullable|string|max:500',
        ]);

        // Update document fields
        $document->update([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'is_public' => $request->boolean('is_public', true),
            'meta_description' => $validated['meta_description'] ?? null,
        ]);

        // If content changed from the current version, create a new version
        $currentContent = $document->currentVersion?->content;
        if ($currentContent !== $validated['content']) {
            $document->createNewVersion(
                $validated['content'],
                $validated['change_notes'] ?? null,
                auth()->id()
            );
        }

        return redirect()
            ->route('admin.legal.edit', $document)
            ->with('success', 'Documento legal actualizado correctamente.');
    }

    /**
     * Delete a legal document (only if no acceptances exist).
     */
    public function destroy(LegalDocument $document)
    {
        if ($document->acceptances()->exists()) {
            return redirect()
                ->route('admin.legal.index')
                ->with('error', 'No se puede eliminar un documento que tiene aceptaciones registradas.');
        }

        $document->delete();

        return redirect()
            ->route('admin.legal.index')
            ->with('success', 'Documento legal eliminado correctamente.');
    }

    /**
     * Paginated list of acceptances for a specific document.
     */
    public function acceptances(LegalDocument $document)
    {
        $acceptances = $document->acceptances()
            ->with('version')
            ->latest('accepted_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.legal.acceptances', compact('document', 'acceptances'));
    }

    /**
     * All acceptances across all documents, with filters.
     */
    public function allAcceptances(Request $request)
    {
        $query = LegalAcceptance::with(['document', 'version'])
            ->latest('accepted_at');

        // Filter by document
        if ($request->filled('document_id')) {
            $query->where('legal_document_id', $request->document_id);
        }

        // Filter by email
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('accepted_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('accepted_at', '<=', $request->date_to . ' 23:59:59');
        }

        $acceptances = $query->paginate(30)->withQueryString();
        $documents = LegalDocument::orderBy('title')->get(['id', 'title']);
        $totalAcceptances = LegalAcceptance::count();
        $thisMonth = LegalAcceptance::where('accepted_at', '>=', now()->startOfMonth())->count();
        $uniqueDocuments = LegalAcceptance::distinct('legal_document_id')->count('legal_document_id');

        return view('admin.legal.all-acceptances', compact('acceptances', 'documents', 'totalAcceptances', 'thisMonth', 'uniqueDocuments'));
    }
}
