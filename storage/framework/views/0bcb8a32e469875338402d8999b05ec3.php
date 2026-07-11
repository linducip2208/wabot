<?php $__env->startSection('title', __('credits.payment_title')); ?>
<?php $__env->startSection('content'); ?>

<div class="max-w-2xl mx-auto">
    <a href="<?php echo e(route('credits.index')); ?>" class="text-sm text-gray-500 hover:text-brand-600">&larr; <?php echo e(__('common.back')); ?></a>
    <h1 class="text-2xl font-extrabold text-gray-900 mt-1 mb-2"><?php echo e(__('credits.payment_title')); ?></h1>
    <p class="text-gray-500 mb-6"><?php echo e($pack->name); ?> — <?php echo e(number_format($pack->credits)); ?> <?php echo e(__('credits.credits')); ?> — Rp <?php echo e(number_format($pack->price, 0, ',', '.')); ?></p>

    <div x-data="paymentForm()" class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-bold text-gray-900 mb-3"><?php echo e(__('common.select')); ?> <?php echo e(__('common.payment_method')); ?></h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                <?php $__currentLoopData = $gateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="button" @click="selectGateway(<?php echo e($g->id); ?>)"
                    class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 transition text-center"
                    :class="gatewayId === <?php echo e($g->id); ?> ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:border-gray-300'">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold" style="background: <?php echo e($g->logo_color ?? '#3b82f6'); ?>">
                        <?php echo e(substr($g->name, 0, 2)); ?>

                    </div>
                    <span class="text-xs font-medium text-gray-700"><?php echo e($g->name); ?></span>
                </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <form method="POST" action="<?php echo e(route('credits.callback', $payment)); ?>" class="bg-white rounded-xl border border-gray-200 p-5" x-show="gatewayId" x-transition>
            <?php echo csrf_field(); ?>
            <input type="hidden" name="gateway_id" x-model="gatewayId">

            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl mb-4">
                <span class="text-sm text-gray-500"><?php echo e(__('common.total')); ?></span>
                <span class="text-xl font-extrabold text-gray-900">Rp <?php echo e(number_format($pack->price, 0, ',', '.')); ?></span>
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-3 font-semibold text-sm hover:bg-brand-700 transition">
                <i class="fas fa-check-circle mr-1"></i> <?php echo e(__('credits.confirm_payment')); ?>

            </button>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
const gatewayData = <?php echo json_encode($gateways->mapWithKeys(fn($g) => [$g->id => ['name' => $g->name, 'color' => $g->logo_color]]), 512) ?>;

document.addEventListener('alpine:init', () => {
    Alpine.data('paymentForm', () => ({
        gatewayId: null,
        selectGateway(id) { this.gatewayId = id; }
    }));
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\credits\payment.blade.php ENDPATH**/ ?>