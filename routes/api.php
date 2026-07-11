<?php

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', [ApiController::class, 'profile']);

    Route::post('send', [ApiController::class, 'send']);
    Route::post('send-bulk', [ApiController::class, 'sendBulk']);

    Route::get('sessions', [ApiController::class, 'sessions']);
    Route::get('sessions/{sessionId}/status', [ApiController::class, 'sessionStatus']);

    Route::get('messages', [ApiController::class, 'messages']);
    Route::get('contacts', [ApiController::class, 'contacts']);
    Route::get('campaigns', [ApiController::class, 'campaigns']);
    Route::get('autoreplies', [ApiController::class, 'autoreplyRules']);

    Route::post('meta/send', [ApiController::class, 'metaSend']);
    Route::post('instagram/send', [ApiController::class, 'instagramSend']);
    Route::post('telegram/send', [ApiController::class, 'telegramSend']);
    Route::get('channels', [ApiController::class, 'channels']);
});

// Public webhook (no middleware, called by Node.js Baileys service)
Route::post('webhook/whatsapp', [\App\Http\Controllers\WebhookController::class, 'whatsapp']);
Route::post('webhook/whatsapp-status', [\App\Http\Controllers\WebhookController::class, 'statusUpdate']);
