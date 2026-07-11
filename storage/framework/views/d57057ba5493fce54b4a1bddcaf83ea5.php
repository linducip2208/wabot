<?php $__env->startSection('title', __('publishing.rss_title') . ' — ' . config('app.name')); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6 flex items-center justify-between flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-gray-900"><i class="fas fa-rss text-brand-500 mr-2"></i><?php echo e(__('publishing.rss_feeds')); ?></h1>
        <p class="text-gray-500 text-sm mt-1"><?php echo e(__('publishing.rss_subtitle', ['count' => $schedules->count()])); ?></p>
    </div>
    <button onclick="document.getElementById('addRssModal').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-xl hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus"></i> <?php echo e(__('publishing.add_rss')); ?>

    </button>
</div>

<div class="space-y-4">
    <?php $__empty_1 = true; $__currentLoopData = $schedules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <h3 class="text-sm font-semibold text-gray-800"><?php echo e($schedule->name); ?></h3>
                    <span class="text-xs px-2 py-0.5 rounded-full <?php echo e($schedule->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>">
                        <?php echo e($schedule->is_active ? __('publishing.active') : __('publishing.inactive')); ?>

                    </span>
                </div>
                <p class="text-xs text-gray-500 mb-2 truncate"><?php echo e($schedule->feed_url); ?></p>
                <div class="flex items-center gap-3 text-xs text-gray-400">
                    <span><i class="far fa-clock"></i> <?php echo e(__('publishing.every')); ?> <?php echo e($schedule->interval_minutes); ?> <?php echo e(__('publishing.minutes')); ?></span>
                    <span><i class="fas fa-history"></i> <?php echo e($schedule->histories_count); ?> <?php echo e(__('publishing.posts_created')); ?></span>
                    <?php if($schedule->last_checked_at): ?>
                    <span><?php echo e(__('publishing.last_checked')); ?>: <?php echo e($schedule->last_checked_at->diffForHumans()); ?></span>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-1 mt-2">
                    <?php $__currentLoopData = $schedule->platform_targets ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $icon = match($p) { 'facebook_page' => 'fab fa-facebook text-blue-600', 'instagram_professional' => 'fab fa-instagram text-pink-600', 'x_twitter' => 'fab fa-x-twitter', 'tiktok' => 'fab fa-tiktok', 'linkedin_page' => 'fab fa-linkedin text-blue-700', default => '' }; ?>
                        <i class="<?php echo e($icon); ?> text-xs" title="<?php echo e($p); ?>"></i>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <div class="flex items-center gap-1 ml-4 flex-shrink-0">
                <form action="<?php echo e(route('publishing.rss.toggle', $schedule)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button class="p-2 text-gray-400 hover:text-gray-700 transition" title="<?php echo e($schedule->is_active ? __('publishing.deactivate') : __('publishing.activate')); ?>">
                        <i class="fas <?php echo e($schedule->is_active ? 'fa-pause' : 'fa-play'); ?> text-sm"></i>
                    </button>
                </form>
                <button onclick="editRss(<?php echo e($schedule->id); ?>, '<?php echo e(addslashes($schedule->name)); ?>', '<?php echo e(addslashes($schedule->feed_url)); ?>', <?php echo e(json_encode($schedule->platform_targets)); ?>, <?php echo e($schedule->interval_minutes); ?>)" class="p-2 text-gray-400 hover:text-brand-600 transition">
                    <i class="fas fa-edit text-sm"></i>
                </button>
                <form action="<?php echo e(route('publishing.rss.destroy', $schedule)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('publishing.delete_confirm')); ?>')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="p-2 text-gray-400 hover:text-red-500 transition"><i class="fas fa-trash text-sm"></i></button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
        <i class="fas fa-rss text-gray-300 text-5xl mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-500 mb-1"><?php echo e(__('publishing.no_rss')); ?></h3>
        <p class="text-sm text-gray-400"><?php echo e(__('publishing.no_rss_desc')); ?></p>
    </div>
    <?php endif; ?>
</div>


<div id="addRssModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold text-gray-900"><?php echo e(__('publishing.add_rss_feed')); ?></h3>
            <button onclick="document.getElementById('addRssModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form action="<?php echo e(route('publishing.rss.store')); ?>" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('common.name')); ?></label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="<?php echo e(__('publishing.rss_name_placeholder')); ?>">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('publishing.feed_url')); ?></label>
                <input type="url" name="feed_url" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="https://example.com/rss">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('publishing.platforms')); ?></label>
                <div class="space-y-1">
                    <?php $__currentLoopData = App\Models\WaSocialAccount::platforms(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="platform_targets[]" value="<?php echo e($key); ?>" class="rounded text-brand-600 focus:ring-brand-500">
                        <span><?php echo e($name); ?></span>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('publishing.check_interval')); ?></label>
                <select name="interval_minutes" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="15">15 <?php echo e(__('publishing.minutes')); ?></option>
                    <option value="30">30 <?php echo e(__('publishing.minutes')); ?></option>
                    <option value="60" selected>1 <?php echo e(__('publishing.hour')); ?></option>
                    <option value="180">3 <?php echo e(__('publishing.hours')); ?></option>
                    <option value="360">6 <?php echo e(__('publishing.hours')); ?></option>
                    <option value="720">12 <?php echo e(__('publishing.hours')); ?></option>
                    <option value="1440">24 <?php echo e(__('publishing.hours')); ?></option>
                </select>
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-xl hover:bg-brand-700 transition">
                <?php echo e(__('publishing.save_rss')); ?>

            </button>
        </form>
    </div>
</div>


<div id="editRssModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold text-gray-900"><?php echo e(__('publishing.edit_rss')); ?></h3>
            <button onclick="document.getElementById('editRssModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form id="editRssForm" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('common.name')); ?></label>
                <input type="text" id="editRssName" name="name" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('publishing.feed_url')); ?></label>
                <input type="url" id="editRssUrl" name="feed_url" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('publishing.platforms')); ?></label>
                <div class="space-y-1" id="editRssPlatforms">
                    <?php $__currentLoopData = App\Models\WaSocialAccount::platforms(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="platform_targets[]" value="<?php echo e($key); ?>" class="rounded text-brand-600 focus:ring-brand-500">
                        <span><?php echo e($name); ?></span>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1"><?php echo e(__('publishing.check_interval')); ?></label>
                <select id="editRssInterval" name="interval_minutes" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="15">15 <?php echo e(__('publishing.minutes')); ?></option>
                    <option value="30">30 <?php echo e(__('publishing.minutes')); ?></option>
                    <option value="60">1 <?php echo e(__('publishing.hour')); ?></option>
                    <option value="180">3 <?php echo e(__('publishing.hours')); ?></option>
                    <option value="360">6 <?php echo e(__('publishing.hours')); ?></option>
                    <option value="720">12 <?php echo e(__('publishing.hours')); ?></option>
                    <option value="1440">24 <?php echo e(__('publishing.hours')); ?></option>
                </select>
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-xl hover:bg-brand-700 transition">
                <?php echo e(__('publishing.update_rss')); ?>

            </button>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function editRss(id, name, url, platforms, interval) {
    document.getElementById('editRssForm').action = '/publishing/rss/' + id;
    document.getElementById('editRssName').value = name;
    document.getElementById('editRssUrl').value = url;
    document.getElementById('editRssInterval').value = interval;
    const checkboxes = document.querySelectorAll('#editRssPlatforms input[type=checkbox]');
    checkboxes.forEach(cb => { cb.checked = platforms.includes(cb.value); });
    document.getElementById('editRssModal').classList.remove('hidden');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\publishing\rss.blade.php ENDPATH**/ ?>