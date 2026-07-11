<?php $__env->startSection('title', __('sendgrid.title') . ' — WABot'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.email-preview { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; min-height: 200px; }
.email-preview h1, .email-preview h2, .email-preview h3 { color: #111827; }
.email-preview p { color: #4b5563; line-height: 1.6; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto">
    
    <div class="flex gap-1 bg-gray-100 rounded-xl p-1 mb-5 w-fit" x-data="{ tab: 'accounts' }">
        <button @click="tab = 'accounts'" :class="tab === 'accounts' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
            class="px-4 py-2 rounded-lg text-sm font-medium transition">
            <i class="fas fa-envelope mr-1.5"></i><?php echo e(__('sendgrid.accounts')); ?>

        </button>
        <button @click="tab = 'templates'" :class="tab === 'templates' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
            class="px-4 py-2 rounded-lg text-sm font-medium transition">
            <i class="fas fa-file-code mr-1.5"></i><?php echo e(__('sendgrid.email_templates')); ?>

        </button>
    </div>

    
    <div x-show="tab === 'accounts'">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900"><?php echo e(__('sendgrid.title')); ?></h1>
                <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('sendgrid.subtitle')); ?></p>
            </div>
            <button onclick="document.getElementById('addAccountModal').classList.remove('hidden')"
                class="bg-blue-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition flex items-center gap-2">
                <i class="fas fa-plus text-xs"></i> <?php echo e(__('sendgrid.create_account')); ?>

            </button>
        </div>

        <?php if($accounts->isEmpty()): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
                <div class="w-14 h-14 mx-auto bg-blue-50 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-envelope text-blue-500 text-xl"></i>
                </div>
                <h3 class="text-gray-500 font-medium mb-1"><?php echo e(__('sendgrid.empty_title')); ?></h3>
                <p class="text-sm text-gray-400 mb-4"><?php echo e(__('sendgrid.empty_desc')); ?></p>
                <button onclick="document.getElementById('addAccountModal').classList.remove('hidden')"
                    class="bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
                    <?php echo e(__('sendgrid.create_account')); ?>

                </button>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift">
                        <div class="flex items-start justify-between flex-wrap gap-4">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-envelope text-white text-sm"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="font-semibold text-gray-900"><?php echo e($acc->name); ?></h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            <?php echo e($acc->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'); ?>">
                                            <?php echo e($acc->is_active ? __('common.connected') : __('common.disconnected')); ?>

                                        </span>
                                    </div>
                                    <?php if($acc->from_email): ?>
                                        <p class="text-xs text-gray-400 mt-0.5"><?php echo e($acc->from_name ? $acc->from_name . ' <' . $acc->from_email . '>' : $acc->from_email); ?></p>
                                    <?php endif; ?>
                                    <?php if($acc->connected_at): ?>
                                        <p class="text-xs text-gray-400"><?php echo e(__('common.created')); ?>: <?php echo e($acc->connected_at->format('d M Y H:i')); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <?php if(!$acc->is_active): ?>
                                    <form action="<?php echo e(route('sendgrid.connect', $acc)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button class="px-3.5 py-2 rounded-xl text-xs font-medium bg-blue-600 text-white hover:bg-blue-700 transition">
                                            <i class="fas fa-envelope mr-1"></i><?php echo e(__('common.connect')); ?>

                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?php echo e(route('sendgrid.disconnect', $acc)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button class="px-3.5 py-2 rounded-xl text-xs font-medium border border-red-200 text-red-600 hover:bg-red-50 transition">
                                            <?php echo e(__('common.disconnect')); ?>

                                        </button>
                                    </form>
                                <?php endif; ?>
                                <button onclick="openEmailTest(<?php echo e($acc->id); ?>)"
                                    class="px-3 py-2 rounded-xl text-xs font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                                    <i class="fas fa-paper-plane mr-1"></i><?php echo e(__('common.test')); ?>

                                </button>
                                <button onclick="openEditAccount(<?php echo e($acc->id); ?>, '<?php echo e(e($acc->name)); ?>', '<?php echo e(e($acc->from_email)); ?>', '<?php echo e(e($acc->from_name)); ?>')"
                                    class="px-3 py-2 rounded-xl text-xs font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                                    <i class="fas fa-edit mr-1"></i><?php echo e(__('common.edit')); ?>

                                </button>
                                <form action="<?php echo e(route('sendgrid.destroy', $acc)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('common.delete')); ?>?')" class="inline">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <div class="mt-4 bg-blue-50 rounded-xl border border-blue-100 p-5">
            <div class="flex items-start gap-4">
                <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-info text-blue-600 text-sm"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 text-sm mb-1"><?php echo e(__('sendgrid.setup_title')); ?></h3>
                    <p class="text-xs text-gray-500"><?php echo __('sendgrid.setup_desc'); ?></p>
                </div>
            </div>
        </div>
    </div>

    
    <div x-show="tab === 'templates'" x-cloak>
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-900"><?php echo e(__('sendgrid.email_templates')); ?></h2>
                <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('sendgrid.templates_subtitle')); ?></p>
            </div>
            <button onclick="document.getElementById('templateModal').classList.remove('hidden')"
                class="bg-blue-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition flex items-center gap-2">
                <i class="fas fa-plus text-xs"></i> <?php echo e(__('sendgrid.create_template')); ?>

            </button>
        </div>

        <?php if($templates->isEmpty()): ?>
            <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
                <div class="w-14 h-14 mx-auto bg-blue-50 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-file-code text-blue-500 text-xl"></i>
                </div>
                <h3 class="text-gray-500 font-medium mb-1"><?php echo e(__('sendgrid.no_templates')); ?></h3>
                <p class="text-sm text-gray-400 mb-4"><?php echo e(__('sendgrid.no_templates_desc')); ?></p>
                <button onclick="document.getElementById('templateModal').classList.remove('hidden')"
                    class="bg-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
                    <?php echo e(__('sendgrid.create_template')); ?>

                </button>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift">
                        <div class="flex items-start justify-between flex-wrap gap-3">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-file-code text-indigo-600 text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-semibold text-gray-900 truncate"><?php echo e($tpl->name); ?></h4>
                                    <p class="text-xs text-gray-500 mt-0.5"><?php echo e(__('sendgrid.subject')); ?>: <?php echo e($tpl->subject); ?></p>
                                    <?php if($tpl->variables): ?>
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            <?php $__currentLoopData = $tpl->variables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="inline-flex text-[10px] bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full font-mono"><?php echo e('{'.$v.'}'); ?></span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <button onclick="previewTemplate('<?php echo e(e($tpl->name)); ?>', '<?php echo e(e($tpl->subject)); ?>', `<?php echo e(e($tpl->body_html)); ?>`)"
                                    class="px-3 py-2 rounded-xl text-xs font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                    <i class="fas fa-eye mr-1"></i><?php echo e(__('common.preview')); ?>

                                </button>
                                <button onclick="editTemplate(<?php echo e($tpl->id); ?>, '<?php echo e(e($tpl->name)); ?>', '<?php echo e(e($tpl->subject)); ?>', `<?php echo e(e($tpl->body_html)); ?>`, '<?php echo e(e(json_encode($tpl->variables ?? []))); ?>')"
                                    class="px-3 py-2 rounded-xl text-xs font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                                    <i class="fas fa-edit mr-1"></i><?php echo e(__('common.edit')); ?>

                                </button>
                                <form action="<?php echo e(route('sendgrid.template.destroy', $tpl)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('common.delete')); ?>?')" class="inline">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>


<div id="addAccountModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><?php echo e(__('sendgrid.create_account')); ?></h2>
        <form action="<?php echo e(route('sendgrid.store')); ?>" method="POST" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.name')); ?></label>
                <input type="text" name="name" placeholder="SendGrid Account" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('sendgrid.api_key')); ?></label>
                <input type="text" name="api_key" placeholder="SG.xxxxx" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('sendgrid.from_email')); ?></label>
                    <input type="email" name="from_email" placeholder="noreply@example.com"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('sendgrid.from_name')); ?></label>
                    <input type="text" name="from_name" placeholder="Company Name"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('addAccountModal').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-blue-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-blue-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>


