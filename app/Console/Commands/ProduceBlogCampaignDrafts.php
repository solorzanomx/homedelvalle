<?php

namespace App\Console\Commands;

use App\Models\BlogCampaign;
use App\Services\BlogCampaignProducer;
use Illuminate\Console\Command;

/**
 * El productor de campañas: corre por scheduler y mantiene el colchón de
 * borradores de cada campaña activa (uno por corrida, para que las corridas
 * sean cortas). El editor recibe notificación por cada borrador listo.
 */
class ProduceBlogCampaignDrafts extends Command
{
    protected $signature = 'blog:campaign-produce';

    protected $description = 'Genera borradores de campañas activas hasta llenar su colchón de revisión';

    public function handle(BlogCampaignProducer $producer): int
    {
        foreach (BlogCampaign::where('status', 'active')->get() as $campaign) {
            $post = $producer->keepBuffer($campaign);

            if ($post) {
                $this->info("«{$campaign->name}»: borrador generado — {$post->title}");
            } else {
                $this->line("«{$campaign->name}»: colchón lleno o sin temas pendientes.");
            }
        }

        return self::SUCCESS;
    }
}
