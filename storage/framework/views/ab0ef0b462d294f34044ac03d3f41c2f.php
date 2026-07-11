<?php $__env->startSection('title', __('common.server') . ' — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('common.server')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('servers.subtitle')); ?></p>
    </div>
    <?php if(Auth::user()->isAdmin()): ?>
    <button onclick="toggleModal()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> <?php echo e(__('common.create')); ?> <?php echo e(__('common.server')); ?>

    </button>
    <?php endif; ?>
</div>


<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center"><i class="fas fa-server text-sky-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('common.total')); ?> <?php echo e(__('common.server')); ?></div><div class="text-xl font-extrabold text-gray-900"><?php echo e($servers->count()); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="fas fa-check-circle text-emerald-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('servers.online')); ?></div><div class="text-xl font-extrabold text-gray-900"><?php echo e($servers->filter(fn($s) => $s->is_active)->count()); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center"><i class="fas fa-mobile-alt text-violet-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('common.total')); ?> <?php echo e(__('common.session')); ?></div><div class="text-xl font-extrabold text-gray-900"><?php echo e($totalSessions); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fas fa-exchange-alt text-amber-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('common.message')); ?><?php echo e(__('servers.per_day')); ?></div><div class="text-xl font-extrabold text-gray-900"><?php echo e(number_format($todayMessages)); ?></div></div>
    </div>
</div>

<?php if($servers->count() > 0): ?>
<div class="grid lg:grid-cols-2 gap-5 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fas fa-chart-bar text-brand-500"></i> <?php echo e(__('servers.messages_per_server')); ?></h2>
        <canvas id="serverMsgChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fas fa-chart-pie text-violet-500"></i> <?php echo e(__('servers.sessions_per_server')); ?></h2>
        <canvas id="serverSessionChart" height="200"></canvas>
    </div>
</div>
<?php endif; ?>


