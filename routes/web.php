<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\AutoreplyController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\RecurringController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\LoggerController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PayoutController as AdminPayoutController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\Admin\ShortenerController;
use App\Http\Controllers\Admin\CmsPageController;
use App\Http\Controllers\AiKeyController;
use App\Http\Controllers\KnowledgeController;
use App\Http\Controllers\FlowController;
use App\Http\Controllers\DripCampaignController;
use App\Http\Controllers\ABCampaignController;
use App\Http\Controllers\ClickTrackController;
use App\Http\Controllers\ContactTagController;
use App\Http\Controllers\InteractiveButtonController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CommerceOrderController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\DealStageController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\TeamInboxController;
use App\Http\Controllers\SlaConfigController;
use App\Http\Controllers\AiAgentController;
use App\Http\Controllers\IntentConfigController;
use App\Http\Controllers\MediaTemplateController;
use App\Http\Controllers\SentimentController;
use App\Http\Controllers\ConversationRatingController;

// Public CMS pages
Route::get('/pages/{slug}', [CmsPageController::class, 'show'])->name('pages.show')->where('slug', '^(?!builder$).*$');

// Public landing (guest only)
Route::get('/welcome', fn() => view('welcome'))->name('welcome');
Route::get('/', fn() => auth()->check() ? redirect()->route('chat.index') : view('welcome'))->name('home');

// Docs
Route::get('/docs', [\App\Http\Controllers\DocsController::class, 'index'])->name('docs');

// Blog (public)
Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');
Route::get('/blog/category/{slug}', [\App\Http\Controllers\BlogController::class, 'category'])->name('blog.category');

// SEO
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Auth
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::get('/register', fn() => view('auth.register'))->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Webhook (no auth)
Route::post('/webhook/whatsapp', [WebhookController::class, 'whatsapp'])->name('webhook.whatsapp');
Route::post('/webhook/whatsapp-status', [WebhookController::class, 'statusUpdate'])->name('webhook.status');

// License pairing routes
require base_path('routes/pair-routes.php');

