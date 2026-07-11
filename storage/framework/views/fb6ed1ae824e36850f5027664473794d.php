<?php $__env->startSection('title', __('sheets.title') . ' — WABot'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900"><?php echo e(__('sheets.title')); ?></h1>
            <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('sheets.subtitle')); ?></p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
            class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> <?php echo e(__('sheets.create_integration')); ?>

        </button>
    </div>

    <?php if($integrations->isEmpty()): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 mx-auto bg-emerald-50 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-table text-emerald-500 text-xl"></i>
            </div>
            <h3 class="text-gray-500 font-medium mb-1"><?php echo e(__('sheets.empty_title')); ?></h3>
            <p class="text-sm text-gray-400 mb-4"><?php echo e(__('sheets.empty_desc')); ?></p>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
                <?php echo e(__('sheets.create_integration')); ?>

            </button>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php $__currentLoopData = $integrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $integration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-start justify-between flex-wrap gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-file-excel text-white text-sm"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <h3 class="font-semibold text-gray-900"><?php echo e($integration->name); ?></h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    <?php echo e($integration->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'); ?>">
                                    <?php echo e($integration->is_active ? __('common.active') : __('common.inactive')); ?>

                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">
                                    <?php echo e($directions[$integration->sync_direction] ?? $integration->sync_direction); ?>

                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-gray-400 flex-wrap">
                                <span><i class="fas fa-table mr-1"></i><?php echo e($integration->spreadsheet_id); ?></span>
                                <span><i class="fas fa-tag mr-1"></i><?php echo e($integration->sheet_name); ?></span>
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
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <?php if(!$integration->is_active): ?>
                            <form action="<?php echo e(route('sheets.connect', $integration)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button class="px-3 py-2 rounded-xl text-xs font-medium bg-green-600 text-white hover:bg-green-700 transition">
                                    <i class="fas fa-plug mr-1"></i><?php echo e(__('store.test_connect')); ?>

                                </button>
                            </form>
                        <?php else: ?>
                            <form action="<?php echo e(route('sheets.sync', $integration)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button class="px-3 py-2 rounded-xl text-xs font-medium bg-emerald-600 text-white hover:bg-emerald-700 transition" <?php echo e($integration->sync_status === 'syncing' ? 'disabled' : ''); ?>>
                                    <i class="fas fa-sync mr-1 <?php echo e($integration->sync_status === 'syncing' ? 'fa-spin' : ''); ?>"></i><?php echo e(__('sheets.sync_now')); ?>

                                </button>
                            </form>
                        <?php endif; ?>
                        <button onclick="openEditModal(<?php echo e($integration->id); ?>)"
                            class="px-2.5 py-2 rounded-xl text-xs font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                            <i class="fas fa-pen"></i>
                        </button>
                        <form method="POST" action="<?php echo e(route('sheets.destroy', $integration)); ?>" onsubmit="return confirm('<?php echo e(__('common.delete')); ?>?')">
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
        <h2 class="text-lg font-bold mb-4"><?php echo e(__('sheets.create_integration')); ?></h2>
        <form method="POST" action="<?php echo e(route('sheets.store')); ?>" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.name')); ?></label>
                <input type="text" name="name" required placeholder="<?php echo e(__('sheets.name_placeholder')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('sheets.spreadsheet_id')); ?></label>
                    <input type="text" name="spreadsheet_id" required placeholder="1BxiMVs0..." class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('sheets.sheet_name')); ?></label>
                    <input type="text" name="sheet_name" value="Sheet1" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('sheets.sync_direction')); ?></label>
                <select name="sync_direction" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="import"><?php echo e(__('sheets.import')); ?></option>
                    <option value="export"><?php echo e(__('sheets.export')); ?></option>
                    <option value="both"><?php echo e(__('sheets.both')); ?></option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('sheets.service_account_json')); ?></label>
                <textarea name="service_account_json" rows="5" required placeholder='{"type": "service_account", "project_id": "..."}' class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
                <p class="text-[11px] text-gray-400 mt-1"><?php echo e(__('sheets.json_hint')); ?></p>
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
        <h2 class="text-lg font-bold mb-4"><?php echo e(__('common.edit')); ?> <?php echo e(__('sheets.integration')); ?></h2>
        <form method="POST" id="editForm" class="space-y-3">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.name')); ?></label>
                <input type="text" name="name" id="editName" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('sheets.spreadsheet_id')); ?></label>
                    <input type="text" name="spreadsheet_id" id="editSpreadsheetId" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('sheets.sheet_name')); ?></label>
                    <input type="text" name="sheet_name" id="editSheetName" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('sheets.sync_direction')); ?></label>
                <select name="sync_direction" id="editDirection" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="import"><?php echo e(__('sheets.import')); ?></option>
                    <option value="export"><?php echo e(__('sheets.export')); ?></option>
                    <option value="both"><?php echo e(__('sheets.both')); ?></option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('sheets.service_account_json')); ?> <span class="text-gray-400">(<?php echo e(__('common.leave_empty_to_keep')); ?>)</span></label>
                <textarea name="service_account_json" rows="4" placeholder='{"type": "service_account", ...}' class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function openEditModal(id) {
    const integrations = <?php echo json_encode($integrations->keyBy('id'), 15, 512) ?>;
    const data = integrations[id];
    if (!data) return;

    document.getElementById('editName').value = data.name;
    document.getElementById('editSpreadsheetId').value = data.spreadsheet_id;
    document.getElementById('editSheetName').value = data.sheet_name;
    document.getElementById('editDirection').value = data.sync_direction;
    document.getElementById('editForm').action = '/sheets/' + id;
    document.getElementById('editModal').classList.remove('hidden');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\sheets\index.blade.php ENDPATH**/ ?>