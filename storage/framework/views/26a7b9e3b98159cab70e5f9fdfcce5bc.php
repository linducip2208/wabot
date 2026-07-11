<?php $__env->startSection('title', 'Order ' . $order->order_number . ' — WABot'); ?>
<?php $__env->startSection('content'); ?>

<?php
$statusMap = [
    'pending' => ['common.pending','bg-amber-50 text-amber-700'],
    'confirmed' => ['commerce.confirmed','bg-blue-50 text-blue-700'],
    'paid' => ['commerce.paid','bg-emerald-50 text-emerald-700'],
    'shipped' => ['commerce.shipped','bg-indigo-50 text-indigo-700'],
    'delivered' => ['common.completed','bg-teal-50 text-teal-700'],
    'cancelled' => ['common.cancelled','bg-red-50 text-red-700'],
];
$st = $statusMap[$order->status] ?? [$order->status,'bg-gray-100 text-gray-600'];
?>

<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('commerce.index')); ?>" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 class="text-xl font-extrabold text-gray-900"><?php echo e($order->order_number); ?></h1>
            <p class="text-sm text-gray-500 mt-0.5"><?php echo e($order->created_at->format('d M Y H:i')); ?></p>
        </div>
    </div>
    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium <?php echo e($st[1]); ?>"><?php echo e(__($st[0])); ?></span>
</div>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 font-semibold text-gray-800"><?php echo e(__('commerce.order_items')); ?></div>
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase"><th class="px-5 py-2"><?php echo e(__('common.product')); ?></th><th class="px-5 py-2">Qty</th><th class="px-5 py-2"><?php echo e(__('common.price')); ?></th><th class="px-5 py-2 text-right"><?php echo e(__('common.subtotal')); ?></th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="px-5 py-3 font-medium text-gray-800"><?php echo e($it->name); ?></td>
                        <td class="px-5 py-3 text-gray-600"><?php echo e($it->qty); ?></td>
                        <td class="px-5 py-3 text-gray-600">Rp <?php echo e(number_format($it->price, 0, ',', '.')); ?></td>
                        <td class="px-5 py-3 text-right font-semibold">Rp <?php echo e(number_format($it->subtotal, 0, ',', '.')); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400"><?php echo e(__('commerce.no_items')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot><tr class="border-t border-gray-200"><td colspan="3" class="px-5 py-3 text-right font-semibold text-gray-700"><?php echo e(__('common.total')); ?></td><td class="px-5 py-3 text-right font-extrabold text-gray-900">Rp <?php echo e(number_format($order->total, 0, ',', '.')); ?></td></tr></tfoot>
            </table>
        </div>

        <?php if($order->shipping_address || $order->notes): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-5 text-sm">
            <?php if($order->shipping_address): ?><div class="mb-3"><div class="text-xs font-semibold text-gray-500 mb-1"><?php echo e(__('commerce.shipping_address')); ?></div><p class="text-gray-700"><?php echo e($order->shipping_address); ?></p></div><?php endif; ?>
            <?php if($order->notes): ?><div><div class="text-xs font-semibold text-gray-500 mb-1"><?php echo e(__('common.notes')); ?></div><p class="text-gray-700"><?php echo e($order->notes); ?></p></div><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="text-xs font-semibold text-gray-500 mb-2"><?php echo e(__('common.customer')); ?></div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold"><?php echo e(strtoupper(substr($order->contact?->name ?? 'NA', 0, 2))); ?></div>
                <div>
                    <div class="font-medium text-gray-900"><?php echo e($order->contact?->name ?? '-'); ?></div>
                    <div class="text-xs text-gray-400 font-mono"><?php echo e(preg_replace('/@.*$/', '', $order->contact?->phone ?? '')); ?></div>
                </div>
            </div>
            <?php if($order->payment_method): ?><div class="mt-3 text-xs text-gray-500"><?php echo e(__('common.method')); ?>: <span class="font-medium text-gray-700"><?php echo e($order->payment_method); ?></span></div><?php endif; ?>
            <?php if($order->paid_at): ?><div class="text-xs text-gray-500"><?php echo e(__('commerce.paid_at')); ?>: <?php echo e($order->paid_at->format('d M Y H:i')); ?></div><?php endif; ?>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-2">
            <div class="text-xs font-semibold text-gray-500 mb-1"><?php echo e(__('common.action')); ?></div>
            <?php if($order->status === 'pending'): ?>
            <form method="POST" action="<?php echo e(route('commerce.confirm', $order)); ?>"><?php echo csrf_field(); ?><button class="w-full bg-blue-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-blue-700"><i class="fas fa-check mr-1"></i> <?php echo e(__('commerce.confirm_order')); ?></button></form>
            <?php endif; ?>
            <?php if($order->status === 'confirmed'): ?>
            <form method="POST" action="<?php echo e(route('commerce.paid', $order)); ?>" class="space-y-2"><?php echo csrf_field(); ?>
                <input type="text" name="payment_method" required placeholder="<?php echo e(__('common.method')); ?> <?php echo e(__('common.payment')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                <input type="url" name="payment_proof_url" placeholder="<?php echo e(__('commerce.payment_proof_url')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                <button class="w-full bg-emerald-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-emerald-700"><i class="fas fa-money-bill-wave mr-1"></i> <?php echo e(__('commerce.mark_as_paid')); ?></button>
            </form>
            <?php endif; ?>
            <?php if($order->status === 'paid'): ?>
            <form method="POST" action="<?php echo e(route('commerce.ship', $order)); ?>"><?php echo csrf_field(); ?><button class="w-full bg-indigo-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-indigo-700"><i class="fas fa-truck mr-1"></i> <?php echo e(__('commerce.send_order')); ?></button></form>
            <?php endif; ?>
            <?php if(!in_array($order->status, ['shipped','delivered','cancelled'])): ?>
            <form method="POST" action="<?php echo e(route('commerce.cancel', $order)); ?>" onsubmit="return confirm('<?php echo e(__('commerce.cancel_order_confirm')); ?>')"><?php echo csrf_field(); ?><button class="w-full bg-red-50 text-red-700 rounded-xl py-2.5 text-sm font-semibold hover:bg-red-100"><i class="fas fa-times mr-1"></i> <?php echo e(__('commerce.cancel_order')); ?></button></form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\commerce\show.blade.php ENDPATH**/ ?>