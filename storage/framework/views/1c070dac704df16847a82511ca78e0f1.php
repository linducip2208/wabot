<?php $__env->startSection('title', __('templates.title') . ' — WABot'); ?>
<?php $__env->startSection('content'); ?>

<?php
$formatMeta = [
    'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'fa-whatsapp', 'cls' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'iconCls' => 'text-emerald-500', 'bgIcon' => 'bg-emerald-50'],
    'text'     => ['label' => __('templates.plain_text'), 'icon' => 'fa-align-left', 'cls' => 'bg-gray-100 text-gray-600 border-gray-200', 'iconCls' => 'text-gray-500', 'bgIcon' => 'bg-gray-100'],
    'markdown' => ['label' => 'Markdown', 'icon' => 'fa-hashtag', 'cls' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'iconCls' => 'text-indigo-500', 'bgIcon' => 'bg-indigo-50'],
];
?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('templates.title')); ?></h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('templates.subtitle', ['count' => $templates->count()])); ?></p>
    </div>
    <button onclick="openCreate()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> <?php echo e(__('common.create')); ?> Template
    </button>
</div>

<div class="grid gap-3">
    <?php $__empty_1 = true; $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php $meta = $formatMeta[$t->format] ?? $formatMeta['text']; ?>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3 flex-1 min-w-0">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 <?php echo e($meta['bgIcon']); ?>">
                    <i class="fas <?php echo e($meta['icon']); ?> <?php echo e($meta['iconCls']); ?>"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-sm text-gray-900 truncate"><?php echo e($t->name); ?></span>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-md border <?php echo e($meta['cls']); ?>"><?php echo e($meta['label']); ?></span>
                    </div>
                    <div class="text-sm text-gray-600 bg-gray-50 px-3 py-2 rounded-lg whitespace-pre-line line-clamp-3 break-words"><?php echo e($t->message); ?></div>
                </div>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
                <button onclick="editTemplate(<?php echo e($t->id); ?>)"
                    class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                <form method="POST" action="<?php echo e(route('templates.destroy', $t)); ?>" onsubmit="return confirm('<?php echo e(__('templates.delete_confirm')); ?>')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-file-lines text-2xl text-gray-400"></i>
        </div>
        <p class="text-gray-500 font-medium mb-1"><?php echo e(__('templates.empty_title')); ?></p>
        <p class="text-sm text-gray-400 mb-4"><?php echo e(__('templates.empty_desc')); ?></p>
        <button onclick="openCreate()" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
            <i class="fas fa-plus text-xs"></i> <?php echo e(__('templates.create_button')); ?>

        </button>
    </div>
    <?php endif; ?>
</div>


<div id="tplModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="tplModalTitle"><?php echo e(__('templates.create_title')); ?></h2>
        <form method="POST" action="<?php echo e(route('templates.store')); ?>" class="space-y-3" id="tplForm">
            <?php echo csrf_field(); ?>
            <div id="tplMethodField"></div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.name')); ?></label>
                    <input type="text" name="name" placeholder="Promo Akhir Pekan" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500"><?php echo e(__('templates.format')); ?></label>
                    <select name="format" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="whatsapp">WhatsApp</option>
                        <option value="text"><?php echo e(__('templates.plain_text')); ?></option>
                        <option value="markdown">Markdown</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('templates.message_content')); ?> <span class="text-gray-400"><?php echo e(__('templates.formatting_hint')); ?></span></label>
                <textarea name="message" rows="5" required placeholder="Halo {name}, ada promo menarik untukmu hari ini!" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="closeModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>

<?php
$templatesJson = $templates->keyBy('id')->map(function($t) {
    return ['name' => $t->name, 'message' => $t->message, 'format' => $t->format];
});
?>

<?php $__env->startPush('scripts'); ?>
<script>
const templatesData = <?php echo json_encode($templatesJson); ?>;

function closeModal() {
    document.getElementById('tplModal').classList.add('hidden');
}

function openCreate() {
    const f = document.getElementById('tplForm');
    document.getElementById('tplModalTitle').textContent = '<?php echo e(__('templates.create_title')); ?>';
    f.action = '<?php echo e(route('templates.store')); ?>';
    f.querySelector('input[name="name"]').value = '';
    f.querySelector('textarea[name="message"]').value = '';
    f.querySelector('select[name="format"]').value = 'whatsapp';
    document.getElementById('tplMethodField').innerHTML = '';
    document.getElementById('tplModal').classList.remove('hidden');
}

function editTemplate(id) {
    const data = templatesData[id];
    if (!data) return;
    const f = document.getElementById('tplForm');
    document.getElementById('tplModalTitle').textContent = '<?php echo e(__('templates.edit_title')); ?>';
    f.action = '/templates/' + id;
    f.querySelector('input[name="name"]').value = data.name;
    f.querySelector('textarea[name="message"]').value = data.message;
    f.querySelector('select[name="format"]').value = data.format;
    document.getElementById('tplMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('tplModal').classList.remove('hidden');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\templates\index.blade.php ENDPATH**/ ?>