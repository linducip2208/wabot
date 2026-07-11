<?php $__env->startSection('title', 'Discord Bot — WABot'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">Discord Bot</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage Discord bot accounts & auto-reply via Discord channels</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
            class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> Add Bot
        </button>
    </div>

    <?php if($accounts->isEmpty()): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 mx-auto bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                <i class="fab fa-discord text-indigo-500 text-xl"></i>
            </div>
            <h3 class="text-gray-500 font-medium mb-1">No Discord bots yet</h3>
            <p class="text-sm text-gray-400 mb-4">Add a Discord bot to receive messages and handle slash commands.</p>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition">
                Add Bot
            </button>
        </div>
    <?php else: ?>
        <div class="space-y-3">
            <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift">
                    <div class="flex items-start justify-between flex-wrap gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center flex-shrink-0">
                                <i class="fab fa-discord text-white text-sm"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-semibold text-gray-900"><?php echo e($acc->name); ?></h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        <?php echo e($acc->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'); ?>">
                                        <?php echo e($acc->is_active ? __('common.connected') : __('common.disconnected')); ?>

                                    </span>
                                </div>
                                <?php if($acc->bot_name): ?>
                                    <p class="text-xs text-gray-400 mt-0.5"><?php echo e($acc->bot_name); ?></p>
                                <?php endif; ?>
                                <?php if($acc->guild_id): ?>
                                    <p class="text-xs text-gray-400">Guild: <?php echo e($acc->guild_id); ?></p>
                                <?php endif; ?>
                                <?php if($acc->application_id): ?>
                                    <p class="text-xs text-gray-400">App ID: <?php echo e($acc->application_id); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <?php if(!$acc->is_active): ?>
                                <form action="<?php echo e(route('discord.connect', $acc)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button class="px-3.5 py-2 rounded-xl text-xs font-medium bg-indigo-600 text-white hover:bg-indigo-700 transition">
                                        <i class="fab fa-discord mr-1"></i><?php echo e(__('common.connect')); ?>

                                    </button>
                                </form>
                            <?php else: ?>
                                <form action="<?php echo e(route('discord.disconnect', $acc)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button class="px-3.5 py-2 rounded-xl text-xs font-medium border border-red-200 text-red-600 hover:bg-red-50 transition">
                                        <?php echo e(__('common.disconnect')); ?>

                                    </button>
                                </form>
                            <?php endif; ?>
                            <button onclick="openTestModal(<?php echo e($acc->id); ?>)"
                                class="px-3 py-2 rounded-xl text-xs font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                                <i class="fas fa-paper-plane mr-1"></i><?php echo e(__('common.test')); ?>

                            </button>
                            <form action="<?php echo e(route('discord.destroy', $acc)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('common.delete')); ?>?')" class="inline">
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

    <div class="mt-4 bg-indigo-50 rounded-xl border border-indigo-100 p-5">
        <div class="flex items-start gap-4">
            <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-info text-indigo-600 text-sm"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 text-sm mb-1">Setup Guide</h3>
                <p class="text-xs text-gray-500">
                    1. Create a bot at <a href="https://discord.com/developers/applications" class="text-indigo-600 underline" target="_blank">Discord Developer Portal</a>.<br>
                    2. Copy the Bot Token from the Bot section.<br>
                    3. Set Interactions Endpoint URL to: <code class="bg-indigo-100 px-1 rounded"><?php echo e(route('webhook.discord')); ?></code><br>
                    4. Invite the bot to your server using OAuth2 URL Generator with <code>bot</code> + <code>applications.commands</code> scopes.
                </p>
            </div>
        </div>
    </div>
</div>


<div id="addModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Add Discord Bot</h2>
        <form action="<?php echo e(route('discord.store')); ?>" method="POST" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.name')); ?></label>
                <input type="text" name="name" placeholder="Support Bot" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Bot Token</label>
                <input type="text" name="bot_token" placeholder="From Discord Developer Portal" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Application ID</label>
                <input type="text" name="application_id" placeholder="From General Information"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Public Key</label>
                <input type="text" name="public_key" placeholder="From General Information"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-indigo-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-indigo-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>


<?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div id="testModal<?php echo e($acc->id); ?>" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><?php echo e(__('common.test')); ?> <?php echo e(__('common.send')); ?>: <?php echo e($acc->name); ?></h2>
        <form action="<?php echo e(route('discord.test', $acc)); ?>" method="POST" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Channel ID</label>
                <input type="text" name="channel_id" placeholder="123456789012345678" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.message')); ?></label>
                <textarea name="message" rows="3" placeholder="Test message..." required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('testModal<?php echo e($acc->id); ?>').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-indigo-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-indigo-700"><?php echo e(__('common.send')); ?></button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openTestModal(id) { document.getElementById('testModal'+id).classList.remove('hidden'); }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\discord\index.blade.php ENDPATH**/ ?>