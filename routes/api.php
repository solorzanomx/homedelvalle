<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Webhooks\MifielWebhookController;

Route::post('/webhook/lead', [WebhookController::class, 'lead'])
    ->middleware('throttle:30,1');

// Mifiel — fuera de auth, throttle generoso para reintentos automáticos
Route::post('/webhooks/mifiel', [MifielWebhookController::class, 'handle'])
    ->middleware('throttle:60,1');

