<?php $__env->startSection('title', __('facebook.title') . ' — WABot'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900"><?php echo e(__('facebook.title')); ?></h1>
            <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('facebook.subtitle')); ?></p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
            class="bg-blue-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> <?php echo e(__('facebook.create_account')); ?>

        </button>
    </div>

    <?php if($accounts->isEmpty()): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 mx-auto bg-blue-50 rounded-full flex items-center justify-center mb-4">
                <i class="fab fa-facebook text-blue-500 text-xl"></i>
            </div>
            <h3 class="text-gray-500 font-medium mb-1"><?php echo e(__('facebook.empty_title')); ?></h3>
            <p class="text-sm text-gray-400 mb-4"><?php echo e(__('facebook.empty_desc')); ?></p>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
                <?php echo e(__('facebook.create_account')); ?>

            </button>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center flex-shrink-0">
                                <i class="fab fa-facebook text-white text-sm"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-semibold text-gray-900"><?php echo e($acc->name); ?></h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php echo e($acc->status === 'connected' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'); ?>">
                                        <?php echo e($acc->status === 'connected' ? __('common.connected') : __('common.disconnected')); ?>

                                    </span>
                                </div>
                                <?php if($acc->page_name): ?>
                                    <p class="text-xs text-gray-400 mt-0.5"><?php echo e($acc->page_name); ?></p>
                                <?php endif; ?>
                                <p class="text-xs text-gray-400 mt-0.5">Page ID: <?php echo e($acc->page_id); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <?php if($acc->status !== 'connected'): ?>
                                <form action="<?php echo e(route('facebook.connect', $acc)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button class="px-3.5 py-2 rounded-xl text-xs font-medium bg-blue-600 text-white hover:bg-blue-700 transition">
                                        <i class="fab fa-facebook mr-1"></i><?php echo e(__('common.connect')); ?>

                                    </button>
                                </form>
                            <?php else: ?>
                                <form action="<?php echo e(route('facebook.disconnect', $acc)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button class="px-3.5 py-2 rounded-xl text-xs font-medium border border-red-200 text-red-600 hover:bg-red-50 transition">
                                        <?php echo e(__('common.disconnect')); ?>

                                    </button>
                                </form>
                            <?php endif; ?>
                            <form action="<?php echo e(route('facebook.destroy', $acc)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('common.delete')); ?>?')" class="inline">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <div class="mt-4 bg-blue-50 rounded-xl border border-blue-100 p-5">
        <div class="flex items-start gap-4">
            <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-link text-blue-600 text-sm"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 text-sm mb-1"><?php echo e(__('facebook.webhook_url')); ?></h3>
                <p class="text-xs text-gray-500 mb-2"><?php echo e(__('facebook.webhook_hint')); ?></p>
                <code class="inline-block bg-white border border-blue-200 rounded-lg px-3 py-1.5 text-xs font-mono text-blue-700 break-all">
                    <?php echo e(route('webhook.facebook')); ?>

                </code>
            </div>
        </div>
    </div>
</div>

<div id="addModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><?php echo e(__('facebook.create_account')); ?></h2>
        <form action="<?php echo e(route('facebook.store')); ?>" method="POST" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.name')); ?></label>
                <input type="text" name="name" placeholder="My Facebook Page" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('facebook.page_id')); ?></label>
                <input type="text" name="page_id" placeholder="Dari Meta Developer" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('facebook.page_token')); ?></label>
                <input type="text" name="page_token" placeholder="Page Access Token" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('facebook.app_secret_label')); ?></label>
                <input type="text" name="app_secret" placeholder="App Secret / Verify Token"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-blue-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-blue-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\facebook\index.blade.php ENDPATH**/ ?>