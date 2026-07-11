<?php $__env->startSection('title', 'API Token — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div><h1 class="text-xl font-extrabold text-gray-900">API Token</h1><p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('tokens.subtitle')); ?></p></div>
    <button onclick="toggleModal()" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2"><i class="fas fa-plus text-xs"></i> <?php echo e(__('tokens.generate')); ?></button>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider"><th class="px-5 py-3"><?php echo e(__('common.name')); ?></th><th class="px-5 py-3">Token</th><th class="px-5 py-3 hidden md:table-cell"><?php echo e(__('common.created')); ?></th><th class="px-5 py-3 w-16"></th></tr></thead>
        <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $tokens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3 font-medium text-gray-900"><?php echo e($t->name); ?></td>
                <td class="px-5 py-3"><code class="text-xs bg-gray-100 px-2 py-1 rounded"><?php echo e(substr($t->token, 0, 16)); ?>...</code></td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-gray-400"><?php echo e($t->created_at->format('d M Y')); ?></td>
                <td class="px-5 py-3">
                    <form method="POST" action="<?php echo e(route('tokens.destroy', $t)); ?>" onsubmit="return confirm('<?php echo e(__('common.delete')); ?> token?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="4" class="px-5 py-16 text-center text-gray-500"><?php echo e(__('tokens.no_tokens')); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-4 bg-gray-50 rounded-xl p-4 text-sm text-gray-600">
    <p class="font-medium mb-2"><?php echo e(__('tokens.how_to_use')); ?></p>
    <code class="text-xs bg-white px-2 py-1 rounded border block mb-2">curl -H "Authorization: Bearer TOKEN" http://wabot.test/api/...</code>
</div>

<div id="tokenModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4"><?php echo e(__('tokens.generate_title')); ?></h2>
        <form method="POST" action="<?php echo e(route('tokens.store')); ?>" class="space-y-3">
            <?php echo csrf_field(); ?>
            <input type="text" name="name" placeholder="<?php echo e(__('tokens.name_placeholder')); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            <div class="flex gap-2">
                <button type="button" onclick="toggleModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold"><?php echo e(__('tokens.generate_btn')); ?></button>
            </div>
        </form>
    </div>
</div>

<script>function toggleModal(){document.getElementById('tokenModal').classList.toggle('hidden');}</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\tokens\index.blade.php ENDPATH**/ ?>