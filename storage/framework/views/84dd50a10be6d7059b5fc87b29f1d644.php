<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($seoMeta['title']); ?></title>
    <meta name="description" content="<?php echo e($seoMeta['description']); ?>">
    <meta property="og:title" content="<?php echo e($seoMeta['title']); ?>">
    <meta property="og:description" content="<?php echo e($seoMeta['description']); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo e($seoMeta['canonical']); ?>">
    <link rel="canonical" href="<?php echo e($seoMeta['canonical']); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        html { scroll-behavior: smooth; }
        .reveal { opacity: 0; transform: translateY(30px); transition: opacity .7s, transform .7s cubic-bezier(.16,1,.3,1); }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .card-lift { transition: transform .25s, box-shadow .25s; }
        .card-lift:hover { transform: translateY(-2px); box-shadow: 0 8px 24px -8px rgba(0,0,0,.12); }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
                    }
                }
            }
        }
    </script>
    <script type="application/ld+json">
    {
        "<?php $__contextArgs = [];
if (context()->has($__contextArgs[0])) :
if (isset($value)) { $__contextPrevious[] = $value; }
$value = context()->get($__contextArgs[0]); ?>": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Dokumentasi WABot — WhatsApp Marketing SaaS",
        "description": "<?php echo e($seoMeta['description']); ?>",
        "author": { "@type": "Organization", "name": "WABot" },
        "url": "<?php echo e($seoMeta['canonical']); ?>"
    }
    </script>
</head>
<body class="bg-white text-gray-900">


<nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-gray-100">
    <div class="max-w-6xl mx-auto flex items-center justify-between px-5 h-16">
        <a href="/" class="flex items-center gap-2 text-xl font-extrabold tracking-tight"><i class="fas fa-paper-plane text-brand-500"></i> WABot</a>
        <div class="flex items-center gap-4">
            <a href="/docs" class="text-sm text-brand-600 font-semibold"><?php echo e(__('nav.docs')); ?></a>
            <a href="/blog" class="text-sm text-gray-600 hover:text-brand-600 font-medium"><?php echo e(__('nav.blog')); ?></a>
            <a href="<?php echo e(route('login')); ?>" class="text-sm text-gray-600 hover:text-brand-600 font-medium"><?php echo e(__('nav.login')); ?></a>
            <a href="<?php echo e(route('register')); ?>" class="bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-700 transition"><?php echo e(__('nav.register')); ?></a>
        </div>
    </div>
</nav>


<div class="sticky top-16 z-40 bg-white border-b border-gray-100 overflow-x-auto" x-data>
    <div class="max-w-6xl mx-auto flex items-center gap-1 px-5 py-2 text-sm">
        <a href="#demo" class="px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 whitespace-nowrap font-medium"><?php echo e(__('docs.jump_demo')); ?></a>
        <a href="#menu" class="px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 whitespace-nowrap font-medium"><?php echo e(__('docs.jump_menu')); ?></a>
        <a href="#tutorial" class="px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 whitespace-nowrap font-medium"><?php echo e(__('docs.jump_tutorial')); ?></a>
        <a href="#fitur" class="px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 whitespace-nowrap font-medium"><?php echo e(__('docs.jump_features')); ?></a>
        <a href="#cta" class="px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 whitespace-nowrap font-medium"><?php echo e(__('docs.jump_start')); ?></a>
    </div>
</div>


<section class="bg-gradient-to-r from-brand-600 to-brand-800 py-16 lg:py-20">
    <div class="max-w-6xl mx-auto px-5 text-white text-center">
        <h1 class="text-3xl lg:text-4xl font-extrabold mb-3"><?php echo e(__('docs.hero_title')); ?></h1>
        <p class="text-brand-200 text-lg max-w-xl mx-auto"><?php echo e(__('docs.hero_description')); ?></p>
    </div>
</section>


<section id="demo" class="max-w-6xl mx-auto px-5 py-16">
    <h2 class="text-2xl font-extrabold mb-6 text-center"><?php echo e(__('docs.demo_section_title')); ?></h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm border border-gray-200 rounded-xl overflow-hidden">
            <thead>
                <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-5 py-3"><?php echo e(__('common.role')); ?></th>
                    <th class="px-5 py-3">Email</th>
                    <th class="px-5 py-3"><?php echo e(__('common.password')); ?></th>
                    <th class="px-5 py-3"><?php echo e(__('docs.scope')); ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $__currentLoopData = $demoAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-5 py-3 font-semibold text-gray-900"><?php echo e($acc['role']); ?></td>
                    <td class="px-5 py-3 font-mono text-sm text-brand-600"><?php echo e($acc['email']); ?></td>
                    <td class="px-5 py-3 font-mono text-sm text-gray-600"><?php echo e($acc['password']); ?></td>
                    <td class="px-5 py-3 text-sm text-gray-500"><?php echo e($acc['scope']); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <div class="mt-4 text-center">
        <a href="<?php echo e(route('login')); ?>" class="inline-flex items-center gap-2 text-brand-600 hover:underline text-sm font-semibold">
            <i class="fas fa-sign-in-alt"></i> <?php echo e(__('docs.login_demo')); ?>

        </a>
    </div>
