@extends('layouts.app')
@section('title', __('abtests.edit_title'))
@section('content')

<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('ab-tests.index') }}" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('abtests.heading_edit') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $test->name }}</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl">
    <form method="POST" action="{{ route('ab-tests.update', $test) }}" class="space-y-4">
        @csrf @method('PUT')
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('abtests.name_label') }}</label>
                <input type="text" name="name" value="{{ old('name', $test->name) }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('catalogs.session_label') }}</label>
                <select name="session_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    @foreach($sessions as $s)<option value="{{ $s->id }}" {{ $test->session_id==$s->id ? 'selected':'' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div class="border border-blue-200 rounded-xl p-3 bg-blue-50/30">
                <label class="text-xs font-bold text-blue-600">{{ __('abtests.variant_a') }}</label>
                <textarea name="variant_a_message" rows="3" required class="w-full mt-1 rounded-xl border border-gray-300 px-3 py-2 text-sm">{{ old('variant_a_message', $test->variant_a_message) }}</textarea>
                <input type="url" name="media_url_a" value="{{ old('media_url_a', $test->media_url_a) }}" placeholder="Media URL A (opsional)" class="w-full mt-2 rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="border border-purple-200 rounded-xl p-3 bg-purple-50/30">
                <label class="text-xs font-bold text-purple-600">{{ __('abtests.variant_b') }}</label>
                <textarea name="variant_b_message" rows="3" required class="w-full mt-1 rounded-xl border border-gray-300 px-3 py-2 text-sm">{{ old('variant_b_message', $test->variant_b_message) }}</textarea>
                <input type="url" name="media_url_b" value="{{ old('media_url_b', $test->media_url_b) }}" placeholder="Media URL B (opsional)" class="w-full mt-2 rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>
        <div class="flex gap-2 pt-1">
            <a href="{{ route('ab-tests.index') }}" class="flex-1 text-center bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</a>
            <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
        </div>
    </form>
</div>
@endsection
