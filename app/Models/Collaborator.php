<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Collaborator extends Model
{
    protected $fillable = [
        'name', 'role', 'bio', 'photo_path', 'link_url', 'link_label', 'email',
        'sort_order', 'is_active',
        'consent_token', 'consent_status', 'consent_snapshot', 'consent_at',
        'consent_ip', 'consent_user_agent', 'decline_note',
        'link_sent_at', 'confirmation_email_sent_at',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'sort_order'      => 'integer',
        'consent_snapshot' => 'array',
        'consent_at'      => 'datetime',
        'link_sent_at'    => 'datetime',
        'confirmation_email_sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $collaborator) {
            if (empty($collaborator->consent_token)) {
                $collaborator->consent_token = Str::random(48);
            }
        });
    }

    /**
     * Campos cuyo cambio invalida una autorización ya otorgada — el
     * colaborador solo autorizó lo que vio, no cualquier edición futura.
     */
    public const CONSENT_RELEVANT_FIELDS = ['name', 'role', 'bio', 'photo_path', 'link_url', 'link_label'];

    public function currentSnapshot(): array
    {
        return [
            'name'       => $this->name,
            'role'       => $this->role,
            'bio'        => $this->bio,
            'photo_path' => $this->photo_path,
            'link_url'   => $this->link_url,
            'link_label' => $this->link_label,
        ];
    }

    public function resetConsent(): void
    {
        $this->consent_status = 'pending';
        $this->consent_snapshot = null;
        $this->consent_at = null;
        $this->consent_ip = null;
        $this->consent_user_agent = null;
        $this->decline_note = null;
        $this->consent_token = Str::random(48);
        $this->link_sent_at = null;
        $this->confirmation_email_sent_at = null;
    }

    public function scopePublished($query)
    {
        return $query->where('is_active', true)->where('consent_status', 'authorized');
    }

    public function isAuthorized(): bool
    {
        return $this->consent_status === 'authorized';
    }
}
