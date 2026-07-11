<?php $__env->startSection('title', 'Pipeline — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Pipeline</h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('deals.board_subtitle')); ?></p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo e(route('deals.index')); ?>" class="bg-white border border-gray-300 text-gray-700 px-3.5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2"><i class="fas fa-list text-xs"></i> List</a>
        <a href="<?php echo e(route('deals.create')); ?>" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2"><i class="fas fa-plus text-xs"></i> <?php echo e(__('deals.new_deal')); ?></a>
    </div>
</div>

<div class="flex gap-4 overflow-x-auto pb-4" ondragover="event.preventDefault()">
    <?php $__empty_1 = true; $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="flex-shrink-0 w-72" ondragover="event.preventDefault()" ondrop="dropDeal(event, <?php echo e($stage->id); ?>)">
        <div class="flex items-center justify-between mb-3 px-1">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full" style="background: <?php echo e($stage->color ?? '#6366f1'); ?>"></span>
                <span class="font-semibold text-gray-800 text-sm"><?php echo e($stage->name); ?></span>
                <span class="text-xs text-gray-400"><?php echo e($stage->deals->count()); ?></span>
            </div>
            <span class="text-[10px] text-gray-400">Rp <?php echo e(number_format($stage->deals->sum('value'), 0, ',', '.')); ?></span>
        </div>
        <div class="space-y-2 min-h-[100px] rounded-xl bg-gray-100/60 p-2">
            <?php $__currentLoopData = $stage->deals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-lg border border-gray-200 p-3 cursor-move card-lift" draggable="true" ondragstart="dragDeal(event, <?php echo e($d->id); ?>)">
                <a href="<?php echo e(route('deals.show', $d)); ?>" class="font-medium text-sm text-gray-900 hover:text-brand-600 block mb-1"><?php echo e($d->title); ?></a>
                <div class="text-xs text-gray-500 mb-1"><i class="fas fa-user mr-1 text-gray-400"></i> <?php echo e($d->contact?->name ?? '-'); ?></div>
                <div class="text-xs font-semibold text-emerald-600">Rp <?php echo e(number_format($d->value, 0, ',', '.')); ?></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="w-full bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><i class="fas fa-columns text-gray-400 text-lg"></i></div>
        <p class="text-gray-500 font-medium"><?php echo e(__('deals.no_stages_hint')); ?></p>
        <p class="text-sm text-gray-400 mt-1 mb-4"><?php echo e(__('deals.no_stages_board_hint')); ?></p>
        <a href="<?php echo e(route('deal-stages.index')); ?>" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700"><i class="fas fa-plus text-xs"></i> <?php echo e(__('deals.manage_stages')); ?></a>
    </div>
    <?php endif; ?>
</div>

<form id="moveForm" method="POST" class="hidden"><?php echo csrf_field(); ?><input type="hidden" name="stage_id" id="moveStageId"></form>
<script>
let draggedDealId = null;
function dragDeal(e, id) { draggedDealId = id; e.dataTransfer.effectAllowed = 'move'; }
function dropDeal(e, stageId) {
    e.preventDefault();
    if (!draggedDealId) return;
    const f = document.getElementById('moveForm');
    f.action = '/deals/' + draggedDealId + '/move';
    document.getElementById('moveStageId').value = stageId;
    f.submit();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\deals\board.blade.php ENDPATH**/ ?>