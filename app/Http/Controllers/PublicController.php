<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use App\Models\Broker;
use App\Models\ContactSubmission;
use App\Models\NewsletterSubscriber;
use App\Models\Property;
use App\Models\User;
use App\Services\AutomationEngine;
use App\Services\SpamProtectionService;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function propiedades(Request $request)
    {
        $query = Property::available();

        if ($request->filled('operation_type')) {
            $query->where('operation_type', $request->operation_type);
        }
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('city', 'like', "%{$search}%")
                  ->orWhere('colony', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        $query = match($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default => $query->latest(),
        };

        $totalCount = $query->count();
        $properties = $query->paginate(12)->withQueryString();

        return view('public.propiedades', compact('properties', 'totalCount'));
    }

    public function propiedadShow(int $id, string $slug = null)
    {
        $property = Property::findOrFail($id);

        $similar = Property::available()
            ->where('id', '!=', $property->id)
            ->where(fn($q) => $q->where('operation_type', $property->operation_type)
                                ->orWhere('property_type', $property->property_type))
            ->latest()
            ->take(3)
            ->get();

        return view('public.propiedad', compact('property', 'similar'));
    }

    public function nosotros()
    {
        $teamMembers = User::where('show_on_website', true)
            ->where('is_active', true)
            ->orderBy('website_order')
            ->orderBy('name')
            ->get();

        return view('public.nosotros', compact('teamMembers'));
    }

    public function servicios()
    {
        return view('public.servicios');
    }

    public function contacto()
    {
        return view('public.contacto');
    }

    public function contactoStore(ContactFormRequest $request, SpamProtectionService $spam, AutomationEngine $engine)
    {
        $validated = $request->validated();

        // Honeypot check — bots fill hidden fields
        if ($request->filled('website_url')) {
            return redirect()->back()->with('success', 'Mensaje enviado correctamente.');
        }

        // Spam protection (reCAPTCHA + content analysis + IP rate)
        $spamCheck = $spam->check(
            $validated,
            $request->input('recaptcha_token'),
            $request->ip(),
            'contact'
        );

        if (! $spamCheck['pass']) {
            // Return fake success so bots don't know they were blocked
            return redirect()->back()->with('success', '¡Gracias por tu mensaje! Te contactaremos pronto.');
        }

        ContactSubmission::create($validated);

        // Trigger automation engine — enroll lead
        $source = $request->input('form_source', 'contact');
        $engine->processFormSubmitted($validated, $source);

        // Record privacy acceptance if checkbox was checked
        if ($request->boolean('accept_privacy')) {
            $privacyDoc = \App\Models\LegalDocument::where('type', 'aviso_privacidad')
                ->where('status', 'published')
                ->first();
            if ($privacyDoc && $privacyDoc->current_version_id) {
                \App\Models\LegalAcceptance::record(
                    $privacyDoc->id,
                    $privacyDoc->current_version_id,
                    $validated['email'],
                    $request,
                    'contacto',
                    ['name' => $validated['name']]
                );
            }
        }

        return redirect()->back()->with('success', '¡Gracias por tu mensaje! Te contactaremos pronto.');
    }

    public function newsletterSubscribe(Request $request, SpamProtectionService $spam)
    {
        $request->validate([
            'email' => 'required|email:rfc,dns|max:255',
            'source' => 'nullable|string|max:50',
        ]);

        // Honeypot
        if ($request->filled('website_url')) {
            return response()->json(['ok' => true]);
        }

        // Disposable email check
        if ($spam->isDisposableEmail($request->email)) {
            return response()->json(['ok' => true]);
        }

        NewsletterSubscriber::updateOrCreate(
            ['email' => $request->email],
            [
                'source' => $request->input('source', 'popup'),
                'ip_address' => $request->ip(),
                'unsubscribed_at' => null,
            ]
        );

        return response()->json(['ok' => true, 'message' => '¡Te has suscrito exitosamente!']);
    }

    public function newsletterUnsubscribe(string $token)
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->first();

        if (! $subscriber) {
            return view('public.newsletter-unsubscribe', ['status' => 'invalid']);
        }

        if ($subscriber->unsubscribed_at) {
            return view('public.newsletter-unsubscribe', ['status' => 'already']);
        }

        $subscriber->update(['unsubscribed_at' => now()]);

        return view('public.newsletter-unsubscribe', ['status' => 'success']);
    }
}