<div id="editAccountModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><?php echo e(__('sendgrid.edit_account')); ?></h2>
        <form id="editAccountForm" method="POST" class="space-y-3">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.name')); ?></label>
                <input type="text" name="name" id="editAccountName" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('sendgrid.api_key')); ?> <span class="text-gray-400">(<?php echo e(__('sendgrid.leave_blank')); ?>)</span></label>
                <input type="text" name="api_key" placeholder="Leave blank to keep unchanged"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('sendgrid.from_email')); ?></label>
                    <input type="email" name="from_email" id="editFromEmail"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('sendgrid.from_name')); ?></label>
                    <input type="text" name="from_name" id="editFromName"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('editAccountModal').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-blue-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-blue-700"><?php echo e(__('common.update')); ?></button>
            </div>
        </form>
    </div>
</div>


<?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div id="emailTest<?php echo e($acc->id); ?>" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><?php echo e(__('common.test')); ?> Email: <?php echo e($acc->name); ?></h2>
        <form action="<?php echo e(route('sendgrid.test', $acc)); ?>" method="POST" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('sendgrid.to_email')); ?></label>
                <input type="email" name="to" placeholder="recipient@example.com" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('sendgrid.subject')); ?></label>
                <input type="text" name="subject" placeholder="Test Subject" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('sendgrid.body')); ?></label>
                <textarea name="body" rows="4" placeholder="<h1>Hello</h1><p>Test email from WABot</p>" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('emailTest<?php echo e($acc->id); ?>').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-blue-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-blue-700"><?php echo e(__('common.send')); ?></button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


