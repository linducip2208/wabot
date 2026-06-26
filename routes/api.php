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
});

// Public webhook (no auth, called by Node.js Baileys service)
Route::post('webhook/receive', [ApiController::class, 'webhookReceive']);
