<?php $__env->startSection('title', 'Blog — Admin'); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Manajemen Blog</h1>
        <p class="text-sm text-gray-500 mt-0.5"><?php echo e($posts->count()); ?> artikel · <?php echo e($categories->count()); ?> <?php echo e(__('common.category')); ?></p>
    </div>
    <button onclick="togglePostModal()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> <?php echo e(__('common.create')); ?> Artikel
    </button>
</div>


<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3"><?php echo e(__('common.title')); ?></th>
                <th class="px-5 py-3 hidden md:table-cell"><?php echo e(__('common.category')); ?></th>
                <th class="px-5 py-3"><?php echo e(__('common.status')); ?></th>
                <th class="px-5 py-3 hidden lg:table-cell">Penulis</th>
                <th class="px-5 py-3 hidden lg:table-cell"><?php echo e(__('common.created')); ?></th>
                <th class="px-5 py-3 w-28 text-right"><?php echo e(__('common.action')); ?></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-5 py-3 font-medium text-gray-900">
                    <a href="<?php echo e(url('/blog/' . $post->slug)); ?>" target="_blank" class="hover:text-brand-600"><?php echo e($post->title); ?></a>
                </td>
                <td class="px-5 py-3 hidden md:table-cell">
                    <?php if($post->category): ?>
                    <span class="text-xs font-medium text-brand-600"><?php echo e($post->category->name); ?></span>
                    <?php else: ?>
                    <span class="text-xs text-gray-400">—</span>
                    <?php endif; ?>
                </td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full <?php echo e($post->is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500'); ?>">
                        <?php echo e($post->is_published ? __('common.published') : __('common.draft')); ?>

                    </span>
                </td>
                <td class="px-5 py-3 hidden lg:table-cell text-sm text-gray-600"><?php echo e($post->author?->name ?? '—'); ?></td>
                <td class="px-5 py-3 hidden lg:table-cell text-xs text-gray-400"><?php echo e($post->created_at->format('d M Y')); ?></td>
                <td class="px-5 py-3 text-right">
                    <a href="<?php echo e(url('/blog/' . $post->slug)); ?>" target="_blank" class="p-1.5 rounded-lg hover:bg-sky-50 text-gray-400 hover:text-sky-600 inline-block" title="<?php echo e(__('common.view')); ?>">
                        <i class="fas fa-eye text-xs"></i>
                    </a>
                    <button onclick='editPost(<?php echo e(json_encode($post->toArray())); ?>)' class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                    <form method="POST" action="<?php echo e(route('admin.blog.destroy', $post)); ?>" class="inline" onsubmit="return confirm('<?php echo e(__('common.delete')); ?> artikel ini?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="6" class="px-5 py-16 text-center text-gray-500">Belum ada artikel.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<div class="bg-white rounded-xl border border-gray-200 p-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-gray-900"><?php echo e(__('common.category')); ?></h2>
        <button onclick="toggleCatModal()" class="text-xs text-brand-600 hover:underline font-semibold">
            <i class="fas fa-plus text-[10px]"></i> <?php echo e(__('common.create')); ?> <?php echo e(__('common.category')); ?>

        </button>
    </div>
    <div class="flex flex-wrap gap-2">
        <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="flex items-center gap-1.5 bg-gray-100 rounded-lg px-3 py-1.5 text-sm">
            <span class="text-gray-700"><?php echo e($cat->name); ?></span>
            <button onclick="editCat(<?php echo e($cat->id); ?>, '<?php echo e(addslashes($cat->name)); ?>', '<?php echo e($cat->slug); ?>')" class="text-gray-400 hover:text-brand-500"><i class="fas fa-edit text-[10px]"></i></button>
            <form method="POST" action="<?php echo e(route('admin.blog.categories.update', $cat)); ?>" class="inline hidden" id="catForm<?php echo e($cat->id); ?>"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?></form>
            <form method="POST" action="<?php echo e(route('admin.blog.categories.destroy', $cat)); ?>" class="inline" onsubmit="return confirm('<?php echo e(__('common.delete')); ?> <?php echo e(__('common.category')); ?> ini?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="text-gray-400 hover:text-red-500"><i class="fas fa-times text-[10px]"></i></button>
            </form>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="text-sm text-gray-400">Belum ada <?php echo e(__('common.category')); ?>.</p>
        <?php endif; ?>
    </div>
</div>


<div id="postModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-xl shadow-xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="postModalTitle"><?php echo e(__('common.create')); ?> Artikel</h2>
        <form method="POST" action="<?php echo e(route('admin.blog.store')); ?>" class="space-y-3" id="postForm">
            <?php echo csrf_field(); ?>
            <div id="postMethodField"></div>
            <div>
                <label class="text-xs font-medium text-gray-500 block mb-1"><?php echo e(__('common.title')); ?></label>
                <input type="text" name="title" placeholder="<?php echo e(__('common.title')); ?> artikel" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 block mb-1">Slug <span class="text-gray-400">(auto-generate jika kosong)</span></label>
                <input type="text" name="slug" placeholder="slug-artikel"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 block mb-1"><?php echo e(__('common.category')); ?></label>
                <select name="category_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="">Tanpa <?php echo e(__('common.category')); ?></option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($cat->id); ?>"><?php echo e($cat->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 block mb-1">Ringkasan</label>
                <textarea name="excerpt" rows="2" placeholder="Ringkasan singkat artikel..." maxlength="500"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 block mb-1">Gambar (URL)</label>
                <input type="text" name="featured_image" placeholder="https://..."
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 block mb-1">Konten (HTML)</label>
                <textarea name="content" rows="10" placeholder="<h2>Heading</h2><p>Isi artikel...</p>" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 font-mono"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500 block mb-1">Meta Title</label>
                    <input type="text" name="meta_title" placeholder="SEO title"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 block mb-1">Meta Description</label>
                    <input type="text" name="meta_description" placeholder="SEO description"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_published" value="1" id="isPublished" class="rounded">
                <label for="isPublished" class="text-sm font-medium text-gray-700">Publikasikan</label>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="togglePostModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>


<div id="catModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="catModalTitle"><?php echo e(__('common.create')); ?> <?php echo e(__('common.category')); ?></h2>
        <form method="POST" action="<?php echo e(route('admin.blog.categories.store')); ?>" class="space-y-3" id="catForm">
            <?php echo csrf_field(); ?>
            <div id="catMethodField"></div>
            <div>
                <label class="text-xs font-medium text-gray-500 block mb-1"><?php echo e(__('common.name')); ?> <?php echo e(__('common.category')); ?></label>
                <input type="text" name="name" placeholder="<?php echo e(__('common.name')); ?> <?php echo e(__('common.category')); ?>" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 block mb-1">Slug <span class="text-gray-400">(auto-generate)</span></label>
                <input type="text" name="slug" placeholder="slug-<?php echo e(__('common.category')); ?>"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="toggleCatModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium"><?php echo e(__('common.cancel')); ?></button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><?php echo e(__('common.save')); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePostModal() {
    const m = document.getElementById('postModal');
    m.classList.toggle('hidden');
    if (!m.classList.contains('hidden')) {
        document.getElementById('postModalTitle').textContent = '<?php echo e(__('common.create')); ?> Artikel';
        const f = document.getElementById('postForm');
        f.action = '<?php echo e(route('admin.blog.store')); ?>';
        f.reset();
        document.getElementById('postMethodField').innerHTML = '';
    }
}

function editPost(data) {
    const m = document.getElementById('postModal');
    m.classList.remove('hidden');
    document.getElementById('postModalTitle').textContent = 'Edit Artikel';
    const f = document.getElementById('postForm');
    f.action = '<?php echo e(url('/admin/blog')); ?>/' + data.id;
    f.querySelector('input[name="title"]').value = data.title;
    f.querySelector('input[name="slug"]').value = data.slug;
    f.querySelector('select[name="category_id"]').value = data.category_id || '';
    f.querySelector('textarea[name="excerpt"]').value = data.excerpt || '';
    f.querySelector('input[name="featured_image"]').value = data.featured_image || '';
    f.querySelector('textarea[name="content"]').value = data.content;
    f.querySelector('input[name="meta_title"]').value = data.meta_title || '';
    f.querySelector('input[name="meta_description"]').value = data.meta_description || '';
    f.querySelector('input[name="is_published"]').checked = data.is_published;
    document.getElementById('postMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
}

function toggleCatModal() {
    const m = document.getElementById('catModal');
    m.classList.toggle('hidden');
    if (!m.classList.contains('hidden')) {
        document.getElementById('catModalTitle').textContent = '<?php echo e(__('common.create')); ?> <?php echo e(__('common.category')); ?>';
        const f = document.getElementById('catForm');
        f.action = '<?php echo e(route('admin.blog.categories.store')); ?>';
        f.reset();
        document.getElementById('catMethodField').innerHTML = '';
    }
}

function editCat(id, name, slug) {
    const m = document.getElementById('catModal');
    m.classList.remove('hidden');
    document.getElementById('catModalTitle').textContent = 'Edit <?php echo e(__('common.category')); ?>';
    const f = document.getElementById('catForm');
    f.action = '<?php echo e(url('/admin/blog/categories')); ?>/' + id;
    f.querySelector('input[name="name"]').value = name;
    f.querySelector('input[name="slug"]').value = slug;
    document.getElementById('catMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\wabot\resources\views\admin\blog\index.blade.php ENDPATH**/ ?>