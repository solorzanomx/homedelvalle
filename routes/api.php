<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookController;

Route::post('/webhook/lead', [WebhookController::class, 'lead'])
    ->middleware('throttle:30,1');
