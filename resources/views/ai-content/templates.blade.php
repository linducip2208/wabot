@extends('layouts.app')
@section('title', __('aistudio.templates_title'))
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('aistudio.templates_title') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('aistudio.templates_subtitle') }}</p>
    </div>
    <a href="{{ route('ai-content.index') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium flex items-center gap-1">
        <i class="fas fa-arrow-left text-xs"></i> {{ __('aistudio.back_to_generator') }}
    </a>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-20">
            <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-plus-circle text-brand-500"></i> <span id="formTitle">{{ __('aistudio.new_template') }}</span>
            </h2>
            <form method="POST" action="{{ route('ai-content.templates.store') }}" id="templateForm" class="space-y-3">
                @csrf
                <input type="hidden" name="_method" value="POST" id="methodOverride">
                <input type="hidden" id="templateId" name="template_id">

                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('aistudio.template_name') }} <span class="text-red-400">*</span></label>
                    <input type="text" name="name" id="templateName" required placeholder="{{ __('aistudio.template_name_placeholder') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('aistudio.category') }}</label>
                    <select name="category" id="templateCategory" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="promo">{{ __('aistudio.cat_promo') }}</option>
                        <option value="greeting">{{ __('aistudio.cat_greeting') }}</option>
                        <option value="followup">{{ __('aistudio.cat_followup') }}</option>
                        <option value="announcement">{{ __('aistudio.cat_announcement') }}</option>
                        <option value="social">{{ __('aistudio.cat_social') }}</option>
                        <option value="general">{{ __('aistudio.cat_general') }}</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-500 flex items-center justify-between">
                        <span>{{ __('aistudio.prompt_template') }} <span class="text-red-400">*</span></span>
                        <span class="text-[10px] text-gray-400">{{ __('aistudio.variables_hint') }}</span>
                    </label>
                    <textarea name="prompt_template" id="templatePrompt" rows="6" required placeholder="{{ __('aistudio.prompt_template_placeholder') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_public" id="templatePublic" value="1" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <label for="templatePublic" class="text-xs text-gray-500">{{ __('aistudio.make_public') }}</label>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-brand-600 to-brand-700 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:from-brand-700 hover:to-brand-800 transition flex items-center justify-center gap-2">
                        <i class="fas fa-save text-xs"></i> {{ __('common.save') }}
                    </button>
                    <button type="button" id="cancelEdit" onclick="resetForm()" class="hidden px-4 py-2.5 rounded-xl text-sm font-medium text-gray-500 hover:bg-gray-100 transition border border-gray-200">
                        {{ __('common.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-4">
        {{-- My Templates --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-bold text-gray-900 text-sm mb-4 flex items-center gap-2">
                <i class="fas fa-folder text-amber-500"></i> {{ __('aistudio.my_templates') }} <span class="text-gray-400 font-normal">({{ $templates->count() }})</span>
            </h3>
            @if($templates->isEmpty())
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-layer-group text-3xl mb-2"></i>
                <p class="text-sm">{{ __('aistudio.no_templates_yet') }}</p>
            </div>
            @else
            <div class="space-y-2">
                @foreach($templates as $tpl)
                <div class="flex items-center justify-between p-3 rounded-lg border border-gray-100 hover:border-brand-200 transition group">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="font-medium text-gray-800 text-sm">{{ $tpl->name }}</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">{{ __('aistudio.cat_' . $tpl->category) }}</span>
                            @if($tpl->is_public)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-green-50 text-green-600">{{ __('aistudio.public') }}</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 truncate">{{ Str::limit($tpl->prompt_template, 100) }}</p>
                    </div>
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition">
                        <button onclick="editTemplate(@js($tpl))" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600" title="{{ __('common.edit') }}"><i class="fas fa-edit text-xs"></i></button>
                        <form method="POST" action="{{ route('ai-content.templates.destroy', $tpl) }}" onsubmit="return confirm('{{ __('common.delete') }}?')">
                            @csrf @method('DELETE')
                            <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Public Templates --}}
        @if($publicTemplates->count())
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-bold text-gray-900 text-sm mb-4 flex items-center gap-2">
                <i class="fas fa-globe text-blue-500"></i> {{ __('aistudio.public_templates') }}
            </h3>
            <div class="space-y-2">
                @foreach($publicTemplates as $tpl)
                <div class="flex items-center justify-between p-3 rounded-lg border border-gray-100 hover:border-blue-200 transition">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="font-medium text-gray-800 text-sm">{{ $tpl->name }}</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">{{ __('aistudio.cat_' . $tpl->category) }}</span>
                            <span class="text-[10px] text-gray-400">by {{ $tpl->user->name }}</span>
                        </div>
                        <p class="text-xs text-gray-400 truncate">{{ Str::limit($tpl->prompt_template, 100) }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function editTemplate(tpl) {
    document.getElementById('formTitle').textContent = '{{ __('aistudio.edit_template') }}';
    document.getElementById('templateName').value = tpl.name;
    document.getElementById('templatePrompt').value = tpl.prompt_template;
    document.getElementById('templateCategory').value = tpl.category;
    document.getElementById('templatePublic').checked = tpl.is_public;
    document.getElementById('templateId').value = tpl.id;
    document.getElementById('templateForm').action = '{{ url('ai-content/templates') }}/' + tpl.id;
    document.getElementById('methodOverride').value = 'PUT';
    @csrf
    document.getElementById('cancelEdit').classList.remove('hidden');
    document.getElementById('templateForm').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('formTitle').textContent = '{{ __('aistudio.new_template') }}';
    document.getElementById('templateName').value = '';
    document.getElementById('templatePrompt').value = '';
    document.getElementById('templateCategory').value = 'general';
    document.getElementById('templatePublic').checked = false;
    document.getElementById('templateId').value = '';
    document.getElementById('templateForm').action = '{{ route('ai-content.templates.store') }}';
    document.getElementById('methodOverride').value = 'POST';
    document.getElementById('cancelEdit').classList.add('hidden');
}
</script>
@endsection
