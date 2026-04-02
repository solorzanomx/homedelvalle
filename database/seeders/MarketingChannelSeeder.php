<?php

namespace Database\Seeders;

use App\Models\MarketingChannel;
use Illuminate\Database\Seeder;

class MarketingChannelSeeder extends Seeder
{
    public function run(): void
    {
        $channels = [
            ['name' => 'Google Ads',             'type' => 'paid',     'color' => '#4285f4', 'sort_order' => 1],
            ['name' => 'Facebook / Instagram',   'type' => 'paid',     'color' => '#1877f2', 'sort_order' => 2],
            ['name' => 'Portal Inmobiliario',    'type' => 'paid',     'color' => '#f59e0b', 'sort_order' => 3],
            ['name' => 'Referido',               'type' => 'referral', 'color' => '#10b981', 'sort_order' => 4],
            ['name' => 'Directo / Walk-in',      'type' => 'direct',   'color' => '#8b5cf6', 'sort_order' => 5],
            ['name' => 'TikTok',                 'type' => 'paid',     'color' => '#000000', 'sort_order' => 6],
            ['name' => 'Espectacular / Volante', 'type' => 'paid',     'color' => '#ec4899', 'sort_order' => 7],
            ['name' => 'Otro',                   'type' => 'organic',  'color' => '#64748b', 'sort_order' => 99],
        ];

        foreach ($channels as $channel) {
            MarketingChannel::firstOrCreate(['name' => $channel['name']], $channel);
        }
    }
}
