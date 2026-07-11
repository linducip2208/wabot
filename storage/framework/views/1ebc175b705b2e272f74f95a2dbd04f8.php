<?php $__env->startSection('title', __('common.send') . ' ' . __('common.message') . ' — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-5">
        <a href="<?php echo e(route('messages.sent')); ?>" class="text-sm text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left mr-1"></i> <?php echo e(__('common.back')); ?></a>
        <span class="text-gray-300">|</span>
        <a href="<?php echo e(route('messages.received')); ?>" class="text-xs text-gray-500 hover:text-brand-600 <?php echo e(request()->is('messages/received') ? 'font-semibold text-brand-600' : ''); ?>"><?php echo e(__('messages.inbox')); ?></a>
        <a href="<?php echo e(route('messages.sent')); ?>" class="text-xs text-gray-500 hover:text-brand-600 <?php echo e(request()->is('messages/sent') ? 'font-semibold text-brand-600' : ''); ?>"><?php echo e(__('common.sent')); ?></a>
        <a href="<?php echo e(route('messages.queue')); ?>" class="text-xs text-gray-500 hover:text-brand-600 <?php echo e(request()->is('messages/queue') ? 'font-semibold text-brand-600' : ''); ?>"><?php echo e(__('messages.queue')); ?></a>
    </div>

    <h1 class="text-xl font-extrabold text-gray-900 mb-1"><?php echo e(__('common.send')); ?> <?php echo e(__('common.message')); ?></h1>
    <p class="text-sm text-gray-500 mb-5"><?php echo e(__('common.send')); ?> WhatsApp langsung ke nomor atau <?php echo e(__('common.contact')); ?></p>

    <form method="POST" action="<?php echo e(route('messages.send')); ?>" class="bg-white rounded-2xl border border-gray-200 p-5 space-y-4">
        <?php echo csrf_field(); ?>

        <div x-data="{ channel: 'whatsapp' }">
            <div>
                <label class="text-xs font-medium text-gray-500">Channel</label>
                <select name="channel" x-model="channel" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="whatsapp">WhatsApp (Baileys)</option>
                    <option value="meta">WhatsApp Cloud (Meta)</option>
                    <option value="telegram">Telegram</option>
                </select>
            </div>

            <div x-show="channel === 'whatsapp'" class="mt-4">
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.session')); ?> WhatsApp</label>
                <select name="session_id" :required="channel === 'whatsapp'" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value=""><?php echo e(__('common.select')); ?> <?php echo e(__('common.session')); ?> <?php echo e(__('common.active')); ?></option>
                    <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($s->id); ?>"><?php echo e($s->name); ?> (<?php echo e($s->phone ?? 'offline'); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div x-show="channel === 'meta'" class="mt-4">
                <label class="text-xs font-medium text-gray-500">Meta Account</label>
                <select name="meta_account_id" :required="channel === 'meta'" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Pilih Meta Account</option>
                    <?php $__currentLoopData = $metaAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($m->id); ?>"><?php echo e($m->name); ?> (<?php echo e($m->phone_number ?? '-'); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div x-show="channel === 'telegram'" class="mt-4">
                <label class="text-xs font-medium text-gray-500">Telegram Account</label>
                <select name="telegram_account_id" :required="channel === 'telegram'" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Pilih Telegram Account</option>
                    <?php $__currentLoopData = $telegramAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($t->id); ?>"><?php echo e($t->name); ?> ({{ $t->bot_username }})</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

        </div>

        <div>
            <label class="text-xs font-medium text-gray-500"><?php echo e(__('messages.target')); ?></label>
            <div class="flex gap-2 mb-2">
                <button type="button" onclick="switchTab('manual')" id="tabManual" class="text-xs px-3 py-1.5 rounded-lg bg-brand-600 text-white font-medium"><?php echo e(__('messages.manual_number')); ?></button>
                <button type="button" onclick="switchTab('contact')" id="tabContact" class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 font-medium"><?php echo e(__('messages.from_contact')); ?></button>
            </div>
            <div id="panelManual">
                <input type="text" name="phone" placeholder="6281234567890" value="<?php echo e(old('phone')); ?>"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div id="panelContact" class="hidden">
                <select name="contact_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value=""><?php echo e(__('common.select')); ?> <?php echo e(__('common.contact')); ?></option>
                    <?php $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?> — <?php echo e(preg_replace('/@.*$/', '', $c->phone)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        <div>
            <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.message')); ?> <span class="text-gray-400">(<?php echo e(__('messages.spintax_hint')); ?>)</span></label>
            <textarea name="message" rows="4" required placeholder="<?php echo e(__('messages.type_message')); ?>"
                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"><?php echo e(old('message')); ?></textarea>
            <div class="flex items-center gap-2 mt-1 flex-wrap">
                <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="button" onclick="useTemplate('<?php echo e(addslashes($tpl->message)); ?>')" class="text-[10px] bg-gray-100 text-gray-600 px-2 py-1 rounded-lg hover:bg-gray-200"><?php echo e($tpl->name); ?></button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-3 font-semibold text-sm hover:bg-brand-700 transition">
            <i class="fas fa-paper-plane mr-1"></i> <?php echo e(__('common.send')); ?> <?php echo e(__('common.message')); ?>

        </button>
    </form>
</div>

<script>
function switchTab(tab) {
    document.getElementById('panelManual').classList.toggle('hidden', tab !== 'manual');
    document.getElementById('panelContact').classList.toggle('hidden', tab !== 'contact');
    document.getElementById('tabManual').classList.toggle('bg-brand-600 text-white', tab === 'manual');
    document.getElementById('tabManual').classList.toggle('bg-gray-100 text-gray-700', tab !== 'manual');
    document.getElementById('tabContact').classList.toggle('bg-brand-600 text-white', tab === 'contact');
    document.getElementById('tabContact').classList.toggle('bg-gray-100 text-gray-700', tab !== 'contact');
}
function useTemplate(msg) {
    document.querySelector('textarea[name="message"]').value = msg;
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\messages\send.blade.php ENDPATH**/ ?>