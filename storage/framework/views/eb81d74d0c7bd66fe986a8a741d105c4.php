<?php $__env->startSection('title', __('aistudio.content_title')); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('aistudio.content_title')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('aistudio.content_subtitle')); ?></p>
    </div>
    <a href="<?php echo e(route('ai-content.templates')); ?>" class="bg-white border border-gray-300 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition flex items-center gap-2">
        <i class="fas fa-layer-group text-xs"></i> <?php echo e(__('aistudio.manage_templates')); ?>

    </a>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-pen-to-square text-brand-500"></i> <?php echo e(__('aistudio.prompt_input')); ?>

            </h2>
            <form method="POST" action="<?php echo e(route('ai-content.generate')); ?>" id="contentForm">
                <?php echo csrf_field(); ?>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500"><?php echo e(__('aistudio.your_prompt')); ?> <span class="text-red-400">*</span></label>
                        <textarea name="prompt" rows="4" required placeholder="<?php echo e(__('aistudio.prompt_placeholder')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-200 focus:border-brand-400"><?php echo e(old('prompt')); ?></textarea>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500"><?php echo e(__('aistudio.platform')); ?></label>
                        <select name="platform" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                            <option value="general"><?php echo e(__('aistudio.platform_general')); ?></option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="instagram">Instagram</option>
                            <option value="facebook">Facebook</option>
                            <option value="twitter">X / Twitter</option>
                            <option value="telegram">Telegram</option>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-xs font-medium text-gray-500"><?php echo e(__('aistudio.tone')); ?></label>
                            <select name="tone" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                                <option value="professional"><?php echo e(__('aistudio.tone_professional')); ?></option>
                                <option value="casual"><?php echo e(__('aistudio.tone_casual')); ?></option>
                                <option value="humorous"><?php echo e(__('aistudio.tone_humorous')); ?></option>
                                <option value="persuasive"><?php echo e(__('aistudio.tone_persuasive')); ?></option>
                                <option value="urgent"><?php echo e(__('aistudio.tone_urgent')); ?></option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500"><?php echo e(__('aistudio.length')); ?></label>
                            <select name="length" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                                <option value="short"><?php echo e(__('aistudio.length_short')); ?></option>
                                <option value="medium" selected><?php echo e(__('aistudio.length_medium')); ?></option>
                                <option value="long"><?php echo e(__('aistudio.length_long')); ?></option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500"><?php echo e(__('aistudio.language')); ?></label>
                        <select name="language" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                            <option value="id">Bahasa Indonesia</option>
                            <option value="en">English</option>
                            <option value="auto"><?php echo e(__('aistudio.language_auto')); ?></option>
                        </select>
                    </div>

                    <?php if($templates->count()): ?>
                    <div>
                        <label class="text-xs font-medium text-gray-500"><?php echo e(__('aistudio.use_template')); ?></label>
                        <select name="template_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                            <option value=""><?php echo e(__('aistudio.no_template')); ?></option>
                            <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($tpl->id); ?>"><?php echo e($tpl->name); ?> <?php if($tpl->user_id !== Auth::id()): ?> (<?php echo e(__('aistudio.public')); ?>) <?php endif; ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <button type="submit" class="w-full bg-gradient-to-r from-brand-600 to-brand-700 text-white px-4 py-3 rounded-xl text-sm font-semibold hover:from-brand-700 hover:to-brand-800 transition flex items-center justify-center gap-2 card-lift" id="generateBtn">
                        <i class="fas fa-wand-magic-sparkles"></i> <?php echo e(__('aistudio.generate_content')); ?>

                    </button>
                </div>
            </form>
        </div>

        
        <?php if($templates->where('user_id', Auth::id())->count()): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-bold text-gray-900 text-sm mb-3 flex items-center gap-2">
                <i class="fas fa-bookmark text-brand-500"></i> <?php echo e(__('aistudio.your_templates')); ?>

            </h3>
            <div class="space-y-2">
                <?php $__currentLoopData = $templates->where('user_id', Auth::id())->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button onclick="document.querySelector('select[name=template_id]').value='<?php echo e($tpl->id); ?>'; document.querySelector('textarea[name=prompt]').focus()" class="w-full text-left p-2.5 rounded-lg border border-gray-100 hover:border-brand-200 hover:bg-brand-50/30 transition text-sm">
                        <div class="font-medium text-gray-800 text-xs"><?php echo e($tpl->name); ?></div>
                        <div class="text-[11px] text-gray-400 mt-0.5 truncate"><?php echo e(Str::limit($tpl->prompt_template, 60)); ?></div>
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    
    <div class="lg:col-span-2 space-y-4">
        <?php if(session('generated_content')): ?>
        <div class="bg-white rounded-xl border border-brand-200 shadow-sm p-5" x-data="{ copied: false }">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center"><i class="fas fa-wand-magic-sparkles text-brand-500 text-xs"></i></div>
                    <div>
                        <div class="font-semibold text-gray-900 text-sm"><?php echo e(__('aistudio.generated_content')); ?></div>
                        <div class="text-[11px] text-gray-400"><?php echo e(__('aistudio.based_on_prompt')); ?>: <?php echo e(Str::limit(session('generated_prompt'), 80)); ?></div>
                    </div>
                </div>
                <button @click="navigator.clipboard.writeText(`<?php echo e(addslashes(session('generated_content'))); ?>`); copied = true; setTimeout(() => copied = false, 2000)" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600 transition relative">
                    <i class="fas" :class="copied ? 'fa-check text-green-500' : 'fa-copy'"></i>
                    <span x-show="copied" class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded whitespace-nowrap" x-cloak>Copied!</span>
                </button>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 max-h-[500px] overflow-y-auto">
                <div class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap"><?php echo nl2br(e(session('generated_content'))); ?></div>
            </div>
        </div>
        <?php elseif(session('success') && !session('generated_content')): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-wand-magic-sparkles text-2xl text-gray-400"></i></div>
            <p class="text-gray-500 font-medium"><?php echo e(__('aistudio.ready_to_generate')); ?></p>
            <p class="text-sm text-gray-400 mt-1"><?php echo e(__('aistudio.fill_prompt_left')); ?></p>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="w-20 h-20 rounded-2xl bg-brand-50 flex items-center justify-center mx-auto mb-5">
                <i class="fas fa-wand-magic-sparkles text-3xl text-brand-400"></i>
            </div>
            <h3 class="text-lg font-extrabold text-gray-900 mb-2"><?php echo e(__('aistudio.ai_content_studio')); ?></h3>
            <p class="text-gray-500 text-sm max-w-md mx-auto"><?php echo e(__('aistudio.studio_description')); ?></p>
        </div>
        <?php endif; ?>

        
        <?php $history = session('ai_content_history', []); ?>
        <?php if(count($history) > 0): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-bold text-gray-900 text-sm mb-3 flex items-center gap-2">
                <i class="fas fa-clock-rotate-left text-gray-400"></i> <?php echo e(__('aistudio.recent_generated')); ?>

            </h3>
            <div class="space-y-3 max-h-[400px] overflow-y-auto">
                <?php $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="p-3 rounded-lg border border-gray-100 hover:border-gray-200 transition">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[11px] font-medium text-gray-400"><?php echo e($item['platform'] ?? 'general'); ?> &middot; <?php echo e($item['tone'] ?? 'professional'); ?> &middot; <span x-data x-text="new Date('<?php echo e($item['created_at']); ?>').toLocaleString('id-ID')" class="text-[11px]"></span></span>
                        <button onclick="navigator.clipboard.writeText(`<?php echo e(addslashes($item['result'])); ?>`)" class="text-gray-400 hover:text-brand-600 transition text-xs"><i class="fas fa-copy"></i></button>
                    </div>
                    <p class="text-xs text-gray-600 line-clamp-2"><?php echo e(Str::limit($item['result'], 150)); ?></p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\ai-content\index.blade.php ENDPATH**/ ?>