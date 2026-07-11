<?php $__env->startSection('title', __('publishing.composer_title') . ' — ' . config('app.name')); ?>

<?php $__env->startSection('content'); ?>
<div class="mb-6">
    <h1 class="text-2xl font-extrabold text-gray-900"><i class="fas fa-pen-to-square text-brand-500 mr-2"></i><?php echo e(__('publishing.composer')); ?></h1>
    <p class="text-gray-500 text-sm mt-1"><?php echo e(__('publishing.composer_subtitle')); ?></p>
</div>

<?php if($accountsCount === 0): ?>
<div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-6 mb-6">
    <div class="flex items-start gap-3">
        <i class="fas fa-exclamation-triangle text-amber-500 text-xl mt-0.5"></i>
        <div>
            <h3 class="font-semibold mb-1"><?php echo e(__('publishing.no_accounts_title')); ?></h3>
            <p class="text-sm"><?php echo e(__('publishing.no_accounts_desc')); ?></p>
        </div>
    </div>
</div>
<?php endif; ?>

<form action="<?php echo e(route('publishing.store')); ?>" method="POST" class="grid lg:grid-cols-3 gap-6">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="action" value="draft" id="formAction">

    
    <div class="lg:col-span-2 space-y-6">
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2"><?php echo e(__('publishing.content')); ?></label>
            <textarea name="content" rows="6" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none" placeholder="<?php echo e(__('publishing.content_placeholder')); ?>"><?php echo e(old('content')); ?></textarea>
            <div class="mt-3 flex items-center gap-4">
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="text-xs text-brand-600 hover:text-brand-700 font-medium flex items-center gap-1">
                        <i class="fas fa-book-open"></i> <?php echo e(__('publishing.use_caption')); ?>

                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute top-8 left-0 bg-white border border-gray-200 rounded-xl shadow-lg z-10 w-72 max-h-48 overflow-y-auto">
                        <?php $__empty_1 = true; $__currentLoopData = $captions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $caption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <button type="button" @click="$el.closest('form').querySelector('textarea[name=content]').value = `<?php echo e(str_replace('`', '\`', addslashes($caption->content))); ?>`; open = false" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-50 border-b border-gray-100 last:border-0">
                            <div class="font-medium text-gray-800"><?php echo e($caption->name); ?></div>
                            <div class="text-xs text-gray-500 truncate"><?php echo e(Str::limit($caption->content, 60)); ?></div>
                        </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="px-4 py-3 text-sm text-gray-500"><?php echo e(__('publishing.no_captions')); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <span class="text-xs text-gray-400"><?php echo e(__('publishing.spintax_hint')); ?></span>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6" x-data="{ urls: [], newUrl: '' }">
            <label class="block text-sm font-semibold text-gray-700 mb-2"><?php echo e(__('publishing.media_urls')); ?></label>
            <div class="flex gap-2 mb-3">
                <input type="url" x-model="newUrl" @keydown.enter.prevent="if(newUrl.trim()){ urls.push(newUrl.trim()); newUrl = '' }" class="flex-1 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="<?php echo e(__('publishing.media_url_placeholder')); ?>">
                <button type="button" @click="if(newUrl.trim()){ urls.push(newUrl.trim()); newUrl = '' }" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition"><?php echo e(__('publishing.add')); ?></button>
            </div>
            <template x-if="urls.length > 0">
                <div class="space-y-2">
                    <template x-for="(url, idx) in urls" :key="idx">
                        <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2">
                            <span class="flex-1 text-sm text-gray-700 truncate" x-text="url"></span>
                            <button type="button" @click="urls.splice(idx, 1)" class="text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button>
                            <input type="hidden" name="media_urls[]" :value="url">
                        </div>
                    </template>
                </div>
            </template>
        </div>

        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3"><?php echo e(__('publishing.recent_posts')); ?></h3>
            <?php $__empty_1 = true; $__currentLoopData = $recentPosts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800 truncate"><?php echo e(Str::limit($rp->content, 80) ?: __('publishing.no_content')); ?></p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs px-2 py-0.5 rounded-full <?php echo e($rp->status === 'published' ? 'bg-green-100 text-green-700' : ($rp->status === 'scheduled' ? 'bg-blue-100 text-blue-700' : ($rp->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'))); ?>">
                            <?php echo e(__('common.' . $rp->status)); ?>

                        </span>
                        <span class="text-xs text-gray-400"><?php echo e($rp->created_at->diffForHumans()); ?></span>
                    </div>
                </div>
                <?php if(!$rp->isPublished()): ?>
                <form action="<?php echo e(route('publishing.destroy', $rp)); ?>" method="POST" class="ml-2" onsubmit="return confirm('<?php echo e(__('publishing.delete_confirm')); ?>')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="text-gray-400 hover:text-red-500 text-sm"><i class="fas fa-trash"></i></button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-sm text-gray-500"><?php echo e(__('publishing.no_posts_yet')); ?></p>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="space-y-6">
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <label class="block text-sm font-semibold text-gray-700 mb-3"><?php echo e(__('publishing.platforms')); ?></label>
            <div class="space-y-2">
                <?php
                $platformAccounts = [
                    'facebook_page' => ['name' => 'Facebook Page', 'icon' => 'fab fa-facebook text-blue-600', 'accounts' => $facebookAccounts],
                    'instagram_professional' => ['name' => 'Instagram Professional', 'icon' => 'fab fa-instagram text-pink-600', 'accounts' => $instagramAccounts],
                    'x_twitter' => ['name' => 'X / Twitter', 'icon' => 'fab fa-x-twitter text-gray-800', 'accounts' => $twitterAccounts],
                    'tiktok' => ['name' => 'TikTok', 'icon' => 'fab fa-tiktok text-gray-800', 'accounts' => $tiktokAccounts],
                ];
                ?>
                <?php $__currentLoopData = $platformAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $hasAccount = $p['accounts']->count() > 0; ?>
                <label class="flex items-center gap-3 p-2.5 rounded-xl border <?php echo e($hasAccount ? 'border-gray-200 hover:bg-gray-50' : 'border-gray-100 bg-gray-50 opacity-50'); ?> cursor-pointer transition">
                    <input type="checkbox" name="platform_targets[]" value="<?php echo e($key); ?>" <?php echo e($hasAccount ? '' : 'disabled'); ?> class="rounded text-brand-600 focus:ring-brand-500">
                    <i class="<?php echo e($p['icon']); ?> w-5 text-center"></i>
                    <div>
                        <div class="text-sm font-medium text-gray-800"><?php echo e($p['name']); ?></div>
                        <?php if(!$hasAccount): ?>
                        <div class="text-[11px] text-gray-400"><?php echo e(__('publishing.no_connected_account')); ?></div>
                        <?php else: ?>
                        <div class="text-[11px] text-gray-500"><?php echo e($p['accounts']->first()->name ?? $p['accounts']->first()->page_name ?? $p['name']); ?></div>
                        <?php endif; ?>
                    </div>
                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6" x-data="{ schedule: false }">
            <label class="flex items-center gap-2 mb-3">
                <input type="checkbox" x-model="schedule" class="rounded text-brand-600 focus:ring-brand-500">
                <span class="text-sm font-semibold text-gray-700"><?php echo e(__('publishing.schedule_post')); ?></span>
            </label>
            <div x-show="schedule" class="mt-3">
                <input type="datetime-local" name="scheduled_at" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <p class="text-xs text-gray-400 mt-1"><?php echo e(__('publishing.schedule_hint')); ?></p>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1"><?php echo e(__('publishing.campaign')); ?></label>
                <select name="campaign_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value=""><?php echo e(__('publishing.no_campaign')); ?></option>
                    <?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1"><?php echo e(__('publishing.label')); ?></label>
                <select name="label_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value=""><?php echo e(__('publishing.no_label')); ?></option>
                    <?php $__currentLoopData = $labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($l->id); ?>">
                        <span class="inline-block w-2.5 h-2.5 rounded-full mr-2" style="background:<?php echo e($l->color); ?>"></span>
                        <?php echo e($l->name); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>

        
        <div class="flex flex-col gap-2">
            <button type="submit" onclick="document.getElementById('formAction').value='publish'" class="w-full bg-gradient-to-r from-brand-600 to-brand-700 text-white font-semibold py-3 rounded-xl hover:shadow-lg hover:from-brand-700 hover:to-brand-800 transition flex items-center justify-center gap-2">
                <i class="fas fa-paper-plane"></i> <?php echo e(__('publishing.publish_now')); ?>

            </button>
            <button type="submit" onclick="document.getElementById('formAction').value='schedule'" class="w-full bg-blue-50 text-blue-700 font-semibold py-3 rounded-xl border border-blue-200 hover:bg-blue-100 transition flex items-center justify-center gap-2">
                <i class="fas fa-clock"></i> <?php echo e(__('publishing.schedule')); ?>

            </button>
            <button type="submit" onclick="document.getElementById('formAction').value='draft'" class="w-full bg-gray-50 text-gray-700 font-semibold py-3 rounded-xl border border-gray-200 hover:bg-gray-100 transition flex items-center justify-center gap-2">
                <i class="fas fa-file-lines"></i> <?php echo e(__('publishing.save_draft')); ?>

            </button>
        </div>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\publishing\index.blade.php ENDPATH**/ ?>