<?php $__env->startSection('title', __('forms.title') . ' — WABot'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto" x-data="formBuilder()">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900"><?php echo e(__('forms.title')); ?></h1>
            <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('forms.subtitle')); ?></p>
        </div>
        <button @click="showBuilder = true"
            class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> <?php echo e(__('forms.create_button')); ?>

        </button>
    </div>

    <?php if($forms->isEmpty()): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
            <div class="w-14 h-14 mx-auto bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-wpforms text-indigo-500 text-xl"></i>
            </div>
            <h3 class="text-gray-500 font-medium mb-1"><?php echo e(__('forms.empty_title')); ?></h3>
            <p class="text-sm text-gray-400 mb-4"><?php echo e(__('forms.empty_desc')); ?></p>
            <button @click="showBuilder = true"
                class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
                <?php echo e(__('forms.create_first')); ?>

            </button>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <?php $__currentLoopData = $forms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $form): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-xl border border-gray-200 p-5 hover:border-brand-300 hover:shadow-sm transition">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900"><?php echo e($form->name); ?></h3>
                            <p class="text-xs text-gray-400 mt-0.5"><?php echo e($form->metaAccount?->name ?? __('forms.no_meta_account')); ?></p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            <?php echo e($form->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'); ?>">
                            <?php echo e($form->status === 'active' ? __('common.active') : __('common.draft')); ?>

                        </span>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-gray-400 mb-3">
                        <span><i class="fas fa-cube mr-1"></i> <?php echo e(__('forms.fields_count', ['count' => count($form->components ?? [])])); ?></span>
                        <span><i class="fas fa-envelope mr-1"></i> <?php echo e(__('forms.submission_count', ['count' => $form->submission_count])); ?></span>
                    </div>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <a href="<?php echo e(route('forms.submissions', $form)); ?>"
                            class="px-3 py-1.5 rounded-lg text-[11px] font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                            <i class="fas fa-list mr-1"></i>Submissions
                        </a>
                        <button onclick="openSendModal(<?php echo e($form->id); ?>)"
                            class="px-3 py-1.5 rounded-lg text-[11px] font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                            <i class="fas fa-paper-plane mr-1"></i><?php echo e(__('common.send')); ?>

                        </button>
                        <form action="<?php echo e(route('forms.destroy', $form)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('common.delete')); ?>?')" class="inline">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    
    <div x-show="showBuilder" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" x-cloak
        @click.self="showBuilder = false">
        <div class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-xl max-h-[90vh] overflow-y-auto" @click.stop="">
            <h2 class="text-lg font-bold text-gray-900 mb-4"><?php echo e(__('forms.builder_title')); ?></h2>
            <form :action="editingId ? '/forms/' + editingId : '<?php echo e(route('forms.store')); ?>'" method="POST" class="space-y-3">
                <?php echo csrf_field(); ?>
                <template x-if="editingId">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.name')); ?></label>
                        <input type="text" name="name" x-model="formName" required
                            class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('forms.meta_account')); ?></label>
                        <select name="meta_account_id" x-model="formMetaId"
                            class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            <option value="">-- <?php echo e(__('common.select')); ?> --</option>
                            <?php $__currentLoopData = \App\Models\WaMetaAccount::where('user_id', Auth::id())->where('status','connected')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($acc->id); ?>"><?php echo e($acc->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Header <span class="text-gray-400">(max 60)</span></label>
                        <input type="text" name="header_text" x-model="headerText" maxlength="60"
                            class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Body <span class="text-gray-400">(max 1024)</span></label>
                        <input type="text" name="body_text" x-model="bodyText" maxlength="1024"
                            class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-gray-500"><?php echo e(__('forms.fields')); ?></span>
                        <button type="button" @click="addField()"
                            class="px-3 py-1 rounded-lg text-[11px] font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                            <i class="fas fa-plus mr-1"></i><?php echo e(__('forms.create_field')); ?>

                        </button>
                    </div>
                    <div class="space-y-1.5 max-h-60 overflow-y-auto rounded-xl border border-gray-200 p-3 bg-gray-50">
                        <template x-for="(field, i) in fields" :key="i">
                            <div class="flex items-center gap-2 p-2 bg-white rounded-lg border border-gray-200 text-xs">
                                <span class="text-gray-400 w-5 text-center" x-text="i+1"></span>
                                <select x-model="field.type" class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs w-28">
                                    <option value="text_input">Text Input</option>
                                    <option value="text_area">Text Area</option>
                                    <option value="number">Number</option>
                                    <option value="email">Email</option>
                                    <option value="phone_number">Phone</option>
                                    <option value="dropdown">Dropdown</option>
                                    <option value="radio">Radio</option>
                                    <option value="checkbox">Checkbox</option>
                                    <option value="date_picker">Date</option>
                                </select>
                                <input type="text" x-model="field.label" placeholder="<?php echo e(__('forms.label')); ?>" class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs flex-1">
                                <input type="text" x-model="field.placeholder" placeholder="<?php echo e(__('forms.placeholder')); ?>" class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs w-24"
                                    x-show="['text_input','text_area','number','email','phone_number'].includes(field.type)">
                                <input type="text" x-model="field.optionsStr" placeholder="A, B, C"
                                    class="border border-gray-200 rounded-lg px-2 py-1.5 text-xs w-20"
                                    x-show="['dropdown','radio','checkbox'].includes(field.type)">
                                <label class="flex items-center gap-1 text-gray-500 whitespace-nowrap">
                                    <input type="checkbox" x-model="field.required"> <?php echo e(__('forms.required')); ?>

                                </label>
                                <button type="button" @click="fields.splice(i, 1)" class="text-red-400 hover:text-red-600 ml-auto">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>
                        <div x-show="fields.length === 0" class="text-center text-gray-400 text-sm py-4">
                            <?php echo e(__('forms.click_to_add')); ?>

                        </div>
                    </div>
                </div>

                <input type="hidden" name="components" :value="JSON.stringify(fields.map(f => ({...f, options: f.optionsStr ? f.optionsStr.split(',').map(s => s.trim()) : []})))">

                <div class="flex gap-2 pt-1">
                    <button type="button" @click="showBuilder = false; resetForm()"
                        class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                    <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700 transition">
                        <span x-text="editingId ? '<?php echo e(__('forms.update')); ?>' : '<?php echo e(__('common.save')); ?>'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php $__currentLoopData = $forms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $form): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div id="sendModal<?php echo e($form->id); ?>" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><?php echo e(__('common.send')); ?>: <?php echo e($form->name); ?></h2>
        <form action="<?php echo e(route('forms.send', $form)); ?>" method="POST" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('forms.meta_account')); ?></label>
                <select name="meta_account_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <?php $__currentLoopData = \App\Models\WaMetaAccount::where('user_id', Auth::id())->where('status','connected')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($acc->id); ?>" <?php echo e($form->meta_account_id == $acc->id ? 'selected' : ''); ?>><?php echo e($acc->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('forms.target_number')); ?></label>
                <input type="text" name="phone" placeholder="62812xxxx" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('sendModal<?php echo e($form->id); ?>').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.send')); ?></button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function formBuilder() {
    return {
        showBuilder: false, editingId: null,
        formName: '', formMetaId: '', headerText: '', bodyText: '', fields: [],
        addField() { this.fields.push({ type: 'text_input', label: '', placeholder: '', required: false, options: [], optionsStr: '' }); },
        resetForm() { this.editingId = null; this.formName = ''; this.formMetaId = ''; this.headerText = ''; this.bodyText = ''; this.fields = []; }
    };
}
function openSendModal(id) { document.getElementById('sendModal' + id).classList.remove('hidden'); }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\forms\index.blade.php ENDPATH**/ ?>