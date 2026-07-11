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
use App\Http\Controllers\MetaController;
use App\Http\Controllers\MetaWebhookController;
use App\Http\Controllers\WaFormController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\InstagramController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\WidgetController;
use App\Http\Controllers\GbmController;
use App\Http\Controllers\DiscordController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\TikTokController;
use App\Http\Controllers\LineController;
use App\Http\Controllers\TwitterController;
use App\Http\Controllers\PublishingController;

// Public widget embed script & lead capture
Route::get('/widget/{embedKey}.js', [WidgetController::class, 'embedScript'])->name('widget.embed');
Route::post('/widget/{embedKey}/lead', [WidgetController::class, 'storeLead'])->name('widget.lead');

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
Route::get('/register', fn() => view('auth.register'))->name('register')->middleware(\App\Http\Middleware\CaptureAffiliateReferral::class);
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Language switch
Route::get('/lang/{locale}', function ($locale) {
    $lang = \App\Models\Language::where('iso', $locale)->where('is_active', true)->first();
    if ($lang) {
        session(['locale' => $locale]);
        if (auth()->check()) {
            auth()->user()->update(['language_id' => $lang->id]);
        }
    }
    return redirect()->back();
})->name('lang.switch');

// Webhook (no auth)
Route::post('/webhook/whatsapp', [WebhookController::class, 'whatsapp'])->name('webhook.whatsapp');
Route::post('/webhook/whatsapp-status', [WebhookController::class, 'statusUpdate'])->name('webhook.status');
Route::match(['get', 'post'], '/webhook/meta', [MetaWebhookController::class, 'receive'])->name('webhook.meta');
Route::match(['get', 'post'], '/webhook/instagram', [InstagramController::class, 'webhook'])->name('webhook.instagram');
Route::post('/webhook/telegram/{account}', [TelegramController::class, 'webhook'])->name('webhook.telegram');
Route::post('/webhook/twilio', [App\Http\Controllers\TwilioController::class, 'webhook'])->name('webhook.twilio');
Route::post('/webhook/sendgrid', [App\Http\Controllers\SendGridController::class, 'webhook'])->name('webhook.sendgrid');
Route::match(['get', 'post'], '/webhook/facebook', [FacebookController::class, 'webhook'])->name('webhook.facebook');
Route::post('/webhook/gbm', [GbmController::class, 'webhook'])->name('webhook.gbm');
Route::match(['get', 'post'], '/webhook/discord', [DiscordController::class, 'webhook'])->name('webhook.discord');
Route::match(['get', 'post'], '/webhook/tiktok', [\App\Http\Controllers\TikTokController::class, 'webhook'])->name('webhook.tiktok');
Route::post('/webhook/line', [\App\Http\Controllers\LineController::class, 'webhook'])->name('webhook.line');
Route::match(['get', 'post'], '/webhook/twitter', [\App\Http\Controllers\TwitterController::class, 'webhook'])->name('webhook.twitter');
Route::post('/webhook/stripe', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'stripeWebhook'])->name('webhook.stripe')->withoutMiddleware(['web', \App\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/webhook/razorpay', [\App\Http\Controllers\Admin\PaymentGatewayController::class, 'razorpayWebhook'])->name('webhook.razorpay')->withoutMiddleware(['web', \App\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/webhook/store/{integration}', [\App\Http\Controllers\StoreController::class, 'webhook'])->name('store.webhook')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

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
    Route::get('messages/search', [\App\Http\Controllers\MessageController::class, 'search'])->name('messages.search');
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
    Route::post('flows/ai-generate', [FlowController::class, 'aiGenerate'])->name('flows.ai-generate');

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

    // ── AI Content Studio ───────────────────────────────────────
    Route::get('ai-content', [\App\Http\Controllers\AiContentController::class, 'index'])->name('ai-content.index');
    Route::post('ai-content/generate', [\App\Http\Controllers\AiContentController::class, 'generate'])->name('ai-content.generate');
    Route::get('ai-content/templates', [\App\Http\Controllers\AiContentController::class, 'templates'])->name('ai-content.templates');
    Route::post('ai-content/templates', [\App\Http\Controllers\AiContentController::class, 'templateStore'])->name('ai-content.templates.store');
    Route::put('ai-content/templates/{template}', [\App\Http\Controllers\AiContentController::class, 'templateUpdate'])->name('ai-content.templates.update');
    Route::delete('ai-content/templates/{template}', [\App\Http\Controllers\AiContentController::class, 'templateDestroy'])->name('ai-content.templates.destroy');

    Route::get('ai-image', [\App\Http\Controllers\AiImageController::class, 'index'])->name('ai-image.index');
    Route::post('ai-image/generate', [\App\Http\Controllers\AiImageController::class, 'generate'])->name('ai-image.generate');
    Route::get('ai-image/list', [\App\Http\Controllers\AiImageController::class, 'list'])->name('ai-image.list');

    Route::get('ai-planner', [\App\Http\Controllers\AiPlannerController::class, 'index'])->name('ai-planner.index');
    Route::post('ai-planner/generate', [\App\Http\Controllers\AiPlannerController::class, 'generate'])->name('ai-planner.generate');
    Route::delete('ai-planner/{plan}', [\App\Http\Controllers\AiPlannerController::class, 'destroy'])->name('ai-planner.destroy');

    Route::get('ai-best-time', [\App\Http\Controllers\AiBestTimeController::class, 'index'])->name('ai-best-time.index');
    Route::post('ai-best-time/suggest', [\App\Http\Controllers\AiBestTimeController::class, 'suggest'])->name('ai-best-time.suggest');

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

    // ── Meta Cloud API ──────────────────────────────────────────
    Route::resource('meta', MetaController::class)->except(['create', 'show', 'edit'])->parameters(['meta' => 'account']);
    Route::post('meta/{account}/connect', [MetaController::class, 'connect'])->name('meta.connect');
    Route::post('meta/{account}/disconnect', [MetaController::class, 'disconnect'])->name('meta.disconnect');
    Route::post('meta/{account}/session', [MetaController::class, 'sessionStore'])->name('meta.session.store');
    Route::post('meta/{account}/test', [MetaController::class, 'testSend'])->name('meta.test');

    // ── WhatsApp Forms ──────────────────────────────────────────
    Route::resource('forms', WaFormController::class)->except(['create', 'show', 'edit']);
    Route::post('forms/{form}/send', [WaFormController::class, 'sendForm'])->name('forms.send');
    Route::post('forms/{form}/send-bulk', [WaFormController::class, 'sendBulk'])->name('forms.send-bulk');
    Route::get('forms/{form}/submissions', [WaFormController::class, 'submissions'])->name('forms.submissions');
    Route::get('forms/{form}/export', [WaFormController::class, 'exportSubmissions'])->name('forms.export');

    // ── WhatsApp Calling ────────────────────────────────────────
    Route::resource('calls', CallController::class)->only(['index', 'store', 'destroy']);
    Route::get('calls/{broadcast}/logs', [CallController::class, 'logs'])->name('calls.logs');
    Route::post('calls/reply', [CallController::class, 'handleReply'])->name('calls.reply');

    // ── Instagram ───────────────────────────────────────────────
    Route::resource('instagram', InstagramController::class)->except(['create', 'show', 'edit']);
    Route::get('instagram/callback', [InstagramController::class, 'callback'])->name('instagram.callback');
    Route::post('instagram/{account}/connect', [InstagramController::class, 'connect'])->name('instagram.connect');
    Route::post('instagram/{account}/disconnect', [InstagramController::class, 'disconnect'])->name('instagram.disconnect');

    // ── Telegram ────────────────────────────────────────────────
    Route::resource('telegram', TelegramController::class)->except(['create', 'show', 'edit']);
    Route::post('telegram/{account}/connect', [TelegramController::class, 'connect'])->name('telegram.connect');
    Route::post('telegram/{account}/disconnect', [TelegramController::class, 'disconnect'])->name('telegram.disconnect');
    Route::post('telegram/{account}/test', [TelegramController::class, 'testSend'])->name('telegram.test');

    // ── Facebook Messenger ──────────────────────────────────────
    Route::resource('facebook', FacebookController::class)->except(['create', 'show', 'edit']);
    Route::post('facebook/{account}/connect', [FacebookController::class, 'connect'])->name('facebook.connect');
    Route::post('facebook/{account}/disconnect', [FacebookController::class, 'disconnect'])->name('facebook.disconnect');

    // ── Twilio (SMS) ────────────────────────────────────────────
    Route::resource('twilio', \App\Http\Controllers\TwilioController::class)->except(['create', 'show', 'edit']);
    Route::post('twilio/{account}/connect', [\App\Http\Controllers\TwilioController::class, 'connect'])->name('twilio.connect');
    Route::post('twilio/{account}/disconnect', [\App\Http\Controllers\TwilioController::class, 'disconnect'])->name('twilio.disconnect');
    Route::post('twilio/{account}/test', [\App\Http\Controllers\TwilioController::class, 'testSend'])->name('twilio.test');

    // ── SendGrid (Email) ────────────────────────────────────────
    Route::resource('sendgrid', \App\Http\Controllers\SendGridController::class)->except(['create', 'show', 'edit']);
    Route::post('sendgrid/{account}/connect', [\App\Http\Controllers\SendGridController::class, 'connect'])->name('sendgrid.connect');
    Route::post('sendgrid/{account}/disconnect', [\App\Http\Controllers\SendGridController::class, 'disconnect'])->name('sendgrid.disconnect');
    Route::post('sendgrid/{account}/test', [\App\Http\Controllers\SendGridController::class, 'testSend'])->name('sendgrid.test');
    Route::post('sendgrid/template', [\App\Http\Controllers\SendGridController::class, 'templateStore'])->name('sendgrid.template.store');
    Route::put('sendgrid/template/{template}', [\App\Http\Controllers\SendGridController::class, 'templateUpdate'])->name('sendgrid.template.update');
    Route::delete('sendgrid/template/{template}', [\App\Http\Controllers\SendGridController::class, 'templateDestroy'])->name('sendgrid.template.destroy');

    // ── Google Business Messages ─────────────────────────────────
    Route::resource('gbm', GbmController::class)->except(['create', 'show', 'edit']);
    Route::post('gbm/{account}/connect', [GbmController::class, 'connect'])->name('gbm.connect');
    Route::post('gbm/{account}/disconnect', [GbmController::class, 'disconnect'])->name('gbm.disconnect');

    // ── Discord ──────────────────────────────────────────────────
    Route::resource('discord', DiscordController::class)->except(['create', 'show', 'edit']);
    Route::post('discord/{account}/connect', [DiscordController::class, 'connect'])->name('discord.connect');
    Route::post('discord/{account}/disconnect', [DiscordController::class, 'disconnect'])->name('discord.disconnect');
    Route::post('discord/{account}/test', [DiscordController::class, 'testSend'])->name('discord.test');

    // ── TikTok ─────────────────────────────────────────────────
    Route::resource('tiktok', TikTokController::class)->except(['create', 'show', 'edit']);
    Route::post('tiktok/{account}/connect', [TikTokController::class, 'connect'])->name('tiktok.connect');
    Route::post('tiktok/{account}/disconnect', [TikTokController::class, 'disconnect'])->name('tiktok.disconnect');
    Route::post('tiktok/{account}/test', [TikTokController::class, 'testSend'])->name('tiktok.test');
    Route::get('tiktok/callback', [TikTokController::class, 'callback'])->name('tiktok.callback');

    // ── LINE ───────────────────────────────────────────────────
    Route::resource('line', LineController::class)->except(['create', 'show', 'edit']);
    Route::post('line/{account}/connect', [LineController::class, 'connect'])->name('line.connect');
    Route::post('line/{account}/disconnect', [LineController::class, 'disconnect'])->name('line.disconnect');
    Route::post('line/{account}/test', [LineController::class, 'testSend'])->name('line.test');
    Route::get('line/{account}/richmenus', [LineController::class, 'richMenuList'])->name('line.richmenus');

    // ── X/Twitter ──────────────────────────────────────────────
    Route::resource('twitter', TwitterController::class)->except(['create', 'show', 'edit']);
    Route::post('twitter/{account}/connect', [TwitterController::class, 'connect'])->name('twitter.connect');
    Route::post('twitter/{account}/disconnect', [TwitterController::class, 'disconnect'])->name('twitter.disconnect');
    Route::post('twitter/{account}/test', [TwitterController::class, 'testSend'])->name('twitter.test');
    Route::get('twitter/callback', [TwitterController::class, 'callback'])->name('twitter.callback');

    // ── Appointments ───────────────────────────────────────────
    Route::resource('appointments', AppointmentController::class)->except(['create', 'show', 'edit']);
    Route::post('appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])->name('appointments.confirm');
    Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::post('appointments/{appointment}/complete', [AppointmentController::class, 'complete'])->name('appointments.complete');
    Route::post('appointments/{appointment}/reminder', [AppointmentController::class, 'sendReminder'])->name('appointments.reminder');
    Route::get('api/appointments/slots', [AppointmentController::class, 'getSlots'])->name('api.appointments.slots');

    // ── Services ───────────────────────────────────────────────
    Route::post('services', [AppointmentController::class, 'serviceStore'])->name('services.store');
    Route::put('services/{service}', [AppointmentController::class, 'serviceUpdate'])->name('services.update');
    Route::delete('services/{service}', [AppointmentController::class, 'serviceDestroy'])->name('services.destroy');

    // ── Availabilities ─────────────────────────────────────────
    Route::post('availabilities', [AppointmentController::class, 'availabilityStore'])->name('availabilities.store');
    Route::post('availabilities/{availability}/toggle', [AppointmentController::class, 'availabilityToggle'])->name('availabilities.toggle');
    Route::delete('availabilities/{availability}', [AppointmentController::class, 'availabilityDestroy'])->name('availabilities.destroy');

        // ── Store Integration ────────────────────────────────────────
        Route::get('store', [App\Http\Controllers\StoreController::class, 'index'])->name('store.index');
        Route::post('store', [App\Http\Controllers\StoreController::class, 'store'])->name('store.store');
        Route::put('store/{integration}', [App\Http\Controllers\StoreController::class, 'update'])->name('store.update');
        Route::delete('store/{integration}', [App\Http\Controllers\StoreController::class, 'destroy'])->name('store.destroy');
        Route::post('store/{integration}/connect', [App\Http\Controllers\StoreController::class, 'connect'])->name('store.connect');
        Route::post('store/{integration}/sync', [App\Http\Controllers\StoreController::class, 'sync'])->name('store.sync');
        Route::patch('store/{integration}/settings', [App\Http\Controllers\StoreController::class, 'updateSettings'])->name('store.settings');

        // ── Google Sheets Sync ──────────────────────────────────────
        Route::get('sheets', [App\Http\Controllers\SheetsController::class, 'index'])->name('sheets.index');
        Route::post('sheets', [App\Http\Controllers\SheetsController::class, 'store'])->name('sheets.store');
        Route::put('sheets/{integration}', [App\Http\Controllers\SheetsController::class, 'update'])->name('sheets.update');
        Route::delete('sheets/{integration}', [App\Http\Controllers\SheetsController::class, 'destroy'])->name('sheets.destroy');
        Route::post('sheets/{integration}/connect', [App\Http\Controllers\SheetsController::class, 'connect'])->name('sheets.connect');
        Route::post('sheets/{integration}/sync', [App\Http\Controllers\SheetsController::class, 'sync'])->name('sheets.sync');

        // ── Widget Builder ──────────────────────────────────────────
        Route::resource('widgets', WidgetController::class)->only(['index', 'store', 'update', 'destroy']);

    // ── Kanban ──────────────────────────────────────────────────
    Route::get('kanban', [KanbanController::class, 'index'])->name('kanban.index');
    Route::post('kanban/move', [KanbanController::class, 'move'])->name('kanban.move');

    // ── Social Media Publishing ─────────────────────────────────
    Route::resource('publishing', PublishingController::class)->only(['index', 'store', 'destroy']);
    Route::post('publishing/{post}/publish', [PublishingController::class, 'publish'])->name('publishing.publish');
    Route::get('publishing-calendar', [PublishingController::class, 'calendar'])->name('publishing.calendar');
    Route::get('publishing-queue', [PublishingController::class, 'queue'])->name('publishing.queue');
    Route::get('publishing-drafts', [PublishingController::class, 'drafts'])->name('publishing.drafts');
    Route::get('publishing-campaigns', [PublishingController::class, 'campaigns'])->name('publishing.campaigns.index');
    Route::post('publishing-campaigns', [PublishingController::class, 'campaigns'])->name('publishing.campaigns.store');
    Route::put('publishing-campaigns/{campaign}', [PublishingController::class, 'updateCampaign'])->name('publishing.campaigns.update');
    Route::delete('publishing-campaigns/{campaign}', [PublishingController::class, 'destroyCampaign'])->name('publishing.campaigns.destroy');
    Route::get('publishing-labels', [PublishingController::class, 'labels'])->name('publishing.labels.index');
    Route::post('publishing-labels', [PublishingController::class, 'labels'])->name('publishing.labels.store');
    Route::put('publishing-labels/{label}', [PublishingController::class, 'updateLabel'])->name('publishing.labels.update');
    Route::delete('publishing-labels/{label}', [PublishingController::class, 'destroyLabel'])->name('publishing.labels.destroy');
    Route::get('publishing-captions', [PublishingController::class, 'captions'])->name('publishing.captions.index');
    Route::post('publishing-captions', [PublishingController::class, 'captions'])->name('publishing.captions.store');
    Route::put('publishing-captions/{caption}', [PublishingController::class, 'updateCaption'])->name('publishing.captions.update');
    Route::delete('publishing-captions/{caption}', [PublishingController::class, 'destroyCaption'])->name('publishing.captions.destroy');
    Route::get('publishing-rss', [PublishingController::class, 'rssSchedules'])->name('publishing.rss.index');
    Route::post('publishing-rss', [PublishingController::class, 'rssSchedules'])->name('publishing.rss.store');
    Route::put('publishing-rss/{schedule}', [PublishingController::class, 'updateRssSchedule'])->name('publishing.rss.update');
    Route::delete('publishing-rss/{schedule}', [PublishingController::class, 'destroyRssSchedule'])->name('publishing.rss.destroy');
    Route::post('publishing-rss/{schedule}/toggle', [PublishingController::class, 'toggleRssSchedule'])->name('publishing.rss.toggle');

    // ── Credits ──────────────────────────────────────────────────
    Route::get('credits', [\App\Http\Controllers\CreditController::class, 'index'])->name('credits.index');
    Route::post('credits/purchase', [\App\Http\Controllers\CreditController::class, 'purchase'])->name('credits.purchase');
    Route::get('credits/payment/{payment}', [\App\Http\Controllers\CreditController::class, 'payment'])->name('credits.payment');
    Route::post('credits/payment/{payment}/callback', [\App\Http\Controllers\CreditController::class, 'callback'])->name('credits.callback');

    // ── Affiliate ────────────────────────────────────────────────
    Route::get('affiliate', [\App\Http\Controllers\AffiliateController::class, 'index'])->name('affiliate.index');
    Route::post('affiliate/withdrawal', [\App\Http\Controllers\AffiliateController::class, 'requestWithdrawal'])->name('affiliate.withdrawal.request');

    // ── Coupon ───────────────────────────────────────────────────
    Route::post('coupons/validate', [\App\Http\Controllers\CouponController::class, 'validate'])->name('coupons.validate');
    Route::post('coupons/apply', [\App\Http\Controllers\CouponController::class, 'apply'])->name('coupons.apply');

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
        Route::resource('plans', \App\Http\Controllers\Admin\PlanController::class)->except(['create', 'show', 'edit']);

        // ── Monetization Admin ──────────────────────────────────
        Route::get('credit-packs', [\App\Http\Controllers\Admin\CreditPackController::class, 'index'])->name('credit-packs.index');
        Route::post('credit-packs', [\App\Http\Controllers\Admin\CreditPackController::class, 'store'])->name('credit-packs.store');
        Route::post('credit-packs/{pack}/toggle', [\App\Http\Controllers\Admin\CreditPackController::class, 'toggle'])->name('credit-packs.toggle');
        Route::delete('credit-packs/{pack}', [\App\Http\Controllers\Admin\CreditPackController::class, 'destroy'])->name('credit-packs.destroy');
        Route::get('coupons', [\App\Http\Controllers\Admin\CouponController::class, 'index'])->name('coupons.index');
        Route::post('coupons', [\App\Http\Controllers\Admin\CouponController::class, 'store'])->name('coupons.store');
        Route::post('coupons/{coupon}/toggle', [\App\Http\Controllers\Admin\CouponController::class, 'toggle'])->name('coupons.toggle');
        Route::delete('coupons/{coupon}', [\App\Http\Controllers\Admin\CouponController::class, 'destroy'])->name('coupons.destroy');
        Route::get('affiliate-withdrawals', [\App\Http\Controllers\Admin\AffiliateWithdrawalController::class, 'index'])->name('affiliate-withdrawals.index');
        Route::post('affiliate-withdrawals/{withdrawal}/approve', [\App\Http\Controllers\Admin\AffiliateWithdrawalController::class, 'approve'])->name('affiliate-withdrawals.approve');
        Route::post('affiliate-withdrawals/{withdrawal}/reject', [\App\Http\Controllers\Admin\AffiliateWithdrawalController::class, 'reject'])->name('affiliate-withdrawals.reject');
        Route::get('credit-transactions', [\App\Http\Controllers\Admin\CreditTransactionController::class, 'index'])->name('credit-transactions.index');
        Route::post('credit-transactions/grant', [\App\Http\Controllers\Admin\CreditTransactionController::class, 'grant'])->name('credit-transactions.grant');
    });
});
