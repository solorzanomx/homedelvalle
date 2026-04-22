<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CarouselImageTestController extends Controller
{
    public function show()
    {
        $providers = $this->providerStatuses();
        return view('admin.carousels.image-test', compact('providers'));
    }

    public function test(Request $request)
    {
        $request->validate([
            'provider' => ['required', 'string'],
            'prompt'   => ['required', 'string', 'max:4000'],
            'size'     => ['nullable', 'string'],
        ]);

        $provider = $request->input('provider');
        $prompt   = $request->input('prompt');
        $size     = $request->input('size', '1024x1792');

        $startedAt = microtime(true);

        try {
            $result = match ($provider) {
                'dalle3' => $this->testDalle($prompt, $size, 'dall-e-3'),
                'dalle2' => $this->testDalle($prompt, $size === '1024x1792' ? '1024x1024' : $size, 'dall-e-2'),
                default  => throw new \InvalidArgumentException("Proveedor desconocido: {$provider}"),
            };

            $elapsed = round(microtime(true) - $startedAt, 2);

            Log::info('CarouselImageTest: success', [
                'provider' => $provider,
                'prompt'   => $prompt,
                'elapsed'  => $elapsed,
            ]);

            return response()->json([
                'ok'       => true,
                'provider' => $provider,
                'imageUrl' => $result['url'],
                'elapsed'  => $elapsed,
                'meta'     => $result['meta'] ?? [],
            ]);
        } catch (\Throwable $e) {
            $elapsed = round(microtime(true) - $startedAt, 2);

            Log::warning('CarouselImageTest: failed', [
                'provider' => $provider,
                'prompt'   => $prompt,
                'error'    => $e->getMessage(),
                'elapsed'  => $elapsed,
            ]);

            return response()->json([
                'ok'      => false,
                'error'   => $e->getMessage(),
                'elapsed' => $elapsed,
            ], 422);
        }
    }

    // ── Providers ─────────────────────────────────────────────────────────────

    private function testDalle(string $prompt, string $size, string $model): array
    {
        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            throw new \RuntimeException('OPENAI_API_KEY no está configurada en .env');
        }

        $payload = [
            'model'           => $model,
            'prompt'          => $prompt,
            'n'               => 1,
            'size'            => $size,
            'quality'         => 'standard',
            'response_format' => 'url',
        ];

        $response = Http::withToken($apiKey)
            ->timeout(120)
            ->post('https://api.openai.com/v1/images/generations', $payload);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \RuntimeException("API error {$response->status()}: {$error}");
        }

        $url = $response->json('data.0.url');
        if (!$url) {
            throw new \RuntimeException('No se recibió URL de imagen. Respuesta: ' . $response->body());
        }

        return [
            'url'  => $url,
            'meta' => [
                'model'            => $model,
                'size'             => $size,
                'revised_prompt'   => $response->json('data.0.revised_prompt'),
                'http_status'      => $response->status(),
            ],
        ];
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function providerStatuses(): array
    {
        $openaiKey = config('services.openai.api_key');

        return [
            [
                'id'      => 'dalle3',
                'name'    => 'DALL-E 3',
                'company' => 'OpenAI',
                'note'    => 'Alta calidad, portrait 1024×1792',
                'sizes'   => ['1024x1024', '1024x1792', '1792x1024'],
                'default_size' => '1024x1792',
                'active'  => (bool) $openaiKey,
                'key_hint'=> $openaiKey ? ('sk-…' . substr($openaiKey, -6)) : 'No configurada',
            ],
            [
                'id'      => 'dalle2',
                'name'    => 'DALL-E 2',
                'company' => 'OpenAI',
                'note'    => 'Rápido, cuadrado hasta 1024×1024',
                'sizes'   => ['256x256', '512x512', '1024x1024'],
                'default_size' => '1024x1024',
                'active'  => (bool) $openaiKey,
                'key_hint'=> $openaiKey ? ('sk-…' . substr($openaiKey, -6)) : 'No configurada',
            ],
        ];
    }
}