</section>


<section id="menu" class="bg-gray-50 py-16">
    <div class="max-w-6xl mx-auto px-5">
        <h2 class="text-2xl font-extrabold mb-8 text-center"><?php echo e(__('docs.menu_structure_admin')); ?></h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php $__currentLoopData = $menuGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-brand-100 flex items-center justify-center">
                        <i class="fas <?php echo e($group['icon']); ?> text-brand-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900"><?php echo e($group['title']); ?></h3>
                </div>
                <ul class="space-y-2">
                    <?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex items-start gap-2 text-sm">
                        <i class="fas <?php echo e($item['icon']); ?> text-gray-400 mt-0.5 text-xs w-4 text-center"></i>
                        <div>
                            <span class="font-medium text-gray-800"><?php echo e($item['label']); ?></span>
                            <p class="text-xs text-gray-400 mt-0.5"><?php echo e($item['desc']); ?></p>
                        </div>
                    </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>


<section id="tutorial" class="max-w-5xl mx-auto px-5 py-16">
    <h2 class="text-2xl font-extrabold mb-8 text-center"><?php echo e(__('docs.tutorial_section_title')); ?></h2>
    <div class="space-y-8">
        <?php $__currentLoopData = $tutorialPhases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $phase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="reveal bg-white border border-gray-200 rounded-2xl p-6 card-lift">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-brand-600 flex items-center justify-center">
                    <i class="fas <?php echo e($phase['icon']); ?> text-white text-sm"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900"><?php echo e($phase['phase']); ?></h3>
            </div>
            <ol class="space-y-2">
                <?php $__currentLoopData = $phase['steps']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex items-start gap-3 text-sm text-gray-600">
                    <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5"><?php echo e($i + 1); ?></span>
                    <span><?php echo e($step); ?></span>
                </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ol>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>


<section id="fitur" class="bg-gray-50 py-16">
    <div class="max-w-6xl mx-auto px-5">
        <h2 class="text-2xl font-extrabold mb-8 text-center"><?php echo e(__('docs.features_section_title')); ?></h2>
        <div class="grid md:grid-cols-2 gap-6">
            <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $featureGroup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-9 h-9 rounded-lg bg-brand-100 flex items-center justify-center">
                        <i class="fas <?php echo e($featureGroup['icon']); ?> text-brand-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-lg text-gray-900"><?php echo e($featureGroup['group']); ?></h3>
                </div>
                <div class="space-y-3">
                    <?php $__currentLoopData = $featureGroup['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white border border-gray-200 rounded-xl p-4 card-lift">
                        <h4 class="font-semibold text-gray-900 text-sm mb-1"><?php echo e($item['title']); ?></h4>
                        <p class="text-sm text-gray-500 leading-relaxed"><?php echo e($item['desc']); ?></p>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>


<section id="cta" class="bg-gradient-to-r from-brand-600 to-brand-800 py-16">
    <div class="max-w-xl mx-auto text-center px-5 text-white">
        <h2 class="text-2xl font-extrabold mb-3"><?php echo e(__('docs.cta_heading')); ?></h2>
        <p class="text-brand-200 mb-6"><?php echo e(__('docs.cta_subtitle')); ?></p>
        <div class="flex items-center justify-center gap-3">
            <a href="<?php echo e(route('register')); ?>" class="bg-white text-brand-700 px-6 py-3 rounded-xl font-bold hover:shadow-xl transition"><?php echo e(__('nav.register')); ?></a>
            <a href="<?php echo e(route('login')); ?>" class="bg-brand-500/20 text-white px-6 py-3 rounded-xl font-semibold hover:bg-brand-500/30 transition"><?php echo e(__('nav.login')); ?></a>
        </div>
    </div>
</section>


<footer class="bg-gray-900 text-gray-400 py-10 text-sm">
    <div class="max-w-6xl mx-auto px-5 flex flex-col md:flex-row justify-between gap-6">
        <div><span class="text-white font-bold text-lg">WABot</span><p class="mt-1"><?php echo e(__('app.tagline')); ?></p></div>
        <div class="flex gap-6">
            <a href="/docs" class="hover:text-white"><?php echo e(__('nav.docs')); ?></a>
            <a href="/blog" class="hover:text-white"><?php echo e(__('nav.blog')); ?></a>
            <a href="<?php echo e(route('login')); ?>" class="hover:text-white"><?php echo e(__('footer.login')); ?></a>
        </div>
    </div>
</footer>

<script>
const observer = new IntersectionObserver(entries => entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible') }), { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
</body>
</html>
<?php /**PATH D:\project laravel\wabot\resources\views\docs\index.blade.php ENDPATH**/ ?>