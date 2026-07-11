<div x-data="languageSwitcher()" class="relative">
    <button @click="open = !open"
        class="flex items-center gap-2 px-3 py-2 text-sm text-gray-300 hover:text-white hover:bg-white/5 rounded-lg transition">
        <span class="fi fi-{{ $languages->firstWhere('iso', $currentLocale)?->flag ?? 'id' }} rounded-sm" style="width:18px;height:12px;"></span>
        <span class="hidden lg:inline">{{ strtoupper($currentLocale) }}</span>
        <i class="fas fa-chevron-down text-[9px] transition-transform" :class="open && 'rotate-180'"></i>
    </button>

    <div x-show="open" @click.outside="open = false" x-cloak
        class="absolute bottom-full right-0 mb-2 w-44 bg-sidebar-bg border border-white/10 rounded-xl shadow-xl overflow-hidden z-50">
        @foreach($languages as $lang)
        <a href="{{ route('lang.switch', $lang->iso) }}"
            class="flex items-center gap-3 px-4 py-2.5 text-sm transition hover:bg-white/5 {{ $currentLocale === $lang->iso ? 'text-brand-400 bg-white/5' : 'text-gray-300' }}">
            <span class="fi fi-{{ $lang->flag }} rounded-sm" style="width:18px;height:12px;"></span>
            <span class="flex-1">{{ $lang->native_name }}</span>
            @if($currentLocale === $lang->iso)
            <i class="fas fa-check text-brand-400 text-xs"></i>
            @endif
        </a>
        @endforeach
    </div>
</div>

<script>
function languageSwitcher() {
    return { open: false };
}
</script>
