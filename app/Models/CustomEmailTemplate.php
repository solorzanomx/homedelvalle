<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;

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
            $html = str_replace("{{$key}}", $value, $html);
        }

        return $html;
    }

    public function send($to, array $data = []): void
    {
        $subject = $this->subject;
        foreach ($data as $key => $value) {
            $subject = str_replace("{{$key}}", $value, $subject);
        }

        try {
            Mail::html($this->render($data), function ($message) use ($to, $subject) {
                $message->to($to)
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject($subject);
            });

            $this->testingLogs()->create([
                'test_email' => $to,
                'test_data' => $data,
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
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
