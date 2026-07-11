<?php $__env->startSection('title', __('store.title') . ' — WABot'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900"><?php echo e(__('store.title')); ?></h1>
            <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('store.subtitle')); ?></p>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo e(route('store.download-plugin')); ?>" class="bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2">
                <i class="fas fa-download text-xs"></i> <?php echo e(__('store.download_plugin')); ?>

            </a>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
                <i class="fas fa-plus text-xs"></i> <?php echo e(__('store.create_integration')); ?>

            </button>
        </div>
    </div>

    <?php if($integrations->isEmpty()): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 mx-auto bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-store text-indigo-500 text-xl"></i>
            </div>
            <h3 class="text-gray-500 font-medium mb-1"><?php echo e(__('store.empty_title')); ?></h3>
            <p class="text-sm text-gray-400 mb-4"><?php echo e(__('store.empty_desc')); ?></p>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
                <?php echo e(__('store.create_integration')); ?>

            </button>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php $__currentLoopData = $integrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $integration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-start justify-between flex-wrap gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
                            <?php echo e($integration->platform === 'woocommerce' ? 'bg-purple-500' : 'bg-green-600'); ?>">
                            <i class="fas fa-<?php echo e($integration->platform === 'woocommerce' ? 'wordpress' : 'shopping-bag'); ?> text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <h3 class="font-semibold text-gray-900"><?php echo e($integration->name); ?></h3>
                                <span class="text-[10px] px-1.5 py-0.5 rounded font-medium uppercase
                                    <?php echo e($integration->platform === 'woocommerce' ? 'bg-purple-50 text-purple-700' : 'bg-green-50 text-green-700'); ?>">
                                    <?php echo e($integration->platform); ?>

                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    <?php echo e($integration->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'); ?>">
                                    <?php echo e($integration->is_active ? __('common.active') : __('common.inactive')); ?>

                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-gray-400 flex-wrap">
                                <span><i class="fas fa-link mr-1"></i><?php echo e($integration->base_url); ?></span>
                                <span>
                                    <i class="fas fa-sync mr-1"></i>
                                    <?php if($integration->sync_status === 'synced'): ?>
                                        <?php echo e(__('store.synced')); ?> <?php echo e($integration->last_synced_at?->diffForHumans()); ?>

                                    <?php elseif($integration->sync_status === 'syncing'): ?>
                                        <?php echo e(__('store.syncing')); ?>...
                                    <?php elseif($integration->sync_status === 'failed'): ?>
                                        <span class="text-red-500"><?php echo e(__('store.sync_failed')); ?></span>
                                    <?php else: ?>
                                        <?php echo e(__('store.never_synced')); ?>

                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="mt-2 text-[11px] text-gray-400 font-mono bg-gray-50 rounded-lg px-2.5 py-1.5 inline-block">
                                <i class="fas fa-bolt mr-1"></i>Webhook: <span class="select-all"><?php echo e($integration->getWebhookUrl()); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <?php if(!$integration->is_active): ?>
                            <form action="<?php echo e(route('store.connect', $integration)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button class="px-3 py-2 rounded-xl text-xs font-medium bg-green-600 text-white hover:bg-green-700 transition">
                                    <i class="fas fa-plug mr-1"></i><?php echo e(__('store.test_connect')); ?>

                                </button>
                            </form>
                        <?php else: ?>
                            <form action="<?php echo e(route('store.sync', $integration)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button class="px-3 py-2 rounded-xl text-xs font-medium bg-indigo-600 text-white hover:bg-indigo-700 transition" <?php echo e($integration->sync_status === 'syncing' ? 'disabled' : ''); ?>>
                                    <i class="fas fa-sync mr-1 <?php echo e($integration->sync_status === 'syncing' ? 'fa-spin' : ''); ?>"></i><?php echo e(__('store.sync_now')); ?>

                                </button>
                            </form>
                        <?php endif; ?>
                        <button onclick="openEditModal(<?php echo e($integration->id); ?>)"
                            class="px-2.5 py-2 rounded-xl text-xs font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button onclick="openSettingsModal(<?php echo e($integration->id); ?>)"
                            class="px-2.5 py-2 rounded-xl text-xs font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                            <i class="fas fa-cog"></i>
                        </button>
                        <form method="POST" action="<?php echo e(route('store.destroy', $integration)); ?>" onsubmit="return confirm('<?php echo e(__('common.delete')); ?>?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="px-2.5 py-2 rounded-xl text-xs font-medium border border-red-200 text-red-600 hover:bg-red-50 transition">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>


<div id="addModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4"><?php echo e(__('store.create_integration')); ?></h2>
        <form method="POST" action="<?php echo e(route('store.store')); ?>" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('store.platform')); ?></label>
                <select name="platform" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="woocommerce">WooCommerce</option>
                    <option value="shopify">Shopify</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.name')); ?></label>
                <input type="text" name="name" required placeholder="<?php echo e(__('store.name_placeholder')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('store.base_url')); ?></label>
                <input type="url" name="base_url" required placeholder="https://yourstore.com" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('store.api_key')); ?></label>
                <input type="text" name="api_key" required placeholder="<?php echo e(__('store.api_key_placeholder')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('store.api_secret')); ?></label>
                <input type="text" name="api_secret" required placeholder="<?php echo e(__('store.api_secret_placeholder')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('store.webhook_secret')); ?> <span class="text-gray-400">(<?php echo e(__('common.optional')); ?>)</span></label>
                <input type="text" name="webhook_secret" placeholder="<?php echo e(__('store.webhook_secret_placeholder')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>


