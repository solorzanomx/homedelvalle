<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CustomEmailTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'template_type',
        'subject',
        'preview_text',
        'html_body',
        'text_body',
        'available_placeholders',
        'status',
        'created_by',
        'published_at',
        'archived_at',
    ];

    protected $casts = [
        'available_placeholders' => 'array',
        'published_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(EmailTemplateAssignment::class, 'template_id');
    }

    public function testingLogs(): HasMany
    {
        return $this->hasMany(EmailTemplateTesting::class, 'template_id');
    }

    public function render(array $data = []): string
    {
        $html = $this->html_body;

        foreach ($data as $key => $value) {
            $html = str_replace('{{' . $key . '}}', $value, $html);
        }

        return $html;
    }

    /**
     * $trackable (opcional): modelo al que ligar el mensaje (Client, Broker,
     * FormSubmission...) — queda en Message.trackable, y si es un Client
     * también en Message.client_id para que aparezca en su timeline como
     * cualquier otro correo. El pixel de apertura usa external_id como token
     * y reutiliza Message::markOpened() — un solo log para todo.
     */
    public function send($to, array $data = [], ?Model $trackable = null): void
    {
        $subject = $this->subject;
        foreach ($data as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
        }

        $token = Str::random(40);

        $message = Message::create([
            'client_id' => $trackable instanceof Client ? $trackable->id : null,
            'trackable_type' => $trackable ? get_class($trackable) : null,
            'trackable_id' => $trackable?->getKey(),
            'channel' => 'email',
            'subject' => $subject,
            'body' => $this->render($data),
            'status' => 'queued',
            'external_id' => $token,
            'metadata' => ['to' => $to, 'custom_email_template_id' => $this->id, 'template_slug' => $this->slug],
        ]);

        $html = $this->injectTrackingPixel($this->render($data), $token);

        try {
            Mail::html($html, function ($mail) use ($to, $subject) {
                $mail->to($to)
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject($subject);
            });

            $message->markSent();

            $this->testingLogs()->create([
                'test_email' => $to,
                'test_data' => $data,
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $message->markFailed();

            $this->testingLogs()->create([
                'test_email' => $to,
                'test_data' => $data,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            throw $e;
        }
    }

    private function injectTrackingPixel(string $html, string $token): string
    {
        $pixel = '<img src="' . route('email.pixel', $token) . '" width="1" height="1" alt="" style="display:none;border:0;">';

        return str_contains($html, '</body>')
            ? str_replace('</body>', $pixel . '</body>', $html)
            : $html . $pixel;
    }

    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function archive(): void
    {
        $this->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function activeAssignments()
    {
        return $this->assignments()->where('is_active', true);
    }
}
