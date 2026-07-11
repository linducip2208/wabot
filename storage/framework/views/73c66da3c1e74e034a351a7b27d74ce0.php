<?php $__env->startSection('title', __('aibesttime.title')); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('aibesttime.title')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('aibesttime.subtitle')); ?></p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-20">
            <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-clock text-brand-500"></i> <?php echo e(__('aibesttime.analyze')); ?>

            </h2>
            <form method="POST" action="<?php echo e(route('ai-best-time.suggest')); ?>" class="space-y-3">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('aibesttime.platform')); ?> <span class="text-red-400">*</span></label>
                    <select name="platform" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <?php $__currentLoopData = $platforms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p); ?>" <?php echo e(old('platform', session('selected_platform')) === $p ? 'selected' : ''); ?>><?php echo e(ucfirst($p)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('aibesttime.niche')); ?></label>
                    <input type="text" name="niche" placeholder="<?php echo e(__('aibesttime.niche_placeholder')); ?>" value="<?php echo e(old('niche')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('aibesttime.target_audience')); ?></label>
                    <input type="text" name="target_audience" placeholder="<?php echo e(__('aibesttime.audience_placeholder')); ?>" value="<?php echo e(old('target_audience')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('aibesttime.timezone')); ?></label>
                    <select name="timezone" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="WIB (UTC+7)">WIB (UTC+7) — Jakarta</option>
                        <option value="WITA (UTC+8)">WITA (UTC+8) — Bali, Makassar</option>
                        <option value="WIT (UTC+9)">WIT (UTC+9) — Papua</option>
                        <option value="UTC">UTC</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-amber-500 to-orange-500 text-white px-4 py-3 rounded-xl text-sm font-semibold hover:from-amber-600 hover:to-orange-600 transition flex items-center justify-center gap-2 card-lift">
                    <i class="fas fa-magnifying-glass-chart"></i> <?php echo e(__('aibesttime.get_recommendations')); ?>

                </button>
            </form>
        </div>
    </div>

    
    <div class="lg:col-span-2 space-y-4">
        <?php $recs = session('recommendations'); ?>
        <?php if($recs): ?>
        <div class="bg-white rounded-xl border border-amber-200 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-4 text-white">
                <div class="flex items-center gap-2">
                    <i class="fas fa-clock text-lg"></i>
                    <div>
                        <h3 class="font-bold"><?php echo e(__('aibesttime.recommendations_for')); ?> <?php echo e(ucfirst($recs['platform'] ?? session('selected_platform', ''))); ?></h3>
                        <p class="text-sm text-amber-100"><?php echo e($recs['timezone'] ?? 'WIB (UTC+7)'); ?></p>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <?php $schedule = $recs['schedule'] ?? []; ?>
                <?php if(count($schedule) > 0): ?>
                    
                    <div class="overflow-x-auto mb-5">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr>
                                    <th class="text-left py-2 px-2 text-[11px] font-semibold text-gray-400 uppercase w-20"><?php echo e(__('aibesttime.time')); ?></th>
                                    <?php $__currentLoopData = array_keys($schedule); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <th class="text-center py-2 px-2 text-[11px] font-semibold text-gray-600 w-16"><?php echo e(Str::limit($day, 3, '')); ?></th>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $timeSlots = ['06:00','07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00','22:00'];
                                    $dayKeys = array_keys($schedule);
                                ?>
                                <?php $__currentLoopData = $timeSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="border-t border-gray-100">
                                    <td class="py-1.5 px-2 text-[10px] text-gray-400"><?php echo e($slot); ?></td>
                                    <?php $__currentLoopData = $dayKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $slots = $schedule[$day] ?? [];
                                            $match = collect($slots)->first(fn($s) => ($s['time'] ?? '') === $slot);
                                            $score = $match['score'] ?? 0;
                                            $bg = $score >= 85 ? 'bg-emerald-100 text-emerald-800' : ($score >= 70 ? 'bg-lime-100 text-lime-800' : ($score >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-transparent'));
                                        ?>
                                        <td class="text-center py-1.5 px-1">
                                            <?php if($score > 0): ?>
                                                <span class="inline-block w-full text-[10px] font-semibold rounded px-1 py-0.5 <?php echo e($bg); ?>" title="<?php echo e($match['reason'] ?? ''); ?> — Score: <?php echo e($score); ?>"><?php echo e($score); ?></span>
                                            <?php else: ?>
                                                <span class="text-[10px] text-gray-200">&middot;</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    
                    <div class="flex items-center gap-3 mb-5 text-[10px] text-gray-500">
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-100"></span> 85-100 (<?php echo e(__('aibesttime.best')); ?>)</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-lime-100"></span> 70-84 (<?php echo e(__('aibesttime.good')); ?>)</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-amber-50"></span> 50-69 (<?php echo e(__('aibesttime.okay')); ?>)</span>
                    </div>

                    
                    <?php
                        $allSlots = [];
                        foreach ($schedule as $day => $slots) {
                            foreach ($slots as $s) {
                                $allSlots[] = array_merge($s, ['day' => $day]);
                            }
                        }
                        usort($allSlots, fn($a, $b) => ($b['score'] ?? 0) <=> ($a['score'] ?? 0));
                        $topSlots = array_slice($allSlots, 0, 10);
                    ?>
                    <div class="mb-5">
                        <h4 class="font-semibold text-gray-900 text-sm mb-2"><?php echo e(__('aibesttime.top_recommendations')); ?></h4>
                        <div class="grid sm:grid-cols-2 gap-2">
                            <?php $__currentLoopData = $topSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center gap-2 p-2.5 rounded-lg bg-gray-50 border border-gray-100">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-xs font-bold <?php echo e(($slot['score'] ?? 0) >= 85 ? 'bg-emerald-500 text-white' : (($slot['score'] ?? 0) >= 70 ? 'bg-lime-500 text-white' : 'bg-amber-400 text-white')); ?>"><?php echo e($slot['score'] ?? '?'); ?></div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-800"><?php echo e($slot['day']); ?> <?php echo e($slot['time'] ?? ''); ?></div>
                                    <div class="text-[10px] text-gray-400"><?php echo e($slot['reason'] ?? ''); ?></div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    
                    <?php $tips = $recs['tips'] ?? []; ?>
                    <?php if(count($tips) > 0): ?>
                    <div>
                        <h4 class="font-semibold text-gray-900 text-sm mb-2"><?php echo e(__('aibesttime.tips')); ?></h4>
                        <div class="space-y-1.5">
                            <?php $__currentLoopData = $tips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-start gap-2 text-xs text-gray-600">
                                    <i class="fas fa-lightbulb text-amber-400 mt-0.5 flex-shrink-0"></i>
                                    <span><?php echo e($tip); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php elseif(isset($recs['raw'])): ?>
                    <div class="text-sm text-gray-600 whitespace-pre-wrap"><?php echo e($recs['raw']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-20 h-20 rounded-2xl bg-amber-50 flex items-center justify-center mx-auto mb-5">
                <i class="fas fa-clock text-3xl text-amber-400"></i>
            </div>
            <h3 class="text-lg font-extrabold text-gray-900 mb-2"><?php echo e(__('aibesttime.empty_title')); ?></h3>
            <p class="text-gray-500 text-sm max-w-md mx-auto"><?php echo e(__('aibesttime.empty_desc')); ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\ai-best-time\index.blade.php ENDPATH**/ ?>