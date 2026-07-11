<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" <?php if(session('language_rtl')): ?> dir="rtl" <?php endif; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', config('app.name')); ?> — WhatsApp SaaS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.3.2/css/flag-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <?php echo $__env->yieldPushContent('styles'); ?>
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        :root { --sidebar-width: 260px; }
        .sidebar { width: var(--sidebar-width); transition: transform .3s ease; }
        @media (max-width: 1023px) {
            .sidebar { position: fixed; top: 0; left: 0; bottom: 0; z-index: 50; transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay { display: none; }
            .sidebar-overlay.show { display: block; }
        }
        .nav-group-header { cursor: pointer; user-select: none; scroll-margin-top: 8px; }
        .nav-group-header .chevron { transition: transform .2s ease; }
        .nav-group-header.open .chevron { transform: rotate(90deg); }
        .nav-group-body { overflow: hidden; transition: max-height .3s ease; }
        .nav-group-body.open { overflow: visible; }
        .nav-link { transition: all .15s ease; }
        .nav-link.active { background: rgba(99,102,241,.15); color: #a5b4fc; font-weight: 600; }
        .nav-link:hover { background: rgba(255,255,255,.06); padding-left: 1.25rem; }
        .nav-link.active { background: rgba(59,130,246,.15); color: #60a5fa; border-left: 3px solid #3b82f6; }
        .topbar { backdrop-filter: blur(12px) saturate(180%); background: rgba(255,255,255,.8); }
        .card-lift { transition: transform .25s, box-shadow .25s; }
        .card-lift:hover { transform: translateY(-2px); box-shadow: 0 8px 24px -8px rgba(0,0,0,.12); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
                        sidebar: { bg: '#1e293b', hover: '#334155', active: '#1e3a5f' },
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">

<?php if(auth()->guard()->check()): ?>
<div class="flex min-h-screen">

<div class="sidebar-overlay fixed inset-0 bg-black/50 z-40 lg:hidden"
    :class="sidebarOpen && 'show'" @click="sidebarOpen = false"></div>


<aside class="sidebar bg-sidebar-bg flex flex-col h-screen fixed lg:sticky top-0 left-0 z-50"
    :class="sidebarOpen && 'open'">
    <div class="flex items-center gap-3 px-5 h-16 border-b border-white/10 flex-shrink-0">
        <i class="fas fa-paper-plane text-brand-400 text-lg"></i>
        <span class="text-white font-extrabold text-lg tracking-tight"><?php echo e(config('app.name')); ?></span>
    </div>

    <nav class="flex-1 overflow-y-auto py-3 px-3 space-y-1">
        <?php $langs = \App\Models\Language::active()->ordered()->get(); $cur = app()->getLocale(); ?>

        
        <div class="nav-group-header flex items-center justify-between px-3 py-2 text-[11px] font-semibold uppercase tracking-wider text-gray-500 open" x-data="{ open: true }" @click="open = !open; $el.classList.toggle('open'); if(open) { let el = $el; setTimeout(() => { el.scrollIntoView({block: 'nearest', behavior: 'instant'}) }, 150) }">
            <span><?php echo e(__('sidebar.overview')); ?></span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 200px;">
            <a href="<?php echo e(route('dashboard.stats')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('dashboard*') ? 'active' : ''); ?>">
                <i class="fas fa-chart-pie w-4 text-center"></i> <?php echo e(__('sidebar.dashboard')); ?>

            </a>
            <a href="<?php echo e(route('logger.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('logger*') ? 'active' : ''); ?>">
                <i class="fas fa-history w-4 text-center"></i> <?php echo e(__('sidebar.activity_feed')); ?>

            </a>
        </div>

        
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 open" x-data="{ open: true }" @click="open = !open; $el.classList.toggle('open'); if(open) { let el = $el; setTimeout(() => { el.scrollIntoView({block: 'nearest', behavior: 'instant'}) }, 150) }">
            <span>WhatsApp</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 500px;">
            <a href="<?php echo e(route('sessions.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('sessions*') ? 'active' : ''); ?>">
                <i class="fab fa-whatsapp w-4 text-center"></i> Sessions / Agents
            </a>
        </div>

        
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 open" x-data="{ open: true }" @click="open = !open; $el.classList.toggle('open'); if(open) { let el = $el; setTimeout(() => { el.scrollIntoView({block: 'nearest', behavior: 'instant'}) }, 150) }">
            <span><?php echo e(__('sidebar.appointments')); ?></span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 200px;">
            <a href="<?php echo e(route('appointments.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('appointments*') ? 'active' : ''); ?>">
                <i class="fas fa-calendar-check w-4 text-center"></i> <?php echo e(__('sidebar.appointments')); ?>

            </a>
        </div>

        
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 open" x-data="{ open: true }" @click="open = !open; $el.classList.toggle('open'); if(open) { let el = $el; setTimeout(() => { el.scrollIntoView({block: 'nearest', behavior: 'instant'}) }, 150) }">
            <span><?php echo e(__('sidebar.inbox')); ?></span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 500px;">
            <a href="<?php echo e(route('chat.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('chat*') || request()->is('/') && !request()->is('dashboard*') ? 'active' : ''); ?>">
                <i class="fas fa-comments w-4 text-center"></i> <?php echo e(__('sidebar.live_chat')); ?>

            </a>
            <a href="<?php echo e(route('messages.received')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('messages/received*') ? 'active' : ''); ?>">
                <i class="fas fa-inbox w-4 text-center"></i> <?php echo e(__('sidebar.conversations')); ?>

            </a>
            <a href="<?php echo e(route('messages.queue')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('messages/queue*') ? 'active' : ''); ?>">
                <i class="fas fa-clock w-4 text-center"></i> <?php echo e(__('sidebar.queue')); ?>

            </a>
            <a href="<?php echo e(route('messages.sent')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('messages/sent*') ? 'active' : ''); ?>">
                <i class="fas fa-check-double w-4 text-center"></i> <?php echo e(__('sidebar.sent_messages')); ?>

            </a>
            <a href="<?php echo e(route('contacts.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('contacts*') ? 'active' : ''); ?>">
                <i class="fas fa-address-book w-4 text-center"></i> <?php echo e(__('sidebar.contacts')); ?>

            </a>
            <a href="<?php echo e(route('groups.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('groups*') ? 'active' : ''); ?>">
                <i class="fas fa-layer-group w-4 text-center"></i> <?php echo e(__('sidebar.groups')); ?>

            </a>
        </div>

        
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open'); if(open) { let el = $el; setTimeout(() => { el.scrollIntoView({block: 'nearest', behavior: 'instant'}) }, 150) }">
            <span><?php echo e(__('sidebar.marketing')); ?></span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 400px;' : 'max-height: 0;'">
            <a href="<?php echo e(route('messages.send.form')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('messages/send*') ? 'active' : ''); ?>">
                <i class="fas fa-paper-plane w-4 text-center"></i> <?php echo e(__('sidebar.broadcast')); ?>

            </a>
            <a href="<?php echo e(route('campaigns.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('campaigns*') ? 'active' : ''); ?>">
                <i class="fas fa-bullhorn w-4 text-center"></i> <?php echo e(__('sidebar.campaigns')); ?>

            </a>
            <a href="<?php echo e(route('recurrings.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('recurrings*') ? 'active' : ''); ?>">
                <i class="fas fa-calendar-alt w-4 text-center"></i> <?php echo e(__('sidebar.scheduled_messages')); ?>

            </a>
            <a href="<?php echo e(route('drips.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('drips*') ? 'active' : ''); ?>">
                <i class="fas fa-water w-4 text-center"></i> <?php echo e(__('sidebar.drip_campaign')); ?>

            </a>
            <a href="<?php echo e(route('ab-tests.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('ab-tests*') ? 'active' : ''); ?>">
                <i class="fas fa-flask w-4 text-center"></i> <?php echo e(__('sidebar.ab_testing')); ?>

            </a>
            <a href="<?php echo e(route('click-stats.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('click-stats*') ? 'active' : ''); ?>">
                <i class="fas fa-mouse-pointer w-4 text-center"></i> <?php echo e(__('sidebar.click_tracking')); ?>

            </a>
            <a href="<?php echo e(route('templates.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('templates*') ? 'active' : ''); ?>">
                <i class="fas fa-file-lines w-4 text-center"></i> <?php echo e(__('sidebar.templates')); ?>

            </a>
        </div>

        
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open'); if(open) { let el = $el; setTimeout(() => { el.scrollIntoView({block: 'nearest', behavior: 'instant'}) }, 150) }">
            <span><?php echo e(__('sidebar.ai_studio')); ?></span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 300px;' : 'max-height: 0;'">
            <a href="<?php echo e(route('ai-content.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('ai-content*') ? 'active' : ''); ?>">
                <i class="fas fa-pen-fancy w-4 text-center"></i> <?php echo e(__('sidebar.ai_content')); ?>

            </a>
            <a href="<?php echo e(route('ai-image.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('ai-image*') ? 'active' : ''); ?>">
                <i class="fas fa-image w-4 text-center"></i> <?php echo e(__('sidebar.ai_image')); ?>

            </a>
            <a href="<?php echo e(route('ai-planner.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('ai-planner*') ? 'active' : ''); ?>">
                <i class="fas fa-calendar-check w-4 text-center"></i> <?php echo e(__('sidebar.ai_planner')); ?>

            </a>
            <a href="<?php echo e(route('ai-best-time.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('ai-best-time*') ? 'active' : ''); ?>">
                <i class="fas fa-clock w-4 text-center"></i> <?php echo e(__('sidebar.ai_best_time')); ?>

            </a>
        </div>

        
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open'); if(open) { let el = $el; setTimeout(() => { el.scrollIntoView({block: 'nearest', behavior: 'instant'}) }, 150) }">
            <span><?php echo e(__('sidebar.ai_automation')); ?></span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 400px;' : 'max-height: 0;'">
            <a href="<?php echo e(route('ai-agents.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('ai-agents*') ? 'active' : ''); ?>">
                <i class="fas fa-robot w-4 text-center"></i> <?php echo e(__('sidebar.ai_agents')); ?>

            </a>
            <a href="<?php echo e(route('intents.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('intents*') ? 'active' : ''); ?>">
                <i class="fas fa-brain w-4 text-center"></i> <?php echo e(__('sidebar.intent')); ?>

            </a>
            <a href="<?php echo e(route('autoreplies.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('autoreplies*') ? 'active' : ''); ?>">
                <i class="fas fa-reply w-4 text-center"></i> <?php echo e(__('sidebar.auto_reply')); ?>

            </a>
            <a href="<?php echo e(route('knowledge.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('knowledge*') ? 'active' : ''); ?>">
                <i class="fas fa-database w-4 text-center"></i> <?php echo e(__('sidebar.knowledge_base')); ?>

            </a>
            <a href="<?php echo e(route('ai-keys.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('ai-keys*') ? 'active' : ''); ?>">
                <i class="fas fa-key w-4 text-center"></i> <?php echo e(__('sidebar.ai_api_keys')); ?>

            </a>
        </div>

        
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open'); if(open) { let el = $el; setTimeout(() => { el.scrollIntoView({block: 'nearest', behavior: 'instant'}) }, 150) }">
            <span><?php echo e(__('sidebar.commerce')); ?></span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 300px;' : 'max-height: 0;'">
            <a href="<?php echo e(route('catalogs.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('catalogs*') ? 'active' : ''); ?>">
                <i class="fas fa-shopping-bag w-4 text-center"></i> <?php echo e(__('sidebar.catalog')); ?>

                <span class="ml-auto text-[9px] text-blue-400 border border-blue-400/40 rounded px-1.5 py-0">Meta</span>
            </a>
            <a href="<?php echo e(route('commerce.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('commerce*') ? 'active' : ''); ?>">
                <i class="fas fa-shopping-cart w-4 text-center"></i> <?php echo e(__('sidebar.orders')); ?>

            </a>
            <a href="<?php echo e(route('buttons.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('buttons*') ? 'active' : ''); ?>">
                <i class="fas fa-hand-pointer w-4 text-center"></i> <?php echo e(__('sidebar.buttons')); ?>

            </a>
            <a href="<?php echo e(route('forms.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('forms*') ? 'active' : ''); ?>">
                <i class="fas fa-wpforms w-4 text-center"></i> <?php echo e(__('sidebar.forms')); ?>

                <span class="ml-auto text-[9px] text-blue-400 border border-blue-400/40 rounded px-1.5 py-0">Meta</span>
            </a>
            <a href="<?php echo e(route('media-templates.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('media-templates*') ? 'active' : ''); ?>">
                <i class="fas fa-photo-video w-4 text-center"></i> <?php echo e(__('sidebar.media')); ?>

            </a>
        </div>

        
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open'); if(open) { let el = $el; setTimeout(() => { el.scrollIntoView({block: 'nearest', behavior: 'instant'}) }, 150) }">
            <span><?php echo e(__('sidebar.crm')); ?></span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 300px;' : 'max-height: 0;'">
            <a href="<?php echo e(route('deals.board')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('deals-board*') ? 'active' : ''); ?>">
                <i class="fas fa-funnel-dollar w-4 text-center"></i> <?php echo e(__('sidebar.pipeline')); ?>

            </a>
            <a href="<?php echo e(route('deals.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('deals') || request()->is('deals/*') && !request()->is('deals-board*') ? 'active' : ''); ?>">
                <i class="fas fa-handshake w-4 text-center"></i> <?php echo e(__('sidebar.deals')); ?>

            </a>
            <a href="<?php echo e(route('kanban.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('kanban*') ? 'active' : ''); ?>">
                <i class="fas fa-columns w-4 text-center"></i> <?php echo e(__('sidebar.kanban')); ?>

            </a>
            <a href="<?php echo e(route('contact-tags.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('contact-tags*') ? 'active' : ''); ?>">
                <i class="fas fa-tags w-4 text-center"></i> <?php echo e(__('sidebar.tags')); ?>

            </a>
        </div>

        
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open'); if(open) { let el = $el; setTimeout(() => { el.scrollIntoView({block: 'nearest', behavior: 'instant'}) }, 150) }">
            <span><?php echo e(__('sidebar.social_media')); ?></span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 300px;' : 'max-height: 0;'">
            <a href="<?php echo e(route('publishing.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->routeIs('publishing.index') ? 'active' : ''); ?>">
                <i class="fas fa-pen-to-square w-4 text-center"></i> <?php echo e(__('sidebar.publishing_composer')); ?>

            </a>
            <a href="<?php echo e(route('publishing.calendar')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->routeIs('publishing.calendar') ? 'active' : ''); ?>">
                <i class="fas fa-calendar-days w-4 text-center"></i> <?php echo e(__('sidebar.publishing_calendar')); ?>

            </a>
            <a href="<?php echo e(route('publishing.queue')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->routeIs('publishing.queue') ? 'active' : ''); ?>">
                <i class="fas fa-clock w-4 text-center"></i> <?php echo e(__('sidebar.publishing_queue')); ?>

            </a>
            <a href="<?php echo e(route('publishing.drafts')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->routeIs('publishing.drafts') ? 'active' : ''); ?>">
                <i class="fas fa-file-lines w-4 text-center"></i> <?php echo e(__('sidebar.publishing_drafts')); ?>

            </a>
            <a href="<?php echo e(route('publishing.rss.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->routeIs('publishing.rss.*') ? 'active' : ''); ?>">
                <i class="fas fa-rss w-4 text-center"></i> <?php echo e(__('sidebar.publishing_rss')); ?>

            </a>
            <a href="<?php echo e(route('publishing.captions.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->routeIs('publishing.captions.*') ? 'active' : ''); ?>">
                <i class="fas fa-book-open w-4 text-center"></i> <?php echo e(__('sidebar.publishing_captions')); ?>

            </a>
        </div>

    
    <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open'); if(open) { let el = $el; setTimeout(() => { el.scrollIntoView({block: 'nearest', behavior: 'instant'}) }, 150) }">
        <span><?php echo e(__('sidebar.channels')); ?></span>
        <i class="fas fa-chevron-right text-[9px] chevron"></i>
    </div>
    <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 600px;' : 'max-height: 0;'">
            <a href="<?php echo e(route('meta.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('meta*') ? 'active' : ''); ?>">
                <i class="fab fa-meta w-4 text-center"></i> <?php echo e(__('sidebar.whatsapp_cloud_api')); ?>

            </a>
            <a href="<?php echo e(route('calls.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('calls*') ? 'active' : ''); ?>">
                <i class="fas fa-phone-volume w-4 text-center"></i> <?php echo e(__('sidebar.whatsapp_calling')); ?>

                <span class="ml-auto text-[9px] text-blue-400 border border-blue-400/40 rounded px-1.5 py-0">Meta</span>
            </a>
            <a href="<?php echo e(route('instagram.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('instagram*') ? 'active' : ''); ?>">
                <i class="fab fa-instagram w-4 text-center"></i> <?php echo e(__('sidebar.instagram')); ?>

                <span class="ml-auto text-[9px] text-blue-400 border border-blue-400/40 rounded px-1.5 py-0">Meta</span>
            </a>
            <a href="<?php echo e(route('webhooks.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('webhooks*') ? 'active' : ''); ?>">
                <i class="fas fa-bolt w-4 text-center"></i> <?php echo e(__('sidebar.webhooks')); ?>

            </a>
            <a href="<?php echo e(route('telegram.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('telegram*') ? 'active' : ''); ?>">
                <i class="fab fa-telegram w-4 text-center"></i> <?php echo e(__('sidebar.telegram')); ?>

            </a>
            <a href="<?php echo e(route('twilio.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('twilio*') ? 'active' : ''); ?>">
                <i class="fas fa-sms w-4 text-center"></i> <?php echo e(__('sidebar.sms')); ?>

            </a>
            <a href="<?php echo e(route('sendgrid.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('sendgrid*') ? 'active' : ''); ?>">
                <i class="fas fa-envelope w-4 text-center"></i> <?php echo e(__('sidebar.email')); ?>

            </a>
            <a href="<?php echo e(route('facebook.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('facebook*') ? 'active' : ''); ?>">
                <i class="fab fa-facebook w-4 text-center"></i> <?php echo e(__('sidebar.facebook')); ?>

            </a>
            <a href="<?php echo e(route('gbm.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('gbm*') ? 'active' : ''); ?>">
                <i class="fab fa-google w-4 text-center"></i> <?php echo e(__('sidebar.gbm')); ?>

            </a>
            <a href="<?php echo e(route('discord.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('discord*') ? 'active' : ''); ?>">
                <i class="fab fa-discord w-4 text-center"></i> <?php echo e(__('sidebar.discord')); ?>

            </a>
            <a href="<?php echo e(route('tiktok.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('tiktok*') ? 'active' : ''); ?>">
                <i class="fab fa-tiktok w-4 text-center"></i> <?php echo e(__('sidebar.tiktok')); ?>

            </a>
            <a href="<?php echo e(route('line.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('line*') ? 'active' : ''); ?>">
                <i class="fab fa-line w-4 text-center"></i> <?php echo e(__('sidebar.line')); ?>

            </a>
            <a href="<?php echo e(route('twitter.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('twitter*') ? 'active' : ''); ?>">
                <i class="fab fa-x-twitter w-4 text-center"></i> <?php echo e(__('sidebar.twitter')); ?>

            </a>
            <a href="<?php echo e(route('widgets.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('widgets*') ? 'active' : ''); ?>">
                <i class="fas fa-puzzle-piece w-4 text-center"></i> <?php echo e(__('sidebar.widgets')); ?>

            </a>
        </div>

        
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open'); if(open) { let el = $el; setTimeout(() => { el.scrollIntoView({block: 'nearest', behavior: 'instant'}) }, 150) }">
            <span><?php echo e(__('sidebar.team')); ?></span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 300px;' : 'max-height: 0;'">
            <a href="<?php echo e(route('inbox.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('inbox*') ? 'active' : ''); ?>">
                <i class="fas fa-inbox w-4 text-center"></i> <?php echo e(__('sidebar.shared_inbox')); ?>

            </a>
            <a href="<?php echo e(route('team-members.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('team-members*') ? 'active' : ''); ?>">
                <i class="fas fa-users w-4 text-center"></i> <?php echo e(__('sidebar.members')); ?>

            </a>
            <a href="<?php echo e(route('sla.dashboard')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('sla-dashboard*') ? 'active' : ''); ?>">
                <i class="fas fa-tachometer-alt w-4 text-center"></i> <?php echo e(__('sidebar.sla')); ?>

            </a>
            <a href="<?php echo e(route('sla-configs.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('sla-configs*') || request()->is('sla-logs*') ? 'active' : ''); ?>">
                <i class="fas fa-stopwatch w-4 text-center"></i> <?php echo e(__('sidebar.sla_settings')); ?>

            </a>
        </div>

        
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open'); if(open) { let el = $el; setTimeout(() => { el.scrollIntoView({block: 'nearest', behavior: 'instant'}) }, 150) }">
            <span><?php echo e(__('sidebar.analytics')); ?></span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 300px;' : 'max-height: 0;'">
            <a href="<?php echo e(route('dashboard.stats')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('dashboard*') ? 'active' : ''); ?>">
                <i class="fas fa-chart-bar w-4 text-center"></i> <?php echo e(__('sidebar.dashboard')); ?>

            </a>
            <a href="<?php echo e(route('sentiment.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('sentiment*') ? 'active' : ''); ?>">
                <i class="fas fa-smile w-4 text-center"></i> <?php echo e(__('sidebar.sentiment')); ?>

            </a>
            <a href="<?php echo e(route('ratings.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('ratings*') ? 'active' : ''); ?>">
                <i class="fas fa-star w-4 text-center"></i> <?php echo e(__('sidebar.ratings')); ?>

            </a>
            <a href="<?php echo e(route('logger.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('logger*') ? 'active' : ''); ?>">
                <i class="fas fa-history w-4 text-center"></i> <?php echo e(__('sidebar.activity_logs')); ?>

            </a>
        </div>

        
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open'); if(open) { let el = $el; setTimeout(() => { el.scrollIntoView({block: 'nearest', behavior: 'instant'}) }, 150) }">
            <span><?php echo e(__('sidebar.integrations')); ?></span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 300px;' : 'max-height: 0;'">
            <a href="<?php echo e(route('store.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('store*') ? 'active' : ''); ?>">
                <i class="fas fa-store w-4 text-center"></i> <?php echo e(__('sidebar.e_commerce')); ?>

            </a>
            <a href="<?php echo e(route('sheets.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('sheets*') ? 'active' : ''); ?>">
                <i class="fas fa-table w-4 text-center"></i> <?php echo e(__('sidebar.google_sheets')); ?>

            </a>
        </div>

        
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open'); if(open) { let el = $el; setTimeout(() => { el.scrollIntoView({block: 'nearest', behavior: 'instant'}) }, 150) }">
            <span><?php echo e(__('sidebar.earn')); ?></span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 300px;' : 'max-height: 0;'">
            <a href="<?php echo e(route('credits.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('credits*') ? 'active' : ''); ?>">
                <i class="fas fa-coins w-4 text-center"></i> <?php echo e(__('sidebar.credits')); ?>

            </a>
            <a href="<?php echo e(route('affiliate.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('affiliate*') ? 'active' : ''); ?>">
                <i class="fas fa-hand-holding-heart w-4 text-center"></i> <?php echo e(__('sidebar.affiliate')); ?>

            </a>
        </div>

        
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open'); if(open) { let el = $el; setTimeout(() => { el.scrollIntoView({block: 'nearest', behavior: 'instant'}) }, 150) }">
            <span><?php echo e(__('sidebar.settings')); ?></span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 500px;' : 'max-height: 0;'">
            <a href="<?php echo e(route('servers.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('servers*') ? 'active' : ''); ?>">
                <i class="fas fa-server w-4 text-center"></i> <?php echo e(__('sidebar.server')); ?>

            </a>
            <a href="<?php echo e(route('subscriptions.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('subscriptions*') ? 'active' : ''); ?>">
                <i class="fas fa-id-card w-4 text-center"></i> <?php echo e(__('sidebar.subscription')); ?>

            </a>
            <a href="<?php echo e(route('plans.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('plans*') && !request()->is('admin*') ? 'active' : ''); ?>">
                <i class="fas fa-box w-4 text-center"></i> <?php echo e(__('sidebar.plans')); ?>

            </a>
            <a href="<?php echo e(route('tokens.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('tokens*') ? 'active' : ''); ?>">
                <i class="fas fa-key w-4 text-center"></i> <?php echo e(__('sidebar.api_tokens')); ?>

            </a>
            <a href="<?php echo e(route('payouts.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('payouts*') && !request()->is('admin*') ? 'active' : ''); ?>">
                <i class="fas fa-wallet w-4 text-center"></i> <?php echo e(__('sidebar.payout')); ?>

            </a>
            <?php if(Auth::user()->isAdmin()): ?>
            <div class="mt-2 pt-2 border-t border-white/10">
                <div class="px-3 py-1 text-[10px] font-semibold text-gray-600 uppercase tracking-wider"><?php echo e(__('sidebar.admin')); ?></div>
            </div>
            <a href="<?php echo e(route('admin.users.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('admin/users*') ? 'active' : ''); ?>">
                <i class="fas fa-users-cog w-4 text-center"></i> <?php echo e(__('sidebar.users')); ?>

            </a>
            <a href="<?php echo e(route('admin.plans.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('admin/plans*') ? 'active' : ''); ?>">
                <i class="fas fa-box w-4 text-center"></i> <?php echo e(__('sidebar.plans')); ?>

            </a>
            <a href="<?php echo e(route('admin.vouchers.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('admin/vouchers*') ? 'active' : ''); ?>">
                <i class="fas fa-ticket-alt w-4 text-center"></i> <?php echo e(__('sidebar.vouchers')); ?>

            </a>
            <a href="<?php echo e(route('admin.transactions.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('admin/transactions*') ? 'active' : ''); ?>">
                <i class="fas fa-exchange-alt w-4 text-center"></i> <?php echo e(__('sidebar.transactions')); ?>

            </a>
            <a href="<?php echo e(route('admin.shorteners.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('admin/shorteners*') ? 'active' : ''); ?>">
                <i class="fas fa-link w-4 text-center"></i> <?php echo e(__('sidebar.url_shortener')); ?>

            </a>
            <a href="<?php echo e(route('admin.pages.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('admin/pages*') ? 'active' : ''); ?>">
                <i class="fas fa-file-alt w-4 text-center"></i> <?php echo e(__('sidebar.cms')); ?>

            </a>
            <a href="<?php echo e(route('admin.blog.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('admin/blog*') ? 'active' : ''); ?>">
                <i class="fas fa-blog w-4 text-center"></i> <?php echo e(__('sidebar.blog')); ?>

            </a>
            <a href="<?php echo e(route('admin.payouts.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('admin/payouts*') ? 'active' : ''); ?>">
                <i class="fas fa-hand-holding-usd w-4 text-center"></i> <?php echo e(__('sidebar.payout_approval')); ?>

            </a>
            <div class="mt-2 pt-2 border-t border-white/10">
                <div class="px-3 py-1 text-[10px] font-semibold text-gray-600 uppercase tracking-wider"><?php echo e(__('sidebar.monetization')); ?></div>
            </div>
            <a href="<?php echo e(route('admin.credit-packs.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('admin/credit-packs*') ? 'active' : ''); ?>">
                <i class="fas fa-box-open w-4 text-center"></i> <?php echo e(__('sidebar.credit_packs')); ?>

            </a>
            <a href="<?php echo e(route('admin.coupons.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('admin/coupons*') ? 'active' : ''); ?>">
                <i class="fas fa-percent w-4 text-center"></i> <?php echo e(__('sidebar.coupons')); ?>

            </a>
            <a href="<?php echo e(route('admin.credit-transactions.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('admin/credit-transactions*') ? 'active' : ''); ?>">
                <i class="fas fa-list-alt w-4 text-center"></i> <?php echo e(__('sidebar.credit_transactions')); ?>

            </a>
            <a href="<?php echo e(route('admin.affiliate-withdrawals.index')); ?>" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 <?php echo e(request()->is('admin/affiliate-withdrawals*') ? 'active' : ''); ?>">
                <i class="fas fa-money-bill-wave w-4 text-center"></i> <?php echo e(__('sidebar.affiliate_withdrawals')); ?>

            </a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="p-3 border-t border-white/10 flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold">
            <?php echo e(strtoupper(substr(Auth::user()->name, 0, 2))); ?>

        </div>
        <div class="flex-1 min-w-0">
            <div class="text-sm font-medium text-white truncate"><?php echo e(Auth::user()->name); ?></div>
            <div class="text-[11px] text-gray-500"><?php echo e(__('sidebar.online')); ?></div>
        </div>
        <div class="flex items-center gap-1">
            <?php echo $__env->make('components.language-switcher', [
                'languages' => $langs ?? \App\Models\Language::active()->ordered()->get(),
                'currentLocale' => $cur ?? app()->getLocale(),
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <form action="<?php echo e(route('logout')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <button class="text-gray-500 hover:text-red-400 transition"><i class="fas fa-sign-out-alt text-sm"></i></button>
        </form>
    </div>
</aside>


<div class="flex-1 flex flex-col min-h-screen min-w-0">
    
    <header class="topbar sticky top-0 z-30 border-b border-gray-200/60">
        <div class="flex items-center justify-between px-5 h-14">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition">
                <i class="fas fa-bars text-gray-600 text-lg"></i>
            </button>
            <div class="flex items-center gap-4 ml-auto">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-50 border border-green-200">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    <span class="text-xs font-medium text-green-700"><?php echo e(\App\Models\WaSession::where('user_id', Auth::id())->where('status','connected')->count()); ?> <?php echo e(__('sidebar.agents_online')); ?></span>
                </div>
            </div>
        </div>
    </header>

    
    <main class="flex-1 p-5 lg:p-6">
        <?php if(session('success')): ?>
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm font-medium flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500"></i> <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-medium flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-red-500"></i> <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>
        <?php if(session('warning')): ?>
            <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 text-sm font-medium flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-amber-500"></i> <?php echo e(session('warning')); ?>

            </div>
        <?php endif; ?>
        <?php echo $__env->yieldContent('content'); ?>
    </main>
</div>
</div>
<?php endif; ?>

<?php if(auth()->guard()->guest()): ?>
<main class="min-h-screen flex items-center justify-center p-4">
    <?php echo $__env->yieldContent('content'); ?>
</main>
<?php endif; ?>

<?php echo $__env->yieldPushContent('scripts'); ?>
<script>
// Sidebar — scroll to active link, expand parent group
(function() {
    var nav = document.querySelector('nav.overflow-y-auto');
    if (!nav) return;

    var active = nav.querySelector('.nav-link.active');
    if (active) {
        // Expand parent group if collapsed
        var body = active.closest('.nav-group-body');
        if (body) {
            var header = body.previousElementSibling;
            if (header && header.classList.contains('nav-group-header') && !header.classList.contains('open')) {
                header.classList.add('open');
                body.style.maxHeight = '1000px';
            }
        }
        // Scroll to active link
        setTimeout(function() {
            active.scrollIntoView({block: 'center', behavior: 'instant'});
        }, 200);
        return;
    }

    // Restore last scroll position
    var saved = sessionStorage.getItem('sidebar-scroll');
    if (saved) {
        requestAnimationFrame(function() {
            nav.scrollTop = parseInt(saved);
        });
    }
    nav.addEventListener('scroll', function() {
        sessionStorage.setItem('sidebar-scroll', nav.scrollTop);
    });
})();
</script>
</body>
</html>
<?php /**PATH D:\project laravel\wabot\resources\views\layouts\app.blade.php ENDPATH**/ ?>