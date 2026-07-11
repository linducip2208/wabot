<?php $__env->startSection('title', __('meta.title') . ' — WABot'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900"><?php echo e(__('meta.title')); ?></h1>
            <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('meta.subtitle')); ?></p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
            class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> <?php echo e(__('meta.create_account')); ?>

        </button>
    </div>

    <?php if($accounts->isEmpty()): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 mx-auto bg-blue-50 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-plug text-blue-500 text-xl"></i>
            </div>
            <h3 class="text-gray-500 font-medium mb-1"><?php echo e(__('meta.empty_title')); ?></h3>
            <p class="text-sm text-gray-400 mb-4"><?php echo e(__('meta.empty_desc')); ?></p>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
                <?php echo e(__('meta.create_account')); ?>

            </button>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0">
                                <i class="fab fa-meta text-white text-sm"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-semibold text-gray-900"><?php echo e($account->name); ?></h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php echo e($account->status === 'connected' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'); ?>">
                                        <?php echo e($account->status === 'connected' ? __('common.connected') : __('common.disconnected')); ?>

                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-400">
                                    <?php if($account->phone_number): ?>
                                        <span><i class="fas fa-phone mr-1"></i><?php echo e($account->phone_number); ?></span>
                                    <?php endif; ?>
                                    <?php if($account->waba_id): ?>
                                        <span><i class="fas fa-building mr-1"></i><?php echo e(Str::limit($account->waba_id, 18)); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <?php if($account->status !== 'connected'): ?>
                                <form action="<?php echo e(route('meta.connect', $account)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button class="px-3.5 py-2 rounded-xl text-xs font-medium bg-green-600 text-white hover:bg-green-700 transition">
                                        <?php echo e(__('common.connect')); ?>

                                    </button>
                                </form>
                            <?php else: ?>
                                <form action="<?php echo e(route('meta.disconnect', $account)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button class="px-3.5 py-2 rounded-xl text-xs font-medium border border-red-200 text-red-600 hover:bg-red-50 transition">
                                        <?php echo e(__('common.disconnect')); ?>

                                    </button>
                                </form>
                            <?php endif; ?>
                            <button onclick="openTestModal(<?php echo e($account->id); ?>)"
                                class="px-3 py-2 rounded-xl text-xs font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                                <i class="fas fa-paper-plane mr-1"></i><?php echo e(__('common.test')); ?>

                            </button>
                            <button onclick="openEditModal(<?php echo e($account->id); ?>)"
                                class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600 transition">
                                <i class="fas fa-pen text-xs"></i>
                            </button>
                            <form action="<?php echo e(route('meta.destroy', $account)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('meta.delete_confirm')); ?>')" class="inline">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button class="p-2 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <?php if($account->status === 'connected'): ?>
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <?php $metaSessions = $account->sessions ?? collect(); ?>
                            <?php if($metaSessions->isNotEmpty()): ?>
                                <div class="flex flex-wrap gap-1.5 mb-3">
                                    <?php $__currentLoopData = $metaSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-gray-100 text-gray-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            <?php echo e($s->name); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                            <form action="<?php echo e(route('meta.session.store', $account)); ?>" method="POST" class="flex items-end gap-3">
                                <?php echo csrf_field(); ?>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('meta.create_session')); ?></label>
                                    <input type="text" name="name" placeholder="<?php echo e(__('common.name')); ?> <?php echo e(__('common.session')); ?>" required
                                        class="rounded-xl border border-gray-300 px-3 py-2 text-sm w-44 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                </div>
                                <button class="bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
                                    <?php echo e(__('meta.create_session_btn')); ?>

                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <div class="mt-4 bg-brand-50 rounded-xl border border-brand-200 p-5">
        <div class="flex items-start gap-4">
            <div class="w-9 h-9 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-link text-brand-600 text-sm"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 text-sm mb-1"><?php echo e(__('meta.webhook_url')); ?></h3>
                <p class="text-xs text-gray-500 mb-2"><?php echo e(__('meta.webhook_hint')); ?></p>
                <code class="inline-block bg-white border border-brand-200 rounded-lg px-3 py-1.5 text-xs font-mono text-brand-700 break-all">
                    <?php echo e(route('webhook.meta')); ?>

                </code>
            </div>
        </div>
    </div>
</div>


<div id="addModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><?php echo e(__('meta.create_account')); ?></h2>
        <form action="<?php echo e(route('meta.store')); ?>" method="POST" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('meta.account_name')); ?></label>
                <input type="text" name="name" placeholder="Contoh: Bisnis Utama" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('meta.phone_number_id')); ?></label>
                <input type="text" name="phone_number_id" placeholder="Dari Meta Developer Dashboard" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('meta.access_token')); ?></label>
                <input type="text" name="access_token" placeholder="Permanent access token" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('meta.app_id')); ?> <span class="text-gray-400">(<?php echo e(__('common.optional')); ?>)</span></label>
                    <input type="text" name="app_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('meta.verify_token')); ?> <span class="text-gray-400">(<?php echo e(__('common.optional')); ?>)</span></label>
                    <input type="text" name="webhook_verify_token" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>


<?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div id="editModal<?php echo e($account->id); ?>" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><?php echo e(__('meta.edit_account')); ?>: <?php echo e($account->name); ?></h2>
        <form action="<?php echo e(route('meta.update', $account)); ?>" method="POST" class="space-y-3">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.name')); ?></label>
                <input type="text" name="name" value="<?php echo e($account->name); ?>" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('meta.phone_number_id')); ?></label>
                <input type="text" name="phone_number_id" value="<?php echo e($account->phone_number_id); ?>" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('meta.access_token')); ?></label>
                <input type="text" name="access_token" value="<?php echo e($account->access_token); ?>" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('editModal<?php echo e($account->id); ?>').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.update')); ?></button>
            </div>
        </form>
    </div>
</div>

<div id="testModal<?php echo e($account->id); ?>" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><?php echo e(__('meta.test_send')); ?>: <?php echo e($account->name); ?></h2>
        <form action="<?php echo e(route('meta.test', $account)); ?>" method="POST" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('meta.target_number')); ?></label>
                <input type="text" name="phone" placeholder="62812xxxx" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.message')); ?></label>
                <textarea name="message" rows="3" placeholder="Test message..." required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('testModal<?php echo e($account->id); ?>').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.send')); ?></button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openEditModal(id) { document.getElementById('editModal'+id).classList.remove('hidden'); }
function openTestModal(id) { document.getElementById('testModal'+id).classList.remove('hidden'); }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\meta\index.blade.php ENDPATH**/ ?>