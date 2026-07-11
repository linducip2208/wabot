<?php $__env->startSection('title', __('publishing.campaigns_title') . ' — ' . config('app.name')); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6 flex items-center justify-between flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-gray-900"><i class="fas fa-bullhorn text-brand-500 mr-2"></i><?php echo e(__('publishing.post_campaigns')); ?></h1>
        <p class="text-gray-500 text-sm mt-1"><?php echo e(__('publishing.campaigns_subtitle', ['count' => $campaigns->count()])); ?></p>
    </div>
    <button onclick="document.getElementById('addCampaignModal').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-xl hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus"></i> <?php echo e(__('publishing.add_campaign')); ?>

    </button>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php $__empty_1 = true; $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition" style="border-left: 4px solid <?php echo e($campaign->color); ?>">
        <div class="flex items-start justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-800"><?php echo e($campaign->name); ?></h3>
            <div class="flex items-center gap-1">
                <button onclick="editCampaign(<?php echo e($campaign->id); ?>, '<?php echo e(addslashes($campaign->name)); ?>', '<?php echo e(addslashes($campaign->description ?? '')); ?>', '<?php echo e($campaign->color); ?>')" class="p-1 text-gray-400 hover:text-brand-600 transition"><i class="fas fa-edit text-xs"></i></button>
                <form action="<?php echo e(route('publishing.campaigns.destroy', $campaign)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('publishing.delete_confirm')); ?>')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="p-1 text-gray-400 hover:text-red-500 transition"><i class="fas fa-trash text-xs"></i></button>
                </form>
            </div>
        </div>
        <?php if($campaign->description): ?>
        <p class="text-sm text-gray-600 mb-2"><?php echo e($campaign->description); ?></p>
        <?php endif; ?>
        <div class="text-xs text-gray-500">
            <span class="font-medium"><?php echo e($campaign->posts_count); ?></span> <?php echo e(__('publishing.posts_count')); ?>

        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="col-span-full bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
        <i class="fas fa-bullhorn text-gray-300 text-5xl mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-500 mb-1"><?php echo e(__('publishing.no_campaigns')); ?></h3>
        <p class="text-sm text-gray-400"><?php echo e(__('publishing.no_campaigns_desc')); ?></p>
    </div>
    <?php endif; ?>
</div>


<div id="addCampaignModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold text-gray-900"><?php echo e(__('publishing.add_campaign')); ?></h3>
            <button onclick="document.getElementById('addCampaignModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form action="<?php echo e(route('publishing.campaigns.store')); ?>" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('common.name')); ?></label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="Summer Launch 2025">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('common.description')); ?></label>
                <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none" placeholder="<?php echo e(__('publishing.campaign_desc_placeholder')); ?>"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('common.color')); ?></label>
                <input type="color" name="color" value="#3b82f6" class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-xl hover:bg-brand-700 transition">
                <?php echo e(__('publishing.save_campaign')); ?>

            </button>
        </form>
    </div>
</div>


<div id="editCampaignModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold text-gray-900"><?php echo e(__('publishing.edit_campaign')); ?></h3>
            <button onclick="document.getElementById('editCampaignModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form id="editCampaignForm" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('common.name')); ?></label>
                <input type="text" id="editCampaignName" name="name" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('common.description')); ?></label>
                <textarea id="editCampaignDesc" name="description" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('common.color')); ?></label>
                <input type="color" id="editCampaignColor" name="color" class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-xl hover:bg-brand-700 transition">
                <?php echo e(__('publishing.update_campaign')); ?>

            </button>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function editCampaign(id, name, desc, color) {
    document.getElementById('editCampaignForm').action = '/publishing/campaigns/' + id;
    document.getElementById('editCampaignName').value = name;
    document.getElementById('editCampaignDesc').value = desc;
    document.getElementById('editCampaignColor').value = color;
    document.getElementById('editCampaignModal').classList.remove('hidden');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\publishing\campaigns.blade.php ENDPATH**/ ?>