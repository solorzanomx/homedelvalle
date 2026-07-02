<?php

namespace App\Support;

use App\Models\Post;
use Illuminate\Http\Request;

/**
 * Traduce la ruta actual a una etiqueta legible para el reporte de
 * atribución de origen — ver App\Support\Attribution.
 */
class AttributionLabeler
{
    public static function label(Request $request): string
    {
        $routeName = $request->route()?->getName();

        if (!$routeName) {
            return $request->path();
        }

        if ($routeName === 'blog.show') {
            $post = static::postFromRoute($request);
            return $post ? 'Blog: ' . $post->title : 'Blog';
        }

        return match (true) {
            $routeName === 'home' => 'Home',
            $routeName === 'testimonios' => 'Testimonios',
            str_starts_with($routeName, 'propiedades.') => 'Propiedades',
            $routeName === 'landing.vende' => 'Landing: Vende',
            $routeName === 'landing.compra' => 'Landing: Compra',
            $routeName === 'landing.desarrolladores' => 'Landing: Desarrolladores',
            in_array($routeName, ['landing.rentar', 'landing.renta-tu-propiedad']) => 'Landing: Renta',
            str_starts_with($routeName, 'precios.') => 'Precios',
            $routeName === 'nosotros' => 'Nosotros',
            $routeName === 'servicios' => 'Servicios',
            $routeName === 'contacto' => 'Contacto',
            default => $routeName,
        };
    }

    public static function postIdFromRoute(Request $request): ?int
    {
        return $request->route()?->getName() === 'blog.show'
            ? static::postFromRoute($request)?->id
            : null;
    }

    private static function postFromRoute(Request $request): ?Post
    {
        $slug = $request->route('slug');
        return $slug ? Post::where('slug', $slug)->select('id', 'title')->first() : null;
    }
}
