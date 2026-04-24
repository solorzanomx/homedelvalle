<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EasyBrokerSetting extends Model
{
    protected $fillable = ['api_key', 'base_url', 'auto_publish', 'default_property_type', 'default_operation_type', 'default_currency', 'default_city_id', 'default_admin_division_id', 'default_latitude', 'default_longitude'];
    protected $table = 'easybroker_settings';

    protected function casts(): array
    {
        return [
            'api_key' => 'encrypted',
            'auto_publish' => 'boolean',
        ];
    }
}
