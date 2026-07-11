<?php $__env->startSection('title', 'Flow Builder — ' . $flow->name); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('flows.index')); ?>" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 class="text-xl font-extrabold text-gray-900"><?php echo e($flow->name); ?></h1>
            <p class="text-sm text-gray-500 mt-0.5"><?php echo e(__('flows.subtitle_nodes')); ?> <span class="font-mono bg-gray-100 px-1.5 rounded"><?php echo e($flow->trigger_keyword); ?></span></p>
        </div>
    </div>
</div>

<div x-data="flowBuilder()" class="space-y-4">
    <div class="flex flex-wrap items-center gap-2 bg-white rounded-xl border border-gray-200 p-3">
        <span class="text-xs font-semibold text-gray-500 mr-1"><?php echo e(__('common.create')); ?> <?php echo e(__('flows.nodes')); ?>:</span>
        <button type="button" @click="addNode('message')" class="text-xs bg-sky-50 text-sky-700 hover:bg-sky-100 px-3 py-1.5 rounded-lg font-medium"><i class="fas fa-comment-dots mr-1"></i> <?php echo e(__('flows.node_type_message')); ?></button>
        <button type="button" @click="addNode('condition')" class="text-xs bg-amber-50 text-amber-700 hover:bg-amber-100 px-3 py-1.5 rounded-lg font-medium"><i class="fas fa-code-branch mr-1"></i> <?php echo e(__('flows.node_type_condition')); ?></button>
        <button type="button" @click="addNode('ai')" class="text-xs bg-violet-50 text-violet-700 hover:bg-violet-100 px-3 py-1.5 rounded-lg font-medium"><i class="fas fa-robot mr-1"></i> <?php echo e(__('flows.node_type_ai')); ?></button>
        <button type="button" @click="addNode('wait')" class="text-xs bg-gray-100 text-gray-700 hover:bg-gray-200 px-3 py-1.5 rounded-lg font-medium"><i class="fas fa-hourglass-half mr-1"></i> <?php echo e(__('flows.node_type_wait')); ?></button>
        <button type="button" @click="addNode('booking')" class="text-xs bg-teal-50 text-teal-700 hover:bg-teal-100 px-3 py-1.5 rounded-lg font-medium"><i class="fas fa-calendar-alt mr-1"></i> Booking</button>
    </div>

    <form method="POST" action="<?php echo e(route('flows.nodes.store', $flow)); ?>" @submit="prepareSubmit">
        <?php echo csrf_field(); ?>
        <div id="nodesPayload"></div>

        <div class="space-y-3">
            <template x-for="(node, idx) in nodes" :key="idx">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold"
                                :class="{'bg-sky-500': node.type==='message','bg-amber-500': node.type==='condition','bg-violet-500': node.type==='ai','bg-gray-500': node.type==='wait','bg-teal-500': node.type==='booking'}"
                                x-text="idx+1"></span>
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-500" x-text="typeLabel(node.type)"></span>
                            <template x-if="node.type==='message' && node.channel">
                                <span class="text-[10px] px-1.5 py-0.5 rounded-md font-medium bg-indigo-50 text-indigo-700" x-text="node.channel"></span>
                            </template>
                        </div>
                        <button type="button" @click="removeNode(idx)" class="text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </div>
                    <div class="grid gap-3">
                        <div>
                            <label class="text-xs font-medium text-gray-500"><?php echo e(__('flows.node_label')); ?></label>
                            <input type="text" x-model="node.label" required placeholder="<?php echo e(__('flows.node_label_placeholder')); ?>" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        
                        <template x-if="node.type==='message' || node.type==='ai'">
                            <div>
                                <label class="text-xs font-medium text-gray-500" x-text="node.type==='ai' ? '<?php echo e(__('flows.node_prompt_ai')); ?>' : '<?php echo e(__('flows.node_message_reply')); ?>'"></label>
                                <textarea x-model="node.reply_message" rows="2" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"></textarea>
                            </div>
                        </template>
                        <template x-if="node.type==='message'">
                            <div>
                                <label class="text-xs font-medium text-gray-500">Channel</label>
                                <select x-model="node.channel" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">Auto (detect from contact)</option>
                                    <option value="whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp / Baileys</option>
                                    <option value="meta">Meta (Cloud API)</option>
                                    <option value="instagram">Instagram</option>
                                    <option value="telegram">Telegram</option>
                                </select>
                            </div>
                        </template>
                        <template x-if="node.type==='ai'">
                            <div>
                                <label class="text-xs font-medium text-gray-500"><?php echo e(__('aiagents.ai_key')); ?></label>
                                <select x-model="node.ai_key_id" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                                    <option value=""><?php echo e(__('aiagents.select_ai_key')); ?></option>
                                    <?php $__currentLoopData = $aiKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($k->id); ?>"><?php echo e($k->name); ?> (<?php echo e($k->provider); ?>)</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </template>
                        <template x-if="node.type==='condition'">
                            <div class="grid grid-cols-3 gap-2">
                                <input type="text" x-model="node.condition_field" placeholder="<?php echo e(__('flows.node_field_placeholder')); ?>" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
                                <select x-model="node.condition_operator" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
                                    <option value="equals">=</option>
                                    <option value="contains"><?php echo e(__('flows.match_contains')); ?></option>
                                    <option value="starts_with"><?php echo e(__('flows.match_starts_with')); ?></option>
                                </select>
                                <input type="text" x-model="node.condition_value" placeholder="<?php echo e(__('flows.node_value')); ?>" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
                            </div>
                        </template>
                        <template x-if="node.type==='wait'">
                            <div>
                                <label class="text-xs font-medium text-gray-500"><?php echo e(__('flows.node_wait_seconds')); ?></label>
                                <input type="number" x-model="node.wait_seconds" min="1" placeholder="5" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                            </div>
                        </template>
                        <template x-if="node.type==='booking'">
                            <div>
                                <label class="text-xs font-medium text-gray-500"><?php echo e(__('flows.booking_service')); ?></label>
                                <select x-model="node.config.service_id" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                                    <option value=""><?php echo e(__('flows.select_service')); ?></option>
                                    <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $svc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($svc->id); ?>"><?php echo e($svc->name); ?> (<?php echo e($svc->duration_minutes); ?>m)</option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="nodes.length===0" class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <i class="fas fa-sitemap text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500"><?php echo e(__('flows.empty_nodes')); ?></p>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" x-show="nodes.length>0" class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700"><i class="fas fa-save mr-1"></i> <?php echo e(__('flows.save_flow')); ?></button>
        </div>
    </form>
