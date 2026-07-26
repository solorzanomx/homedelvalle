<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BirthdayEmailLog extends Model
{
    protected $fillable = ['recipient_type', 'recipient_id', 'year', 'sent_at'];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public static function alreadySentThisYear(string $type, int $id, int $year): bool
    {
        return static::where('recipient_type', $type)
            ->where('recipient_id', $id)
            ->where('year', $year)
            ->exists();
    }
}
