<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use App\Models\Message;
use App\Services\EmailService;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    // ── Subscribers ─────────────────────────────────

    public function index(Request $request)
    {
        $query = NewsletterSubscriber::with('client');

        if ($request->filled('search')) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }

        if ($request->input('status') === 'subscribed') {
            $query->active();
        } elseif ($request->input('status') === 'unsubscribed') {
            $query->unsubscribed();
        }

        $subscribers = $query->latest()->paginate(30)->withQueryString();

        return view('admin.newsletters.subscribers', [
            'subscribers' => $subscribers,
            'totalCount' => NewsletterSubscriber::count(),
            'activeCount' => NewsletterSubscriber::active()->count(),
            'unsubscribedCount' => NewsletterSubscriber::unsubscribed()->count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email',
            'source' => 'nullable|string|max:50',
        ]);

        NewsletterSubscriber::create([
            'email' => $request->email,
            'source' => $request->input('source', 'manual'),
            'subscribed_at' => now(),
        ]);

        return redirect()->route('admin.newsletters.subscribers')->with('success', 'Suscriptor agregado.');
    }

    public function destroy(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();
        return redirect()->route('admin.newsletters.subscribers')->with('success', 'Suscriptor eliminado.');
    }

    public function export(Request $request)
    {
        $query = NewsletterSubscriber::with('client');

        if ($request->input('status') === 'subscribed') {
            $query->active();
        } elseif ($request->input('status') === 'unsubscribed') {
            $query->unsubscribed();
        }

        $subscribers = $query->orderBy('email')->get();

        return response()->streamDownload(function () use ($subscribers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Email', 'Fuente', 'Suscrito', 'Desuscrito', 'Cliente']);

            foreach ($subscribers as $sub) {
                fputcsv($handle, [
                    $sub->email,
                    $sub->source ?? '-',
                    $sub->subscribed_at?->format('d/m/Y H:i') ?? '-',
                    $sub->unsubscribed_at?->format('d/m/Y H:i') ?? '-',
                    $sub->client?->name ?? '-',
                ]);
            }

            fclose($handle);
        }, 'suscriptores-newsletter-' . date('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    // ── Campaigns ───────────────────────────────────

    public function campaigns()
    {
        $campaigns = NewsletterCampaign::with('creator')->latest()->paginate(20);
        return view('admin.newsletters.campaigns', compact('campaigns'));
    }

    public function createCampaign()
    {
        return view('admin.newsletters.campaign-form', [
            'campaign' => null,
            'activeSubscribers' => NewsletterSubscriber::active()->count(),
        ]);
    }

    public function storeCampaign(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $campaign = NewsletterCampaign::create([
            'subject' => $request->subject,
            'body' => $request->body,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.newsletters.campaigns.show', $campaign)->with('success', 'Campana creada como borrador.');
    }

    public function showCampaign(NewsletterCampaign $campaign)
    {
        return view('admin.newsletters.campaign-show', [
            'campaign' => $campaign,
            'activeSubscribers' => NewsletterSubscriber::active()->count(),
        ]);
    }

    public function editCampaign(NewsletterCampaign $campaign)
    {
        if ($campaign->status !== 'draft') {
            return redirect()->route('admin.newsletters.campaigns.show', $campaign)->with('error', 'Solo se pueden editar campanas en borrador.');
        }

        return view('admin.newsletters.campaign-form', [
            'campaign' => $campaign,
            'activeSubscribers' => NewsletterSubscriber::active()->count(),
        ]);
    }

    public function updateCampaign(Request $request, NewsletterCampaign $campaign)
    {
        if ($campaign->status !== 'draft') {
            return redirect()->route('admin.newsletters.campaigns.show', $campaign)->with('error', 'Solo se pueden editar campanas en borrador.');
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $campaign->update([
            'subject' => $request->subject,
            'body' => $request->body,
        ]);

        return redirect()->route('admin.newsletters.campaigns.show', $campaign)->with('success', 'Campana actualizada.');
    }

    public function destroyCampaign(NewsletterCampaign $campaign)
    {
        $campaign->delete();
        return redirect()->route('admin.newsletters.campaigns')->with('success', 'Campana eliminada.');
    }

    public function previewCampaign(NewsletterCampaign $campaign)
    {
        return response($campaign->body)->header('Content-Type', 'text/html');
    }

    public function sendCampaign(Request $request, NewsletterCampaign $campaign, EmailService $emailService)
    {
        if ($campaign->status !== 'draft') {
            return back()->with('error', 'Esta campana ya fue enviada.');
        }

        $subscribers = NewsletterSubscriber::active()->get();

        if ($subscribers->isEmpty()) {
            return back()->with('error', 'No hay suscriptores activos.');
        }

        set_time_limit(0);

        $campaign->update(['status' => 'sending', 'sent_at' => now()]);

        $sent = 0;
        $failed = 0;

        foreach ($subscribers as $subscriber) {
            $unsubscribeUrl = route('newsletter.unsubscribe', $subscriber->unsubscribe_token);
            $htmlWithFooter = $campaign->body
                . '<div style="text-align:center; margin-top:32px; padding:16px; border-top:1px solid #eee; font-size:12px; color:#999;">'
                . '<a href="' . e($unsubscribeUrl) . '" style="color:#999; text-decoration:underline;">Cancelar suscripcion</a>'
                . '</div>';

            $success = $emailService->send(
                $subscriber->email,
                $campaign->subject,
                $htmlWithFooter
            );

            if ($success) {
                $sent++;
                if ($subscriber->client_id) {
                    Message::create([
                        'client_id' => $subscriber->client_id,
                        'channel' => 'email',
                        'direction' => 'outbound',
                        'subject' => $campaign->subject,
                        'body' => $htmlWithFooter,
                        'status' => 'sent',
                        'sent_at' => now(),
                        'metadata' => ['newsletter_campaign_id' => $campaign->id],
                    ]);
                }
            } else {
                $failed++;
            }

            usleep(200000); // 200ms between sends
        }

        $campaign->update([
            'status' => 'sent',
            'sent_to_count' => $sent,
            'failed_count' => $failed,
            'completed_at' => now(),
        ]);

        return redirect()->route('admin.newsletters.campaigns.show', $campaign)
            ->with('success', "Newsletter enviado a {$sent} suscriptores" . ($failed ? " ({$failed} fallidos)" : '') . '.');
    }
}
