<?php $__env->startSection('title', __('common.edit') . ' Button — WABot'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center gap-3 mb-5">
    <a href="<?php echo e(route('buttons.index')); ?>" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900"><?php echo e(__('common.edit')); ?> Interactive Button</h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e($button->name); ?></p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-xl" x-data="btnForm(<?php echo json_encode($button->buttons ?? [], 15, 512) ?>)">
    <form method="POST" action="<?php echo e(route('buttons.update', $button)); ?>" class="space-y-4" @submit="syncButtons">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <input type="hidden" name="buttons" x-ref="buttonsInput">
        <div>
            <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.name')); ?> Template</label>
            <input type="text" name="name" value="<?php echo e(old('name', $button->name)); ?>" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-medium text-gray-500">Tipe Header</label>
                <select name="header_type" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <?php $__currentLoopData = ['text'=>'Teks','image'=>'Gambar','video'=>'Video','document'=>'Dokumen']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($v); ?>" <?php echo e($button->header_type==$v ? 'selected':''); ?>><?php echo e($l); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500"><?php echo e(__('common.session')); ?></label>
                <select name="session_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($s->id); ?>" <?php echo e($button->session_id==$s->id ? 'selected':''); ?>><?php echo e($s->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">Header Text</label>
            <input type="text" name="header_text" value="<?php echo e(old('header_text', $button->header_text)); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">Header Media URL (opsional)</label>
            <input type="url" name="header_media_url" value="<?php echo e(old('header_media_url', $button->header_media_url)); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">Body Text</label>
            <textarea name="body_text" rows="3" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"><?php echo e(old('body_text', $button->body_text)); ?></textarea>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">Footer Text (opsional)</label>
            <input type="text" name="footer_text" value="<?php echo e(old('footer_text', $button->footer_text)); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
        </div>
        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-xs font-medium text-gray-500">Tombol</label>
                <button type="button" @click="add" class="text-xs text-brand-600 hover:underline"><i class="fas fa-plus"></i> <?php echo e(__('common.create')); ?> tombol</button>
            </div>
            <div class="space-y-2">
                <template x-for="(b, i) in list" :key="i">
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="b.text" placeholder="Teks tombol" class="flex-1 rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        <button type="button" @click="list.splice(i,1)" class="text-gray-400 hover:text-red-600"><i class="fas fa-times"></i></button>
                    </div>
                </template>
            </div>
        </div>
        <div class="flex gap-2 pt-1">
            <a href="<?php echo e(route('buttons.index')); ?>" class="flex-1 text-center bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></a>
            <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
        </div>
    </form>
</div>

<script>
function btnForm(initial) {
    return {
        list: (Array.isArray(initial) ? initial : []).map(b => ({ text: b.text || b.title || '' })),
        init() { if (this.list.length === 0) this.list.push({ text: '' }); },
        add() { this.list.push({ text: '' }); },
        syncButtons() { this.$refs.buttonsInput.value = JSON.stringify(this.list.filter(b => b.text)); }
    };
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\buttons\edit.blade.php ENDPATH**/ ?>