<?php $__env->startSection('title', __('publishing.captions_title') . ' — ' . config('app.name')); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6 flex items-center justify-between flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-gray-900"><i class="fas fa-book-open text-brand-500 mr-2"></i><?php echo e(__('publishing.caption_library')); ?></h1>
        <p class="text-gray-500 text-sm mt-1"><?php echo e(__('publishing.captions_subtitle', ['count' => $captions->count()])); ?></p>
    </div>
    <button onclick="document.getElementById('addCaptionModal').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-xl hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus"></i> <?php echo e(__('publishing.add_caption')); ?>

    </button>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php $__empty_1 = true; $__currentLoopData = $captions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $caption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition">
        <div class="flex items-start justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-800"><?php echo e($caption->name); ?></h3>
            <div class="flex items-center gap-1">
                <button onclick="editCaption(<?php echo e($caption->id); ?>, '<?php echo e(addslashes($caption->name)); ?>', `<?php echo e(str_replace('`', '\`', addslashes($caption->content ?? ''))); ?>`, <?php echo e(json_encode($caption->tags ?? [])); ?>)" class="p-1.5 text-gray-400 hover:text-brand-600 transition"><i class="fas fa-edit text-xs"></i></button>
                <form action="<?php echo e(route('publishing.captions.destroy', $caption)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('publishing.delete_confirm')); ?>')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="p-1.5 text-gray-400 hover:text-red-500 transition"><i class="fas fa-trash text-xs"></i></button>
                </form>
            </div>
        </div>
        <p class="text-sm text-gray-600 line-clamp-3 whitespace-pre-wrap"><?php echo e($caption->content); ?></p>
        <?php if($caption->tags): ?>
        <div class="flex flex-wrap gap-1 mt-3">
            <?php $__currentLoopData = $caption->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span class="text-[10px] px-2 py-0.5 rounded-full bg-brand-50 text-brand-700"><?php echo e($tag); ?></span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>
        <button onclick="useCaption(`<?php echo e(str_replace('`', '\`', addslashes($caption->content ?? ''))); ?>`)" class="mt-3 text-xs text-brand-600 hover:text-brand-700 font-medium flex items-center gap-1">
            <i class="fas fa-copy"></i> <?php echo e(__('publishing.use_in_composer')); ?>

        </button>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="col-span-full bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
        <i class="fas fa-book-open text-gray-300 text-5xl mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-500 mb-1"><?php echo e(__('publishing.no_captions')); ?></h3>
        <p class="text-sm text-gray-400"><?php echo e(__('publishing.no_captions_desc')); ?></p>
    </div>
    <?php endif; ?>
</div>


<div id="addCaptionModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold text-gray-900"><?php echo e(__('publishing.add_caption')); ?></h3>
            <button onclick="document.getElementById('addCaptionModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form action="<?php echo e(route('publishing.captions.store')); ?>" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('common.name')); ?></label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="Product Launch Caption">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('publishing.content')); ?></label>
                <textarea name="content" required rows="4" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none" placeholder="<?php echo e(__('publishing.caption_content_placeholder')); ?>"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('publishing.tags')); ?></label>
                <input type="text" name="tags[]" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="promo, launch, product">
                <p class="text-xs text-gray-400 mt-1"><?php echo e(__('publishing.tags_hint')); ?></p>
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-xl hover:bg-brand-700 transition">
                <?php echo e(__('publishing.save_caption')); ?>

            </button>
        </form>
    </div>
</div>


<div id="editCaptionModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold text-gray-900"><?php echo e(__('publishing.edit_caption')); ?></h3>
            <button onclick="document.getElementById('editCaptionModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form id="editCaptionForm" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('common.name')); ?></label>
                <input type="text" id="editCaptionName" name="name" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('publishing.content')); ?></label>
                <textarea id="editCaptionContent" name="content" required rows="4" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('publishing.tags')); ?></label>
                <input type="text" id="editCaptionTags" name="tags[]" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-xl hover:bg-brand-700 transition">
                <?php echo e(__('publishing.update_caption')); ?>

            </button>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function editCaption(id, name, content, tags) {
    document.getElementById('editCaptionForm').action = '/publishing/captions/' + id;
    document.getElementById('editCaptionName').value = name;
    document.getElementById('editCaptionContent').value = content;
    document.getElementById('editCaptionTags').value = tags.join(', ');
    document.getElementById('editCaptionModal').classList.remove('hidden');
}
function useCaption(content) {
    const textarea = document.querySelector('textarea[name=content]');
    if (textarea) { textarea.value = content; window.scrollTo({top:0,behavior:'smooth'}); }
    else { window.location.href = '<?php echo e(route('publishing.index')); ?>'; }
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\publishing\captions.blade.php ENDPATH**/ ?>