<div id="templateModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-4" id="templateModalTitle"><?php echo e(__('sendgrid.create_template')); ?></h2>
        <form id="templateForm" action="<?php echo e(route('sendgrid.template.store')); ?>" method="POST" class="space-y-3">
            <?php echo csrf_field(); ?>
            <div id="templateMethod"></div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.name')); ?></label>
                    <input type="text" name="name" id="tplName" placeholder="Welcome Email" required
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('sendgrid.subject')); ?></label>
                    <input type="text" name="subject" id="tplSubject" placeholder="Welcome to <?php echo e('{company}'); ?>" required
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('sendgrid.body_html')); ?></label>
                <textarea name="body_html" id="tplBody" rows="12" placeholder="<div style='font-family:sans-serif;padding:20px'><h1>Hello {name}</h1><p>{message}</p></div>" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('sendgrid.variables')); ?> <span class="text-gray-400">(JSON array)</span></label>
                <input type="text" name="variables" id="tplVariables" placeholder='["name","email","company"]'
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1"><?php echo e(__('common.preview')); ?> (<span class="text-gray-400"><?php echo e(__('sendgrid.test_data_placeholder')); ?></span>)</label>
                <div class="email-preview" id="emailPreview"></div>
                <p class="text-[10px] text-gray-400 mt-1"><?php echo e(__('sendgrid.preview_hint')); ?></p>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('templateModal').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-blue-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-blue-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>


<div id="previewModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4"
    onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold text-gray-900 mb-2" id="previewTitle"></h2>
        <p class="text-sm text-gray-500 mb-4" id="previewSubject"></p>
        <div class="email-preview" id="previewContent"></div>
        <button onclick="document.getElementById('previewModal').classList.add('hidden')"
            class="mt-4 w-full bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium hover:bg-gray-200 transition">
            <?php echo e(__('common.close')); ?>

        </button>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openEmailTest(id) { document.getElementById('emailTest'+id).classList.remove('hidden'); }

function openEditAccount(id, name, fromEmail, fromName) {
    document.getElementById('editAccountForm').action = '/sendgrid/' + id;
    document.getElementById('editAccountName').value = name;
    document.getElementById('editFromEmail').value = fromEmail || '';
    document.getElementById('editFromName').value = fromName || '';
    document.getElementById('editAccountModal').classList.remove('hidden');
}

function previewTemplate(name, subject, body) {
    document.getElementById('previewTitle').textContent = name;
    document.getElementById('previewSubject').textContent = 'Subject: ' + subject;
    document.getElementById('previewContent').innerHTML = body;
    document.getElementById('previewModal').classList.remove('hidden');
}

function editTemplate(id, name, subject, body, variables) {
    document.getElementById('templateForm').action = '/sendgrid/template/' + id;
    document.getElementById('templateModalTitle').textContent = 'Edit Template';
    document.getElementById('templateMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('tplName').value = name;
    document.getElementById('tplSubject').value = subject;
    document.getElementById('tplBody').value = body;
    document.getElementById('tplVariables').value = variables || '';
    updatePreview();
    document.getElementById('templateModal').classList.remove('hidden');
}

function updatePreview() {
    var html = document.getElementById('tplBody').value;
    try {
        var vars = JSON.parse(document.getElementById('tplVariables').value || '[]');
        vars.forEach(function(v) {
            html = html.replace(new RegExp('\\{'+v+'\\}', 'g'), '<mark style="background:#c7d2fe;padding:1px 4px;border-radius:3px">'+v+'</mark>');
        });
    } catch(e) {}
    document.getElementById('emailPreview').innerHTML = html;
}
document.getElementById('tplBody').addEventListener('input', updatePreview);
document.getElementById('tplVariables').addEventListener('input', updatePreview);

// Reset create mode when opening template modal via create button
var createBtn = document.querySelector('[onclick*="templateModal"]');
if (createBtn) {
    var origClick = createBtn.onclick;
    createBtn.onclick = function() {
        document.getElementById('templateForm').action = '<?php echo e(route('sendgrid.template.store')); ?>';
        document.getElementById('templateModalTitle').textContent = '<?php echo e(__('sendgrid.create_template')); ?>';
        document.getElementById('templateMethod').innerHTML = '';
        document.getElementById('tplName').value = '';
        document.getElementById('tplSubject').value = '';
        document.getElementById('tplBody').value = '';
        document.getElementById('tplVariables').value = '';
        document.getElementById('emailPreview').innerHTML = '';
        document.getElementById('templateModal').classList.remove('hidden');
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\sendgrid\index.blade.php ENDPATH**/ ?>