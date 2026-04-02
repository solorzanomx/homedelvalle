<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['smtp_server', 'port', 'from_email', 'from_name', 'password', 'enable_ssl'])]
class EmailSetting extends Model
{
    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'enable_ssl' => 'boolean',
            'password' => 'encrypted',
        ];
    }
}
