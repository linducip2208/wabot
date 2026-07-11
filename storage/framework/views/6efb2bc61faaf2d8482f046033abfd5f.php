<?php $__env->startSection('title', __('publishing.labels_title') . ' — ' . config('app.name')); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6 flex items-center justify-between flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-gray-900"><i class="fas fa-tags text-brand-500 mr-2"></i><?php echo e(__('publishing.labels')); ?></h1>
        <p class="text-gray-500 text-sm mt-1"><?php echo e(__('publishing.labels_subtitle', ['count' => $labels->count()])); ?></p>
    </div>
    <button onclick="document.getElementById('addLabelModal').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-xl hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus"></i> <?php echo e(__('publishing.add_label')); ?>

    </button>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
    <?php $__empty_1 = true; $__currentLoopData = $labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full" style="background:<?php echo e($label->color); ?>"></span>
                <span class="text-sm font-semibold text-gray-800"><?php echo e($label->name); ?></span>
            </div>
            <div class="flex items-center gap-1">
                <button onclick="editLabel(<?php echo e($label->id); ?>, '<?php echo e(addslashes($label->name)); ?>', '<?php echo e($label->color); ?>')" class="p-1 text-gray-400 hover:text-brand-600 transition"><i class="fas fa-edit text-xs"></i></button>
                <form action="<?php echo e(route('publishing.labels.destroy', $label)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('publishing.delete_confirm')); ?>')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="p-1 text-gray-400 hover:text-red-500 transition"><i class="fas fa-trash text-xs"></i></button>
                </form>
            </div>
        </div>
        <div class="text-xs text-gray-500"><?php echo e($label->posts_count); ?> <?php echo e(__('publishing.posts_count')); ?></div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="col-span-full bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
        <i class="fas fa-tags text-gray-300 text-5xl mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-500 mb-1"><?php echo e(__('publishing.no_labels')); ?></h3>
        <p class="text-sm text-gray-400"><?php echo e(__('publishing.no_labels_desc')); ?></p>
    </div>
    <?php endif; ?>
</div>


<div id="addLabelModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold text-gray-900"><?php echo e(__('publishing.add_label')); ?></h3>
            <button onclick="document.getElementById('addLabelModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form action="<?php echo e(route('publishing.labels.store')); ?>" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('common.name')); ?></label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="Promo">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('common.color')); ?></label>
                <div class="flex items-center gap-2">
                    <input type="color" name="color" value="#3b82f6" class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                    <span class="text-xs text-gray-500"><?php echo e(__('publishing.color_hint')); ?></span>
                </div>
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-xl hover:bg-brand-700 transition">
                <?php echo e(__('publishing.save_label')); ?>

            </button>
        </form>
    </div>
</div>


<div id="editLabelModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold text-gray-900"><?php echo e(__('publishing.edit_label')); ?></h3>
            <button onclick="document.getElementById('editLabelModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form id="editLabelForm" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('common.name')); ?></label>
                <input type="text" id="editLabelName" name="name" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('common.color')); ?></label>
                <input type="color" id="editLabelColor" name="color" class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-xl hover:bg-brand-700 transition">
                <?php echo e(__('publishing.update_label')); ?>

            </button>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function editLabel(id, name, color) {
    document.getElementById('editLabelForm').action = '/publishing/labels/' + id;
    document.getElementById('editLabelName').value = name;
    document.getElementById('editLabelColor').value = color;
    document.getElementById('editLabelModal').classList.remove('hidden');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\publishing\labels.blade.php ENDPATH**/ ?>