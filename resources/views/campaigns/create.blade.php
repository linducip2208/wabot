@extends('layouts.app')
@section('title', 'Kampanye Baru — WABot')
@section('content')

<div x-data="campaignWizard()" class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('campaigns.index') }}" class="text-sm text-gray-500 hover:text-brand-600">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    {{-- Stepper --}}
    <div class="flex items-center justify-center mb-8">
        <template x-for="(step, i) in steps" :key="i">
            <div class="flex items-center">
                <div class="flex items-center gap-2" :class="i > 0 && 'ml-2'">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition"
                        :class="current >= i ? 'bg-brand-600 text-white' : 'bg-gray-200 text-gray-500'">
                        <span x-show="current > i"><i class="fas fa-check text-[10px]"></i></span>
                        <span x-show="current <= i" x-text="i+1"></span>
                    </div>
                    <span class="text-sm font-medium hidden sm:inline" :class="current >= i ? 'text-brand-700' : 'text-gray-400'" x-text="step"></span>
                </div>
                <div class="w-8 sm:w-16 h-px mx-1 sm:mx-2" :class="i < 2 ? (current > i ? 'bg-brand-400' : 'bg-gray-200') : 'hidden'"></div>
            </div>
        </template>
    </div>

    <form method="POST" action="{{ route('campaigns.store') }}" @submit="submitting = true" class="bg-white rounded-2xl border border-gray-200 shadow-sm">
        @csrf

        {{-- Step 1: Audience --}}
        <div x-show="current === 0" class="p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-1">Pilih Audiens</h2>
            <p class="text-sm text-gray-500 mb-5">Tentukan sesi WhatsApp dan target kontak</p>

            <div class="grid grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="text-xs font-medium text-gray-500">Sesi WhatsApp</label>
                    <select name="session_id" x-model="sessionId" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        <option value="">Pilih sesi</option>
                        @foreach($sessions as $s)
                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->phone ?? 'offline' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Nama Kampanye</label>
                    <input type="text" name="name" required placeholder="Promo Lebaran 2026" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>

            <div class="flex items-center gap-2 mb-3">
                <span class="text-xs font-medium text-gray-500">Pilih kontak</span>
                <span class="text-[11px] text-gray-400" x-text="selectedCount + ' dari ' + totalContacts + ' dipilih'"></span>
                <button type="button" @click="selectAll()" class="text-[11px] text-brand-600 hover:underline ml-auto">Pilih Semua</button>
                <button type="button" @click="deselectAll()" class="text-[11px] text-gray-500 hover:underline">Hapus</button>
            </div>

            <div class="border border-gray-200 rounded-xl max-h-64 overflow-y-auto divide-y divide-gray-100">
                @forelse($contacts as $c)
                <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 cursor-pointer transition">
                    <input type="checkbox" name="recipient_ids[]" value="{{ $c->id }}" x-model="selectedIds" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900">{{ $c->name !== $c->phone ? $c->name : preg_replace('/@.*$/', '', $c->phone) }}</div>
                        <div class="text-xs text-gray-500">{{ preg_replace('/@.*$/', '', $c->phone) }}</div>
                    </div>
                </label>
                @empty
                <p class="px-4 py-8 text-center text-sm text-gray-500">Belum ada kontak. <a href="{{ route('contacts.index') }}" class="text-brand-600 hover:underline">Tambah kontak</a></p>
                @endforelse
            </div>
        </div>

        {{-- Step 2: Message --}}
        <div x-show="current === 1" class="p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-1">Tulis Pesan</h2>
            <p class="text-sm text-gray-500 mb-5">Gunakan spintax & variable untuk variasi</p>

            <div class="mb-4">
                <label class="text-xs font-medium text-gray-500">Pesan <span class="text-gray-400">({'{Halo|Hai|Pagi}'} = spintax, {'{name}'}, {'{phone}'})</span></label>
                <textarea name="message" x-model="messageText" rows="5" required placeholder="Halo {'{name}'}! {'{Kami ada promo spesial|Jangan lewatkan diskon 50%|Ada penawaran menarik untuk Anda}'}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-xs font-medium text-gray-500">Delay antar pesan (detik)</label>
                    <input type="number" name="delay_seconds" x-model="delaySeconds" min="1" max="60" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <p class="text-[10px] text-gray-400 mt-0.5">Rekomendasi 3-10 detik untuk hindari banned</p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Media URL (opsional)</label>
                    <input type="url" name="media_url" placeholder="https://example.com/image.jpg" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>

            {{-- Live preview --}}
            <div class="bg-[#efeae2] rounded-xl p-4">
                <div class="text-[10px] text-gray-500 mb-2 font-medium uppercase tracking-wide">Preview Pesan</div>
                <div class="bg-[#d9fdd3] rounded-lg rounded-br-none px-3.5 py-2 text-sm shadow-sm inline-block max-w-[80%]" x-text="previewMessage() || 'Pesan akan muncul di sini...'"></div>
                <div class="text-[10px] text-gray-400 mt-1"><span x-text="selectedCount"></span> penerima</div>
            </div>
        </div>

        {{-- Step 3: Review & Launch --}}
        <div x-show="current === 2" class="p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-1">Review & Kirim</h2>
            <p class="text-sm text-gray-500 mb-5">Periksa sebelum mengirim</p>

            <div class="bg-gray-50 rounded-xl p-4 space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Kampanye</span><span class="font-medium" x-text="'Promo Lebaran 2026'"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Penerima</span><span class="font-medium" x-text="selectedCount + ' kontak'"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Delay</span><span class="font-medium" x-text="delaySeconds + ' detik'"></span></div>
                <div class="flex justify-between"><span class="text-gray-500">Estimasi selesai</span><span class="font-medium" x-text="estimateFinish()"></span></div>
                <div class="border-t border-gray-200 pt-3 mt-3">
                    <div class="text-xs text-gray-500 mb-1">Pesan:</div>
                    <div class="bg-white rounded-lg p-3 text-sm whitespace-pre-wrap" x-text="messageText || '-'"></div>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50/50 rounded-b-2xl">
            <button type="button" @click="prev()" x-show="current > 0"
                class="bg-gray-100 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left mr-1"></i> Sebelumnya
            </button>
            <div x-show="current === 0"></div>
            <button type="button" @click="next()" x-show="current < 2"
                class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition ml-auto">
                Lanjut <i class="fas fa-arrow-right ml-1"></i>
            </button>
            <button type="submit" x-show="current === 2" :disabled="submitting || selectedCount === 0"
                class="bg-emerald-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-emerald-700 disabled:bg-gray-300 transition ml-auto">
                <span x-show="!submitting"><i class="fas fa-paper-plane mr-1"></i> Kirim Kampanye</span>
                <span x-show="submitting"><i class="fas fa-spinner fa-spin mr-1"></i> Mengirim...</span>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('campaignWizard', () => ({
        current: 0,
        steps: ['Audiens', 'Pesan', 'Kirim'],
        selectedIds: [],
        messageText: '',
        delaySeconds: 3,
        sessionId: '',
        submitting: false,

        get totalContacts() { return {{ $contacts->count() }}; },
        get selectedCount() { return this.selectedIds.length; },

        selectAll() { this.selectedIds = Array.from(document.querySelectorAll('input[name="recipient_ids[]"]')).map(el => el.value); },
        deselectAll() { this.selectedIds = []; },

        next() { if (this.current < 2) this.current++; },
        prev() { if (this.current > 0) this.current--; },

        previewMessage() {
            let msg = this.messageText || '';
            msg = msg.replace(/\{[^}]+\}/g, m => {
                const opts = m.slice(1, -1).split('|');
                return opts[0];
            });
            return msg || null;
        },

        estimateFinish() {
            const msgs = this.selectedCount;
            const delay = parseInt(this.delaySeconds) || 3;
            const totalSeconds = msgs * (delay + 0.5);
            const mins = Math.ceil(totalSeconds / 60);
            if (mins < 1) return '< 1 menit';
            if (mins < 60) return `~${mins} menit`;
            return `~${Math.ceil(mins / 60)} jam ${mins % 60} menit`;
        }
    }));
});
</script>
@endsection
