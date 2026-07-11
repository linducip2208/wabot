<?php $__env->startSection('title', 'Voucher — Admin'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('admin.voucher_mgmt')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('admin.voucher_count', ['count' => $vouchers->count()])); ?></p>
    </div>
    <button onclick="toggleModal()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> <?php echo e(__('common.create')); ?> Voucher
    </button>
</div>


<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <?php
        $active = $vouchers->filter(fn($v) => $v->is_active && $v->used_count < $v->max_uses);
        $expired = $vouchers->filter(fn($v) => !$v->is_active || $v->used_count >= $v->max_uses);
    ?>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center"><i class="fas fa-ticket-alt text-sky-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('admin.total_vouchers')); ?></div><div class="text-xl font-extrabold text-gray-900"><?php echo e($vouchers->count()); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="fas fa-check-circle text-emerald-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('common.active')); ?></div><div class="text-xl font-extrabold text-gray-900"><?php echo e($active->count()); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fas fa-clock text-amber-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('common.used')); ?></div><div class="text-xl font-extrabold text-gray-900"><?php echo e($vouchers->sum('used_count')); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center"><i class="fas fa-ban text-red-400"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('common.exhausted')); ?></div><div class="text-xl font-extrabold text-gray-900"><?php echo e($vouchers->where('used_count', '>=', 'max_uses')->count()); ?></div></div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3"><?php echo e(__('common.code')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('common.plan')); ?></th>
                <th class="px-5 py-3 hidden md:table-cell"><?php echo e(__('common.duration')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('common.usage')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('common.status')); ?></th>
                <th class="px-5 py-3 hidden lg:table-cell"><?php echo e(__('common.created')); ?></th>
                <th class="px-5 py-3 w-20 text-right"><?php echo e(__('common.action')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-5 py-3">
                    <span class="font-mono text-xs font-semibold text-gray-900 bg-gray-100 px-2 py-1 rounded-md tracking-wider"><?php echo e($v->code); ?></span>
                </td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full <?php echo e($v->plan->price > 0 ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-600'); ?>">
                        <?php echo e($v->plan->name); ?>

                    </span>
                </td>
                <td class="px-5 py-3 text-gray-600 hidden md:table-cell text-xs"><?php echo e($v->duration_days); ?> <?php echo e(__('common.days')); ?></td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-16 lg:w-24 bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full <?php echo e($v->max_uses > 0 && $v->used_count >= $v->max_uses ? 'bg-red-400' : 'bg-emerald-400'); ?>"
                                 style="width: <?php echo e($v->max_uses > 0 ? min(($v->used_count / $v->max_uses) * 100, 100) : 0); ?>%"></div>
                        </div>
                        <span class="text-xs text-gray-500 font-mono"><?php echo e($v->used_count); ?>/<?php echo e($v->max_uses); ?></span>
                    </div>
                </td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full <?php echo e($v->is_active && $v->used_count < $v->max_uses ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'); ?>">
                        <?php echo e($v->is_active && $v->used_count < $v->max_uses ? __('common.active') : __('common.inactive')); ?>

                    </span>
                </td>
                <td class="px-5 py-3 hidden lg:table-cell text-xs text-gray-400"><?php echo e($v->created_at->format('d M Y H:i')); ?></td>
                <td class="px-5 py-3 text-right">
                    <form method="POST" action="<?php echo e(route('admin.vouchers.destroy', $v)); ?>" class="inline" onsubmit="return confirm('<?php echo e(__('common.delete')); ?> voucher <?php echo e($v->code); ?>?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="7" class="px-5 py-16 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-ticket-alt text-xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 font-medium"><?php echo e(__('admin.no_vouchers')); ?></p>
                    <p class="text-sm text-gray-400"><?php echo e(__('admin.create_voucher_hint')); ?></p>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<div id="voucherModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4"><?php echo e(__('admin.create_new_voucher')); ?></h2>
        <form method="POST" action="<?php echo e(route('admin.vouchers.store')); ?>" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.plan')); ?></label>
                <select name="plan_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value=""><?php echo e(__('common.select')); ?> <?php echo e(__('common.plan')); ?></option>
                    <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?> <?php echo e($p->price > 0 ? '(Rp '.number_format($p->price).')' : ''); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('admin.max_uses')); ?></label>
                <input type="number" name="max_uses" value="1" min="1" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <p class="text-[11px] text-gray-400 mt-1"><?php echo e(__('admin.max_uses_hint')); ?></p>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('admin.duration_days')); ?></label>
                <input type="number" name="duration_days" value="30" min="1" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <p class="text-[11px] text-gray-400 mt-1"><?php echo e(__('admin.duration_hint')); ?></p>
            </div>
            <div class="pt-1 bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-700 flex items-start gap-2">
                <i class="fas fa-info-circle mt-0.5 flex-shrink-0"></i>
                <span><?php echo e(__('admin.voucher_auto_code_hint')); ?></span>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="toggleModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.create')); ?> Voucher</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal() {
    document.getElementById('voucherModal').classList.toggle('hidden');
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\admin\vouchers\index.blade.php ENDPATH**/ ?>