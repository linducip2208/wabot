<?php $__env->startSection('title', __('common.session') . ' WhatsApp — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-extrabold text-gray-900"><?php echo e(__('common.session')); ?> WhatsApp</h1>
    <div class="flex gap-2">
        <a href="<?php echo e(route('sessions.index')); ?>" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-sm font-semibold hover:bg-gray-200 transition">
            Refresh <?php echo e(__('common.status')); ?>

        </a>
        <button onclick="document.getElementById('createSessionModal').classList.remove('hidden')"
            class="bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
            + <?php echo e(__('common.create')); ?> <?php echo e(__('common.session')); ?>

        </button>
    </div>
</div>

<div class="grid gap-4">
    <?php $__empty_1 = true; $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <a href="<?php echo e(route('sessions.show', $s)); ?>"
        class="block bg-white rounded-xl border border-gray-200 p-5 hover:border-brand-300 hover:shadow-sm transition">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <span class="inline-block w-3 h-3 rounded-full
                        <?php echo e($s->status === 'connected' ? 'bg-green-500 animate-pulse' : ''); ?>

                        <?php echo e($s->status === 'qr_ready' ? 'bg-yellow-500' : ''); ?>

                        <?php echo e($s->status === 'connecting' ? 'bg-blue-500 animate-pulse' : ''); ?>

                        <?php echo e($s->status === 'disconnected' ? 'bg-red-500' : ''); ?>

                        <?php echo e($s->status === 'reconnecting' ? 'bg-orange-500 animate-pulse' : ''); ?>

                        <?php echo e($s->status === 'pending' ? 'bg-gray-400' : ''); ?>">
                    </span>
                </div>
                <div>
                    <div class="font-semibold text-gray-900"><?php echo e($s->name); ?></div>
                    <div class="text-sm text-gray-500"><?php echo e($s->phone ?? __('sessions.not_connected')); ?></div>
                    <?php if($s->server): ?>
                    <div class="text-xs text-gray-400 mt-0.5"><?php echo e($s->server->name); ?> (<?php echo e($s->server->host); ?>)</div>
                    <?php endif; ?>
                </div>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                <?php echo e($s->status === 'connected' ? 'bg-green-100 text-green-800' : ''); ?>

                <?php echo e($s->status === 'qr_ready' ? 'bg-yellow-100 text-yellow-800' : ''); ?>

                <?php echo e($s->status === 'connecting' ? 'bg-blue-100 text-blue-800' : ''); ?>

                <?php echo e($s->status === 'reconnecting' ? 'bg-orange-100 text-orange-800' : ''); ?>

                <?php echo e($s->status === 'disconnected' ? 'bg-red-100 text-red-800' : ''); ?>

                <?php echo e($s->status === 'pending' ? 'bg-gray-100 text-gray-600' : ''); ?>">
                <?php echo e(str_replace('_', ' ', $s->status)); ?>

            </span>
        </div>
    </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <p class="text-gray-500 mb-2"><?php echo e(__('sessions.empty')); ?></p>
        <p class="text-sm text-gray-400"><?php echo e(__('sessions.empty_hint')); ?></p>
    </div>
    <?php endif; ?>
</div>


<div id="createSessionModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
        <h2 class="text-lg font-bold mb-4"><?php echo e(__('common.create')); ?> <?php echo e(__('common.session')); ?> WhatsApp</h2>
        <form method="POST" action="<?php echo e(route('sessions.store')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <select name="server_id" required class="w-full rounded-xl border-gray-300 px-4 py-2.5 border text-sm">
                <option value=""><?php echo e(__('common.select')); ?> <?php echo e(__('common.server')); ?> Baileys</option>
                <?php $__currentLoopData = $servers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $srv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($srv->id); ?>"><?php echo e($srv->name); ?> (<?php echo e($srv->host); ?>:<?php echo e($srv->port); ?>)</option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input type="text" name="name" placeholder="<?php echo e(__('sessions.name_placeholder')); ?>" required
                class="w-full rounded-xl border-gray-300 px-4 py-2.5 border text-sm">
            <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">
                <?php echo e(__('sessions.create_session')); ?>

            </button>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\sessions\index.blade.php ENDPATH**/ ?>