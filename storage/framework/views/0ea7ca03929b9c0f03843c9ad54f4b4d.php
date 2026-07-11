<?php $__env->startSection('title', 'SLA Config — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">SLA Config</h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('sla.subtitle', ['count' => $configs->count()])); ?></p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo e(route('sla-logs.index')); ?>" class="bg-white border border-gray-300 text-gray-700 px-3.5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2"><i class="fas fa-list text-xs"></i> Logs</a>
        <button onclick="openModal()" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2"><i class="fas fa-plus text-xs"></i> <?php echo e(__('sla.new_config')); ?></button>
    </div>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
    <?php $__empty_1 = true; $__currentLoopData = $configs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-lg bg-cyan-50 flex items-center justify-center"><i class="fas fa-stopwatch text-cyan-500"></i></div>
                <div class="font-semibold text-gray-900 text-sm"><?php echo e($c->name); ?></div>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium <?php echo e($c->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'); ?>"><?php echo e($c->is_active ? __('common.active') : __('common.inactive')); ?></span>
        </div>
        <div class="space-y-1.5 text-xs text-gray-600 mb-3">
            <div class="flex items-center justify-between"><span><i class="fas fa-reply mr-1 text-gray-400"></i> <?php echo e(__('sla.first_response')); ?></span><span class="font-semibold"><?php echo e($c->first_response_minutes); ?> <?php echo e(__('common.minute')); ?></span></div>
            <div class="flex items-center justify-between"><span><i class="fas fa-check-circle mr-1 text-gray-400"></i> <?php echo e(__('sla.resolution')); ?></span><span class="font-semibold"><?php echo e($c->resolution_minutes); ?> <?php echo e(__('common.minute')); ?></span></div>
            <div class="flex items-center justify-between"><span><i class="fas fa-business-time mr-1 text-gray-400"></i> <?php echo e(__('sla.business_hours_only')); ?></span><span class="font-semibold"><?php echo e($c->business_hours_only ? __('common.yes') : __('common.no')); ?></span></div>
        </div>
        <div class="flex items-center justify-end gap-1">
            <button onclick='editConfig(<?php echo json_encode($c, 15, 512) ?>)' class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
            <form method="POST" action="<?php echo e(route('sla-configs.destroy', $c)); ?>" onsubmit="return confirm('<?php echo e(__('common.delete')); ?> config?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button></form>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="col-span-full bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-stopwatch text-2xl text-gray-400"></i></div>
        <p class="text-gray-500 font-medium mb-1"><?php echo e(__('sla.no_configs')); ?></p>
        <p class="text-sm text-gray-400 mb-4"><?php echo e(__('sla.no_configs_hint')); ?></p>
        <button onclick="openModal()" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition"><i class="fas fa-plus text-xs"></i> <?php echo e(__('sla.new_config')); ?></button>
    </div>
    <?php endif; ?>
</div>


<div id="slaModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="slaModalTitle"><?php echo e(__('sla.new_config_title')); ?></h2>
        <form method="POST" action="<?php echo e(route('sla-configs.store')); ?>" class="space-y-3" id="slaForm">
            <?php echo csrf_field(); ?>
            <div id="slaMethod"></div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('sla.name_config')); ?></label>
                <input type="text" name="name" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('sla.response_minutes')); ?></label>
                    <input type="number" name="first_response_minutes" min="1" value="15" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('sla.resolution_minutes')); ?></label>
                    <input type="number" name="resolution_minutes" min="1" value="120" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
            </div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="business_hours_only" value="1" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                <span class="text-xs text-gray-600"><?php echo e(__('sla.count_business_hours_only')); ?></span>
            </label>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('slaModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    const m = document.getElementById('slaModal'); m.classList.remove('hidden');
    document.getElementById('slaModalTitle').textContent = '<?php echo e(__('sla.new_config_title')); ?>';
    const f = document.getElementById('slaForm'); f.action = '<?php echo e(route('sla-configs.store')); ?>'; f.reset();
    document.getElementById('slaMethod').innerHTML = '';
}
function editConfig(c) {
    const m = document.getElementById('slaModal'); m.classList.remove('hidden');
    document.getElementById('slaModalTitle').textContent = '<?php echo e(__('sla.edit_config_title')); ?>';
    const f = document.getElementById('slaForm'); f.action = '/sla-configs/' + c.id;
    f.querySelector('[name="name"]').value = c.name;
    f.querySelector('[name="first_response_minutes"]').value = c.first_response_minutes;
    f.querySelector('[name="resolution_minutes"]').value = c.resolution_minutes;
    f.querySelector('[name="business_hours_only"]').checked = !!c.business_hours_only;
    document.getElementById('slaMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\sla\index.blade.php ENDPATH**/ ?>