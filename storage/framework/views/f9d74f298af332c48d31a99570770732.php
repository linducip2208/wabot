<?php $__env->startSection('title', __('aiplanner.title')); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('aiplanner.title')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('aiplanner.subtitle')); ?></p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-20">
            <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-calendar-plus text-brand-500"></i> <?php echo e(__('aiplanner.new_plan')); ?>

            </h2>
            <form method="POST" action="<?php echo e(route('ai-planner.generate')); ?>" class="space-y-3">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('aiplanner.plan_name')); ?> <span class="text-red-400">*</span></label>
                    <input type="text" name="name" required placeholder="<?php echo e(__('aiplanner.plan_name_placeholder')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm" value="<?php echo e(old('name')); ?>">
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('aiplanner.niche')); ?> <span class="text-red-400">*</span></label>
                    <input type="text" name="niche" required placeholder="<?php echo e(__('aiplanner.niche_placeholder')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm" value="<?php echo e(old('niche')); ?>">
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('aiplanner.platforms')); ?> <span class="text-red-400">*</span></label>
                    <div class="grid grid-cols-3 gap-1.5 mt-1">
                        <?php $plats = ['whatsapp'=>'WA','instagram'=>'IG','facebook'=>'FB','twitter'=>'X','telegram'=>'TG','email'=>'Email']; $platIcons = ['fa-whatsapp','fa-instagram','fa-facebook','fa-x-twitter','fa-telegram','fa-envelope']; ?>
                        <?php $__currentLoopData = $plats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="cursor-pointer">
                            <input type="checkbox" name="platforms[]" value="<?php echo e($key); ?>" <?php echo e(in_array($key, old('platforms', [])) ? 'checked' : ''); ?> class="peer sr-only">
                            <div class="flex items-center gap-1.5 p-2 rounded-lg border border-gray-200 peer-checked:border-brand-400 peer-checked:bg-brand-50 text-gray-500 peer-checked:text-brand-600 transition text-[10px] font-medium">
                                <i class="fab <?php echo e($platIcons[$loop->index]); ?> text-xs"></i> <?php echo e($label); ?>

                            </div>
                        </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-xs font-medium text-gray-500"><?php echo e(__('aiplanner.frequency')); ?></label>
                        <select name="frequency" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                            <option value="weekly"><?php echo e(__('aiplanner.weekly')); ?></option>
                            <option value="daily"><?php echo e(__('aiplanner.daily')); ?></option>
                            <option value="monthly"><?php echo e(__('aiplanner.monthly')); ?></option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500"><?php echo e(__('aiplanner.duration_weeks')); ?></label>
                        <select name="duration" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                            <?php $__currentLoopData = [1,2,4,6,8,12]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($w); ?>" <?php echo e($w === 4 ? 'selected' : ''); ?>><?php echo e($w); ?> <?php echo e(__('aiplanner.weeks')); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 text-white px-4 py-3 rounded-xl text-sm font-semibold hover:from-emerald-700 hover:to-teal-700 transition flex items-center justify-center gap-2 card-lift">
                    <i class="fas fa-calendar-check"></i> <?php echo e(__('aiplanner.generate_plan')); ?>

                </button>
            </form>
        </div>
    </div>

    
    <div class="lg:col-span-2 space-y-4">
        <?php $viewPlanId = session('view_plan_id'); ?>
        <?php if($viewPlanId): ?>
            <?php $plan = $plans->find($viewPlanId); ?>
        <?php endif; ?>

        <?php if(isset($plan) && $plan): ?>
        <div class="bg-white rounded-xl border border-emerald-200 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-500 to-teal-500 px-5 py-4 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-lg"><?php echo e($plan->name); ?></h3>
                        <p class="text-sm text-emerald-100"><?php echo e(__('aiplanner.plan_details', ['start' => $plan->start_date->format('d M Y'), 'end' => $plan->end_date->format('d M Y')])); ?></p>
                    </div>
                    <form method="POST" action="<?php echo e(route('ai-planner.destroy', $plan)); ?>" onsubmit="return confirm('<?php echo e(__('common.delete')); ?>?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="p-2 rounded-lg bg-white/10 hover:bg-white/20 text-white transition"><i class="fas fa-trash text-sm"></i></button>
                    </form>
                </div>
            </div>
            <div class="p-5">
                <?php $calendar = $plan->calendar_data; $weeks = $calendar['weeks'] ?? []; ?>
                <?php if(count($weeks) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-2 px-3 text-xs font-semibold text-gray-400 uppercase"><?php echo e(__('aiplanner.week')); ?></th>
                                    <th class="text-left py-2 px-3 text-xs font-semibold text-gray-400 uppercase"><?php echo e(__('aiplanner.theme')); ?></th>
                                    <?php $__currentLoopData = $plan->platforms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <th class="text-left py-2 px-3 text-xs font-semibold text-gray-400 uppercase"><?php echo e(strtoupper($p)); ?></th>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $weeks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50/50">
                                    <td class="py-2.5 px-3 font-semibold text-gray-800"><?php echo e($week['week'] ?? $loop->iteration); ?></td>
                                    <td class="py-2.5 px-3 text-gray-600"><?php echo e($week['theme'] ?? '-'); ?></td>
                                    <?php $__currentLoopData = $plan->platforms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="py-2.5 px-3">
                                            <?php $topics = $week['platforms'][$p] ?? $week['platforms'][strtolower($p)] ?? []; ?>
                                            <?php if(is_array($topics) && count($topics) > 0): ?>
                                                <div class="space-y-1">
                                                    <?php $__currentLoopData = $topics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <span class="inline-block text-[11px] bg-gray-100 text-gray-700 px-2 py-0.5 rounded"><?php echo e(is_array($topic) ? ($topic['topic'] ?? json_encode($topic)) : $topic); ?></span>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-300">—</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-calendar text-2xl mb-2"></i>
                        <p class="text-sm"><?php echo e(isset($calendar['raw']) ? Str::limit($calendar['raw'], 300) : __('aiplanner.no_calendar_data')); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if($plans->count() > 0): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-bold text-gray-900 text-sm mb-4 flex items-center gap-2">
                <i class="fas fa-folder-tree text-emerald-500"></i> <?php echo e(__('aiplanner.saved_plans')); ?> <span class="text-gray-400 font-normal">(<?php echo e($plans->count()); ?>)</span>
            </h3>
            <div class="grid sm:grid-cols-2 gap-3">
                <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('ai-planner.index', ['view' => $p->id])); ?>" class="block p-4 rounded-xl border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50/30 transition card-lift <?php echo e(($viewPlanId ?? 0) === $p->id ? 'border-emerald-400 bg-emerald-50/50' : ''); ?>">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-gray-900 text-sm"><?php echo e($p->name); ?></span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full <?php echo e($p->status === 'generated' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'); ?>"><?php echo e(__('aiplanner.status_' . $p->status)); ?></span>
                    </div>
                    <div class="flex flex-wrap gap-1 mb-2">
                        <?php $__currentLoopData = $p->platforms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded"><?php echo e(strtoupper($plat)); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <div class="text-[11px] text-gray-400">
                        <i class="fas fa-calendar mr-1"></i> <?php echo e($p->start_date->format('d M')); ?> — <?php echo e($p->end_date->format('d M Y')); ?>

                        <span class="mx-1">&middot;</span> <?php echo e(__('aiplanner.' . $p->frequency)); ?>

                    </div>
                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php elseif(!isset($plan)): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-20 h-20 rounded-2xl bg-emerald-50 flex items-center justify-center mx-auto mb-5">
                <i class="fas fa-calendar-check text-3xl text-emerald-400"></i>
            </div>
            <h3 class="text-lg font-extrabold text-gray-900 mb-2"><?php echo e(__('aiplanner.no_plans_yet')); ?></h3>
            <p class="text-gray-500 text-sm max-w-md mx-auto"><?php echo e(__('aiplanner.start_planning')); ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\ai-planner\index.blade.php ENDPATH**/ ?>