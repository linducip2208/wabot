<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\AutoreplyController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\RecurringController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Admin\UserController;

// Auth
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::get('/register', fn() => view('auth.register'))->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Public landing
Route::get('/welcome', fn() => view('welcome'))->name('welcome');

// Webhook (no auth)
Route::post('/webhook/whatsapp', [WebhookController::class, 'whatsapp'])->name('webhook.whatsapp');
Route::post('/webhook/whatsapp-status', [WebhookController::class, 'statusUpdate'])->name('webhook.status');

// License pairing routes
require base_path('routes/pair-routes.php');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.stats');

    // Sessions
    Route::resource('sessions', SessionController::class)->except(['create', 'edit']);
    Route::get('sessions/{session}/status', [SessionController::class, 'status'])->name('sessions.status');

    // Autoreplies
    Route::resource('autoreplies', AutoreplyController::class)->except(['create', 'show', 'edit']);

    // Recurring Schedules
    Route::resource('recurrings', RecurringController::class)->except(['create', 'show', 'edit']);
    Route::post('recurrings/{recurring}/toggle', [RecurringController::class, 'toggle'])->name('recurrings.toggle');

    // Campaigns
    Route::resource('campaigns', CampaignController::class)->except(['edit']);

    // Contacts
    Route::resource('contacts', ContactController::class)->except(['create', 'edit']);
    Route::post('contacts/import', [ContactController::class, 'import'])->name('contacts.import');

    // Contact Groups
    Route::resource('groups', GroupController::class)->except(['create', 'show', 'edit']);
    Route::post('groups/assign', [GroupController::class, 'assign'])->name('groups.assign');

    // API Tokens
    Route::resource('tokens', ApiTokenController::class)->except(['create', 'show', 'edit']);

    // Servers
    Route::get('servers', [App\Http\Controllers\ServerController::class, 'index'])->name('servers.index');
    Route::post('servers', [App\Http\Controllers\ServerController::class, 'store'])->name('servers.store');
    Route::put('servers/{server}', [App\Http\Controllers\ServerController::class, 'update'])->name('servers.update');
    Route::delete('servers/{server}', [App\Http\Controllers\ServerController::class, 'destroy'])->name('servers.destroy');

    // Plans
    Route::get('plans', [App\Http\Controllers\PlanController::class, 'index'])->name('plans.index');
    Route::post('plans/{plan}/subscribe', [App\Http\Controllers\PlanController::class, 'subscribe'])->name('plans.subscribe');

    // Chat (omni-channel)
    Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('chat/{contact}', [ChatController::class, 'conversation'])->name('chat.conversation');
    Route::post('chat/{contact}/send', [ChatController::class, 'send'])->name('chat.send');
    Route::put('chat/{contact}/update', [ChatController::class, 'updateContact'])->name('chat.update-contact');
    Route::get('api/chat/messages/{contact}', [ChatController::class, 'pollMessages'])->name('chat.poll');
    Route::get('api/chat/contacts', [ChatController::class, 'pollContacts'])->name('chat.contacts');

    // Admin
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
        Route::post('users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate');
    });
});
