<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarouselPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CarouselApprovalController extends Controller
{
    /** Approve a carousel and fire n8n webhook */
    public function approve(Request $request, CarouselPost $carousel): RedirectResponse
    {
        abort_if($carousel->status !== 'review', 403, 'El carrusel no está en revisión.');

        $carousel->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        $this->fireWebhook($carousel);

        return back()->with('success', 'Carrusel aprobado. El webhook a n8n fue enviado.');
    }

    /** Reject: revert to draft */
    public function reject(CarouselPost $carousel): RedirectResponse
    {
        abort_if(!in_array($carousel->status, ['review', 'approved']), 403);

        $carousel->update([
            'status'      => 'draft',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return back()->with('success', 'Carrusel rechazado y devuelto a borrador.');
    }

    /** Re-fire the n8n webhook for an already-approved carousel */
    public function webhook(CarouselPost $carousel): RedirectResponse
    {
        abort_if($carousel->status !== 'approved', 403, 'Solo se puede re-enviar un carrusel aprobado.');

        $this->fireWebhook($carousel);

        return back()->with('success', 'Webhook re-enviado a n8n.');
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function fireWebhook(CarouselPost $carousel): void
    {
        $webhookUrl = config('services.n8n.carousel_webhook_url');

        if (!$webhookUrl) {
            Log::info('CarouselApprovalController: N8N_CAROUSEL_WEBHOOK_URL not set, skipping webhook.', [
                'carousel_id' => $carousel->id,
            ]);
            return;
        }

        $carousel->load(['slides' => fn ($q) => $q->orderBy('order'), 'user', 'approvedBy']);

        $payload = [
            'carousel_id'    => $carousel->id,
            'title'          => $carousel->title,
            'type'           => $carousel->type,
            'caption_short'  => $carousel->caption_short,
            'caption_long'   => $carousel->caption_long,
            'hashtags'       => $carousel->hashtags_string,
            'cta'            => $carousel->cta,
            'approved_at'    => $carousel->approved_at?->toIso8601String(),
            'approved_by'    => $carousel->approvedBy?->name,
            'slides'         => $carousel->slides->map(fn ($s) => [
                'order'      => $s->order,
                'type'       => $s->type,
                'headline'   => $s->headline,
                'image_url'  => $s->rendered_image_path
                                    ? \Storage::url($s->rendered_image_path)
                                    : null,
            ]),
        ];

        try {
            Http::timeout(15)->post($webhookUrl, $payload);
        } catch (\Throwable $e) {
            Log::warning('CarouselApprovalController webhook failed', [
                'carousel_id' => $carousel->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
