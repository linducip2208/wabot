<?php $__env->startSection('title', __('publishing.queue_title') . ' — ' . config('app.name')); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6 flex items-center justify-between flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-gray-900"><i class="fas fa-clock text-brand-500 mr-2"></i><?php echo e(__('publishing.scheduled_queue')); ?></h1>
        <p class="text-gray-500 text-sm mt-1"><?php echo e(__('publishing.queue_subtitle', ['count' => $posts->total()])); ?></p>
    </div>
    <a href="<?php echo e(route('publishing.index')); ?>" class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-xl hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus"></i> <?php echo e(__('publishing.new_post')); ?>

    </a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
    <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="flex items-center justify-between p-4 border-b border-gray-100 last:border-0 hover:bg-gray-50 transition">
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 truncate"><?php echo e(Str::limit($post->content, 100) ?: __('publishing.no_content')); ?></p>
            <div class="flex items-center gap-3 mt-1.5">
                <span class="text-xs text-gray-500 flex items-center gap-1">
                    <i class="far fa-calendar"></i> <?php echo e($post->scheduled_at?->format('d M Y H:i')); ?>

                </span>
                <?php if($post->label): ?>
                <span class="text-xs px-2 py-0.5 rounded-full" style="background:<?php echo e($post->label->color); ?>20;color:<?php echo e($post->label->color); ?>">
                    <?php echo e($post->label->name); ?>

                </span>
                <?php endif; ?>
                <?php if($post->campaign): ?>
                <span class="text-xs text-gray-500"><?php echo e($post->campaign->name); ?></span>
                <?php endif; ?>
                <span class="text-xs text-gray-400">
                    <?php $__currentLoopData = $post->platform_targets ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $icon = match($p) { 'facebook_page' => 'fab fa-facebook text-blue-600', 'instagram_professional' => 'fab fa-instagram text-pink-600', 'x_twitter' => 'fab fa-x-twitter', 'tiktok' => 'fab fa-tiktok', 'linkedin_page' => 'fab fa-linkedin text-blue-700', default => '' }; ?>
                        <i class="<?php echo e($icon); ?> mr-1" title="<?php echo e($p); ?>"></i>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </span>
            </div>
        </div>
        <div class="flex items-center gap-2 ml-4">
            <form action="<?php echo e(route('publishing.publish', $post)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <button class="px-3 py-1.5 bg-green-50 text-green-700 text-xs font-medium rounded-lg border border-green-200 hover:bg-green-100 transition">
                    <i class="fas fa-paper-plane"></i> <?php echo e(__('publishing.publish_now_btn')); ?>

                </button>
            </form>
            <form action="<?php echo e(route('publishing.destroy', $post)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('publishing.delete_confirm')); ?>')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="px-3 py-1.5 text-red-600 text-xs font-medium rounded-lg hover:bg-red-50 transition">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="p-12 text-center">
        <i class="fas fa-clock text-gray-300 text-5xl mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-500 mb-1"><?php echo e(__('publishing.no_scheduled')); ?></h3>
        <p class="text-sm text-gray-400"><?php echo e(__('publishing.no_scheduled_desc')); ?></p>
    </div>
    <?php endif; ?>
</div>

<div class="mt-4">
    <?php echo e($posts->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\publishing\queue.blade.php ENDPATH**/ ?>