<div id="editModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4"><?php echo e(__('common.edit')); ?> <?php echo e(__('store.integration')); ?></h2>
        <form method="POST" id="editForm" class="space-y-3">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.name')); ?></label>
                <input type="text" name="name" id="editName" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('store.base_url')); ?></label>
                <input type="url" name="base_url" id="editUrl" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('store.api_key')); ?> <span class="text-gray-400">(<?php echo e(__('common.leave_empty_to_keep')); ?>)</span></label>
                <input type="password" name="api_key" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('store.api_secret')); ?> <span class="text-gray-400">(<?php echo e(__('common.leave_empty_to_keep')); ?>)</span></label>
                <input type="password" name="api_secret" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('store.webhook_secret')); ?> <span class="text-gray-400">(<?php echo e(__('common.leave_empty_to_keep')); ?>)</span></label>
                <input type="password" name="webhook_secret" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="editActive" value="1" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                <label for="editActive" class="text-sm text-gray-600"><?php echo e(__('common.active')); ?></label>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>


<div id="settingsModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4"><?php echo e(__('store.notification_settings')); ?></h2>
        <form method="POST" id="settingsForm" class="space-y-3">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_method" value="PATCH">
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('store.order_template')); ?></label>
                <select name="order_template_id" id="settingsTemplate" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value=""><?php echo e(__('store.default_message')); ?></option>
                    <?php $__currentLoopData = Auth::user()->waMessageTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($tpl->id); ?>"><?php echo e($tpl->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('store.auto_reply_keywords')); ?></label>
                <input type="text" name="auto_reply_keywords" id="settingsKeywords" placeholder="<?php echo e(__('store.keywords_placeholder')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <p class="text-[11px] text-gray-400 mt-1"><?php echo e(__('store.keywords_hint')); ?></p>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('settingsModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function openEditModal(id) {
    const integration = <?php echo json_encode($integrations->keyBy('id'), 15, 512) ?>;
    const data = integration[id];
    if (!data) return;

    document.getElementById('editName').value = data.name;
    document.getElementById('editUrl').value = data.base_url;
    document.getElementById('editActive').checked = data.is_active;
    document.getElementById('editForm').action = '/store/' + id;
    document.getElementById('editModal').classList.remove('hidden');
}

function openSettingsModal(id) {
    const integration = <?php echo json_encode($integrations->keyBy('id'), 15, 512) ?>;
    const data = integration[id];
    if (!data) return;

    const settings = data.settings || {};
    document.getElementById('settingsTemplate').value = settings.order_template_id || '';
    document.getElementById('settingsKeywords').value = settings.auto_reply_keywords || '';
    document.getElementById('settingsForm').action = '/store/' + id + '/settings';
    document.getElementById('settingsModal').classList.remove('hidden');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\store\index.blade.php ENDPATH**/ ?>