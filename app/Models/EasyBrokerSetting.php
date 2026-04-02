<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['api_key', 'base_url', 'auto_publish', 'default_property_type', 'default_operation_type', 'default_currency'])]
class EasyBrokerSetting extends Model
{
    protected $table = 'easybroker_settings';

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'auto_publish' => 'boolean',
        ];
    }
}
