<?php $__env->startSection('title', __('campaigns.index_title')); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('campaigns.heading')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('campaigns.subtitle')); ?></p>
    </div>
    <a href="<?php echo e(route('campaigns.create')); ?>" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> <?php echo e(__('campaigns.new_campaign')); ?>

    </a>
</div>

<div class="grid gap-3">
    <?php $__empty_1 = true; $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <a href="<?php echo e(route('campaigns.show', $c)); ?>" class="block bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center
                    <?php echo e($c->status === 'sent' ? 'bg-emerald-50' : ''); ?>

                    <?php echo e($c->status === 'sending' ? 'bg-blue-50' : ''); ?>

                    <?php echo e($c->status === 'paused' ? 'bg-orange-50' : ''); ?>

                    <?php echo e($c->status === 'draft' ? 'bg-amber-50' : ''); ?>

                    <?php echo e($c->status === 'failed' ? 'bg-red-50' : ''); ?>">
                    <i class="fas <?php echo e($c->status === 'sent' ? 'fa-check-circle text-emerald-500' : ($c->status === 'sending' ? 'fa-spinner fa-spin text-blue-500' : ($c->status === 'paused' ? 'fa-pause-circle text-orange-500' : ($c->status === 'draft' ? 'fa-clock text-amber-500' : 'fa-exclamation-circle text-red-500')))); ?>"></i>
                </div>
                <div>
                    <div class="font-semibold text-gray-900"><?php echo e($c->name); ?></div>
                    <div class="text-xs text-gray-500">
                        <?php
                            $channelLabel = $c->channel ?? 'whatsapp';
                            $channelBadge = [
                                'whatsapp' => 'bg-emerald-50 text-emerald-700',
                                'meta' => 'bg-blue-50 text-blue-700',
                                'telegram' => 'bg-sky-50 text-sky-700',
                            ][$channelLabel] ?? 'bg-gray-50 text-gray-700';
                            $channelName = [
                                'whatsapp' => 'WhatsApp',
                                'meta' => 'Meta Cloud',
                                'telegram' => 'Telegram',
                            ][$channelLabel] ?? 'WhatsApp';
                        ?>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium <?php echo e($channelBadge); ?>"><?php echo e($channelName); ?></span>
                        · <?php echo e($c->session?->name ?? $c->metaAccount?->name ?? $c->telegramAccount?->name ?? '-'); ?> · <?php echo e($c->delay_seconds ?? 3); ?>s delay</div>
                </div>
            </div>
            <span class="text-[11px] font-medium px-2 py-0.5 rounded-full
                <?php echo e($c->status === 'sent' ? 'bg-emerald-50 text-emerald-700' : ''); ?>

                <?php echo e($c->status === 'sending' ? 'bg-blue-50 text-blue-700' : ''); ?>

                <?php echo e($c->status === 'paused' ? 'bg-orange-50 text-orange-700' : ''); ?>

                <?php echo e($c->status === 'draft' ? 'bg-amber-50 text-amber-700' : ''); ?>

                <?php echo e($c->status === 'failed' ? 'bg-red-50 text-red-700' : ''); ?>">
                <?php echo e(['sent' => __('common.sent'), 'sending' => __('common.sending'), 'paused' => __('campaigns.pause'), 'draft' => __('common.draft'), 'failed' => __('common.failed')][$c->status] ?? $c->status); ?>

            </span>
        </div>
        <p class="text-sm text-gray-500 mb-3 line-clamp-1"><?php echo e(Str::limit($c->message, 100)); ?></p>
        <div class="flex items-center gap-4 text-xs text-gray-400">
            <span><i class="fas fa-users mr-1"></i> <?php echo e($c->sent_count); ?>/<?php echo e($c->total_recipients); ?></span>
            <?php if($c->failed_count): ?> <span class="text-red-500"><i class="fas fa-times mr-1"></i> <?php echo e($c->failed_count); ?> <?php echo e(__('common.failed')); ?></span> <?php endif; ?>
            <span><?php echo e($c->scheduled_at ? __('campaigns.scheduled', ['datetime' => $c->scheduled_at->format('d M H:i')]) : $c->created_at->format('d M Y H:i')); ?></span>
            <div class="ml-auto flex gap-1" onclick="event.preventDefault(); event.stopPropagation();">
                <?php if(in_array($c->status, ['sent','failed'])): ?>
                <form method="POST" action="<?php echo e(route('campaigns.resend', $c)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button class="text-[11px] bg-amber-50 text-amber-700 hover:bg-amber-100 px-2 py-1 rounded-lg font-medium"><?php echo e(__('campaigns.resend')); ?></button>
                </form>
                <?php endif; ?>
                <?php if($c->status === 'sending'): ?>
                <form method="POST" action="<?php echo e(route('campaigns.pause', $c)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button class="text-[11px] bg-orange-50 text-orange-700 hover:bg-orange-100 px-2 py-1 rounded-lg font-medium"><?php echo e(__('campaigns.pause')); ?></button>
                </form>
                <?php endif; ?>
                <?php if($c->status === 'paused'): ?>
                <form method="POST" action="<?php echo e(route('campaigns.resume', $c)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button class="text-[11px] bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-2 py-1 rounded-lg font-medium"><?php echo e(__('campaigns.resume')); ?></button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <i class="fas fa-bullhorn text-4xl text-gray-300 mb-3"></i>
        <p class="text-gray-500 mb-1"><?php echo e(__('campaigns.empty_title')); ?></p>
        <p class="text-sm text-gray-400"><?php echo e(__('campaigns.empty_subtitle')); ?></p>
        <a href="<?php echo e(route('campaigns.create')); ?>" class="inline-block mt-4 bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-700 transition"><?php echo e(__('campaigns.empty_cta')); ?></a>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\campaigns\index.blade.php ENDPATH**/ ?>