@extends('layouts.app')
@section('title', 'Item Katalog — ' . $catalog->name)
@section('content')

<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('catalogs.index') }}" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Item: {{ $catalog->name }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $catalog->items->count() }} produk dalam katalog ini</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-4 py-3">Produk</th>
                    <th class="px-4 py-3">Harga</th>
                    <th class="px-4 py-3">Stok</th>
                    <th class="px-4 py-3 w-16 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($catalog->items as $item)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($item->image_url)<img src="{{ $item->image_url }}" class="w-10 h-10 rounded-lg object-cover border border-gray-200">@else<div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center"><i class="fas fa-box text-gray-400"></i></div>@endif
                            <div>
                                <div class="font-medium text-gray-900">{{ $item->name }}</div>
                                @if($item->product_code)<div class="text-[10px] text-gray-400 font-mono">{{ $item->product_code }}</div>@endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 font-semibold text-gray-800">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->stock }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('catalogs.items.destroy', [$catalog, $item]) }}" onsubmit="return confirm('Hapus item?')">@csrf @method('DELETE')<button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button></form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-box-open text-3xl mb-2"></i><p>Belum ada item</p></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5 h-fit">
        <h2 class="font-bold text-gray-900 mb-3">Tambah Item</h2>
        <form method="POST" action="{{ route('catalogs.items.store', $catalog) }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-medium text-gray-500">Nama Produk</label>
                <input type="text" name="name" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs font-medium text-gray-500">Harga</label>
                    <input type="number" name="price" min="0" step="0.01" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Stok</label>
                    <input type="number" name="stock" min="0" value="0" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Kode Produk (opsional)</label>
                <input type="text" name="product_code" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Image URL (opsional)</label>
                <input type="url" name="image_url" placeholder="https://..." class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><i class="fas fa-plus mr-1"></i> Tambah Item</button>
        </form>
    </div>
</div>
@endsection
