<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = FormSubmission::query()->latest();

        if ($type = $request->get('type')) {
            $query->where('form_type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($tag = $request->get('tag')) {
            $query->where('lead_tag', $tag);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email',    'like', "%{$search}%")
                  ->orWhere('phone',    'like', "%{$search}%");
            });
        }

        $submissions = $query->paginate(25)->withQueryString();

        $counts = [
            'total'     => FormSubmission::count(),
            'new'       => FormSubmission::where('status', 'new')->count(),
            'vendedor'  => FormSubmission::where('form_type', 'vendedor')->count(),
            'comprador' => FormSubmission::where('form_type', 'comprador')->count(),
            'b2b'       => FormSubmission::where('form_type', 'b2b')->count(),
            'contacto'  => FormSubmission::where('form_type', 'contacto')->count(),
        ];

        return view('admin.form-submissions.index', compact('submissions', 'counts'));
    }

    public function show(FormSubmission $formSubmission)
    {
        return view('admin.form-submissions.show', ['submission' => $formSubmission]);
    }

    public function updateStatus(Request $request, FormSubmission $formSubmission)
    {
        $request->validate(['status' => 'required|in:new,contacted,qualified,won,lost']);
        $formSubmission->update([
            'status'       => $request->status,
            'contacted_at' => $request->status === 'contacted' && !$formSubmission->contacted_at ? now() : $formSubmission->contacted_at,
        ]);
        return back()->with('success', 'Estado actualizado');
    }

    public function updateNotes(Request $request, FormSubmission $formSubmission)
    {
        $request->validate(['notes' => 'nullable|string|max:2000']);
        $formSubmission->update(['notes' => $request->notes]);
        return back()->with('success', 'Notas guardadas');
    }
}
