<?php

namespace App\Actions\FacebookPost;

use App\Models\FacebookPost;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Publica un FacebookPost en la página de Facebook via Graph API v21.0.
 *
 * Requiere en SiteSetting:
 *   fb_api_enabled = true
 *   fb_page_id     = ID numérico de la página
 *   fb_page_access_token = Page Access Token con permiso pages_manage_posts
 *
 * Uso:
 *   $result = (new PublishToFacebookAction)->execute($post);
 *   // $result = ['fb_page_post_id' => '...', 'fb_post_url' => '...']
 */
class PublishToFacebookAction
{
    public function execute(FacebookPost $post): array
    {
        $settings = SiteSetting::first();

        if (! $settings || ! $settings->fb_api_enabled) {
            throw new \RuntimeException('La integración con Facebook no está habilitada. Configúrala en Configuración → Integraciones.');
        }

        $pageId = trim($settings->fb_page_id ?? '');
        $token  = trim($settings->fb_page_access_token ?? '');

        if (! $pageId || ! $token) {
            throw new \RuntimeException('Falta Page ID o Access Token. Completa la configuración en Integraciones → Facebook Publishing.');
        }

        if (! $post->rendered_image_path) {
            throw new \RuntimeException('Primero renderiza la imagen antes de publicar.');
        }

        // ── Construir caption ─────────────────────────────────────────
        $caption  = trim($post->caption ?? '');
        $hashtags = $post->hashtagsString();
        if ($hashtags) {
            $caption = $caption !== '' ? $caption . "\n\n" . $hashtags : $hashtags;
        }

        // ── Publicar foto en la página ────────────────────────────────
        $absolutePath = Storage::disk('public')->path($post->rendered_image_path);

        if (! file_exists($absolutePath)) {
            throw new \RuntimeException('No se encontró el archivo de imagen renderizada.');
        }

        $response = Http::timeout(60)
            ->attach('source', file_get_contents($absolutePath), 'hdv-fb-' . $post->id . '.png')
            ->post("https://graph.facebook.com/v21.0/{$pageId}/photos", [
                'caption'      => $caption,
                'access_token' => $token,
            ]);

        if (! $response->successful()) {
            $fbError = $response->json('error.message') ?? $response->body();
            Log::error('PublishToFacebookAction: Graph API error', [
                'post_id' => $post->id,
                'status'  => $response->status(),
                'error'   => $fbError,
            ]);
            throw new \RuntimeException("Error de Facebook API: {$fbError}");
        }

        $data       = $response->json();
        $fbPostId   = $data['post_id'] ?? null;    // formato: pageId_localId
        $fbPhotoId  = $data['id']      ?? null;    // ID de la foto

        // ── Construir URL del post ────────────────────────────────────
        $fbPostUrl = null;
        if ($fbPostId) {
            // post_id viene como "PAGE_ID_LOCAL_POST_ID"
            $parts     = explode('_', $fbPostId, 2);
            $localId   = $parts[1] ?? $fbPostId;
            $fbPostUrl = "https://www.facebook.com/{$pageId}/posts/{$localId}";
        } elseif ($fbPhotoId) {
            $fbPostUrl = "https://www.facebook.com/photo?fbid={$fbPhotoId}";
        }

        Log::info('PublishToFacebookAction: publicado', [
            'post_id'    => $post->id,
            'fb_post_id' => $fbPostId,
            'fb_url'     => $fbPostUrl,
        ]);

        return [
            'fb_page_post_id' => $fbPostId ?? $fbPhotoId,
            'fb_post_url'     => $fbPostUrl,
        ];
    }
}
