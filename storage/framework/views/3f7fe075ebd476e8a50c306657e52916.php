<?php $__env->startSection('title', 'WhatsApp Calling — WABot'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">WhatsApp Calling</h1>
            <p class="text-sm text-gray-500 mt-0.5">Voice broadcast via Meta API + ElevenLabs TTS</p>
        </div>
        <button onclick="document.getElementById('createModal').classList.remove('hidden')"
            class="bg-orange-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-orange-700 transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> <?php echo e(__('calls.create')); ?>

        </button>
    </div>

    <?php if($broadcasts->isEmpty()): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 mx-auto bg-orange-50 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-phone-volume text-orange-500 text-xl"></i>
            </div>
            <h3 class="text-gray-500 font-medium mb-1"><?php echo e(__('calls.no_broadcasts')); ?></h3>
            <p class="text-sm text-gray-400"><?php echo e(__('calls.no_broadcasts_hint')); ?></p>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php $__currentLoopData = $broadcasts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div>
                            <h3 class="font-semibold text-gray-900"><?php echo e($b->name); ?></h3>
                            <p class="text-xs text-gray-400 mt-0.5"><?php echo e($b->metaAccount?->name ?? '-'); ?> &middot; <?php echo e($b->created_at->format('d/m/Y H:i')); ?></p>
                            <div class="flex items-center gap-3 mt-2 flex-wrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?php echo e($b->status === 'completed' ? 'bg-green-100 text-green-800' : ''); ?>

                                    <?php echo e($b->status === 'sending' ? 'bg-blue-100 text-blue-800' : ''); ?>

                                    <?php echo e($b->status === 'failed' ? 'bg-red-100 text-red-800' : ''); ?>

                                    <?php echo e($b->status === 'draft' ? 'bg-gray-100 text-gray-600' : ''); ?>">
                                    <?php echo e(ucfirst($b->status)); ?>

                                </span>
                                <span class="text-xs text-gray-500"><i class="fas fa-phone mr-1"></i><?php echo e($b->called_count); ?>/<?php echo e($b->total_recipients); ?> <?php echo e(__('common.sent')); ?></span>
                                <span class="text-xs text-gray-500"><i class="fas fa-check mr-1"></i><?php echo e($b->answered_count); ?> <?php echo e(__('calls.answered')); ?></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a href="<?php echo e(route('calls.logs', $b)); ?>"
                                class="px-3 py-1.5 rounded-lg text-[11px] font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                                <i class="fas fa-list mr-1"></i>Logs
                            </a>
                            <form action="<?php echo e(route('calls.destroy', $b)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('common.delete')); ?>?')" class="inline">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>

<div id="createModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><?php echo e(__('calls.create')); ?></h2>
        <form action="<?php echo e(route('calls.store')); ?>" method="POST" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.name')); ?> Broadcast</label>
                <input type="text" name="name" placeholder="Contoh: Promo Lebaran" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('calls.meta_account')); ?></label>
                <select name="meta_account_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($acc->id); ?>"><?php echo e($acc->name); ?> (<?php echo e($acc->phone_number); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.message')); ?> <?php echo e(__('calls.voice')); ?> <span class="text-gray-400"><?php echo e(__('calls.voice_hint')); ?></span></label>
                <textarea name="message" rows="3" placeholder="<?php echo e(__('calls.voice_placeholder')); ?>" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.contact')); ?></label>
                <select name="recipient_ids[]" multiple class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm min-h-[100px]">
                    <?php $__currentLoopData = \App\Models\WaContact::where('user_id', Auth::id())->limit(100)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?> (<?php echo e($c->display_phone ?? $c->phone); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <p class="text-xs text-gray-400 mt-1"><?php echo e(__('calls.manual_numbers_hint')); ?></p>
                <textarea name="manual_numbers" rows="2" placeholder="62812xxxx&#10;62813xxxx"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm mt-1"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('calls.delay_seconds')); ?></label>
                <input type="number" name="delay_seconds" value="10" min="5" max="120"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-orange-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-orange-700"><?php echo e(__('calls.start')); ?></button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\calls\index.blade.php ENDPATH**/ ?>