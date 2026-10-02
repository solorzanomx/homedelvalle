<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Incidente real 2026-10-02: hacer clic en "página 2" de Leads web llevaba a un 404
 * (admin.homedelvalle.mx/admin/admin/form-submissions?page=2 — "admin" duplicado).
 *
 * Causa raíz: el resolver de rutas de paginación de Livewire (`Livewire::originalPath()`, lo que
 * usa `WithPagination` por default) devuelve `request()->path()` SIN el "/" inicial —
 * `Paginator::url()` lo concatena tal cual (ver AbstractPaginator::url(), es literalmente
 * `$this->path() . '?' . query`, sin pasar por `url()->to()`). Con una ruta de un solo segmento
 * el navegador resuelve el href relativo por accidente; con el prefijo /admin (dos segmentos) lo
 * resuelve relativo al directorio actual y duplica "admin".
 *
 * Reproducido en vivo en el navegador contra producción antes de corregirlo — Livewire::test()
 * no sirve para probarlo (usa su propio endpoint interno de pruebas, no la ruta real), así que
 * este test guarda el fix por código fuente: `FormSubmissionsTable::render()` debe forzar una
 * URL absoluta con `withPath()`.
 */
class FormSubmissionsPaginationUrlTest extends TestCase
{
    public function test_pagination_forces_an_absolute_path_to_avoid_relative_url_resolution(): void
    {
        $source = file_get_contents(app_path('Livewire/Admin/FormSubmissionsTable.php'));

        $this->assertStringContainsString(
            '$submissions->withPath(',
            $source,
            'Sin withPath(), el href de paginación vuelve a ser relativo (admin/form-submissions en vez de /admin/form-submissions) — el navegador lo resuelve mal y duplica el prefijo de ruta.'
        );
        $this->assertStringContainsString('Livewire::originalPath()', $source);
    }
}
