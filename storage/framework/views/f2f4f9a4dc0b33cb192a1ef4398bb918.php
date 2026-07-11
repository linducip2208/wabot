<?php $__env->startSection('title', $session->name . ' — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <a href="<?php echo e(route('sessions.index')); ?>" class="text-sm text-gray-500 hover:text-brand-600">&larr; <?php echo e(__('common.back')); ?></a>
        <h1 class="text-2xl font-extrabold text-gray-900 mt-1"><?php echo e($session->name); ?></h1>
        <div class="text-sm text-gray-500 mt-1">
            <?php echo e(__('common.status')); ?>:
            <span class="font-semibold <?php echo e($session->status === 'connected' ? 'text-green-600' : 'text-yellow-600'); ?>">
                <?php echo e($session->status === 'qr_ready' ? __('sessions.waiting_scan') : $session->status); ?>

            </span>
            <?php if($session->phone): ?>
                &middot; <span class="font-mono"><?php echo e($session->phone); ?></span>
            <?php endif; ?>
    </div>
</div>

<?php if($logs->count() > 0): ?>
<div class="mt-6 bg-white rounded-xl border border-gray-200 p-5">
    <h3 class="font-bold text-gray-900 mb-4 text-lg flex items-center gap-2"><i class="fas fa-history text-brand-500"></i> <?php echo e(__('common.history')); ?> Uptime</h3>
    <div class="space-y-2">
        <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="flex items-center gap-3 py-2 px-3 rounded-lg hover:bg-gray-50 transition text-sm">
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0
                <?php echo e($log->event === 'connected' ? 'bg-emerald-500' : ''); ?>

                <?php echo e($log->event === 'disconnected' ? 'bg-red-500' : ''); ?>

                <?php echo e($log->event === 'logged_out' ? 'bg-amber-500' : ''); ?>

                <?php echo e($log->event === 'reconnecting' ? 'bg-blue-500 animate-pulse' : ''); ?>"></span>
            <div class="flex-1">
                <span class="font-medium capitalize"><?php echo e(str_replace('_', ' ', $log->event)); ?></span>
                <?php if($log->phone): ?> <span class="text-gray-400 font-mono text-xs ml-2"><?php echo e(preg_replace('/[@:].*$/', '', $log->phone)); ?></span> <?php endif; ?>
                <?php if($log->reason): ?> <span class="text-gray-400 text-xs ml-1">· <?php echo e($log->reason); ?></span> <?php endif; ?>
            </div>
            <span class="text-xs text-gray-400"><?php echo e($log->logged_at->format('d M Y H:i:s')); ?></span>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php endif; ?>
    <div class="flex gap-2">
        <form method="POST" action="<?php echo e(route('sessions.destroy', $session)); ?>" onsubmit="return confirm('<?php echo e(__('sessions.delete_confirm')); ?>')">
            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            <button class="text-sm text-red-500 hover:underline"><?php echo e(__('common.delete')); ?> <?php echo e(__('common.session')); ?></button>
        </form>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 text-center">
        <h3 class="font-bold text-gray-900 mb-4 text-lg">
            <?php if($session->status === 'connected'): ?>
                ✅ WhatsApp Connected
            <?php else: ?>
                Scan QR Code
            <?php endif; ?>
        </h3>

        <?php if($session->status === 'connected'): ?>
            <div class="py-8">
                <div class="text-6xl mb-4">✅</div>
                <p class="text-xl font-bold text-green-600">Connected!</p>
                <?php if($session->phone): ?>
                    <p class="text-lg text-gray-700 mt-2 font-mono"><?php echo e($session->phone); ?></p>
                <?php endif; ?>
                <p class="text-sm text-gray-400 mt-4"><?php echo e(__('sessions.ready_message')); ?></p>
            </div>
        <?php elseif($session->status === 'disconnected'): ?>
            <div class="py-8">
                <div class="text-6xl mb-4">⚠️</div>
                <p class="text-xl font-bold text-red-600"><?php echo e(__('sessions.disconnected_status')); ?></p>
                <p class="text-sm text-gray-500 mt-2"><?php echo e(__('sessions.disconnected_message')); ?></p>
                <div class="mt-4">
                    <form method="POST" action="<?php echo e(route('sessions.destroy', $session)); ?>" onsubmit="return confirm('<?php echo e(__('sessions.delete_confirm')); ?>')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium">
                            <?php echo e(__('sessions.delete_and_create')); ?>

                        </button>
                    </form>
                </div>
            </div>
        <?php elseif($qrImage): ?>
            <div class="inline-block p-4 border border-gray-200 rounded-xl mb-4 bg-white">
                <img src="<?php echo e($qrImage); ?>" alt="QR Code" class="w-72 h-72">
            </div>
            <p class="text-sm text-gray-500 font-medium"><?php echo __('sessions.scan_qr_instruction'); ?></p>
            <p class="text-xs text-gray-400 mt-2"><?php echo e(__('sessions.auto_refresh_5s')); ?></p>
        <?php else: ?>
            <div class="py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-600 mx-auto"></div>
                <p class="text-sm text-gray-500 mt-4"><?php echo e(__('sessions.connecting')); ?></p>
                <p class="text-xs text-gray-400 mt-2"><?php echo e(__('sessions.auto_refresh')); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="font-bold text-gray-900 mb-4 text-lg"><?php echo e(__('sessions.info')); ?></h3>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between py-1.5 border-b border-gray-100">
                <dt class="text-gray-500"><?php echo e(__('common.name')); ?></dt>
                <dd class="text-gray-900 font-medium"><?php echo e($session->name); ?></dd>
            </div>
            <div class="flex justify-between py-1.5 border-b border-gray-100">
                <dt class="text-gray-500"><?php echo e(__('common.status')); ?></dt>
                <dd class="text-gray-900 font-medium capitalize"><?php echo e($session->status === 'qr_ready' ? __('sessions.waiting_scan') : $session->status); ?></dd>
            </div>
            <div class="flex justify-between py-1.5 border-b border-gray-100">
                <dt class="text-gray-500"><?php echo e(__('sessions.number')); ?></dt>
                <dd class="text-gray-900 font-medium font-mono"><?php echo e($session->phone ?? '-'); ?></dd>
            </div>
            <div class="flex justify-between py-1.5 border-b border-gray-100">
                <dt class="text-gray-500">Session ID</dt>
                <dd class="text-gray-900 font-medium font-mono text-xs"><?php echo e($session->session_id); ?></dd>
            </div>
            <div class="flex justify-between py-1.5 border-b border-gray-100">
                <dt class="text-gray-500"><?php echo e(__('common.server')); ?></dt>
                <dd class="text-gray-900 font-medium"><?php echo e($session->server?->name ?? '-'); ?></dd>
            </div>
            <div class="flex justify-between py-1.5 border-b border-gray-100">
                <dt class="text-gray-500"><?php echo e(__('common.created')); ?></dt>
                <dd class="text-gray-900 font-medium"><?php echo e($session->created_at->format('d M Y H:i')); ?></dd>
            </div>
        </dl>

        <?php if($session->status === 'connected'): ?>
            <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                <p class="text-sm text-green-800 font-medium"><?php echo e(__('sessions.active_message')); ?></p>
                <div class="flex gap-3 mt-3">
                    <a href="<?php echo e(route('autoreplies.index')); ?>" class="text-sm text-brand-600 font-semibold hover:underline"><?php echo e(__('sessions.setup_autoreply')); ?> &rarr;</a>
                    <a href="<?php echo e(route('campaigns.create')); ?>" class="text-sm text-brand-600 font-semibold hover:underline"><?php echo e(__('sessions.create_campaign')); ?> &rarr;</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if($refresh): ?>
<meta http-equiv="refresh" content="5">
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\sessions\show.blade.php ENDPATH**/ ?>