<?php $__env->startSection('title', __('common.payment') . ' — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="max-w-2xl mx-auto">
    <a href="<?php echo e(route('plans.index')); ?>" class="text-sm text-gray-500 hover:text-brand-600">&larr; <?php echo e(__('common.back')); ?></a>
    <h1 class="text-2xl font-extrabold text-gray-900 mt-1 mb-2"><?php echo e(__('common.payment')); ?></h1>
    <p class="text-gray-500 mb-6"><?php echo e(__('common.plan')); ?> <?php echo e($plan->name); ?> — Rp <?php echo e(number_format($plan->price, 0, ',', '.')); ?></p>

    
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
        <div class="flex items-center gap-2 mb-3">
            <i class="fas fa-ticket-alt text-violet-500"></i>
            <span class="text-sm font-semibold text-gray-700"><?php echo e(__('coupons.apply_coupon')); ?></span>
        </div>
        <?php if(isset($appliedCoupon) && $appliedCoupon): ?>
        <div class="bg-violet-50 border border-violet-200 rounded-xl p-3 flex items-center justify-between mb-3">
            <div>
                <span class="text-sm font-semibold text-violet-700"><?php echo e($appliedCoupon['code']); ?></span>
                <span class="text-xs text-violet-500 ml-2">-Rp <?php echo e(number_format($discountAmount, 0, ',', '.')); ?></span>
            </div>
            <form method="POST" action="<?php echo e(route('coupons.apply')); ?>" class="inline">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="code" value="">
                <button class="text-xs text-red-500 hover:text-red-700 font-medium"><?php echo e(__('common.remove')); ?></button>
            </form>
        </div>
        <?php else: ?>
        <form method="POST" action="<?php echo e(route('coupons.apply')); ?>" class="flex gap-2">
            <?php echo csrf_field(); ?>
            <input type="text" name="code" placeholder="Masukkan kode kupon" required
                class="flex-1 rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 uppercase">
            <button type="submit" class="bg-violet-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-violet-700 transition flex-shrink-0">
                <?php echo e(__('coupons.apply')); ?>

            </button>
        </form>
        <?php endif; ?>
    </div>

    <div x-data="paymentForm()" class="space-y-5">
        
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-bold text-gray-900 mb-3"><?php echo e(__('common.select')); ?> Metode <?php echo e(__('common.payment')); ?></h2>
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

        
        <div class="bg-white rounded-xl border border-gray-200 p-5" x-show="gatewayId" x-transition>
            <h2 class="font-bold text-gray-900 mb-3">Instruksi <?php echo e(__('common.payment')); ?></h2>
            <div class="bg-gray-50 rounded-xl p-4">
                <div class="flex items-center gap-2 mb-3 pb-3 border-b border-gray-200">
                    <div class="w-6 h-6 rounded flex items-center justify-center text-white text-[10px] font-bold" x-bind:style="'background: ' + selectedColor"></div>
                    <span class="font-semibold text-sm" x-text="selectedName"></span>
                </div>
                <div class="text-sm text-gray-700 whitespace-pre-line leading-relaxed" x-text="selectedInstructions || '<?php echo e(__('common.select')); ?> metode <?php echo e(__('common.payment')); ?> terlebih dahulu.'"></div>
            </div>
        </div>

        
        <form method="POST" action="<?php echo e(route('payment.upload', $subscription)); ?>" class="bg-white rounded-xl border border-gray-200 p-5" x-show="gatewayId" x-transition>
            <?php echo csrf_field(); ?>
            <input type="hidden" name="gateway_id" x-model="gatewayId">
            <input type="hidden" name="amount" value="<?php echo e($finalPrice ?? $plan->price); ?>">

            <h2 class="font-bold text-gray-900 mb-3">Konfirmasi <?php echo e(__('common.payment')); ?></h2>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800 mb-4">
                <i class="fas fa-info-circle mr-1"></i> Setelah transfer, klik "Konfirmasi <?php echo e(__('common.payment')); ?>". Admin akan verifikasi.
            </div>
            <?php if(isset($discountAmount) && $discountAmount > 0): ?>
            <div class="flex justify-between items-center p-2 mb-1">
                <span class="text-sm text-gray-500"><?php echo e(__('common.subtotal')); ?></span>
                <span class="text-sm text-gray-400 line-through">Rp <?php echo e(number_format($plan->price, 0, ',', '.')); ?></span>
            </div>
            <div class="flex justify-between items-center p-2 mb-1">
                <span class="text-sm text-violet-600"><?php echo e(__('coupons.discount')); ?> (<?php echo e($appliedCoupon['code']); ?>)</span>
                <span class="text-sm font-semibold text-violet-600">-Rp <?php echo e(number_format($discountAmount, 0, ',', '.')); ?></span>
            </div>
            <?php endif; ?>
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl mb-4">
                <span class="text-sm text-gray-500"><?php echo e(__('common.total')); ?> <?php echo e(__('common.payment')); ?></span>
                <span class="text-xl font-extrabold text-gray-900">Rp <?php echo e(number_format($finalPrice ?? $plan->price, 0, ',', '.')); ?></span>
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-3 font-semibold text-sm hover:bg-brand-700 transition">
                <i class="fas fa-check-circle mr-1"></i> Konfirmasi <?php echo e(__('common.payment')); ?>

            </button>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
const gatewayData = <?php echo json_encode($gateways->mapWithKeys(fn($g) => [$g->id => ['name' => $g->name, 'color' => $g->logo_color, 'instructions' => $g->instructions]])); ?>;

document.addEventListener('alpine:init', () => {
    Alpine.data('paymentForm', () => ({
        gatewayId: null,
        selectedName: '',
        selectedColor: '#3b82f6',
        selectedInstructions: '',

        selectGateway(id) {
            this.gatewayId = id;
            const g = gatewayData[id];
            this.selectedName = g.name;
            this.selectedColor = g.color || '#3b82f6';
            this.selectedInstructions = g.instructions || '';
        }
    }));
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\payment\page.blade.php ENDPATH**/ ?>