<?php $__env->startSection('title', __('common.contact') . ' — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('common.contact')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e($contacts->total()); ?> <?php echo e(__('common.contact')); ?> <?php echo e(__('contacts.stored')); ?></p>
    </div>
    <div class="flex gap-2">
        <button onclick="document.getElementById('importModal').classList.remove('hidden')"
            class="bg-white border border-gray-300 text-gray-700 px-3.5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2">
            <i class="fas fa-upload text-xs"></i> <?php echo e(__('contacts.import_csv')); ?>

        </button>
        <button onclick="toggleAddModal()"
            class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> <?php echo e(__('common.create')); ?>

        </button>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3"><?php echo e(__('common.contact')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('contacts.number')); ?></th>
                <th class="px-5 py-3 hidden md:table-cell">Tags</th>
                <th class="px-5 py-3 hidden lg:table-cell"><?php echo e(__('contacts.last_chat')); ?></th>
                <th class="px-5 py-3 w-20 text-right"><?php echo e(__('common.action')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: <?php echo e(collect(['#2563eb','#7c3aed','#db2777','#ea580c','#059669','#0891b2'])->get(crc32($c->phone) % 6)); ?>">
                            <?php echo e(strtoupper(substr($c->name, 0, 2))); ?>

                        </div>
                        <span class="font-medium text-gray-900"><?php echo e($c->name); ?></span>
                    </div>
                </td>
                <td class="px-5 py-3 font-mono text-xs text-gray-600"><?php echo e(preg_replace('/@.*$/', '', $c->phone)); ?></td>
                <td class="px-5 py-3 hidden md:table-cell">
                    <?php if($c->tags): ?>
                        <?php $__currentLoopData = $c->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-600 mr-1"><?php echo e($tag); ?></span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <span class="text-gray-400 text-xs">-</span>
                    <?php endif; ?>
                </td>
                <td class="px-5 py-3 hidden lg:table-cell text-xs text-gray-400">
                    <?php echo e($c->messages->last()?->created_at?->diffForHumans() ?? '-'); ?>

                </td>
                <td class="px-5 py-3 text-right">
                    <button onclick='editContact(<?php echo e($c->id); ?>, "<?php echo e(addslashes($c->name)); ?>", "<?php echo e($c->phone); ?>", <?php echo e(json_encode($c->tags ? implode(',', $c->tags) : '')); ?>)'
                        class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                    <form method="POST" action="<?php echo e(route('contacts.destroy', $c)); ?>" class="inline" onsubmit="return confirm('<?php echo e(__('common.delete')); ?>?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5" class="px-5 py-16 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-address-book text-gray-400 text-lg"></i>
                </div>
                <p class="text-gray-500 font-medium"><?php echo e(__('contacts.empty_title')); ?></p>
                <p class="text-sm text-gray-400 mt-1"><?php echo e(__('contacts.empty_desc')); ?></p>
            </td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4"><?php echo e($contacts->links()); ?></div>


<div id="contactModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="contactModalTitle"><?php echo e(__('common.create')); ?> <?php echo e(__('common.contact')); ?></h2>
        <form method="POST" action="<?php echo e(route('contacts.store')); ?>" class="space-y-3" id="contactForm">
            <?php echo csrf_field(); ?>
            <div id="contactMethodField"></div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.name')); ?></label>
                <input type="text" name="name" placeholder="<?php echo e(__('common.name')); ?> <?php echo e(__('common.contact')); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('contacts.phone')); ?></label>
                <input type="text" name="phone" placeholder="6281234567890" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('contacts.tags')); ?> <span class="text-gray-400"><?php echo e(__('contacts.tags_hint')); ?></span></label>
                <input type="text" name="tags" placeholder="VIP, Leads" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="toggleAddModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>


<div id="importModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4"><?php echo e(__('contacts.import_csv_title')); ?></h2>
        <form method="POST" action="<?php echo e(route('contacts.import')); ?>" enctype="multipart/form-data" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-600">
                <p class="font-medium mb-2"><?php echo e(__('contacts.file_format')); ?></p>
                <code class="text-xs bg-white px-2 py-1 rounded border border-gray-200 block"><?php echo e(__('common.name')); ?>, nomor, tag1,tag2</code>
            </div>
            <input type="file" name="file" accept=".csv,.txt" required class="w-full text-sm">
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><i class="fas fa-upload mr-1"></i> <?php echo e(__('common.import')); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAddModal() {
    const m = document.getElementById('contactModal');
    m.classList.toggle('hidden');
    if (!m.classList.contains('hidden')) {
        document.getElementById('contactModalTitle').textContent = '<?php echo e(__('common.create')); ?> <?php echo e(__('common.contact')); ?>';
        const f = document.getElementById('contactForm');
        f.action = '<?php echo e(route('contacts.store')); ?>';
        f.querySelector('input[name="name"]').value = '';
        f.querySelector('input[name="phone"]').value = '';
        f.querySelector('input[name="tags"]').value = '';
        document.getElementById('contactMethodField').innerHTML = '';
    }
}
function editContact(id, name, phone, tags) {
    const m = document.getElementById('contactModal');
    m.classList.remove('hidden');
    document.getElementById('contactModalTitle').textContent = '<?php echo e(__('common.edit')); ?> <?php echo e(__('common.contact')); ?>';
    const f = document.getElementById('contactForm');
    f.action = '/contacts/' + id;
    f.querySelector('input[name="name"]').value = name;
    f.querySelector('input[name="phone"]').value = phone;
    f.querySelector('input[name="tags"]').value = tags || '';
    document.getElementById('contactMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\contacts\index.blade.php ENDPATH**/ ?>