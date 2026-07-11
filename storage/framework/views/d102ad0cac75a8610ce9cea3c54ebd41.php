<?php $__env->startSection('title', __('groups.title') . ' — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div><h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('groups.title')); ?></h1><p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('groups.subtitle')); ?></p></div>
    <button onclick="toggleModal()" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2"><i class="fas fa-plus text-xs"></i> <?php echo e(__('common.create')); ?> <?php echo e(__('groups.group')); ?></button>
</div>

<div class="grid gap-3">
    <?php $__empty_1 = true; $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-3 h-3 rounded-full flex-shrink-0" style="background:<?php echo e($g->color); ?>"></div>
            <div>
                <div class="font-semibold text-gray-900"><?php echo e($g->name); ?></div>
                <div class="text-xs text-gray-500"><?php echo e($g->contacts_count); ?> <?php echo e(__('common.contact')); ?></div>
            </div>
        </div>
        <div class="flex items-center gap-1">
            <button onclick='editGroup(<?php echo e($g->id); ?>, "<?php echo e($g->name); ?>", "<?php echo e($g->color); ?>")' class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
            <form method="POST" action="<?php echo e(route('groups.destroy', $g)); ?>" onsubmit="return confirm('<?php echo e(__('groups.delete_confirm')); ?>')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
            </form>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <i class="fas fa-layer-group text-3xl text-gray-300 mb-3 block"></i>
        <p class="text-gray-500"><?php echo e(__('groups.empty')); ?></p>
    </div>
    <?php endif; ?>
</div>

<div id="groupModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="gTitle"><?php echo e(__('groups.create_title')); ?></h2>
        <form method="POST" action="<?php echo e(route('groups.store')); ?>" class="space-y-3" id="gForm">
            <?php echo csrf_field(); ?>
            <div id="gMethod"></div>
            <input type="text" name="name" placeholder="<?php echo e(__('common.name')); ?> <?php echo e(__('groups.group')); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            <input type="color" name="color" value="#3b82f6" class="w-full h-10 rounded-xl border border-gray-300 cursor-pointer">
            <div class="flex gap-2">
                <button type="button" onclick="toggleModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal() {
    const m = document.getElementById('groupModal'); m.classList.toggle('hidden');
    if (!m.classList.contains('hidden')) { document.getElementById('gTitle').textContent='<?php echo e(__('groups.create_title')); ?>'; document.getElementById('gForm').reset(); document.getElementById('gMethod').innerHTML=''; }
}
function editGroup(id, name, color) {
    const m = document.getElementById('groupModal'); m.classList.remove('hidden');
    document.getElementById('gTitle').textContent='<?php echo e(__('groups.edit_title')); ?>';
    const f = document.getElementById('gForm'); f.action='/groups/'+id;
    f.querySelector('input[name="name"]').value=name; f.querySelector('input[name="color"]').value=color;
    document.getElementById('gMethod').innerHTML='<input type="hidden" name="_method" value="PUT">';
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\groups\index.blade.php ENDPATH**/ ?>