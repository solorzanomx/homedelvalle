<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    protected $fillable = [
        'smtp_server', 'port', 'from_email', 'from_name', 'username', 'password', 'enable_ssl',
        'imap_enabled', 'imap_host', 'imap_port', 'imap_encryption', 'imap_username', 'imap_password',
        'imap_last_checked_at',
    ];
    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'enable_ssl' => 'boolean',
            'password' => 'encrypted',
            'imap_enabled' => 'boolean',
            'imap_port' => 'integer',
            'imap_password' => 'encrypted',
            'imap_last_checked_at' => 'datetime',
        ];
    }
}
