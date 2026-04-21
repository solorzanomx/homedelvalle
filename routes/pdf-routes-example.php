<?php

/**
 * EJEMPLOS DE CONFIGURACIÓN DE RUTAS PARA FICHAS TÉCNICAS PDF
 *
 * Copiar estos ejemplos a tu archivo routes/web.php o routes/api.php
 * según tu estructura de rutas.
 */

// ========================================
// OPCIÓN 1: RUTAS PÚBLICAS (Sin autenticación)
// ========================================

Route::prefix('properties')->group(function () {
    // Descargar PDF - Modo Institucional (sin broker)
    Route::get('{property:slug}/pdf', [\App\Http\Controllers\PDF\PropertySheetController::class, 'downloadPropertySheet'])
        ->name('properties.pdf.download');

    // Preview en navegador
    Route::get('{property:slug}/pdf/preview', [\App\Http\Controllers\PDF\PropertySheetController::class, 'previewPropertySheet'])
        ->name('properties.pdf.preview');
});


// ========================================
// OPCIÓN 2: RUTAS PROTEGIDAS (Require auth)
// ========================================

Route::middleware('auth')->prefix('properties')->group(function () {
    // Descargar PDF con broker
    Route::get('{property:slug}/pdf', [\App\Http\Controllers\PDF\PropertySheetController::class, 'downloadPropertySheet'])
        ->name('properties.pdf.download');

    Route::get('{property:slug}/pdf/preview', [\App\Http\Controllers\PDF\PropertySheetController::class, 'previewPropertySheet'])
        ->name('properties.pdf.preview');
});


// ========================================
// OPCIÓN 3: RUTAS CON PERMISOS (ACL/Policies)
// ========================================

Route::middleware(['auth', 'verified'])->prefix('properties')->group(function () {
    Route::get('{property:slug}/pdf', [\App\Http\Controllers\PDF\PropertySheetController::class, 'downloadPropertySheet'])
        ->name('properties.pdf.download')
        ->middleware('can:view,property');

    Route::get('{property:slug}/pdf/preview', [\App\Http\Controllers\PDF\PropertySheetController::class, 'previewPropertySheet'])
        ->name('properties.pdf.preview')
        ->middleware('can:view,property');
});


// ========================================
// OPCIÓN 4: RUTAS API (JSON responses)
// ========================================

Route::middleware('auth:api')->prefix('api/properties')->group(function () {
    Route::get('{property:slug}/pdf/url', function (Property $property) {
        return response()->json([
            'download_url' => route('properties.pdf.download', [
                'property' => $property,
                'include_broker' => 1
            ]),
            'preview_url' => route('properties.pdf.preview', [
                'property' => $property,
                'include_broker' => 1
            ])
        ]);
    })->name('api.properties.pdf.urls');
});


// ========================================
// EJEMPLOS DE USO EN VISTAS
// ========================================

/*

<!-- Descarga simple sin broker -->
<a href="{{ route('properties.pdf.download', $property) }}" class="btn btn-primary">
    📥 Descargar Ficha Técnica
</a>

<!-- Descarga con broker actual -->
<a href="{{ route('properties.pdf.download', ['property' => $property, 'include_broker' => 1]) }}" class="btn btn-primary">
    📥 Descargar con mis Datos
</a>

<!-- Descarga con broker específico -->
<a href="{{ route('properties.pdf.download', ['property' => $property, 'include_broker' => 1, 'broker_id' => auth()->id()]) }}" class="btn btn-primary">
    📥 Descargar Ficha Personalizada
</a>

<!-- Preview en navegador -->
<button onclick="window.open('{{ route('properties.pdf.preview', $property) }}', '_blank')" class="btn btn-secondary">
    👁️ Ver Previsualización
</button>

<!-- En componente Vue/React -->
<button @click="downloadPDF(property.id)" class="btn">
    Descargar PDF
</button>

<script>
function downloadPDF(propertyId) {
    const url = `/properties/${propertyId}/pdf?include_broker=1`;
    window.location.href = url;
}
</script>

*/