</div>

<script>
function flowBuilder() {
    return {
        nodes: <?php echo json_encode($flow->nodes->map(fn($n) => [
            'id' => $n->id, 'type' => $n->type, 'label' => $n->label) ?>,
        typeLabel(t) { return {message:'<?php echo e(__('flows.node_type_message')); ?>',condition:'<?php echo e(__('flows.node_type_condition')); ?>',ai:'<?php echo e(__('flows.node_type_ai')); ?>',wait:'<?php echo e(__('flows.node_type_wait')); ?>',booking:'Booking'}[t] || t; },
        addNode(type) {
            const node = { id: null, type, label: '', reply_message: '', channel: '', ai_key_id: '', condition_field: '', condition_operator: 'equals', condition_value: '', wait_seconds: 5, config: null };
            if (type === 'booking') node.config = { service_id: '' };
            this.nodes.push(node);
        },
        removeNode(i) { this.nodes.splice(i, 1); },
        prepareSubmit() {
            const box = document.getElementById('nodesPayload'); box.innerHTML = '';
            this.nodes.forEach((n, i) => {
                const fields = { ...n, sort_order: i };
                delete fields.config;
                Object.entries(fields).forEach(([k, v]) => {
                    if (v === null || v === '') return;
                    const inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = `nodes[${i}][${k}]`; inp.value = v;
                    box.appendChild(inp);
                });
                if (n.config && typeof n.config === 'object') {
                    Object.entries(n.config).forEach(([ck, cv]) => {
                        if (cv === null || cv === '') return;
                        const inp = document.createElement('input');
                        inp.type = 'hidden'; inp.name = `nodes[${i}][config][${ck}]`; inp.value = cv;
                        box.appendChild(inp);
                    });
                }
            });
        }
    };
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\flows\nodes.blade.php ENDPATH**/ ?>