<?php

namespace App\Console\Commands;

use App\Models\BlogCampaign;
use App\Services\BlogCampaignProducer;
use Illuminate\Console\Command;

class WorkBlogCampaignRequests extends Command
{
    protected $signature = 'blog:campaign-work';

    protected $description = 'Ejecuta las órdenes pendientes de los botones del hub de campañas (generar mapa / producir borrador) que no caben en un request web.';

    public function handle(BlogCampaignProducer $producer): int
    {
        $campaigns = BlogCampaign::whereNotNull('map_requested_at')
            ->orWhereNotNull('produce_requested_at')
            ->get();

        foreach ($campaigns as $campaign) {
            $this->info("Procesando órdenes de «{$campaign->name}»…");
            $producer->processRequests($campaign);
        }

        return self::SUCCESS;
    }
}