// Click tracking redirect (public, no auth)
Route::get('/click/{token}', [ClickTrackController::class, 'redirect'])->name('click.redirect');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.stats');

    // Sessions
    Route::resource('sessions', SessionController::class)->except(['create', 'edit']);
    Route::get('sessions/{session}/status', [SessionController::class, 'status'])->name('sessions.status');
    Route::post('sessions', [SessionController::class, 'store'])->name('sessions.store')->middleware('check.subscription:sessions');

    // Autoreplies
    Route::resource('autoreplies', AutoreplyController::class)->except(['create', 'show', 'edit']);
    Route::post('autoreplies', [AutoreplyController::class, 'store'])->name('autoreplies.store')->middleware('check.subscription:autoreplies');

    // Knowledge Base / FAQ
    Route::get('knowledge', [KnowledgeController::class, 'index'])->name('knowledge.index');
    Route::post('knowledge', [KnowledgeController::class, 'store'])->name('knowledge.store');
    Route::post('knowledge/import', [KnowledgeController::class, 'importCsv'])->name('knowledge.import');
    Route::post('knowledge/{knowledge}/toggle', [KnowledgeController::class, 'toggle'])->name('knowledge.toggle');
    Route::delete('knowledge/{knowledge}', [KnowledgeController::class, 'destroy'])->name('knowledge.destroy');

    // Message Templates
    Route::resource('templates', TemplateController::class)->except(['create', 'show', 'edit']);

    // Webhooks
    Route::resource('webhooks', WebhookController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('webhooks/{webhook}/test', [WebhookController::class, 'test'])->name('webhooks.test');

    // Recurring Schedules
    Route::resource('recurrings', RecurringController::class)->except(['create', 'show', 'edit']);
    Route::post('recurrings/{recurring}/toggle', [RecurringController::class, 'toggle'])->name('recurrings.toggle');

    // Campaigns
    Route::resource('campaigns', CampaignController::class)->except(['edit']);
    Route::post('campaigns/{campaign}/pause', [CampaignController::class, 'pause'])->name('campaigns.pause');
    Route::post('campaigns/{campaign}/resume', [CampaignController::class, 'resume'])->name('campaigns.resume');
    Route::post('campaigns/{campaign}/resend', [CampaignController::class, 'resend'])->name('campaigns.resend');

    // Messages
    Route::get('messages/send', [\App\Http\Controllers\MessageController::class, 'sendForm'])->name('messages.send.form');
    Route::post('messages/send', [\App\Http\Controllers\MessageController::class, 'send'])->name('messages.send');
    Route::get('messages/sent', [\App\Http\Controllers\MessageController::class, 'sent'])->name('messages.sent');
    Route::get('messages/received', [\App\Http\Controllers\MessageController::class, 'received'])->name('messages.received');
    Route::get('messages/queue', [\App\Http\Controllers\MessageController::class, 'queue'])->name('messages.queue');
    Route::post('messages/{message}/resend', [\App\Http\Controllers\MessageController::class, 'resend'])->name('messages.resend');
    Route::delete('messages/{message}', [\App\Http\Controllers\MessageController::class, 'destroy'])->name('messages.destroy');
    Route::post('messages/bulk-delete', [\App\Http\Controllers\MessageController::class, 'bulkDelete'])->name('messages.bulk-delete');
    Route::resource('contacts', ContactController::class)->except(['create', 'edit']);
    Route::post('contacts/import', [ContactController::class, 'import'])->name('contacts.import');

    // Contact Groups
    Route::resource('groups', GroupController::class)->except(['create', 'show', 'edit']);
    Route::post('groups/assign', [GroupController::class, 'assign'])->name('groups.assign');

    // API Tokens
    Route::resource('tokens', ApiTokenController::class)->except(['create', 'show', 'edit']);

    // AI Keys
    Route::resource('ai-keys', AiKeyController::class)->except(['create', 'show', 'edit']);
    Route::post('ai-keys/{aiKey}/test', [AiKeyController::class, 'test'])->name('ai-keys.test');

    // Servers
    Route::get('servers', [App\Http\Controllers\ServerController::class, 'index'])->name('servers.index');
    Route::middleware('role:admin')->group(function () {
        Route::post('servers', [App\Http\Controllers\ServerController::class, 'store'])->name('servers.store');
        Route::put('servers/{server}', [App\Http\Controllers\ServerController::class, 'update'])->name('servers.update');
        Route::delete('servers/{server}', [App\Http\Controllers\ServerController::class, 'destroy'])->name('servers.destroy');
    });

    // Plans
    Route::get('plans', [App\Http\Controllers\PlanController::class, 'index'])->name('plans.index');
    Route::post('plans/{plan}/subscribe', [App\Http\Controllers\PlanController::class, 'subscribe'])->name('plans.subscribe');

    // Payment
    Route::get('payment/{subscription}', [App\Http\Controllers\PlanController::class, 'payment'])->name('payment.page');
    Route::post('payment/{subscription}/upload', [App\Http\Controllers\PlanController::class, 'uploadPayment'])->name('payment.upload');

    // Chat (omni-channel)
    Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('chat/{contact}', [ChatController::class, 'conversation'])->name('chat.conversation');
    Route::post('chat/{contact}/send', [ChatController::class, 'send'])->name('chat.send');
    Route::put('chat/{contact}/update', [ChatController::class, 'updateContact'])->name('chat.update-contact');
    Route::get('api/chat/messages/{contact}', [ChatController::class, 'pollMessages'])->name('chat.poll');
    Route::get('api/chat/contacts', [ChatController::class, 'pollContacts'])->name('chat.contacts');

    // Payouts (user)
    Route::get('payouts', [PayoutController::class, 'index'])->name('payouts.index');
    Route::post('payouts', [PayoutController::class, 'store'])->name('payouts.store');

    // Activity Log
    Route::get('logger', [LoggerController::class, 'index'])->name('logger.index');

    // Subscriptions (user-facing)
    Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('subscriptions/history', [SubscriptionController::class, 'history'])->name('subscriptions.history');

    // Voucher redeem (user-facing)
    Route::post('vouchers/redeem', [VoucherController::class, 'redeem'])->name('vouchers.redeem');

    // ── Flow Builder ────────────────────────────────────────────
    Route::resource('flows', FlowController::class)->except(['show']);
    Route::get('flows/{flow}/nodes', [FlowController::class, 'nodes'])->name('flows.nodes');
    Route::post('flows/{flow}/nodes', [FlowController::class, 'nodesStore'])->name('flows.nodes.store');

    // ── Drip Campaigns ──────────────────────────────────────────
    Route::resource('drips', DripCampaignController::class)->except(['show']);
    Route::get('drips/{drip}/steps', [DripCampaignController::class, 'steps'])->name('drips.steps');
    Route::post('drips/{drip}/steps', [DripCampaignController::class, 'stepsStore'])->name('drips.steps.store');
    Route::delete('drips/{drip}/steps/{step}', [DripCampaignController::class, 'stepsDestroy'])->name('drips.steps.destroy');

    // ── A/B Testing ─────────────────────────────────────────────
    Route::resource('ab-tests', ABCampaignController::class)->except(['show']);
    Route::post('ab-tests/{test}/start', [ABCampaignController::class, 'start'])->name('ab-tests.start');
    Route::post('ab-tests/{test}/end', [ABCampaignController::class, 'end'])->name('ab-tests.end');

    // ── Click Tracking ──────────────────────────────────────────
    Route::get('click-stats', [ClickTrackController::class, 'index'])->name('click-stats.index');

    // ── Contact Tags ────────────────────────────────────────────
    Route::resource('contact-tags', ContactTagController::class)->except(['show', 'edit']);
    Route::post('contacts/{contact}/tags/{tag}/attach', [ContactTagController::class, 'attach'])->name('contacts.tags.attach');
    Route::post('contacts/{contact}/tags/{tag}/detach', [ContactTagController::class, 'detach'])->name('contacts.tags.detach');

    // ── Interactive Buttons ─────────────────────────────────────
    Route::resource('buttons', InteractiveButtonController::class)->except(['show']);

    // ── Catalogs ────────────────────────────────────────────────
    Route::resource('catalogs', CatalogController::class)->except(['show']);
    Route::get('catalogs/{catalog}/items', [CatalogController::class, 'items'])->name('catalogs.items');
    Route::post('catalogs/{catalog}/items', [CatalogController::class, 'itemsStore'])->name('catalogs.items.store');
    Route::put('catalogs/{catalog}/items/{item}', [CatalogController::class, 'itemsUpdate'])->name('catalogs.items.update');
    Route::delete('catalogs/{catalog}/items/{item}', [CatalogController::class, 'itemsDestroy'])->name('catalogs.items.destroy');

    // ── Commerce Orders ─────────────────────────────────────────
    Route::resource('commerce', CommerceOrderController::class)->except(['create', 'edit', 'destroy']);
    Route::post('commerce/{order}/confirm', [CommerceOrderController::class, 'confirm'])->name('commerce.confirm');
    Route::post('commerce/{order}/paid', [CommerceOrderController::class, 'paid'])->name('commerce.paid');
    Route::post('commerce/{order}/ship', [CommerceOrderController::class, 'ship'])->name('commerce.ship');
    Route::post('commerce/{order}/cancel', [CommerceOrderController::class, 'cancel'])->name('commerce.cancel');

    // ── CRM Deals ───────────────────────────────────────────────
    Route::resource('deals', DealController::class);
    Route::get('deals-board', [DealController::class, 'board'])->name('deals.board');
    Route::post('deals/{deal}/move', [DealController::class, 'move'])->name('deals.move');
    Route::get('deal-stages', [DealStageController::class, 'index'])->name('deal-stages.index');
    Route::post('deal-stages', [DealStageController::class, 'store'])->name('deal-stages.store');
    Route::put('deal-stages/{stage}', [DealStageController::class, 'update'])->name('deal-stages.update');
    Route::delete('deal-stages/{stage}', [DealStageController::class, 'destroy'])->name('deal-stages.destroy');
    Route::post('deal-stages/reorder', [DealStageController::class, 'reorder'])->name('deal-stages.reorder');

    // ── Team Inbox ──────────────────────────────────────────────
    Route::resource('team-members', TeamMemberController::class)->except(['show']);
    Route::get('inbox', [TeamInboxController::class, 'index'])->name('inbox.index');
    Route::post('inbox/assign', [TeamInboxController::class, 'assign'])->name('inbox.assign');
    Route::post('inbox/{assignment}/reassign', [TeamInboxController::class, 'reassign'])->name('inbox.reassign');
    Route::post('inbox/{assignment}/close', [TeamInboxController::class, 'close'])->name('inbox.close');
    Route::get('inbox/stats', [TeamInboxController::class, 'stats'])->name('inbox.stats');

    // ── SLA ─────────────────────────────────────────────────────
    Route::resource('sla-configs', SlaConfigController::class)->except(['show']);
    Route::get('sla-logs', [SlaConfigController::class, 'logs'])->name('sla-logs.index');
    Route::get('sla-dashboard', [SlaConfigController::class, 'dashboard'])->name('sla.dashboard');

    // ── AI Agents ───────────────────────────────────────────────
    Route::resource('ai-agents', AiAgentController::class)->except(['show']);
    Route::post('ai-agents/{agent}/test', [AiAgentController::class, 'test'])->name('ai-agents.test');

    // ── Intent Detection ────────────────────────────────────────
    Route::resource('intents', IntentConfigController::class)->except(['show']);

    // ── Media Templates ─────────────────────────────────────────
    Route::resource('media-templates', MediaTemplateController::class)->except(['show']);

    // ── Sentiment Dashboard ─────────────────────────────────────
    Route::get('sentiment', [SentimentController::class, 'index'])->name('sentiment.index');

    // ── Conversation Ratings ────────────────────────────────────
    Route::resource('ratings', ConversationRatingController::class)->only(['index', 'show']);
    Route::get('ratings-stats', [ConversationRatingController::class, 'stats'])->name('ratings.stats');

    // Admin
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
        Route::post('users/{user}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate');
        Route::get('payouts', [AdminPayoutController::class, 'index'])->name('payouts.index');
        Route::post('payouts/{payout}/approve', [AdminPayoutController::class, 'approve'])->name('payouts.approve');
        Route::post('payouts/{payout}/reject', [AdminPayoutController::class, 'reject'])->name('payouts.reject');
        Route::resource('vouchers', VoucherController::class)->only(['index', 'store', 'destroy']);
        Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::put('transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
        Route::resource('shorteners', ShortenerController::class)->except(['create', 'show', 'edit']);
        Route::get('pages/builder', [CmsPageController::class, 'builder'])->name('pages.builder');
        Route::get('pages/{page}/builder', [CmsPageController::class, 'builder'])->name('pages.builder.edit');
        Route::resource('pages', CmsPageController::class)->except(['create', 'show', 'edit']);
        Route::resource('blog', \App\Http\Controllers\Admin\BlogController::class)->except(['create', 'show', 'edit']);
        Route::post('blog/categories', [\App\Http\Controllers\Admin\BlogController::class, 'storeCategory'])->name('blog.categories.store');
        Route::put('blog/categories/{category}', [\App\Http\Controllers\Admin\BlogController::class, 'updateCategory'])->name('blog.categories.update');
        Route::delete('blog/categories/{category}', [\App\Http\Controllers\Admin\BlogController::class, 'destroyCategory'])->name('blog.categories.destroy');
        Route::resource('gateways', \App\Http\Controllers\Admin\PaymentGatewayController::class)->except(['create', 'show', 'edit']);
    });
});
