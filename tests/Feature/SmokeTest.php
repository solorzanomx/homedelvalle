<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Red de seguridad barata: si alguien rompe la sintaxis de un Blade o una
 * ruta, esto lo detecta antes del deploy. Correr siempre antes de hacer push.
 */
class SmokeTest extends TestCase
{
    public function test_all_blade_views_compile(): void
    {
        $code = Artisan::call('view:cache');
        Artisan::call('view:clear');

        $this->assertSame(0, $code, Artisan::output());
    }

    public function test_routes_load(): void
    {
        $this->assertSame(0, Artisan::call('route:list', ['--json' => true]));
    }

    public function test_document_review_routes_exist(): void
    {
        foreach (['documents.preview', 'documents.update-status', 'documents.download', 'documents.destroy'] as $name) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($name), "Falta la ruta {$name}");
        }
    }
}
