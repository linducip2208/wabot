<?php $__env->startSection('title', 'SLA Dashboard — WABot'); ?>
<?php $__env->startSection('content'); ?>

<?php
    $stats = $stats ?? [
        'total' => 0,
        'first_response_breach' => 0,
        'resolution_breach' => 0,
        'avg_first_response_minutes' => 0,
    ];
    $total = $stats['total'] ?? 0;
    $frBreach = $stats['first_response_breach'] ?? 0;
    $resBreach = $stats['resolution_breach'] ?? 0;
    $compliance = $total > 0 ? round((($total - max($frBreach, $resBreach)) / $total) * 100, 1) : 100;
?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">SLA Dashboard</h1>
        <p class="text-sm text-gray-500 mt-0.5">Ringkasan performa layanan hari ini</p>
    </div>
    <div class="flex gap-2">
        <a href="<?php echo e(route('sla-logs.index')); ?>" class="bg-white border border-gray-300 text-gray-700 px-3.5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2"><i class="fas fa-list text-xs"></i> Logs</a>
        <a href="<?php echo e(route('sla-configs.index')); ?>" class="bg-white border border-gray-300 text-gray-700 px-3.5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2"><i class="fas fa-cog text-xs"></i> Config</a>
    </div>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift">
        <div class="flex items-center justify-between mb-2"><span class="text-xs font-medium text-gray-500"><?php echo e(__('common.total')); ?> Percakapan</span><div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center"><i class="fas fa-comments text-blue-500"></i></div></div>
        <div class="text-2xl font-extrabold text-gray-900" id="statTotal"><?php echo e($total); ?></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift">
        <div class="flex items-center justify-between mb-2"><span class="text-xs font-medium text-gray-500">Compliance</span><div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="fas fa-shield-alt text-emerald-500"></i></div></div>
        <div class="text-2xl font-extrabold text-emerald-600" id="statCompliance"><?php echo e($compliance); ?>%</div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift">
        <div class="flex items-center justify-between mb-2"><span class="text-xs font-medium text-gray-500">Respons Terlambat</span><div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center"><i class="fas fa-reply text-red-500"></i></div></div>
        <div class="text-2xl font-extrabold text-red-600" id="statFrBreach"><?php echo e($frBreach); ?></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift">
        <div class="flex items-center justify-between mb-2"><span class="text-xs font-medium text-gray-500">Rata-rata Respons</span><div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fas fa-stopwatch text-amber-500"></i></div></div>
        <div class="text-2xl font-extrabold text-gray-900" id="statAvg"><?php echo e($stats['avg_first_response_minutes'] ?? 0); ?> <span class="text-sm text-gray-400 font-medium">mnt</span></div>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-bold text-gray-900 mb-4">Pelanggaran SLA</h2>
        <div class="space-y-3">
            <div>
                <div class="flex items-center justify-between text-sm mb-1"><span class="text-gray-600">Respons pertama</span><span class="font-semibold text-red-600"><?php echo e($frBreach); ?> / <?php echo e($total); ?></span></div>
                <div class="h-2 rounded-full bg-gray-100 overflow-hidden"><div class="h-full bg-red-500" style="width: <?php echo e($total > 0 ? min(100, $frBreach / $total * 100) : 0); ?>%"></div></div>
            </div>
            <div>
                <div class="flex items-center justify-between text-sm mb-1"><span class="text-gray-600">Penyelesaian</span><span class="font-semibold text-orange-600"><?php echo e($resBreach); ?> / <?php echo e($total); ?></span></div>
                <div class="h-2 rounded-full bg-gray-100 overflow-hidden"><div class="h-full bg-orange-500" style="width: <?php echo e($total > 0 ? min(100, $resBreach / $total * 100) : 0); ?>%"></div></div>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center justify-center">
        <canvas id="slaChart" height="200"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('slaChart');
    if (ctx && window.Chart) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Sesuai SLA', 'Respons Terlambat', 'Penyelesaian Terlambat'],
                datasets: [{
                    data: [<?php echo e(max(0, $total - $frBreach - $resBreach)); ?>, <?php echo e($frBreach); ?>, <?php echo e($resBreach); ?>],
                    backgroundColor: ['#10b981', '#ef4444', '#f97316'],
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\sla\dashboard.blade.php ENDPATH**/ ?>