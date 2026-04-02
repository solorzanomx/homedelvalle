<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['client_id', 'user_id', 'subject', 'body_html', 'property_ids', 'tracking_id', 'opened_at', 'open_count', 'status'])]
class ClientEmail extends Model
{
    protected function casts(): array
    {
        return [
            'property_ids' => 'array',
            'opened_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ClientEmail $email) {
            if (empty($email->tracking_id)) {
                $email->tracking_id = (string) Str::uuid();
            }
        });
    }

    public function client() { return $this->belongsTo(Client::class); }
    public function user() { return $this->belongsTo(User::class); }

    public function properties()
    {
        if (empty($this->property_ids)) {
            return collect();
        }
        return Property::whereIn('id', $this->property_ids)->get();
    }

    public function markAsOpened(): void
    {
        $this->increment('open_count');
        if (!$this->opened_at) {
            $this->update(['opened_at' => now()]);
        }
    }

    public function getIsOpenedAttribute(): bool
    {
        return $this->opened_at !== null;
    }
}
