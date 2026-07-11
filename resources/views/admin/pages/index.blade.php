@extends('layouts.app')
@section('title', 'CMS Pages — Admin')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Halaman CMS</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola halaman konten publik dengan visual builder</p>
    </div>
    <a href="{{ route('admin.pages.builder') }}"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> {{ __('common.create') }} Halaman
    </a>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">{{ __('common.title') }}</th>
                <th class="px-5 py-3">Slug</th>
                <th class="px-5 py-3 hidden md:table-cell">{{ __('common.status') }}</th>
                <th class="px-5 py-3 hidden lg:table-cell">{{ __('common.created') }}</th>
                <th class="px-5 py-3 w-36 text-right">{{ __('common.action') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($pages as $p)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-5 py-3 font-medium text-gray-900">{{ $p->title }}</td>
                <td class="px-5 py-3">
                    <a href="{{ url('/pages/' . $p->slug) }}" target="_blank" class="text-brand-600 hover:underline text-xs font-mono">
                        /{{ $p->slug }}
                        <i class="fas fa-external-link-alt text-[10px] ml-1"></i>
                    </a>
                </td>
                <td class="px-5 py-3 hidden md:table-cell">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $p->is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $p->is_published ? __('common.published') : __('common.draft') }}
                    </span>
                </td>
                <td class="px-5 py-3 hidden lg:table-cell text-xs text-gray-400">{{ $p->created_at->format('d M Y') }}</td>
                <td class="px-5 py-3 text-right">
                    <a href="{{ url('/pages/' . $p->slug) }}" target="_blank" class="p-1.5 rounded-lg hover:bg-sky-50 text-gray-400 hover:text-sky-600 inline-block" title="{{ __('common.view') }}">
                        <i class="fas fa-eye text-xs"></i>
                    </a>
                    <a href="{{ route('admin.pages.builder.edit', $p) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600 inline-block" title="{{ __('common.edit') }} Builder">
                        <i class="fas fa-edit text-xs"></i>
                    </a>
                    <form method="POST" action="{{ route('admin.pages.destroy', $p) }}" class="inline" onsubmit="return confirm('{{ __('common.delete') }} halaman ini?')">
                        @csrf @method('DELETE')
                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-16 text-center text-gray-500">
                Belum ada halaman CMS.
                <a href="{{ route('admin.pages.builder') }}" class="text-brand-600 hover:underline ml-1">Buat halaman pertama</a>
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Quick create modal --}}
<div id="quickModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">Halaman Baru</h2>
        <form method="POST" action="{{ route('admin.pages.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.title') }}</label>
                <input type="text" name="title" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Slug</label>
                <input type="text" name="slug" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <input type="hidden" name="content" value="<h1>{{ __('common.title') }}</h1><p>Konten halaman...</p>">
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('quickModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">Buat</button>
            </div>
        </form>
    </div>
</div>

@endsection
