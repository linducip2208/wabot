@extends('layouts.app')
@section('title', 'Detail Rating — WABot')
@section('content')

<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('ratings.index') }}" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Detail Rating</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $rating->created_at->format('d M Y H:i') }}</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-6 text-center">
            <div class="mb-2">
                @for($i=1;$i<=5;$i++)<i class="fas fa-star text-2xl {{ $i <= $rating->rating ? 'text-amber-400' : 'text-gray-200' }}"></i>@endfor
            </div>
            <div class="text-3xl font-extrabold text-gray-900">{{ $rating->rating }}<span class="text-lg text-gray-400">/5</span></div>
        </div>
        @if($rating->comment)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="text-xs font-semibold text-gray-500 mb-2">Komentar Pelanggan</div>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $rating->comment }}</p>
        </div>
        @endif
        @if($rating->message)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="text-xs font-semibold text-gray-500 mb-2">Pesan Terkait</div>
            <p class="text-sm text-gray-700">{{ $rating->message->message ?? '-' }}</p>
        </div>
        @endif
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5 h-fit">
        <div class="text-xs font-semibold text-gray-500 mb-2">Kontak</div>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($rating->contact?->name ?? 'NA', 0, 2)) }}</div>
            <div>
                <div class="font-medium text-gray-900">{{ $rating->contact?->name ?? '-' }}</div>
                <div class="text-xs text-gray-400 font-mono">{{ preg_replace('/@.*$/', '', $rating->contact?->phone ?? '') }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
