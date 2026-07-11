<?php $__env->startSection('title', 'Kelola ' . __('common.plan') . ' — WABot'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900"><?php echo e(__('admin.plan_mgmt')); ?></h1>
            <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('admin.plan_mgmt_desc')); ?></p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
            class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> <?php echo e(__('common.create')); ?> <?php echo e(__('common.plan')); ?>

        </button>
    </div>

    <div class="space-y-3">
        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-start justify-between flex-wrap gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-2">
                            <h3 class="font-semibold text-gray-900 text-lg"><?php echo e($plan->name); ?></h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?php echo e($plan->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'); ?>">
                                <?php echo e($plan->is_active ? __('common.active') : __('common.inactive')); ?>

                            </span>
                            <span class="text-xs text-gray-400">/<?php echo e($plan->billing_period); ?></span>
                        </div>

                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xl font-extrabold text-gray-900">
                                <?php echo e($plan->price > 0 ? 'Rp ' . number_format($plan->price, 0, ',', '.') : __('common.free')); ?>

                            </span>
                        </div>

                        <div class="flex flex-wrap gap-1.5 mb-3">
                            <?php $featList = is_string($plan->features) ? json_decode($plan->features, true) : ($plan->features ?? []); ?>
                            <?php $__currentLoopData = $featList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-600"><?php echo e($f); ?></span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full <?php echo e($plan->can_use_meta ? 'bg-green-500' : 'bg-gray-300'); ?>"></span>
                                <span class="text-gray-500">Meta API (<?php echo e($plan->max_meta_accounts); ?>)</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full <?php echo e($plan->can_use_forms ? 'bg-green-500' : 'bg-gray-300'); ?>"></span>
                                <span class="text-gray-500">Forms (<?php echo e($plan->max_forms); ?>)</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full <?php echo e($plan->can_use_calling ? 'bg-green-500' : 'bg-gray-300'); ?>"></span>
                                <span class="text-gray-500">Calling</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full <?php echo e($plan->can_use_flow ? 'bg-green-500' : 'bg-gray-300'); ?>"></span>
                                <span class="text-gray-500">Flow</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full <?php echo e($plan->can_use_deals ? 'bg-green-500' : 'bg-gray-300'); ?>"></span>
                                <span class="text-gray-500">CRM</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full <?php echo e($plan->can_use_commerce ? 'bg-green-500' : 'bg-gray-300'); ?>"></span>
                                <span class="text-gray-500">Commerce</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full <?php echo e($plan->can_use_ai_agent ? 'bg-green-500' : 'bg-gray-300'); ?>"></span>
                                <span class="text-gray-500">AI Agents</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 mt-2 text-xs text-gray-400">
                            <span><?php echo e($plan->max_sessions); ?> <?php echo e(__('common.session')); ?></span>
                            <span><?php echo e(number_format($plan->max_contacts)); ?> <?php echo e(__('common.contact')); ?></span>
                            <span><?php echo e($plan->max_autoreplies); ?> autoreply</span>
                            <span><?php echo e(number_format($plan->max_campaign_recipients)); ?> kampanye</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button onclick="openEditModal(<?php echo e($plan->id); ?>)"
                            class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600 transition">
                            <i class="fas fa-pen text-xs"></i>
                        </button>
                        <form action="<?php echo e(route('admin.plans.destroy', $plan)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('common.delete')); ?> <?php echo e(__('common.plan')); ?>?')" class="inline">
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
</div>


<div id="addModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><?php echo e(__('common.create')); ?> <?php echo e(__('common.plan')); ?></h2>
        <form action="<?php echo e(route('admin.plans.store')); ?>" method="POST" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.name')); ?></label>
                    <input type="text" name="name" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Slug</label>
                    <input type="text" name="slug" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.period')); ?></label>
                    <select name="billing_period" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                        <option value="lifetime">Lifetime</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.price')); ?></label>
                    <input type="number" name="price" value="0" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Max <?php echo e(__('common.session')); ?></label>
                    <input type="number" name="max_sessions" value="1" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Max <?php echo e(__('common.contact')); ?></label>
                    <input type="number" name="max_contacts" value="100" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Max Kampanye</label>
                    <input type="number" name="max_campaign_recipients" value="50" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Max Autoreply</label>
                    <input type="number" name="max_autoreplies" value="10" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Max Meta Akun</label>
                    <input type="number" name="max_meta_accounts" value="0" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Max Forms</label>
                    <input type="number" name="max_forms" value="0" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
            </div>

            <div class="bg-gray-50 rounded-xl p-3">
                <span class="text-xs font-medium text-gray-500 mb-2 block">Fitur Boolean</span>
                <div class="grid grid-cols-4 gap-2">
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_manage_server"> Manage <?php echo e(__('common.server')); ?></label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_meta" checked> Meta API</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_forms"> WA Forms</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_calling"> WA Calling</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_instagram"> Instagram</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_flow"> Flow Builder</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_ai_agent"> AI Agents</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_intent"> Intent</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_drip"> Drip Campaign</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_ab_test"> A/B Test</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_catalog"> Catalog</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_commerce"> Commerce</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_deals"> CRM Deals</label>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600"><input type="checkbox" name="can_use_kanban"> Kanban</label>
                </div>
            </div>

            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>


<?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div id="editModal<?php echo e($plan->id); ?>" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><?php echo e(__('common.edit')); ?>: <?php echo e($plan->name); ?></h2>
        <form action="<?php echo e(route('admin.plans.update', $plan)); ?>" method="POST" class="space-y-3">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.name')); ?></label>
                    <input type="text" name="name" value="<?php echo e($plan->name); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Slug</label>
                    <input type="text" name="slug" value="<?php echo e($plan->slug); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.period')); ?></label>
                    <select name="billing_period" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="monthly" <?php echo e($plan->billing_period === 'monthly' ? 'selected' : ''); ?>>Monthly</option>
                        <option value="yearly" <?php echo e($plan->billing_period === 'yearly' ? 'selected' : ''); ?>>Yearly</option>
                        <option value="lifetime" <?php echo e($plan->billing_period === 'lifetime' ? 'selected' : ''); ?>>Lifetime</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-4 gap-3">
                <div><label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.price')); ?></label><input type="number" name="price" value="<?php echo e($plan->price); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Max <?php echo e(__('common.session')); ?></label><input type="number" name="max_sessions" value="<?php echo e($plan->max_sessions); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Max <?php echo e(__('common.contact')); ?></label><input type="number" name="max_contacts" value="<?php echo e($plan->max_contacts); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Max Kampanye</label><input type="number" name="max_campaign_recipients" value="<?php echo e($plan->max_campaign_recipients); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></div>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Max Autoreply</label><input type="number" name="max_autoreplies" value="<?php echo e($plan->max_autoreplies); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Max Meta Akun</label><input type="number" name="max_meta_accounts" value="<?php echo e($plan->max_meta_accounts); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-500 mb-1">Max Forms</label><input type="number" name="max_forms" value="<?php echo e($plan->max_forms); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></div>
            </div>

            <div class="bg-gray-50 rounded-xl p-3">
                <span class="text-xs font-medium text-gray-500 mb-2 block">Fitur Boolean</span>
                <div class="grid grid-cols-4 gap-2">
                    <?php $bools = ['can_manage_server','can_use_meta','can_use_forms','can_use_calling','can_use_instagram','can_use_flow','can_use_ai_agent','can_use_intent','can_use_drip','can_use_ab_test','can_use_catalog','can_use_commerce','can_use_deals','can_use_kanban']; ?>
                    <?php $__currentLoopData = $bools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center gap-1.5 text-xs text-gray-600">
                            <input type="checkbox" name="<?php echo e($b); ?>" <?php echo e($plan->$b ? 'checked' : ''); ?>>
                            <?php echo e(str_replace(['can_use_','can_'], '', str_replace('_',' ',$b))); ?>

                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('editModal<?php echo e($plan->id); ?>').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">Update</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openEditModal(id) { document.getElementById('editModal'+id).classList.remove('hidden'); }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\admin\plans\index.blade.php ENDPATH**/ ?>