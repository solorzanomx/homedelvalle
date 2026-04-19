<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\AutomationEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function lead(Request $request, AutomationEngine $engine): JsonResponse
    {
        // Authenticate via X-Webhook-Secret header
        $settings = SiteSetting::first();
        $secret = $settings?->webhook_api_key;
        $enabled = $settings?->webhook_enabled;

        if (!$enabled || !$secret || $request->header('X-Webhook-Secret') !== $secret) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Force JSON responses for validation errors
        $request->headers->set('Accept', 'application/json');

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255',
            'phone'         => 'nullable|string|max:50',
            'source'        => 'nullable|string|max:100',
            'lead_type'     => 'nullable|string|in:comprador,vendedor,desarrollador',
            'interest'      => 'nullable|string|in:compra,renta,venta',
            'budget'        => 'nullable|numeric|min:0',
            'zone'          => 'nullable|string|max:255',
            'property_type' => 'nullable|string|max:100',
            'message'       => 'nullable|string|max:2000',
            'utm_source'    => 'nullable|string|max:100',
            'utm_medium'    => 'nullable|string|max:100',
            'utm_campaign'  => 'nullable|string|max:100',
        ]);

        // Map webhook fields to the format processFormSubmitted expects
        $data = [
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'phone'        => $validated['phone'] ?? null,
            'message'      => $validated['message'] ?? null,
            'utm_source'   => $validated['utm_source'] ?? null,
            'utm_medium'   => $validated['utm_medium'] ?? null,
            'utm_campaign' => $validated['utm_campaign'] ?? null,
        ];

        $source = $validated['source'] ?? 'webhook';

        // Create/find client and enroll in automations
        $client = $engine->processFormSubmitted($data, $source);

        if (!$client) {
            return response()->json(['error' => 'No se pudo crear el lead'], 422);
        }

        // Apply extra fields that processFormSubmitted doesn't handle
        $extra = [];
        if (!empty($validated['budget'])) {
            $extra['budget_min'] = $validated['budget'];
        }
        if (!empty($validated['zone'])) {
            $extra['city'] = $validated['zone'];
        }
        if (!empty($validated['interest'])) {
            $extra['interest_types'] = $validated['interest'];
        }
        if (!empty($validated['property_type'])) {
            $extra['property_type'] = $validated['property_type'];
        }
        if (!empty($validated['lead_type'])) {
            $extra['initial_notes'] = trim(
                ($client->initial_notes ?? '') . "\nTipo: " . $validated['lead_type']
            );
        }
        if (!empty($validated['message']) && $client->wasRecentlyCreated) {
            $extra['initial_notes'] = trim(
                ($extra['initial_notes'] ?? $client->initial_notes ?? '') . "\n" . $validated['message']
            );
        }

        if ($extra) {
            $client->update($extra);
        }

        return response()->json([
            'success'   => true,
            'client_id' => $client->id,
            'message'   => 'Lead creado correctamente',
        ], 201);
    }
}
