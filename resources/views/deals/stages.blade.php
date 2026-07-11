@extends('layouts.app')
@section('title', 'Deal Stages — WABot')
@section('content')

<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('deals.index') }}" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Deal Stages</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $stages->count() }} {{ __('deals.stages_subtitle') }}</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-2">
        @forelse($stages as $stage)
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <form method="POST" action="{{ route('deal-stages.update', $stage) }}" class="flex items-center gap-3">
                @csrf @method('PUT')
                <input type="color" name="color" value="{{ $stage->color ?? '#6366f1' }}" class="w-9 h-9 rounded-lg border border-gray-200 cursor-pointer">
                <input type="text" name="name" value="{{ $stage->name }}" required class="flex-1 rounded-xl border border-gray-300 px-3 py-2 text-sm">
                <input type="number" name="sort_order" value="{{ $stage->sort_order }}" class="w-16 rounded-xl border border-gray-300 px-2 py-2 text-sm text-center">
                <span class="text-xs text-gray-400 whitespace-nowrap">{{ $stage->deals_count }} deal</span>
                <button type="submit" class="p-2 rounded-lg bg-brand-50 text-brand-600 hover:bg-brand-100"><i class="fas fa-save text-xs"></i></button>
            </form>
            <form method="POST" action="{{ route('deal-stages.destroy', $stage) }}" class="mt-2 flex justify-end" onsubmit="return confirm('{{ __('common.delete') }} stage?')">
                @csrf @method('DELETE')
                <button class="text-xs text-red-500 hover:text-red-700"><i class="fas fa-trash mr-1"></i> {{ __('common.delete') }}</button>
            </form>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <i class="fas fa-layer-group text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">{{ __('deals.no_stages_hint') }}</p>
        </div>
        @endforelse
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5 h-fit">
        <h2 class="font-bold text-gray-900 mb-3">{{ __('common.create') }} Stage</h2>
        <form method="POST" action="{{ route('deal-stages.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.name') }} Stage</label>
                <input type="text" name="name" required placeholder="Prospek" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('common.color') }}</label>
                    <input type="color" name="color" value="#6366f1" class="w-full h-10 rounded-xl border border-gray-300 cursor-pointer">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('common.order') }}</label>
                    <input type="number" name="sort_order" value="{{ ($stages->max('sort_order') ?? 0) + 1 }}" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                </div>
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><i class="fas fa-plus mr-1"></i> {{ __('common.create') }} Stage</button>
        </form>
    </div>
</div>
@endsection
