<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('login.title') }} — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.3.2/css/flag-icons.min.css">
    <style>body{font-family:'Inter',sans-serif}</style>
    <script>tailwind.config={theme:{extend:{colors:{brand:{50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a'}}}}}</script>
</head>
<body class="bg-gray-50 min-h-screen">

<div class="min-h-screen grid lg:grid-cols-2">
    {{-- Left: Brand Panel --}}
    <div class="hidden lg:flex relative bg-gradient-to-br from-brand-600 via-brand-800 to-gray-900 p-10 flex-col justify-between overflow-hidden">
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 30%, #60a5fa 0%, transparent 50%), radial-gradient(circle at 80% 70%, #3b82f6 0%, transparent 50%)"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-brand-500/10 blur-3xl"></div>

        <div class="relative">
            <a href="/" class="flex items-center gap-2.5 text-white">
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center"><i class="fas fa-paper-plane text-white text-lg"></i></div>
                <span class="font-extrabold text-2xl tracking-tight">{{ config('app.name') }}</span>
            </a>
        </div>

        <div class="relative text-white">
            <h2 class="text-5xl font-extrabold leading-tight mb-4">{{ __('login.hero_tagline') }}</h2>
            <p class="text-brand-200 text-lg leading-relaxed mb-8 max-w-md">{{ __('hero.subtitle') }}</p>
            <div class="grid grid-cols-3 gap-3 max-w-md">
                <div class="bg-white/10 backdrop-blur rounded-xl p-3 text-center">
                    <i class="fas fa-robot text-2xl mb-1 block"></i>
                    <span class="text-xs font-medium">{{ __('login.hero_benefit1') }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl p-3 text-center">
                    <i class="fas fa-bullhorn text-2xl mb-1 block"></i>
                    <span class="text-xs font-medium">{{ __('login.hero_benefit2') }}</span>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl p-3 text-center">
                    <i class="fas fa-comments text-2xl mb-1 block"></i>
                    <span class="text-xs font-medium">{{ __('login.hero_benefit3') }}</span>
                </div>
            </div>
        </div>

        <div class="relative text-brand-300/60 text-xs">&copy; {{ date('Y') }} {{ config('app.name') }}</div>
    </div>

    {{-- Right: Form --}}
    <div class="flex items-center justify-center p-6 lg:p-16">
        <div class="w-full max-w-md">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-4xl font-extrabold text-gray-900">{{ __('login.title') }}</h1>
                <div class="flex items-center gap-2">
                    @include('components.language-switcher', [
                        'languages' => \App\Models\Language::active()->ordered()->get(),
                        'currentLocale' => app()->getLocale(),
                        'position' => 'top',
                    ])
                </div>
            </div>
            <p class="text-gray-500 mb-8">{{ __('login.no_account') }} <a href="{{ route('register') }}" class="text-brand-600 font-semibold hover:underline">{{ __('login.register_link') }}</a></p>

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-medium text-gray-600">{{ __('login.email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-600">{{ __('login.password') }}</label>
                    <input type="password" name="password" required
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-brand-600 to-brand-700 text-white rounded-xl py-3 font-semibold text-sm hover:shadow-lg hover:shadow-brand-500/25 transition-all">
                    {{ __('login.submit') }} <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </form>

            <div class="mt-6 bg-gray-50 border border-gray-200 rounded-xl p-4">
                <div class="text-xs font-semibold text-gray-700 mb-2 flex items-center gap-1.5"><i class="fas fa-key text-brand-500"></i> {{ __('login.demo_title') }}</div>
                <div class="space-y-1 text-xs font-mono text-gray-600">
                    <div><span class="font-bold">{{ __('login.demo_admin') }}:</span> admin@wabot.test / password</div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
