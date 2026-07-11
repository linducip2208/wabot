<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Builder — WABot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; height: 100vh; display: flex; flex-direction: column; }
        #topbar {
            height: 52px; min-height: 52px; display: flex; align-items: center; justify-content: space-between;
            padding: 0 16px; background: #1e293b; border-bottom: 1px solid #334155; z-index: 10;
        }
        #topbar .title { color: #fff; font-weight: 700; font-size: 14px; white-space: nowrap; }
        #topbar input { outline: none; }
        #editor-wrap { flex: 1; display: flex; overflow: hidden; }
        #sidebar {
            width: 280px; min-width: 280px; background: #1e293b; color: #cbd5e1; overflow-y: auto;
            border-right: 1px solid #334155; padding: 12px;
        }
        #sidebar h3 { font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; margin: 16px 0 8px; }
        #sidebar h3:first-child { margin-top: 0; }
        .block-btn {
            display: flex; align-items: center; gap: 8px; width: 100%; padding: 8px 10px;
            background: #334155; border: 1px solid #475569; border-radius: 8px; color: #e2e8f0;
            font-size: 12px; cursor: grab; margin-bottom: 6px; transition: all .15s;
        }
        .block-btn:hover { background: #475569; border-color: #64748b; }
        .block-btn i { width: 16px; text-align: center; color: #94a3b8; }
        #canvas-wrap { flex: 1; overflow-y: auto; background: #e2e8f0; padding: 24px; }
        #canvas {
            min-height: 100%; background: #fff; border-radius: 4px; box-shadow: 0 1px 4px rgba(0,0,0,.1);
            padding: 32px 24px; max-width: 900px; margin: 0 auto;
        }
        #canvas:empty::after { content: 'Klik blok di sidebar untuk mulai membangun ›'; color: #94a3b8; font-size: 18px; display: flex; align-items: center; justify-content: center; min-height: 200px; }
        #canvas .selected { outline: 2px dashed #3b82f6; outline-offset: 4px; }
        #props-panel {
            width: 260px; min-width: 260px; background: #f8fafc; border-left: 1px solid #e2e8f0;
            padding: 12px; overflow-y: auto; display: none;
        }
        #props-panel.active { display: block; }
        #props-panel h3 { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 8px; }
        #props-panel label { display: block; font-size: 11px; color: #475569; margin-bottom: 3px; }
        #props-panel input, #props-panel textarea, #props-panel select {
            width: 100%; padding: 6px 8px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 12px; margin-bottom: 10px;
        }
        #props-panel textarea { min-height: 60px; resize: vertical; }
        .prop-row { display: flex; gap: 4px; }
        .prop-row button {
            padding: 4px 8px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; font-size: 11px; cursor: pointer;
        }
        .prop-row button.active { background: #3b82f6; color: #fff; border-color: #3b82f6; }
        .empty-state { text-align: center; color: #94a3b8; padding: 60px 20px; }
        .empty-state i { font-size: 40px; margin-bottom: 12px; display: block; }
    </style>
</head>
<body>

<div id="topbar">
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('admin.pages.index')); ?>" class="text-gray-400 hover:text-white transition"><i class="fas fa-arrow-left"></i></a>
        <span class="title"><?php echo e(isset($page) ? __('common.edit') . ': ' . $page->title : 'New Page'); ?></span>
        <input type="text" id="pageTitle" value="<?php echo e($page->title ?? ''); ?>" placeholder="<?php echo e(__('common.title')); ?> halaman"
            class="bg-transparent border-none text-white text-sm font-medium w-56 placeholder-gray-500">
    </div>
    <div class="flex items-center gap-2">
        <input type="text" id="pageSlug" value="<?php echo e($page->slug ?? ''); ?>" placeholder="slug-url"
            class="bg-slate-700 border border-slate-600 text-white text-xs rounded-lg px-3 py-1.5 w-36 outline-none focus:border-blue-500 placeholder-gray-500">
        <?php if(isset($page)): ?>
        <a href="<?php echo e(url('/pages/' . $page->slug)); ?>" target="_blank" class="text-[11px] bg-slate-700 text-gray-300 px-3 py-1.5 rounded-lg hover:bg-slate-600 transition no-underline"><i class="fas fa-eye mr-1"></i> <?php echo e(__('common.preview')); ?></a>
        <?php endif; ?>
        <select id="viewMode" onchange="setViewport()" class="bg-slate-700 border border-slate-600 text-white text-xs rounded-lg px-2 py-1.5 outline-none">
            <option value="100%">Desktop</option>
            <option value="768px">Tablet</option>
            <option value="375px">Mobile</option>
        </select>
        <button onclick="savePage()" id="saveBtn" class="text-xs bg-blue-600 text-white px-4 py-1.5 rounded-lg font-semibold hover:bg-blue-700 transition border-none cursor-pointer">
            <i class="fas fa-save mr-1"></i> <?php echo e(__('common.save')); ?>

        </button>
    </div>
</div>

<div id="editor-wrap">
    <div id="sidebar">
        <h3><i class="fas fa-cube"></i> Layout</h3>
        <div class="block-btn" draggable="true" data-block="section"><i class="fas fa-layer-group"></i> Section</div>
        <div class="block-btn" draggable="true" data-block="hero"><i class="fas fa-image"></i> Hero</div>
        <div class="block-btn" draggable="true" data-block="cta"><i class="fas fa-rocket"></i> CTA Banner</div>
        <div class="block-btn" draggable="true" data-block="columns"><i class="fas fa-columns"></i> 2 Kolom</div>
        <div class="block-btn" draggable="true" data-block="cards"><i class="fas fa-th-large"></i> 3 Cards</div>
        <div class="block-btn" draggable="true" data-block="faq"><i class="fas fa-question-circle"></i> FAQ</div>
        
        <h3><i class="fas fa-paragraph"></i> Konten</h3>
        <div class="block-btn" draggable="true" data-block="heading"><i class="fas fa-heading"></i> Heading</div>
        <div class="block-btn" draggable="true" data-block="text"><i class="fas fa-align-left"></i> Teks</div>
        <div class="block-btn" draggable="true" data-block="image"><i class="fas fa-image"></i> Gambar</div>
        <div class="block-btn" draggable="true" data-block="button"><i class="fas fa-hand-pointer"></i> Button</div>
        <div class="block-btn" draggable="true" data-block="divider"><i class="fas fa-minus"></i> Divider</div>
        <div class="block-btn" draggable="true" data-block="spacer"><i class="fas fa-arrows-alt-v"></i> Spacer</div>
        
        <h3><i class="fas fa-tools"></i> Tools</h3>
        <button onclick="deleteSelected()" class="block-btn" style="cursor:pointer"><i class="fas fa-trash text-red-400"></i> <?php echo e(__('common.delete')); ?> Elemen</button>
        <button onclick="duplicateSelected()" class="block-btn" style="cursor:pointer"><i class="fas fa-copy text-yellow-400"></i> Duplikat</button>
        <button onclick="exportHtml()" class="block-btn" style="cursor:pointer"><i class="fas fa-code text-green-400"></i> <?php echo e(__('common.view')); ?> HTML</button>
    </div>

    <div id="canvas-wrap" 
        ondragover="event.preventDefault()"
        ondrop="handleDrop(event)">
        <div id="canvas" onclick="selectElement(event)"></div>
    </div>

    <div id="props-panel">
        <h3>Properti Elemen</h3>
        <div id="props-content"></div>
    </div>
</div>

<script>
let selectedEl = null;
let elementCounter = 0;

const BLOCKS = {
    section: `<section style="padding:60px 24px;background:#fff"><div style="max-width:800px;margin:0 auto"><h2 style="font-size:1.8rem;font-weight:800;margin-bottom:12px">Section Heading</h2><p style="color:#64748b;line-height:1.6">Konten section Anda di sini. Klik untuk mengedit teks.</p></div></section>`,
    hero: `<section style="padding:80px 24px;background:linear-gradient(135deg,#1d4ed8,#1e3a8a);color:#fff;text-align:center"><h1 style="font-size:2.8rem;font-weight:800;margin-bottom:12px">Hero Headline</h1><p style="font-size:1.15rem;opacity:.9;margin-bottom:24px;max-width:600px;margin-left:auto;margin-right:auto"><?php echo e(__('common.description')); ?> singkat tentang <?php echo e(__('common.product')); ?> atau layanan Anda.</p><a href="#" style="display:inline-block;padding:14px 36px;background:#fff;color:#1d4ed8;border-radius:12px;font-weight:700;text-decoration:none;font-size:15px">Call to Action</a></section>`,
    cta: `<div style="margin:20px;padding:48px 32px;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;text-align:center;border-radius:16px"><h2 style="font-size:1.8rem;font-weight:800;margin-bottom:8px">Siap Memulai?</h2><p style="opacity:.9;margin-bottom:20px">Bergabung dengan ribuan pengguna lainnya.</p><a href="#" style="display:inline-block;padding:13px 32px;background:#fff;color:#1d4ed8;border-radius:10px;font-weight:700;text-decoration:none">Daftar Sekarang</a></div>`,
    columns: `<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;padding:20px;max-width:900px;margin:0 auto"><div style="padding:24px;background:#f8fafc;border-radius:12px"><h3 style="font-size:1.25rem;font-weight:700;margin-bottom:8px">Kolom Kiri</h3><p style="color:#64748b">Konten kolom kiri.</p></div><div style="padding:24px;background:#f8fafc;border-radius:12px"><h3 style="font-size:1.25rem;font-weight:700;margin-bottom:8px">Kolom Kanan</h3><p style="color:#64748b">Konten kolom kanan.</p></div></div>`,
    cards: `<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;padding:20px;max-width:960px;margin:0 auto"><div style="padding:28px 20px;border:1px solid #e2e8f0;border-radius:12px;text-align:center"><div style="font-size:2.2rem;margin-bottom:10px">🚀</div><h3 style="font-weight:700;margin-bottom:6px">Fitur 1</h3><p style="color:#64748b;font-size:14px"><?php echo e(__('common.description')); ?> singkat fitur</p></div><div style="padding:28px 20px;border:1px solid #e2e8f0;border-radius:12px;text-align:center"><div style="font-size:2.2rem;margin-bottom:10px">⚡</div><h3 style="font-weight:700;margin-bottom:6px">Fitur 2</h3><p style="color:#64748b;font-size:14px"><?php echo e(__('common.description')); ?> singkat fitur</p></div><div style="padding:28px 20px;border:1px solid #e2e8f0;border-radius:12px;text-align:center"><div style="font-size:2.2rem;margin-bottom:10px">💎</div><h3 style="font-weight:700;margin-bottom:6px">Fitur 3</h3><p style="color:#64748b;font-size:14px"><?php echo e(__('common.description')); ?> singkat fitur</p></div></div>`,
    faq: `<div style="max-width:700px;margin:40px auto;padding:20px"><h2 style="text-align:center;font-size:1.8rem;font-weight:800;margin-bottom:24px">FAQ</h2><details style="border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;margin-bottom:8px"><summary style="font-weight:600;cursor:pointer;font-size:15px">Pertanyaan 1?</summary><p style="margin-top:8px;color:#64748b">Jawaban untuk pertanyaan 1.</p></details><details style="border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;margin-bottom:8px"><summary style="font-weight:600;cursor:pointer;font-size:15px">Pertanyaan 2?</summary><p style="margin-top:8px;color:#64748b">Jawaban untuk pertanyaan 2.</p></details></div>`,
    heading: `<h2 style="font-size:2rem;font-weight:800;margin-bottom:8px;padding:4px 0">Heading Baru</h2>`,
    text: `<p style="color:#475569;line-height:1.7;margin-bottom:12px">Teks paragraf Anda di sini. Klik untuk mengedit. Anda bisa menambahkan <strong>bold</strong>, <em>italic</em>, dan <a href="#" style="color:#3b82f6">link</a>.</p>`,
    image: `<img src="https://placehold.co/600x300/e2e8f0/64748b?text=Gambar" alt="Placeholder" style="max-width:100%;border-radius:8px;margin:12px 0">`,
    button: `<a href="#" style="display:inline-block;padding:12px 28px;background:#3b82f6;color:#fff;border-radius:10px;font-weight:600;text-decoration:none;font-size:14px;margin:8px 0">Klik Disini</a>`,
    divider: `<hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0">`,
    spacer: `<div style="height:40px"></div>`,
};

document.querySelectorAll('.block-btn[draggable]').forEach(btn => {
    btn.addEventListener('dragstart', e => {
        e.dataTransfer.setData('text/plain', btn.dataset.block);
    });
});

function handleDrop(e) {
    e.preventDefault();
    const blockType = e.dataTransfer.getData('text/plain');
    if (!blockType || !BLOCKS[blockType]) return;
    
    const canvas = document.getElementById('canvas');
    const wrapper = document.createElement('div');
    wrapper.className = 'builder-block';
    wrapper.dataset.blockType = blockType;
    wrapper.dataset.blockId = 'el-' + (++elementCounter);
    wrapper.innerHTML = BLOCKS[blockType];
    wrapper.style.position = 'relative';
    wrapper.style.marginBottom = '8px';
    
    wrapper.addEventListener('click', function(ev) {
        ev.stopPropagation();
        selectElementDirect(wrapper);
    });
    
    canvas.appendChild(wrapper);
    selectElementDirect(wrapper);
}

function selectElement(e) {
    if (e.target === document.getElementById('canvas')) {
        deselectAll();
        return;
    }
    const block = e.target.closest('.builder-block');
    if (block) selectElementDirect(block);
}

function selectElementDirect(block) {
    deselectAll();
    block.classList.add('selected');
    selectedEl = block;
    showProps(block);
}

function deselectAll() {
    document.querySelectorAll('.builder-block.selected').forEach(el => el.classList.remove('selected'));
    selectedEl = null;
    document.getElementById('props-panel').classList.remove('active');
}

function showProps(block) {
    const panel = document.getElementById('props-panel');
    const content = document.getElementById('props-content');
    panel.classList.add('active');
    
    const type = block.dataset.blockType;
    let html = `<p style="font-size:10px;color:#94a3b8;margin-bottom:8px">Tipe: ${type} (${block.dataset.blockId})</p>`;
    
    if (['heading','text','button','cta','hero'].includes(type)) {
        const textEl = block.querySelector('h1,h2,h3,h4,p,a,span') || block;
        html += `<label>Teks</label><textarea oninput="updateProp('text', this.value)">${textEl.textContent}</textarea>`;
    }
    
    if (type === 'image') {
        const img = block.querySelector('img');
        html += `<label>URL Gambar</label><input value="${img?.src || ''}" oninput="updateProp('src', this.value)">`;
        html += `<label>Alt Text</label><input value="${img?.alt || ''}" oninput="updateProp('alt', this.value)">`;
    }
    
    if (type === 'button' || type === 'cta' || type === 'hero') {
        const link = block.querySelector('a');
        html += `<label>URL Link</label><input value="${link?.href || '#'}" oninput="updateProp('href', this.value)">`;
    }
    
    html += `<label>Background</label><input type="color" value="#ffffff" oninput="updateProp('bg', this.value)" style="height:36px">`;
    html += `<label>Padding</label>`;
    html += `<div class="prop-row">`;
    ['8px','16px','24px','40px','64px'].forEach(v => {
        html += `<button onclick="updateProp('pad','${v}')" class="prop-btn-pad">${v}</button>`;
    });
    html += `</div>`;
    
    content.innerHTML = html;
}

function updateProp(prop, value) {
    if (!selectedEl) return;
    const type = selectedEl.dataset.blockType;
    
    if (prop === 'text') {
        const el = selectedEl.querySelector('h1,h2,h3,h4,p,a,span') || selectedEl;
        el.textContent = value;
    }
    if (prop === 'src') {
        const img = selectedEl.querySelector('img');
        if (img) img.src = value;
    }
    if (prop === 'alt') {
        const img = selectedEl.querySelector('img');
        if (img) img.alt = value;
    }
    if (prop === 'href') {
        const link = selectedEl.querySelector('a');
        if (link) link.href = value;
    }
    if (prop === 'bg') {
        selectedEl.querySelector('section,div:first-child,img')?.style && (selectedEl.querySelector('section') || selectedEl.querySelector('div:first-child') || selectedEl).style.background = value;
    }
    if (prop === 'pad') {
        const inner = selectedEl.querySelector('section,div:first-child');
        if (inner && ['section','hero','cta'].includes(type)) inner.style.padding = value;
    }
}

function deleteSelected() {
    if (!selectedEl) return alert('<?php echo e(__('common.select')); ?> elemen dulu');
    if (confirm('<?php echo e(__('common.delete')); ?> elemen ini?')) {
        selectedEl.remove();
        document.getElementById('props-panel').classList.remove('active');
        selectedEl = null;
    }
}

function duplicateSelected() {
    if (!selectedEl) return alert('<?php echo e(__('common.select')); ?> elemen dulu');
    const clone = selectedEl.cloneNode(true);
    clone.dataset.blockId = 'el-' + (++elementCounter);
    clone.classList.remove('selected');
    clone.addEventListener('click', function(ev) {
        ev.stopPropagation();
        selectElementDirect(clone);
    });
    selectedEl.after(clone);
    selectElementDirect(clone);
}

function setViewport() {
    const v = document.getElementById('viewMode').value;
    document.getElementById('canvas-wrap').style.maxWidth = v === '100%' ? 'none' : v;
    document.getElementById('canvas-wrap').style.margin = v === '100%' ? '0' : '0 auto';
}

function exportHtml() {
    const canvas = document.getElementById('canvas');
    const html = canvas.innerHTML;
    const w = window.open('', '_blank', 'width=800,height=600');
    w.document.write('<pre style="padding:16px;background:#1e293b;color:#e2e8f0;font-size:12px;white-space:pre-wrap">' + html.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</pre>');
}

async function savePage() {
    const btn = document.getElementById('saveBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';
    btn.disabled = true;

    const canvas = document.getElementById('canvas');
    const html = canvas.innerHTML;
    const title = document.getElementById('pageTitle').value || 'Untitled';
    const slug = document.getElementById('pageSlug').value || title.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/(^-|-$)/g,'');

    const url = '<?php echo e(isset($page) ? route("admin.pages.update", $page) : route("admin.pages.store")); ?>';
    const method = '<?php echo e(isset($page) ? "PUT" : "POST"); ?>';

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'X-HTTP-Method-Override': method,
            },
            body: JSON.stringify({ title, slug, content: html }),
        });
        const data = await res.json();
        if (data.ok) {
            btn.innerHTML = '<i class="fas fa-check mr-1"></i> Tersimpan';
            btn.style.background = '#059669';
            <?php if(!isset($page)): ?>
            if (data.redirect) setTimeout(() => window.location.href = data.redirect, 500);
            <?php endif; ?>
            setTimeout(() => {
                btn.innerHTML = '<i class="fas fa-save mr-1"></i> <?php echo e(__('common.save')); ?>';
                btn.style.background = '';
                btn.disabled = false;
            }, 1500);
        } else {
            alert(data.message || '<?php echo e(__('common.failed')); ?>');
            btn.innerHTML = '<i class="fas fa-save mr-1"></i> <?php echo e(__('common.save')); ?>';
            btn.disabled = false;
        }
    } catch(e) {
        alert('Error: ' + e.message);
        btn.innerHTML = '<i class="fas fa-save mr-1"></i> <?php echo e(__('common.save')); ?>';
        btn.disabled = false;
    }
}

document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); savePage(); }
    if (e.key === 'Delete' || e.key === 'Backspace') {
        if (selectedEl && document.activeElement === document.body) { e.preventDefault(); deleteSelected(); }
    }
});

// Load existing content
<?php if(isset($page) && $page->content): ?>
(function() {
    const canvas = document.getElementById('canvas');
    const content = <?php echo json_encode($page->content); ?>;
    canvas.innerHTML = content;
    
    canvas.querySelectorAll('.builder-block').forEach((block, i) => {
        if (!block.dataset.blockId) block.dataset.blockId = 'el-' + (++elementCounter);
        block.addEventListener('click', function(ev) {
            ev.stopPropagation();
            selectElementDirect(block);
        });
    });
    elementCounter = canvas.querySelectorAll('.builder-block').length;
})();
<?php endif; ?>
</script>
</body>
</html>
<?php /**PATH D:\project laravel\wabot\resources\views\admin\pages\builder.blade.php ENDPATH**/ ?>