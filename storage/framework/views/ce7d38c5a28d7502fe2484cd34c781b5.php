<?php $__env->startSection('title', 'User — Admin'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('admin.user_mgmt')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('admin.registered_users', ['count' => $users->count()])); ?></p>
    </div>
    <button onclick="toggleModal()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> <?php echo e(__('common.create')); ?> User
    </button>
</div>


<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center"><i class="fas fa-users text-sky-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('common.total')); ?> User</div><div class="text-xl font-extrabold text-gray-900"><?php echo e($users->count()); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="fas fa-crown text-emerald-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Growth+</div><div class="text-xl font-extrabold text-gray-900"><?php echo e($users->filter(fn($u) => $u->plan && $u->plan->price > 0)->count()); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fas fa-gift text-amber-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Free</div><div class="text-xl font-extrabold text-gray-900"><?php echo e($users->filter(fn($u) => !$u->plan || $u->plan->price == 0)->count()); ?></div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center"><i class="fas fa-calendar text-violet-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide"><?php echo e(__('common.this_month')); ?></div><div class="text-xl font-extrabold text-gray-900"><?php echo e($users->where('created_at', '>=', now()->startOfMonth())->count()); ?></div></div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">User</th>
                <th class="px-5 py-3 hidden md:table-cell">Email</th>
                <th class="px-5 py-3"><?php echo e(__('common.plan')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('common.role')); ?></th>
                <th class="px-5 py-3 hidden lg:table-cell"><?php echo e(__('common.expired')); ?></th>
                <th class="px-5 py-3 hidden lg:table-cell"><?php echo e(__('common.registered')); ?></th>
                <th class="px-5 py-3 w-24 text-right"><?php echo e(__('common.action')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: <?php echo e(collect(['#2563eb','#7c3aed','#db2777','#ea580c','#059669','#0891b2'])->get(crc32($u->name) % 6)); ?>">
                            <?php echo e(strtoupper(substr($u->name, 0, 2))); ?>

                        </div>
                        <span class="font-medium text-gray-900"><?php echo e($u->name); ?></span>
                    </div>
                </td>
                <td class="px-5 py-3 text-gray-600 hidden md:table-cell"><?php echo e($u->email); ?></td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full <?php echo e($u->plan && $u->plan->price > 0 ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-600'); ?>">
                        <?php echo e($u->plan?->name ?? 'Free'); ?>

                    </span>
                </td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full <?php echo e(is_object($u->role) && $u->role->name === 'admin' ? 'bg-violet-50 text-violet-700' : 'bg-sky-50 text-sky-700'); ?>">
                        <?php echo e($u->role?->name ?? __('common.user')); ?>

                    </span>
                </td>
                <td class="px-5 py-3 hidden lg:table-cell text-xs <?php echo e($u->expires_at && $u->expires_at->isPast() ? 'text-red-600 font-semibold' : 'text-gray-500'); ?>">
                    <?php echo e($u->expires_at ? $u->expires_at->format('d M Y') : ($u->plan_id ? '—' : '—')); ?>

                </td>
                <td class="px-5 py-3 hidden lg:table-cell text-xs text-gray-400"><?php echo e($u->created_at->format('d M Y')); ?></td>
                <td class="px-5 py-3 text-right">
                    <form method="POST" action="<?php echo e(route('admin.users.impersonate', $u)); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button class="p-1.5 rounded-lg hover:bg-violet-50 text-gray-400 hover:text-violet-600" title="Login sebagai"><i class="fas fa-sign-in-alt text-xs"></i></button>
                    </form>
                    <button onclick='editUser(<?php echo e($u->id); ?>, "<?php echo e(addslashes($u->name)); ?>", "<?php echo e($u->email); ?>", <?php echo e($u->role_id ?? 'null'); ?>, <?php echo e($u->plan_id ?? 'null'); ?>, "<?php echo e($u->expires_at?->format('Y-m-d\TH:i') ?? ''); ?>")'
                        class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                    <form method="POST" action="<?php echo e(route('admin.users.destroy', $u)); ?>" class="inline" onsubmit="return confirm('<?php echo e(__('common.delete')); ?> user?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>


<div id="userModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="modalTitle"><?php echo e(__('common.create')); ?> User</h2>
        <form method="POST" action="<?php echo e(route('admin.users.store')); ?>" class="space-y-3" id="userForm">
            <?php echo csrf_field(); ?>
            <div id="methodField"></div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.name')); ?></label>
                <input type="text" name="name" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Email</label>
                <input type="email" name="email" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.password')); ?></label>
                <input type="password" name="password" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.role')); ?></label>
                <select name="role_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($r->id); ?>"><?php echo e($r->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.plan')); ?></label>
                <select name="plan_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value=""><?php echo e(__('common.without')); ?> <?php echo e(__('common.plan')); ?></option>
                    <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?> <?php echo e($p->price > 0 ? '(Rp '.number_format($p->price).')' : ''); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div id="expiryFields" class="hidden space-y-3">
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('admin.subscription_ends')); ?></label>
                    <input type="datetime-local" name="ends_at" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <p class="text-[11px] text-gray-400 mt-0.5"><?php echo e(__('admin.subscription_ends_hint')); ?></p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('admin.access_expires')); ?></label>
                    <input type="datetime-local" name="expires_at" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <p class="text-[11px] text-gray-400 mt-0.5"><?php echo e(__('admin.access_expires_hint')); ?></p>
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="toggleModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal() {
    const m = document.getElementById('userModal');
    m.classList.toggle('hidden');
    if (!m.classList.contains('hidden')) {
        document.getElementById('modalTitle').textContent = '<?php echo e(__('common.create')); ?> User';
        const f = document.getElementById('userForm');
        f.action = '<?php echo e(route('admin.users.store')); ?>';
        f.reset();
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('expiryFields').classList.add('hidden');
    }
}
function editUser(id, name, email, roleId, planId, expiresAt) {
    const m = document.getElementById('userModal');
    m.classList.remove('hidden');
    document.getElementById('modalTitle').textContent = '<?php echo e(__('admin.edit_user')); ?>';
    const f = document.getElementById('userForm');
    f.action = '/admin/users/' + id;
    f.querySelector('input[name="name"]').value = name;
    f.querySelector('input[name="email"]').value = email;
    f.querySelector('input[name="password"]').value = '';
    f.querySelector('input[name="password"]').required = false;
    f.querySelector('select[name="role_id"]').value = roleId || '';
    f.querySelector('select[name="plan_id"]').value = planId || '';
    f.querySelector('input[name="expires_at"]').value = expiresAt || '';
    document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    toggleExpiryFields();
}
function toggleExpiryFields() {
    const planId = document.querySelector('select[name="plan_id"]').value;
    document.getElementById('expiryFields').classList.toggle('hidden', !planId);
}
document.querySelector('select[name="plan_id"]').addEventListener('change', toggleExpiryFields);
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\admin\users\index.blade.php ENDPATH**/ ?>