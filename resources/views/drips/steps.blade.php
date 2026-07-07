@extends('layouts.app')
@section('title', 'Drip Steps — ' . $dripCampaign->name)
@section('content')

<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('drips.index') }}" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Steps: {{ $dripCampaign->name }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $dripCampaign->steps->count() }} step · dikirim berurutan sesuai jeda waktu</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-3">
        @forelse($dripCampaign->steps->sortBy('step_order') as $step)
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <span class="w-8 h-8 rounded-lg bg-teal-500 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">{{ $step->step_order }}</span>
                    <div class="min-w-0">
                        <div class="text-xs text-gray-400 mb-1"><i class="fas fa-hourglass-half mr-1"></i> Tunggu {{ $step->wait_hours }} jam
                            @if($step->ai_key_id)<span class="ml-2 text-violet-600"><i class="fas fa-robot"></i> AI</span>@endif
                        </div>
                        <p class="text-sm text-gray-700">{{ $step->message }}</p>
                        @if($step->media_url)<a href="{{ $step->media_url }}" target="_blank" class="text-xs text-brand-600 hover:underline"><i class="fas fa-paperclip mr-1"></i> Lihat media</a>@endif
                    </div>
                </div>
                <form method="POST" action="{{ route('drips.steps.destroy', [$dripCampaign, $step]) }}" onsubmit="return confirm('Hapus step?')">
                    @csrf @method('DELETE')
                    <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <i class="fas fa-list-ol text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">Belum ada step. Tambahkan di panel kanan.</p>
        </div>
        @endforelse
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5 h-fit">
        <h2 class="font-bold text-gray-900 mb-3">Tambah Step</h2>
        <form method="POST" action="{{ route('drips.steps.store', $dripCampaign) }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs font-medium text-gray-500">Urutan</label>
                    <input type="number" name="step_order" min="1" value="{{ ($dripCampaign->steps->max('step_order') ?? 0) + 1 }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Tunggu (jam)</label>
                    <input type="number" name="wait_hours" min="0" value="24" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Pesan</label>
                <textarea name="message" rows="3" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"></textarea>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Media URL (opsional)</label>
                <input type="url" name="media_url" placeholder="https://..." class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">AI Key (opsional)</label>
                <select name="ai_key_id" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Tanpa AI</option>
                    @foreach($aiKeys as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach
                </select>
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><i class="fas fa-plus mr-1"></i> Tambah Step</button>
        </form>
    </div>
</div>
@endsection
