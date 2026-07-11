<?php $__env->startSection('title', __('knowledge.title')); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-1"><?php echo e(__('knowledge.title')); ?></h1>
    <p class="text-sm text-gray-500 mb-6"><?php echo e(__('knowledge.subtitle')); ?></p>

    <?php if(session('success')): ?>
        <div class="bg-green-50 border border-green-300 text-green-800 rounded-xl px-4 py-3 mb-4 text-sm"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="bg-red-50 border border-red-300 text-red-800 rounded-xl px-4 py-3 mb-4 text-sm"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    
    <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-6 shadow-sm">
        <h2 class="font-semibold text-gray-800 mb-3"><?php echo e(__('knowledge.add_faq')); ?></h2>
        <form method="POST" action="<?php echo e(route('knowledge.store')); ?>" id="faqForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="type" value="faq">
            <div class="mb-3">
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.name')); ?> Knowledge</label>
                <input name="title" required placeholder="<?php echo e(__('knowledge.name_placeholder')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div id="faqRows">
                <div class="faq-row border border-gray-200 rounded-xl p-3 mb-2 bg-gray-50/50">
                    <div class="grid grid-cols-12 gap-2">
                        <input name="faqs[0][category]" placeholder="<?php echo e(__('common.category')); ?> (<?php echo e(__('common.optional')); ?>)" class="col-span-4 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
                        <input name="faqs[0][question]" placeholder="<?php echo e(__('knowledge.question')); ?>" required class="col-span-4 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
                        <input name="faqs[0][answer]" placeholder="<?php echo e(__('knowledge.answer')); ?>" required class="col-span-3 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
                        <button type="button" class="col-span-1 text-red-500 text-xs delete-row hidden">✕</button>
                    </div>
                </div>
            </div>
            <div class="flex gap-2 mt-2">
                <button type="button" onclick="addRow()" class="text-xs bg-gray-100 border border-gray-300 rounded-lg px-3 py-1.5 hover:bg-gray-200">+ <?php echo e(__('knowledge.add_row')); ?></button>
                <button type="submit" class="text-xs bg-brand-600 text-white rounded-lg px-4 py-1.5 hover:bg-brand-700"><?php echo e(__('knowledge.save_faq')); ?></button>
            </div>
        </form>
    </div>

    
    <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-6 shadow-sm">
        <h2 class="font-semibold text-gray-800 mb-3"><?php echo e(__('knowledge.upload_csv')); ?></h2>
        <form method="POST" action="<?php echo e(route('knowledge.import')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.name')); ?> Knowledge</label>
                <input name="title" required placeholder="<?php echo e(__('knowledge.csv_name_placeholder')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="flex gap-2 items-center">
                <input type="file" name="file" accept=".csv,.txt" required class="text-xs border border-gray-300 rounded-lg px-3 py-1.5 file:mr-3 file:py-1 file:px-3 file:border-0 file:bg-brand-50 file:text-brand-700 file:rounded-lg file:text-xs">
                <button type="submit" class="text-xs bg-brand-600 text-white rounded-lg px-4 py-1.5 hover:bg-brand-700"><?php echo e(__('knowledge.upload_csv_btn')); ?></button>
            </div>
            <p class="text-xs text-gray-400 mt-2"><?php echo e(__('knowledge.csv_format')); ?> <code>question,answer</code> (<?php echo e(__('knowledge.csv_format_or')); ?> <code>category,question,answer</code>). <a href="#" onclick="downloadSample()" class="text-brand-600 underline"><?php echo e(__('knowledge.download_sample')); ?></a></p>
        </form>
    </div>

    
    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
        <h2 class="font-semibold text-gray-800 mb-3"><?php echo e(__('knowledge.list')); ?> (<?php echo e($entries->count()); ?>)</h2>
        <?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $rows = $e->rows; ?>
            <div class="border border-gray-100 rounded-xl p-4 mb-3 bg-gray-50/30">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <span class="font-semibold text-sm"><?php echo e($e->title); ?></span>
                        <span class="text-xs text-gray-400 ml-2"><?php echo e(strtoupper($e->type)); ?> · <?php echo e(count($rows)); ?> <?php echo e(__('common.items')); ?></span>
                        <?php if($e->is_active): ?>
                            <span class="ml-2 text-xs text-green-700 bg-green-50 px-2 py-0.5 rounded-full"><?php echo e(__('common.active')); ?></span>
                        <?php else: ?>
                            <span class="ml-2 text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full"><?php echo e(__('common.inactive')); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="flex gap-1">
                        <form method="POST" action="<?php echo e(route('knowledge.toggle', $e)); ?>" class="inline">
                            <?php echo csrf_field(); ?>
                            <button class="text-xs <?php echo e($e->is_active ? 'text-amber-600' : 'text-green-600'); ?> bg-white border border-gray-200 rounded-lg px-2 py-1"><?php echo e($e->is_active ? __('knowledge.deactivate') : __('knowledge.activate')); ?></button>
                        </form>
                        <form method="POST" action="<?php echo e(route('knowledge.destroy', $e)); ?>" class="inline" onsubmit="return confirm('<?php echo e(__('knowledge.delete_confirm')); ?>')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="text-xs text-red-500 bg-white border border-gray-200 rounded-lg px-2 py-1"><?php echo e(__('common.delete')); ?></button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-gray-400"><?php echo e(__('knowledge.empty')); ?></p>
        <?php endif; ?>
    </div>
</div>

<script>
let faqIndex = 1;
function addRow() {
    const wrap = document.getElementById('faqRows');
    const row = document.createElement('div');
    row.className = 'faq-row border border-gray-200 rounded-xl p-3 mb-2 bg-gray-50/50';
    row.innerHTML = `<div class="grid grid-cols-12 gap-2">
        <input name="faqs[${faqIndex}][category]" placeholder="<?php echo e(__('common.category')); ?> (<?php echo e(__('common.optional')); ?>)" class="col-span-4 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
        <input name="faqs[${faqIndex}][question]" placeholder="<?php echo e(__('knowledge.question')); ?>" required class="col-span-4 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
        <input name="faqs[${faqIndex}][answer]" placeholder="<?php echo e(__('knowledge.answer')); ?>" required class="col-span-3 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
        <button type="button" class="col-span-1 text-red-500 text-xs" onclick="this.parentElement.parentElement.remove()">✕</button>
    </div>`;
    wrap.appendChild(row);
    faqIndex++;
}
function downloadSample() {
    const csv = 'category,question,answer\n<?php echo e(__('knowledge.sample_category')); ?>,<?php echo e(__('knowledge.sample_q1')); ?>,<?php echo e(__('knowledge.sample_a1')); ?>\n<?php echo e(__('knowledge.sample_category2')); ?>,<?php echo e(__('knowledge.sample_q2')); ?>,<?php echo e(__('knowledge.sample_a2')); ?>\n<?php echo e(__('knowledge.sample_category3')); ?>,<?php echo e(__('knowledge.sample_q3')); ?>,<?php echo e(__('knowledge.sample_a3')); ?>.';
    const blob = new Blob([csv], {type: 'text/csv'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = url; a.download = 'knowledge-sample.csv'; a.click();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\knowledge\index.blade.php ENDPATH**/ ?>