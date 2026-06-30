<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalNotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'notify_visit_scheduled',
        'notify_visit_confirmed',
        'notify_visit_rescheduled',
        'summary_frequency',
        'notify_process_updates',
    ];

    protected function casts(): array
    {
        return [
            'notify_visit_scheduled'  => 'boolean',
            'notify_visit_confirmed'  => 'boolean',
            'notify_visit_rescheduled'=> 'boolean',
            'notify_process_updates'  => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'notify_visit_scheduled'  => false,
                'notify_visit_confirmed'  => false,
                'notify_visit_rescheduled'=> false,
                'summary_frequency'       => 'weekly',
                'notify_process_updates'  => true,
            ]
        );
    }
}
