<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarouselImagePrompt extends Model
{
    protected $fillable = ['key', 'label', 'prompt', 'is_global'];

    protected $casts = ['is_global' => 'boolean'];

    /** Default prompts seeded on first use */
    public static function defaults(): array
    {
        return [
            [
                'key'       => '_global',
                'label'     => 'Reglas globales (siempre aplicadas)',
                'is_global' => true,
                'prompt'    => 'Hyperrealistic, photorealistic, 8K ultra-HD, cinematic lighting, shot on Sony A7R V, professional commercial photography, sharp focus, no text, no watermarks, no logos, no people unless specified.',
            ],
            [
                'key'       => 'cover',
                'label'     => 'Portada',
                'is_global' => false,
                'prompt'    => 'Luxury real estate exterior, modern architecture, upscale Mexico City neighborhood, dramatic golden hour lighting, cinematic wide shot, empty street, lush landscaping.',
            ],
            [
                'key'       => 'key_stat',
                'label'     => 'Dato clave / Estadística',
                'is_global' => false,
                'prompt'    => 'Abstract architectural lines, modern glass building facade, geometric patterns, deep blue and navy tones, minimalist composition, professional real estate photography.',
            ],
            [
                'key'       => 'explanation',
                'label'     => 'Explicación',
                'is_global' => false,
                'prompt'    => 'Elegant luxury apartment interior, open-plan living room, floor-to-ceiling windows, natural light flooding in, minimalist modern furniture, neutral tones.',
            ],
            [
                'key'       => 'benefit',
                'label'     => 'Beneficio',
                'is_global' => false,
                'prompt'    => 'Premium real estate lifestyle, rooftop terrace with Mexico City skyline at golden hour, infinity pool reflection, dramatic sunset colors, aspirational mood.',
            ],
            [
                'key'       => 'problem',
                'label'     => 'Problema',
                'is_global' => false,
                'prompt'    => 'Urban Mexico City aerial view at dusk, moody dramatic lighting, city traffic, dark blue tones, atmospheric haze, documentary style photography.',
            ],
            [
                'key'       => 'social_proof',
                'label'     => 'Prueba social',
                'is_global' => false,
                'prompt'    => 'Warm luxury home interior, living room with soft natural light, lush indoor plants, cozy premium furniture, lifestyle real estate photography, no people.',
            ],
            [
                'key'       => 'cta',
                'label'     => 'Llamada a la acción',
                'is_global' => false,
                'prompt'    => 'Modern real estate agency office interior, bright and clean space, desk with laptop, premium finishes, professional corporate environment, no people.',
            ],
            [
                'key'       => 'example',
                'label'     => 'Ejemplo',
                'is_global' => false,
                'prompt'    => 'Beautiful residential property exterior, lush tropical garden, modern architecture, Mexico City upscale neighborhood, blue sky, wide establishing shot.',
            ],
        ];
    }

    /** Load all prompts from DB, seeding defaults if empty */
    public static function loadAll(): \Illuminate\Support\Collection
    {
        if (static::count() === 0) {
            static::insert(array_map(fn($d) => array_merge($d, [
                'created_at' => now(),
                'updated_at' => now(),
            ]), static::defaults()));
        }

        return static::orderBy('is_global', 'desc')->orderBy('key')->get();
    }

    /** Get the global suffix string */
    public static function globalSuffix(): string
    {
        return static::where('is_global', true)->value('prompt') ?? '';
    }

    /** Get prompt body for a specific slide type */
    public static function forType(string $type): string
    {
        return static::where('key', $type)->where('is_global', false)->value('prompt') ?? '';
    }
}
