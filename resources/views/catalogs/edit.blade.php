@extends('layouts.app')
@section('title', __('catalogs.edit_title'))
@section('content')

<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('catalogs.index') }}" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('catalogs.heading_edit') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $catalog->name }}</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg">
    <form method="POST" action="{{ route('catalogs.update', $catalog) }}" class="space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="text-xs font-medium text-gray-500">{{ __('catalogs.name_label') }}</label>
            <input type="text" name="name" value="{{ old('name', $catalog->name) }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">{{ __('common.description') }}</label>
            <textarea name="description" rows="3" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">{{ old('description', $catalog->description) }}</textarea>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">{{ __('catalogs.session_label') }}</label>
            <select name="session_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                @foreach($sessions as $s)<option value="{{ $s->id }}" {{ $catalog->session_id==$s->id ? 'selected':'' }}>{{ $s->name }}</option>@endforeach
            </select>
        </div>
        <div class="flex gap-2 pt-1">
            <a href="{{ route('catalogs.items', $catalog) }}" class="flex-1 text-center bg-orange-50 text-orange-700 rounded-xl py-2.5 text-sm font-medium">{{ __('catalogs.manage_items') }}</a>
            <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
        </div>
    </form>
</div>
@endsection
