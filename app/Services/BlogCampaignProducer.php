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
    ) {
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

        foreach (User::whereIn('role', ['admin', 'editor'])->get() as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type'    => 'blog_campaign_draft',
                'title'   => 'Borrador de blog listo para tu OK',
                'body'    => "Campaña «{$campaign->name}»: «{$post->title}» espera revisión.",
                'data'    => ['url' => route('admin.blog-campaigns.show', $campaign), 'post_id' => $post->id],
            ]);
        }
    }
}
