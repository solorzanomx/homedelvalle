<?php

namespace App\Services;

use App\Actions\Blog\GenerateBlogImagesAction;
use App\Actions\Blog\GenerateBlogPostAction;
use App\Models\BlogCampaign;
use App\Models\Notification;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Produce borradores de campaña: toma el siguiente tema pendiente del mapa,
 * genera el post completo (texto + categoría + tags) y sus imágenes, y lo
 * deja como borrador esperando el OK del editor. Usado por el botón
 * "Producir siguiente" y por el comando programado que mantiene el colchón.
 */
class BlogCampaignProducer
{
    public function __construct(
        private readonly GenerateBlogPostAction $postAction,
        private readonly GenerateBlogImagesAction $imagesAction,
        private readonly BlogAIService $blogAI,
    ) {
    }

    /**
     * Genera el mapa de temas y lo guarda en la campaña. Conserva los temas
     * ya trabajados (generados/descartados); los pendientes se reemplazan.
     * Tarda ~2 min con 30 temas — solo llamar desde consola, nunca en un request.
     */
    public function generateMap(BlogCampaign $campaign, int $count = 30): int
    {
        $topics = $this->blogAI->discoverTopics('', [
            'count'     => min(40, max(5, $count)),
            'objetivo'  => $campaign->objetivo,
            'mezcla'    => $campaign->mezcla ?: null,
            'lecciones' => $campaign->lecciones ?: null,
        ]);

        if (empty($topics)) {
            throw new \RuntimeException('La IA respondió pero no se pudo leer ningún tema.');
        }

        $existentes = collect($campaign->topics ?? [])->where('status', '!=', 'pending')->values();
        $nuevos = collect($topics)->map(fn ($t) => [
            'title'       => $t['title'] ?? '',
            'description' => $t['description'] ?? '',
            'keywords'    => $t['suggested_keywords'] ?? [],
            'categoria'   => $t['categoria'] ?? null,
            'score'       => $t['relevance_score'] ?? null,
            'status'      => 'pending',
            'post_id'     => null,
        ]);

        $campaign->update(['topics' => $existentes->concat($nuevos)->values()->all()]);

        return count($topics);
    }

    /**
     * Procesa las órdenes que dejaron los botones del hub (el request web no
     * aguanta los ~2-5 min de generación: Cloudflare corta a los 100s).
     */
    public function processRequests(BlogCampaign $campaign): void
    {
        if ($campaign->map_requested_at) {
            $count = $campaign->map_requested_count ?: 30;
            $campaign->update(['map_requested_at' => null, 'map_requested_count' => null]);

            try {
                $n = $this->generateMap($campaign, $count);
                $this->notifyAdmins($campaign, 'blog_campaign_map', 'Mapa de temas listo',
                    "Campaña «{$campaign->name}»: {$n} temas nuevos esperan tu revisión.");
            } catch (\Throwable $e) {
                Log::error('BlogCampaignProducer: mapa falló', ['campaign_id' => $campaign->id, 'error' => $e->getMessage()]);
                $this->notifyAdmins($campaign, 'blog_campaign_map', 'Falló la generación del mapa',
                    "Campaña «{$campaign->name}»: " . \Illuminate\Support\Str::limit($e->getMessage(), 180));
            }
        }

        if ($campaign->produce_requested_at) {
            $campaign->update(['produce_requested_at' => null]);

            $post = $this->produceNext($campaign);
            if (! $post) {
                $this->notifyAdmins($campaign, 'blog_campaign_draft', 'No se pudo producir el borrador',
                    "Campaña «{$campaign->name}»: no hay temas pendientes o la generación falló (revisa el log).");
            }
            // Si sí se produjo, produceNext ya notificó "listo para tu OK".
        }
    }

    public function produceNext(BlogCampaign $campaign): ?Post
    {
        $topics = $campaign->topics ?? [];
        $index  = collect($topics)->search(fn ($t) => ($t['status'] ?? '') === 'pending');

        if ($index === false) {
            return null;
        }

        $topic = $topics[$index];

        $post = Post::create([
            'title'                => $topic['title'],
            'slug'                 => \Illuminate\Support\Str::slug($topic['title']) . '-' . uniqid(),
            'body'                 => '',
            'status'               => 'draft',
            'ai_generated'         => true,
            'ai_generation_status' => 'pending',
            'blog_campaign_id'     => $campaign->id,
            'user_id'              => User::where('role', 'admin')->value('id') ?? 1,
        ]);

        // Marcar el tema como tomado ANTES de generar — si la generación
        // truena a medias, no se re-toma en bucle (queda visible como fallido).
        $topics[$index]['status']  = 'generated';
        $topics[$index]['post_id'] = $post->id;
        $campaign->update(['topics' => $topics]);

        try {
            $brief = [];
            if ($campaign->lecciones) {
                $brief['key_points'] = "LECCIONES EDITORIALES del editor (respétalas):\n" . $campaign->lecciones;
            }

            $post = $this->postAction->execute($post, $topic['title'], $topic['keywords'] ?? [], '', $brief);
        } catch (\Throwable $e) {
            Log::error('BlogCampaignProducer: generación falló', ['post_id' => $post->id, 'error' => $e->getMessage()]);

            return null;
        }

        // Imágenes automáticas (Gemini). Si fallan, el borrador queda sin
        // imágenes y el paso de imágenes del generador permite reintentarlas.
        try {
            $this->imagesAction->execute($post->fresh());
        } catch (\Throwable $e) {
            Log::warning('BlogCampaignProducer: imágenes fallaron (borrador queda sin ellas)', ['post_id' => $post->id, 'error' => $e->getMessage()]);
        }

        $this->notifyDraftReady($campaign, $post->fresh());

        return $post->fresh();
    }

    /** Mantiene el colchón: genera si hay menos borradores listos que buffer. */
    public function keepBuffer(BlogCampaign $campaign): ?Post
    {
        if ($campaign->status !== 'active') {
            return null;
        }

        if ($campaign->draftsPendingReview()->count() >= $campaign->buffer) {
            return null;
        }

        return $this->produceNext($campaign);
    }

    private function notifyDraftReady(BlogCampaign $campaign, Post $post): void
    {
        $yaAvisado = Notification::where('type', 'blog_campaign_draft')
            ->where('data->post_id', $post->id)
            ->exists();
        if ($yaAvisado) {
            return;
        }

        $this->notifyAdmins($campaign, 'blog_campaign_draft', 'Borrador de blog listo para tu OK',
            "Campaña «{$campaign->name}»: «{$post->title}» espera revisión.", ['post_id' => $post->id]);
    }

    private function notifyAdmins(BlogCampaign $campaign, string $type, string $title, string $body, array $extra = []): void
    {
        foreach (User::whereIn('role', ['admin', 'editor'])->get() as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type'    => $type,
                'title'   => $title,
                'body'    => $body,
                'data'    => array_merge(['url' => route('admin.blog-campaigns.show', $campaign)], $extra),
            ]);
        }
    }
}
