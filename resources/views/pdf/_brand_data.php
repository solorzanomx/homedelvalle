<?php
/**
 * Identidad de marca compartida por los 3 documentos que recibe un
 * propietario (Presentación, Opinión de Valor, Propuesta de Servicios).
 * Antes cada uno tenía su propio navy/acento/logo/fuente, sin ninguna
 * fuente única de verdad — ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md.
 *
 * PHP puro (no .blade.php): se incluye vía `include()` nativo de PHP
 * dentro de un bloque @php de cada documento, para que las variables que
 * define queden disponibles en el archivo que lo incluye. Un @include de
 * Blade NO sirve para esto — corre en su propio scope y no comparte
 * variables definidas hacia afuera.
 *
 * Define: $brandLogoSrc, $brandLogoSrcLight, $brandLogoFallback,
 *         $brandFontB64, $brandNavy, $brandAccent
 */

$siteSetting = \App\Models\SiteSetting::first();

$brandNavy   = '#1e1b4b';
$brandAccent = '#10b981';
$brandLogoFallback = 'Home del Valle · Bienes Raíces';

$brandLogoSrc = null;
$brandLogoPath = $siteSetting?->logo_path
    ? storage_path('app/public/' . $siteSetting->logo_path)
    : public_path('images/logo-homedelvalle.png');
if ($brandLogoPath && file_exists($brandLogoPath)) {
    $brandLogoMime = mime_content_type($brandLogoPath) ?: 'image/png';
    $brandLogoSrc  = 'data:' . $brandLogoMime . ';base64,' . base64_encode(file_get_contents($brandLogoPath));
}

$brandLogoSrcLight = null;
$brandLogoDarkPath = $siteSetting?->logo_path_dark
    ? storage_path('app/public/' . $siteSetting->logo_path_dark)
    : null;
if ($brandLogoDarkPath && file_exists($brandLogoDarkPath)) {
    $brandLogoDarkMime = mime_content_type($brandLogoDarkPath) ?: 'image/png';
    $brandLogoSrcLight = 'data:' . $brandLogoDarkMime . ';base64,' . base64_encode(file_get_contents($brandLogoDarkPath));
}

$brandFontPath = resource_path('fonts/inter-latin.woff2');
$brandFontB64  = file_exists($brandFontPath) ? base64_encode(file_get_contents($brandFontPath)) : null;
