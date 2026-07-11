<?php $__env->startSection('title', 'Sentiment — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Sentiment Analysis</h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('sentiment.description')); ?></p>
    </div>
    <div class="flex items-center gap-2">
        <select id="channelFilter" class="text-sm rounded-xl border border-gray-300 px-3 py-2 bg-white text-gray-700 focus:ring-2 focus:ring-brand-500 focus:border-brand-500" onchange="window.location.href='?channel='+this.value+'&period=<?php echo e($period); ?>'">
            <?php $chLabels = ['all'=>'All Channels','whatsapp'=>'WhatsApp','meta'=>'Meta','instagram'=>'Instagram','telegram'=>'Telegram','facebook'=>'Facebook','gbm'=>'GBM','discord'=>'Discord','tiktok'=>'TikTok','line'=>'LINE','twitter'=>'X/Twitter','sms'=>'SMS','email'=>'Email']; ?>
            <?php $__currentLoopData = $chLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($val); ?>" <?php echo e($channel === $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
    <?php $__currentLoopData = [__('sentiment.today')=>$statsToday, __('sentiment.this_week')=>$statsWeek, __('sentiment.this_month')=>$statsMonth]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide"><?php echo e($label); ?></span>
            <span class="text-xs text-gray-400"><?php echo e($s['total'] ?? 0); ?> <?php echo e(__('common.message')); ?></span>
        </div>
        <div class="space-y-2">
            <div>
                    <div class="flex items-center justify-between text-xs mb-1"><span class="text-emerald-600"><i class="fas fa-smile mr-1"></i> <?php echo e(__('sentiment.positive')); ?></span><span class="font-semibold"><?php echo e($s['positive'] ?? 0); ?>%</span></div>
                <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden"><div class="h-full bg-emerald-500" style="width: <?php echo e($s['positive'] ?? 0); ?>%"></div></div>
            </div>
            <div>
                    <div class="flex items-center justify-between text-xs mb-1"><span class="text-gray-500"><i class="fas fa-meh mr-1"></i> <?php echo e(__('sentiment.neutral')); ?></span><span class="font-semibold"><?php echo e($s['neutral'] ?? 0); ?>%</span></div>
                <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden"><div class="h-full bg-gray-400" style="width: <?php echo e($s['neutral'] ?? 0); ?>%"></div></div>
            </div>
            <div>
                    <div class="flex items-center justify-between text-xs mb-1"><span class="text-red-600"><i class="fas fa-frown mr-1"></i> <?php echo e(__('sentiment.negative')); ?></span><span class="font-semibold"><?php echo e($s['negative'] ?? 0); ?>%</span></div>
                <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden"><div class="h-full bg-red-500" style="width: <?php echo e($s['negative'] ?? 0); ?>%"></div></div>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-bold text-gray-900 mb-4"><?php echo e(__('sentiment.distribution')); ?></h2>
        <canvas id="distChart" height="220"></canvas>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-bold text-gray-900 mb-4">Per-Channel Breakdown</h2>
        <canvas id="channelChart" height="220"></canvas>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 lg:col-span-1">
        <h2 class="font-bold text-gray-900 mb-4"><?php echo e(__('sentiment.trend_14_days')); ?></h2>
        <canvas id="trendChart" height="120"></canvas>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mt-4">
        <div class="px-5 py-3 border-b border-gray-100 font-semibold text-gray-800"><?php echo e(__('sentiment.recent_logs')); ?></div>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase"><th class="px-5 py-2"><?php echo e(__('common.contact')); ?></th><th class="px-5 py-2"><?php echo e(__('common.message')); ?></th><th class="px-5 py-2">Sentiment</th><th class="px-5 py-2">Confidence</th><th class="px-5 py-2"><?php echo e(__('common.time')); ?></th></tr></thead>
        <tbody class="divide-y divide-gray-100">
            <?php $sm = ['positive'=>[__('sentiment.positive'),'bg-emerald-50 text-emerald-700'],'neutral'=>[__('sentiment.neutral'),'bg-gray-100 text-gray-600'],'negative'=>[__('sentiment.negative'),'bg-red-50 text-red-700']]; ?>
            <?php $__empty_1 = true; $__currentLoopData = $recentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $b = $sm[$log->sentiment] ?? [$log->sentiment,'bg-gray-100 text-gray-600']; ?>
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-2.5 text-gray-800"><?php echo e($log->contact?->name ?? '-'); ?></td>
                <td class="px-5 py-2.5 text-gray-600 max-w-xs truncate"><?php echo e($log->message_text); ?></td>
                <td class="px-5 py-2.5"><span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium <?php echo e($b[1]); ?>"><?php echo e($b[0]); ?></span></td>
                <td class="px-5 py-2.5 text-gray-500"><?php echo e(round(($log->confidence ?? 0) * 100)); ?>%</td>
                <td class="px-5 py-2.5 text-xs text-gray-400"><?php echo e($log->created_at->diffForHumans()); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5" class="px-5 py-12 text-center text-gray-400"><i class="fas fa-smile text-3xl mb-2"></i><p><?php echo e(__('sentiment.no_logs')); ?></p></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cd = <?php echo json_encode($chartData, 15, 512) ?>;
    const td = <?php echo json_encode($trendChart, 15, 512) ?>;
    const chd = <?php echo json_encode($channelDistribution, 15, 512) ?>;
    if (window.Chart) {
        new Chart(document.getElementById('distChart'), {
            type: 'doughnut',
            data: { labels: cd.labels, datasets: [{ data: cd.values, backgroundColor: ['#10b981','#9ca3af','#ef4444'] }] },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
        new Chart(document.getElementById('channelChart'), {
            type: 'bar',
            data: {
                labels: ['WhatsApp','Meta','Instagram','Telegram','Facebook','GBM','Discord','TikTok','LINE','X/Twitter','SMS','Email'],
                datasets: [{
                    label: 'Messages',
                    data: [
                        chd.whatsapp||0, chd.meta||0, chd.instagram||0, chd.telegram||0,
                        chd.facebook||0, chd.gbm||0, chd.discord||0, chd.tiktok||0,
                        chd.line||0, chd.twitter||0, chd.sms||0, chd.email||0,
                    ],
                    backgroundColor: ['#25d366','#1877f2','#e4405f','#0088cc','#1877f2','#34a853','#5865f2','#000000','#06c755','#000000','#f22f46','#00a8e8'],
                    borderRadius: 8,
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: { labels: td.labels, datasets: [
                { label: '<?php echo e(__('sentiment.positive')); ?>', data: td.positive, borderColor: '#10b981', tension: .3 },
                { label: '<?php echo e(__('sentiment.neutral')); ?>', data: td.neutral, borderColor: '#9ca3af', tension: .3 },
                { label: '<?php echo e(__('sentiment.negative')); ?>', data: td.negative, borderColor: '#ef4444', tension: .3 },
            ]},
            options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
        });
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\sentiment\index.blade.php ENDPATH**/ ?>