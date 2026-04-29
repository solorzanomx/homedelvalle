<?php

/**
 * routes/portal.php
 * Rutas del Portal del Cliente — miportal.homedelvalle.mx
 *
 * Cargadas desde bootstrap/app.php con Route::domain('miportal.homedelvalle.mx').
 * Middleware web + session ya aplicados por el grupo padre.
 */

use App\Http\Controllers\Portal\AuthController;
use App\Http\Controllers\Portal\PortalDashboardController;
use App\Http\Controllers\Portal\PortalDocumentController;
use App\Http\Controllers\Portal\PortalRentalController;
use App\Http\Controllers\Portal\PortalCaptacionController;
use App\Http\Controllers\Portal\PortalValuacionController;

// ── Auth (sin middleware guest — se maneja en el controller para evitar loops) ─
Route::name('portal.')->group(function () {
    Route::get('/',                  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',            [AuthController::class, 'login'])->name('login.submit');
    Route::get('/recuperar',         [AuthController::class, 'showRecover'])->name('recover');
    Route::post('/recuperar',        [AuthController::class, 'recover'])->name('recover.submit');
    Route::get('/restablecer/{token}',[AuthController::class, 'showReset'])->name('reset');
    Route::post('/restablecer',      [AuthController::class, 'reset'])->name('reset.submit');
    Route::get('/activar/{token}',   [AuthController::class, 'showAcceptInvitation'])->name('accept-invitation');
    Route::post('/activar',          [AuthController::class, 'acceptInvitation'])->name('accept-invitation.submit');
});

// ── Logout (autenticado) ─────────────────────────────────────────────────────
Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('portal.logout');

// ── Aceptación de términos (autenticado, sin gate legal) ─────────────────────
Route::middleware(['auth', 'client'])->name('portal.')->group(function () {
    Route::get('/terminos',         [\App\Http\Controllers\Portal\PortalLegalController::class, 'show'])->name('terminos');
    Route::post('/terminos/aceptar',[\App\Http\Controllers\Portal\PortalLegalController::class, 'aceptar'])->name('terminos.aceptar');
});

// ── Área protegida (autenticado + rol cliente + términos aceptados) ──────────
Route::middleware(['auth', 'client', 'portal.legal'])->name('portal.')->group(function () {

    Route::get('/inicio',                    [PortalDashboardController::class, 'index'])->name('dashboard');
    Route::get('/cuenta',                    [PortalDashboardController::class, 'account'])->name('account');
    Route::put('/cuenta/password',           [PortalDashboardController::class, 'updatePassword'])->name('account.password');

    // Documentos
    Route::get('/documentos',                [PortalDocumentController::class, 'index'])->name('documents.index');
    Route::get('/documentos/{id}/descargar', [PortalDocumentController::class, 'download'])->name('documents.download');
    Route::post('/documentos/subir',         [PortalDocumentController::class, 'upload'])->name('documents.upload');

    // Rentas
    Route::get('/mi-renta',                  [PortalRentalController::class, 'index'])->name('rentals.index');
    Route::get('/mi-renta/{id}',             [PortalRentalController::class, 'show'])->name('rentals.show');

    // Captación (propietario vendedor)
    Route::get('/captacion',                 [PortalCaptacionController::class, 'show'])->name('captacion');
    Route::post('/captacion/documentos',     [PortalCaptacionController::class, 'uploadDocument'])->name('captacion.upload');
    Route::delete('/captacion/documentos/{document}', [PortalCaptacionController::class, 'deleteDocument'])->name('captacion.document.delete');
    Route::post('/captacion/confirmar-precio',[PortalCaptacionController::class, 'confirmPriceAgreement'])->name('captacion.confirm-price');

    // Valuación
    Route::get('/valuacion',                 [PortalValuacionController::class, 'show'])->name('valuacion');
    Route::post('/valuacion/confirmar-precio',[PortalValuacionController::class, 'confirmPrice'])->name('valuacion.confirm-price');
});
