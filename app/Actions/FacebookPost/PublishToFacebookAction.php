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
 * Flujo de dos pasos (requerido desde API v2.9+):
 *   1. POST /{page-id}/photos?published=false → sube la imagen, devuelve photo_id
 *   2. POST /{page-id}/feed con attached_media → crea el post con foto y caption
 *
 * Requiere en SiteSetting:
 *   fb_api_enabled       = true
 *   fb_page_id           = ID numérico de la página
 *   fb_page_access_token = Page Access Token con pages_manage_posts + pages_read_engagement
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

        $absolutePath = Storage::disk('public')->path($post->rendered_image_path);

        if (! file_exists($absolutePath)) {
            throw new \RuntimeException('No se encontró el archivo de imagen renderizada en storage.');
        }

        // ── Construir caption ─────────────────────────────────────────
        $caption  = trim($post->caption ?? '');
        $hashtags = $post->hashtagsString();
        if ($hashtags) {
            $caption = $caption !== '' ? $caption . "\n\n" . $hashtags : $hashtags;
        }

        // ── Paso 1: subir foto sin publicar ───────────────────────────
        $uploadResponse = Http::timeout(60)
            ->attach('source', file_get_contents($absolutePath), 'hdv-fb-' . $post->id . '.png')
            ->post("https://graph.facebook.com/v21.0/{$pageId}/photos", [
                'published'    => 'false',
                'access_token' => $token,
            ]);

        if (! $uploadResponse->successful()) {
            $err = $uploadResponse->json('error.message') ?? $uploadResponse->body();
            Log::error('PublishToFacebookAction: error subiendo foto', [
                'post_id' => $post->id,
                'status'  => $uploadResponse->status(),
                'error'   => $err,
            ]);
            throw new \RuntimeException("Error al subir imagen a Facebook: {$err}");
        }

        $photoId = $uploadResponse->json('id');

        if (! $photoId) {
            throw new \RuntimeException('Facebook no devolvió el ID de la foto subida.');
        }

        // ── Paso 2: crear el post en el feed con la foto adjunta ──────
        $feedResponse = Http::timeout(30)
            ->post("https://graph.facebook.com/v21.0/{$pageId}/feed", [
                'message'        => $caption,
                'attached_media' => json_encode([['media_fbid' => $photoId]]),
                'access_token'   => $token,
            ]);

        if (! $feedResponse->successful()) {
            $err = $feedResponse->json('error.message') ?? $feedResponse->body();
            Log::error('PublishToFacebookAction: error publicando feed', [
                'post_id'  => $post->id,
                'photo_id' => $photoId,
                'status'   => $feedResponse->status(),
                'error'    => $err,
            ]);
            throw new \RuntimeException("Error al publicar en Facebook: {$err}");
        }

        // feed devuelve { "id": "PAGE_ID_LOCAL_POST_ID" }
        $fbPostId = $feedResponse->json('id');

        // ── Construir URL del post ────────────────────────────────────
        $fbPostUrl = null;
        if ($fbPostId) {
            $parts     = explode('_', $fbPostId, 2);
            $localId   = $parts[1] ?? $fbPostId;
            $fbPostUrl = "https://www.facebook.com/{$pageId}/posts/{$localId}";
        }

        Log::info('PublishToFacebookAction: publicado correctamente', [
            'post_id'    => $post->id,
            'fb_post_id' => $fbPostId,
            'photo_id'   => $photoId,
            'fb_url'     => $fbPostUrl,
        ]);

        return [
            'fb_page_post_id' => $fbPostId,
            'fb_post_url'     => $fbPostUrl,
        ];
    }
}
