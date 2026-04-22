<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Webhooks\GoogleDriveWebhookController;

Route::post('/webhook/lead', [WebhookController::class, 'lead'])
    ->middleware('throttle:30,1');

// Google Drive push notifications (Drive Watch API)
Route::post('/webhooks/google-drive', [GoogleDriveWebhookController::class, 'handle'])
    ->middleware('throttle:120,1');

