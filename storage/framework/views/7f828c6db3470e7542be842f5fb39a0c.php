<?php $__env->startSection('title', __('aiimage.title')); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('aiimage.title')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('aiimage.subtitle')); ?></p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-20">
            <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-image text-brand-500"></i> <?php echo e(__('aiimage.generate_image')); ?>

            </h2>
            <form method="POST" action="<?php echo e(route('ai-image.generate')); ?>" class="space-y-3" id="imageForm">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('aiimage.prompt')); ?> <span class="text-red-400">*</span></label>
                    <textarea name="prompt" rows="3" required placeholder="<?php echo e(__('aiimage.prompt_placeholder')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"><?php echo e(old('prompt')); ?></textarea>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('aiimage.style')); ?></label>
                    <div class="grid grid-cols-5 gap-1.5 mt-1">
                        <?php $styles = ['photorealistic','illustration','anime','3d','logo']; $icons = ['fa-camera','fa-paintbrush','fa-dragon','fa-cube','fa-font']; ?>
                        <?php $__currentLoopData = $styles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="style" value="<?php echo e($s); ?>" <?php echo e($s === 'photorealistic' ? 'checked' : ''); ?> class="peer sr-only">
                            <div class="flex flex-col items-center gap-1 p-2 rounded-lg border border-gray-200 peer-checked:border-brand-400 peer-checked:bg-brand-50 text-gray-500 peer-checked:text-brand-600 transition text-[10px] font-medium">
                                <i class="fas <?php echo e($icons[$i]); ?> text-sm"></i>
                                <?php echo e(__('aiimage.style_' . $s)); ?>

                            </div>
                        </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('aiimage.size')); ?></label>
                    <div class="grid grid-cols-3 gap-1.5 mt-1">
                        <?php $sizes = ['square','landscape','portrait']; $sizeIcons = ['fa-square','fa-rectangle-landscape','fa-rectangle-portrait']; ?>
                        <?php $__currentLoopData = $sizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="size" value="<?php echo e($s); ?>" <?php echo e($s === 'square' ? 'checked' : ''); ?> class="peer sr-only">
                            <div class="flex flex-col items-center gap-1 p-2 rounded-lg border border-gray-200 peer-checked:border-brand-400 peer-checked:bg-brand-50 text-gray-500 peer-checked:text-brand-600 transition text-[10px] font-medium">
                                <i class="fas <?php echo e($sizeIcons[$i]); ?> text-sm"></i>
                                <?php echo e(__('aiimage.size_' . $s)); ?>

                            </div>
                        </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('aiimage.count')); ?> (1-4)</label>
                    <input type="number" name="count" min="1" max="4" value="1" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-violet-600 to-purple-600 text-white px-4 py-3 rounded-xl text-sm font-semibold hover:from-violet-700 hover:to-purple-700 transition flex items-center justify-center gap-2 card-lift" id="generateBtn">
                    <i class="fas fa-wand-magic-sparkles"></i> <?php echo e(__('aiimage.generate')); ?>

                </button>
            </form>
        </div>
    </div>

    
    <div class="lg:col-span-2 space-y-4">
        <?php if($jobs->isEmpty()): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-20 h-20 rounded-2xl bg-violet-50 flex items-center justify-center mx-auto mb-5">
                <i class="fas fa-image text-3xl text-violet-400"></i>
            </div>
            <h3 class="text-lg font-extrabold text-gray-900 mb-2"><?php echo e(__('aiimage.no_images_yet')); ?></h3>
            <p class="text-gray-500 text-sm max-w-md mx-auto"><?php echo e(__('aiimage.start_generating')); ?></p>
        </div>
        <?php else: ?>
        <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
            <i class="fas fa-images text-violet-500"></i> <?php echo e(__('aiimage.gallery')); ?> <span class="text-gray-400 font-normal">(<?php echo e($jobs->total()); ?>)</span>
        </h3>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden card-lift">
                <?php if($job->status === 'completed' && $job->results): ?>
                    <?php $img = $job->results[0] ?? null; ?>
                    <?php if($img && isset($img['url'])): ?>
                        <div class="aspect-square bg-gray-100 overflow-hidden relative group">
                            <img src="<?php echo e($img['url']); ?>" alt="<?php echo e($job->prompt); ?>" class="w-full h-full object-cover" loading="lazy">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition flex items-center justify-center opacity-0 group-hover:opacity-100">
                                <a href="<?php echo e($img['url']); ?>" download class="bg-white/90 text-gray-800 px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1 hover:bg-white transition">
                                    <i class="fas fa-download text-[10px]"></i> <?php echo e(__('aiimage.download')); ?>

                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="aspect-square bg-gray-100 flex items-center justify-center"><i class="fas fa-image text-4xl text-gray-300"></i></div>
                    <?php endif; ?>
                <?php elseif($job->status === 'processing' || $job->status === 'pending'): ?>
                    <div class="aspect-square bg-gray-50 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin text-2xl text-violet-400 mb-2"></i>
                            <p class="text-xs text-gray-400"><?php echo e(__('aiimage.generating')); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="aspect-square bg-red-50 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-exclamation-triangle text-2xl text-red-300 mb-2"></i>
                            <p class="text-xs text-red-400"><?php echo e(__('aiimage.failed')); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="p-3">
                    <p class="text-xs text-gray-600 line-clamp-2 mb-1.5"><?php echo e($job->prompt); ?></p>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-violet-50 text-violet-600"><?php echo e(__('aiimage.style_' . $job->style)); ?></span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-500"><?php echo e(__('aiimage.size_' . $job->size)); ?></span>
                        <?php if($job->status === 'completed'): ?>
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-green-50 text-green-600 ml-auto"><?php echo e($job->count); ?> <?php echo e(__('aiimage.images')); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="mt-4">
            <?php echo e($jobs->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\ai-image\index.blade.php ENDPATH**/ ?>