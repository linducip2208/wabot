<?php $__env->startSection('title', 'Kanban Board — WABot'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-full mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">Kanban Board</h1>
            <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('kanban.drag_drop_hint')); ?></p>
        </div>
        <a href="<?php echo e(route('contact-tags.index')); ?>"
            class="bg-white border border-gray-300 text-gray-700 px-3.5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2">
            <i class="fas fa-cog mr-1"></i> <?php echo e(__('kanban.manage_tags')); ?>

        </a>
    </div>

    <?php
        $columns = [];
        $columns['Belum Ditandai'] = $contacts['Belum Ditandai'] ?? collect();
        foreach ($tags as $tag) {
            $columns[$tag->name] = $contacts[$tag->name] ?? collect();
        }
    ?>

    <div class="flex gap-3 overflow-x-auto pb-6" style="min-height: 60vh;">
        <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $columnName => $columnContacts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $displayName = $columnName === 'Belum Ditandai' ? __('kanban.untagged') : $columnName; ?>
            <div class="flex-shrink-0 w-72 bg-gray-50 rounded-xl p-3"
                data-column="<?php echo e($columnName); ?>"
                data-tag-id="<?php echo e($columnName === 'Belum Ditandai' ? '' : ($tags->firstWhere('name', $columnName)?->id ?? '')); ?>"
                ondragover="event.preventDefault()"
                ondrop="handleDrop(event, this)">
                <div class="flex items-center justify-between mb-3 px-1">
                    <h3 class="text-sm font-semibold text-gray-700"><?php echo e($displayName); ?></h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white text-gray-500 border border-gray-200">
                        <?php echo e($columnContacts->count()); ?>

                    </span>
                </div>
                <div class="space-y-2 min-h-[80px]">
                    <?php $__currentLoopData = $columnContacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-white rounded-xl border border-gray-200 p-3 cursor-grab hover:shadow-sm hover:border-brand-200 transition"
                            draggable="true"
                            data-contact-id="<?php echo e($contact->id); ?>"
                            ondragstart="event.dataTransfer.setData('text/plain', '<?php echo e($contact->id); ?>'); this.style.opacity='0.5'"
                            ondragend="this.style.opacity='1'">
                            <p class="text-sm font-medium text-gray-900"><?php echo e($contact->name); ?></p>
                            <p class="text-xs text-gray-400 font-mono mt-0.5"><?php echo e($contact->display_phone ?? Str::limit($contact->phone, 20)); ?></p>
                            <?php if($contact->messages->first()): ?>
                                <p class="text-xs text-gray-500 mt-1.5 line-clamp-2">
                                    <?php echo e(Str::limit($contact->messages->first()->message, 80)); ?>

                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <div class="text-center text-xs text-gray-400 py-4 <?php echo e($columnContacts->isEmpty() ? '' : 'hidden'); ?>">
                        <?php echo e(__('kanban.drop_contact_here')); ?>

                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function handleDrop(event, column) {
    event.preventDefault();
    const contactId = event.dataTransfer.getData('text/plain');
    const tagId = column.dataset.tagId;

    fetch('<?php echo e(route('kanban.move')); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            contact_id: contactId,
            tag_id: tagId || null,
        }),
    }).then(r => r.json()).then(data => {
        if (data.ok) location.reload();
    });
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\kanban\index.blade.php ENDPATH**/ ?>