<div class="grid gap-4">
    <?php $__empty_1 = true; $__currentLoopData = $servers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php
        $sessions = $s->sessions;
        $connected = $sessions->where('status', 'connected')->count();
        $totalSess = $sessions->count();
        $online = false;
        try { $online = app(\App\Services\BaileysService::class)->check($s); } catch(\Exception $e) {}
    ?>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden card-lift">
        <div class="p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl <?php echo e($online ? 'bg-emerald-50' : 'bg-red-50'); ?> flex items-center justify-center">
                        <i class="fas fa-server text-xl <?php echo e($online ? 'text-emerald-500' : 'text-red-400'); ?>"></i>
                    </div>
                    <div>
                        <div class="font-bold text-gray-900 text-lg"><?php echo e($s->name); ?></div>
                        <div class="text-sm text-gray-500 font-mono"><?php echo e($s->host); ?>:<?php echo e($s->port); ?></div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full <?php echo e($online ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'); ?>">
                        <span class="w-2 h-2 rounded-full <?php echo e($online ? 'bg-emerald-500 animate-pulse' : 'bg-red-400'); ?>"></span>
                        <?php echo e($online ? __('servers.online') : __('servers.offline')); ?>

                    </span>
                    <?php if(Auth::user()->isAdmin()): ?>
                    <button onclick="editServer(<?php echo e($s->id); ?>, '<?php echo e($s->name); ?>', '<?php echo e($s->host); ?>', <?php echo e($s->port); ?>, '<?php echo e($s->api_key); ?>')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-sm"></i></button>
                    <form method="POST" action="<?php echo e(route('servers.destroy', $s)); ?>" onsubmit="return confirm('<?php echo e(__('common.delete')); ?> <?php echo e(__('common.server')); ?>?')" class="inline">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-sm"></i></button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <div class="text-2xl font-extrabold <?php echo e($connected > 0 ? 'text-emerald-600' : 'text-gray-400'); ?>"><?php echo e($connected); ?></div>
                    <div class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5"><?php echo e(__('servers.sessions_online')); ?></div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <div class="text-2xl font-extrabold text-gray-700"><?php echo e($totalSess); ?></div>
                    <div class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5"><?php echo e(__('common.total')); ?> <?php echo e(__('common.session')); ?></div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <div class="text-2xl font-extrabold text-gray-700"><?php echo e($s->messages_count ?? 0); ?></div>
                    <div class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5"><?php echo e(__('servers.messages_processed')); ?></div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <div class="text-2xl font-extrabold text-gray-700"><?php echo e(number_format($s->uptime ?? 0, 1)); ?>%</div>
                    <div class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5"><?php echo e(__('servers.uptime_24h')); ?></div>
                </div>
            </div>

            <?php if($totalSess > 0): ?>
            <div class="mt-4 pt-3 border-t border-gray-100">
                <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-2"><?php echo e(__('common.session')); ?> <?php echo e(__('common.active')); ?></div>
                <div class="flex flex-wrap gap-2">
                    <?php $__currentLoopData = $sessions->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ses): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('sessions.show', $ses)); ?>" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium transition
                        <?php echo e($ses->status === 'connected' ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'); ?>">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo e($ses->status === 'connected' ? 'bg-emerald-500' : 'bg-gray-400'); ?>"></span>
                        <?php echo e($ses->name); ?>

                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if($totalSess > 8): ?> <span class="text-xs text-gray-400 self-center">+<?php echo e($totalSess - 8); ?> <?php echo e(__('servers.more')); ?></span> <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-server text-2xl text-gray-400"></i>
        </div>
        <p class="text-gray-500 mb-1 font-medium"><?php echo e(__('servers.empty_title')); ?></p>
        <p class="text-sm text-gray-400 mb-4"><?php echo e(__('servers.empty_desc')); ?></p>
        <button onclick="toggleModal()" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
            <i class="fas fa-plus text-xs"></i> <?php echo e(__('common.create')); ?> <?php echo e(__('common.server')); ?>

        </button>
    </div>
    <?php endif; ?>
</div>

<?php if(Auth::user()->isAdmin()): ?>

<div id="serverModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4"><span id="modalTitle"><?php echo e(__('common.create')); ?></span> <?php echo e(__('common.server')); ?> Baileys</h2>
        <form id="serverForm" method="POST" action="<?php echo e(route('servers.store')); ?>" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div id="methodField"></div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.name')); ?> <?php echo e(__('common.server')); ?></label>
                <input type="text" name="name" placeholder="VPS Jakarta" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('servers.host')); ?></label>
                <input type="text" name="host" placeholder="<?php echo e(__('servers.host_placeholder')); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('servers.port')); ?></label>
                <input type="number" name="port" value="3100" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('servers.api_key')); ?></label>
                <input type="text" name="api_key" placeholder="wabot-secret-key" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="toggleModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal() { document.getElementById('serverModal').classList.toggle('hidden'); }
function editServer(id, name, host, port, apiKey) {
    document.getElementById('serverModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = '<?php echo e(__('common.edit')); ?>';
    const f = document.getElementById('serverForm');
    f.action = '/servers/' + id;
    f.querySelector('input[name="name"]').value = name;
    f.querySelector('input[name="host"]').value = host;
    f.querySelector('input[name="port"]').value = port;
    f.querySelector('input[name="api_key"]').value = apiKey;
    let m = document.getElementById('methodField');
    if (!m.querySelector('input')) m.innerHTML = '<input type="hidden" name="_method" value="PUT">';
}
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<?php if($servers->count() > 0): ?>
<script>
new Chart(document.getElementById('serverMsgChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($serverLabels); ?>,
        datasets: [{ label: '<?php echo e(__('common.message')); ?>', data: <?php echo json_encode($serverMessages); ?>, backgroundColor: ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444'], borderRadius: 8 }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: '#f1f5f9' } }, x: { ticks: { font: { size: 10 } }, grid: { display: false } } }
    }
});
new Chart(document.getElementById('serverSessionChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode($serverLabels); ?>,
        datasets: [{ data: <?php echo json_encode($serverSessions); ?>, backgroundColor: ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444'], borderWidth: 2, borderColor: '#fff' }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16, font: { size: 11 } } } }
    }
});
</script>
<?php endif; ?>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\servers\index.blade.php ENDPATH**/ ?>