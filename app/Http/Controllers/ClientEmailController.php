<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientEmail;
use App\Models\Interaction;
use App\Models\Property;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientEmailController extends Controller
{
    /**
     * Show compose form for a specific client.
     */
    public function compose(Client $client)
    {
        $properties = Property::orderBy('title')->get();
        $user = Auth::user();

        // Check user-level SMTP first, then fall back to global SMTP
        $hasUserSmtp = $user->mailSetting && $user->mailSetting->is_active && $user->mailSetting->isConfigured();
        $globalSmtp = \App\Models\EmailSetting::first();
        $hasGlobalSmtp = $globalSmtp && $globalSmtp->from_email && $globalSmtp->smtp_server;
        $hasSmtp = $hasUserSmtp || $hasGlobalSmtp;

        return view('clients.compose-email', compact('client', 'properties', 'hasSmtp'));
    }

    /**
     * Send an email to the client with selected properties.
     */
    public function send(Request $request, Client $client)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'property_ids' => 'nullable|array',
            'property_ids.*' => 'exists:properties,id',
        ]);

        $user = Auth::user();
        $propertyIds = $validated['property_ids'] ?? [];

        // Build HTML body with properties
        $bodyHtml = $this->buildEmailBody(
            $validated['message'],
            $propertyIds,
            $user,
            $client
        );

        // Create record
        $clientEmail = ClientEmail::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'subject' => $validated['subject'],
            'body_html' => $bodyHtml,
            'property_ids' => $propertyIds ?: null,
            'status' => 'pending',
        ]);

        // Add tracking pixel to HTML
        $trackingUrl = route('email.track', $clientEmail->tracking_id);
        $bodyWithTracking = $bodyHtml . '<img src="' . $trackingUrl . '" width="1" height="1" style="display:none;" alt="">';

        // Send via EmailService with user's SMTP
        $emailService = app(EmailService::class);
        $sent = $emailService->send(
            $client->email,
            $validated['subject'],
            $bodyWithTracking,
            $client->name,
            null,
            $user
        );

        $clientEmail->update(['status' => $sent ? 'sent' : 'failed']);

        // Log interaction
        $propertyNames = '';
        if (!empty($propertyIds)) {
            $propertyNames = Property::whereIn('id', $propertyIds)->pluck('title')->join(', ');
        }

        Interaction::create([
            'client_id' => $client->id,
            'user_id' => $user->id,
            'type' => 'email',
            'description' => 'Correo enviado: "' . $validated['subject'] . '"'
                . ($propertyNames ? ' | Propiedades: ' . $propertyNames : ''),
            'completed_at' => now(),
        ]);

        if ($sent) {
            return redirect()->route('clients.show', $client)
                ->with('success', 'Correo enviado a ' . $client->email);
        }

        return redirect()->route('clients.show', $client)
            ->with('error', 'No se pudo enviar el correo. Verifica tu configuracion SMTP en tu perfil.');
    }

    /**
     * Open tracking pixel — returns 1x1 transparent GIF.
     */
    public function track(string $trackingId)
    {
        $email = ClientEmail::where('tracking_id', $trackingId)->first();

        if ($email) {
            $email->markAsOpened();
        }

        // 1x1 transparent GIF
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($pixel, 200, [
            'Content-Type' => 'image/gif',
            'Content-Length' => strlen($pixel),
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * View a sent email.
     */
    public function show(ClientEmail $email)
    {
        return view('clients.email-detail', compact('email'));
    }

    /**
     * Build the HTML email body with property cards.
     */
    private function buildEmailBody(string $message, array $propertyIds, $user, Client $client): string
    {
        $siteName = \App\Models\SiteSetting::first()?->site_name ?? 'Homedelvalle';
        $senderName = $user->full_name ?? $user->name;
        $senderTitle = $user->title ?? 'Asesor Inmobiliario';
        $senderPhone = $user->phone ?? '';
        $senderEmail = $user->mailSetting?->from_email ?? $user->email;

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        $html .= '<style>
            body { font-family: Arial, Helvetica, sans-serif; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
            .email-wrap { max-width: 640px; margin: 0 auto; background: #fff; }
            .email-header { background: linear-gradient(135deg, #3B82C4, #1E3A5F); padding: 24px 32px; color: #fff; }
            .email-header h1 { margin: 0; font-size: 20px; }
            .email-body { padding: 32px; }
            .email-body p { line-height: 1.6; margin: 0 0 16px; }
            .prop-card { border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin: 16px 0; }
            .prop-img { width: 100%; height: 200px; object-fit: cover; display: block; }
            .prop-info { padding: 16px; }
            .prop-title { font-size: 16px; font-weight: 600; margin: 0 0 4px; color: #1e293b; }
            .prop-price { font-size: 18px; font-weight: 700; color: #3B82C4; margin: 0 0 8px; }
            .prop-details { font-size: 13px; color: #64748b; margin: 0; }
            .email-footer { background: #f8fafc; padding: 24px 32px; border-top: 1px solid #e2e8f0; font-size: 13px; color: #64748b; }
            .sig-name { font-weight: 600; color: #1e293b; font-size: 14px; }
        </style></head><body>';

        $html .= '<div class="email-wrap">';
        $html .= '<div class="email-header"><h1>' . e($siteName) . '</h1></div>';
        $html .= '<div class="email-body">';
        $html .= '<p>Hola <strong>' . e($client->name) . '</strong>,</p>';
        $html .= '<p>' . nl2br(e($message)) . '</p>';

        // Property cards
        if (!empty($propertyIds)) {
            $properties = Property::whereIn('id', $propertyIds)->get();
            foreach ($properties as $prop) {
                $html .= '<div class="prop-card">';
                if ($prop->photo) {
                    $photoUrl = asset('storage/' . $prop->photo);
                    $html .= '<img class="prop-img" src="' . $photoUrl . '" alt="' . e($prop->title) . '">';
                }
                $html .= '<div class="prop-info">';
                $html .= '<p class="prop-title">' . e($prop->title) . '</p>';
                $html .= '<p class="prop-price">' . $prop->formatted_price . ' &middot; ' . $prop->operation_label . '</p>';
                $details = [];
                if ($prop->bedrooms) $details[] = $prop->bedrooms . ' rec.';
                if ($prop->bathrooms) $details[] = $prop->bathrooms . ' banos';
                if ($prop->area) $details[] = number_format($prop->area) . ' m²';
                if ($prop->city) $details[] = $prop->city;
                $html .= '<p class="prop-details">' . implode(' &middot; ', $details) . '</p>';
                $html .= '</div></div>';
            }
        }

        // Signature
        $html .= '<div style="margin-top:32px; padding-top:16px; border-top:1px solid #e2e8f0;">';
        if ($user->email_signature) {
            $html .= $user->email_signature;
        } else {
            $html .= '<p class="sig-name">' . e($senderName) . '</p>';
            $html .= '<p style="margin:0; font-size:13px; color:#64748b;">' . e($senderTitle) . '</p>';
            if ($senderPhone) {
                $html .= '<p style="margin:2px 0 0; font-size:13px; color:#64748b;">' . e($senderPhone) . '</p>';
            }
            $html .= '<p style="margin:2px 0 0; font-size:13px; color:#3B82C4;">' . e($senderEmail) . '</p>';
        }
        $html .= '</div>';

        $html .= '</div>'; // email-body
        $html .= '<div class="email-footer">';
        $html .= '<p style="margin:0;">' . e($siteName) . ' &middot; Tu plataforma de gestion inmobiliaria</p>';
        $html .= '</div>';
        $html .= '</div></body></html>';

        return $html;
    }
}
