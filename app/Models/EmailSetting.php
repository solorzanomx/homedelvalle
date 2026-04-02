<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    protected $fillable = ['smtp_server', 'port', 'from_email', 'from_name', 'password', 'enable_ssl'];
    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'enable_ssl' => 'boolean',
            'password' => 'encrypted',
        ];
    }
}
