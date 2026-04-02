<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\Request;

class ContactSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactSubmission::with('property')->latest();

        if ($request->filled('status')) {
            $query->where('is_read', $request->status === 'read');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
            );
        }

        $submissions = $query->paginate(20)->withQueryString();
        $unreadCount = ContactSubmission::where('is_read', false)->count();

        return view('admin.submissions.index', compact('submissions', 'unreadCount'));
    }

    public function show(ContactSubmission $submission)
    {
        if (!$submission->is_read) {
            $submission->update(['is_read' => true]);
        }

        $submission->load('property');

        return view('admin.submissions.show', compact('submission'));
    }

    public function destroy(ContactSubmission $submission)
    {
        $submission->delete();

        return redirect()->route('admin.submissions.index')->with('success', 'Lead eliminado correctamente.');
    }
}
