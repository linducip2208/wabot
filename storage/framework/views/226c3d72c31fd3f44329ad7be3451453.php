<?php $__env->startSection('title', __('affiliate.page_title')); ?>
<?php $__env->startSection('content'); ?>

<div class="mb-5">
    <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('affiliate.page_title')); ?></h1>
    <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('affiliate.subtitle')); ?></p>
</div>


<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
    <div class="flex items-center gap-3 mb-3">
        <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center"><i class="fas fa-link text-brand-600"></i></div>
        <div>
            <h2 class="font-semibold text-gray-900"><?php echo e(__('affiliate.your_referral_link')); ?></h2>
            <p class="text-xs text-gray-500"><?php echo e(__('affiliate.share_and_earn')); ?></p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <input type="text" id="referralLink" value="<?php echo e($referralLink); ?>" readonly
            class="flex-1 bg-gray-50 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-mono text-gray-700 focus:ring-2 focus:ring-brand-500">
        <button onclick="copyReferralLink()" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2 flex-shrink-0">
            <i class="fas fa-copy text-xs"></i> <?php echo e(__('common.copy')); ?>

        </button>
    </div>
    <div id="copyFeedback" class="text-xs text-emerald-600 mt-2 hidden"><i class="fas fa-check mr-1"></i> <?php echo e(__('common.copied')); ?></div>
</div>


<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center"><i class="fas fa-users text-sky-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('affiliate.total_referrals')); ?></div><div class="text-xl font-extrabold text-gray-900"><?php echo e($summary['total_referrals']); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="fas fa-coins text-emerald-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('affiliate.total_commission')); ?></div><div class="text-xl font-extrabold text-gray-900">Rp <?php echo e(number_format($summary['total_commissions'], 0, ',', '.')); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fas fa-clock text-amber-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('affiliate.pending')); ?></div><div class="text-xl font-extrabold text-gray-900">Rp <?php echo e(number_format($summary['pending'], 0, ',', '.')); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center"><i class="fas fa-check-double text-violet-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('affiliate.paid')); ?></div><div class="text-xl font-extrabold text-gray-900">Rp <?php echo e(number_format($summary['paid'], 0, ',', '.')); ?></div></div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700"><?php echo e(__('affiliate.commissions')); ?></h2>
                <span class="text-xs text-gray-400"><?php echo e($commissions->count()); ?> <?php echo e(__('affiliate.entries')); ?></span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-5 py-3"><?php echo e(__('affiliate.referred_user')); ?></th>
                            <th class="px-5 py-3"><?php echo e(__('affiliate.amount')); ?></th>
                            <th class="px-5 py-3"><?php echo e(__('affiliate.rate')); ?></th>
                            <th class="px-5 py-3"><?php echo e(__('common.status')); ?></th>
                            <th class="px-5 py-3 hidden lg:table-cell"><?php echo e(__('common.date')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-5 py-3 text-gray-900 font-medium"><?php echo e($c->referredUser?->name ?? 'User #'.$c->referred_user_id); ?></td>
                            <td class="px-5 py-3 font-semibold text-emerald-600">Rp <?php echo e(number_format($c->amount, 0, ',', '.')); ?></td>
                            <td class="px-5 py-3 text-gray-500"><?php echo e($c->rate); ?>%</td>
                            <td class="px-5 py-3">
                                <?php if($c->status === 'pending'): ?>
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-amber-50 text-amber-700"><?php echo e(__('common.pending')); ?></span>
                                <?php else: ?>
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700"><?php echo e(__('common.paid')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell"><?php echo e($c->created_at->format('d M Y')); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center text-gray-500"><?php echo e(__('affiliate.no_commissions')); ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div class="space-y-4">
        
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-3"><?php echo e(__('affiliate.request_withdrawal')); ?></h2>
            <div class="bg-gray-50 rounded-xl p-3 mb-3 flex items-center justify-between">
                <span class="text-xs text-gray-500"><?php echo e(__('affiliate.available')); ?></span>
                <span class="text-sm font-bold text-gray-900">Rp <?php echo e(number_format($summary['pending'], 0, ',', '.')); ?></span>
            </div>
            <form method="POST" action="<?php echo e(route('affiliate.withdrawal.request')); ?>" class="space-y-3">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.amount')); ?> (Rp)</label>
                    <input type="number" name="amount" min="10000" step="1000" placeholder="100000" required
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.method')); ?></label>
                    <select name="payment_method" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="bank_transfer"><?php echo e(__('affiliate.bank_transfer')); ?></option>
                        <option value="paypal">PayPal</option>
                        <option value="ewallet"><?php echo e(__('affiliate.ewallet')); ?></option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('affiliate.payment_details')); ?></label>
                    <textarea name="payment_details" rows="2" required placeholder="<?php echo e(__('affiliate.details_placeholder')); ?>"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
                </div>
                <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700 transition">
                    <?php echo e(__('affiliate.submit_withdrawal')); ?>

                </button>
            </form>
        </div>

        
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-3"><?php echo e(__('affiliate.withdrawal_history')); ?></h2>
            <?php $__empty_1 = true; $__currentLoopData = $withdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="py-2 border-b border-gray-100 last:border-0">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-900">Rp <?php echo e(number_format($w->amount, 0, ',', '.')); ?></span>
                    <?php if($w->status === 'pending'): ?>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700"><?php echo e(__('common.pending')); ?></span>
                    <?php elseif($w->status === 'approved'): ?>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700"><?php echo e(__('common.approved')); ?></span>
                    <?php else: ?>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-red-50 text-red-700"><?php echo e(__('common.rejected')); ?></span>
                    <?php endif; ?>
                </div>
                <div class="text-xs text-gray-400 mt-0.5"><?php echo e($w->payment_method); ?> · <?php echo e($w->created_at->format('d M Y')); ?></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-xs text-gray-400 text-center py-4"><?php echo e(__('affiliate.no_withdrawals')); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function copyReferralLink() {
    const input = document.getElementById('referralLink');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value);
    document.getElementById('copyFeedback').classList.remove('hidden');
    setTimeout(() => document.getElementById('copyFeedback').classList.add('hidden'), 2000);
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\affiliate\index.blade.php ENDPATH**/ ?>