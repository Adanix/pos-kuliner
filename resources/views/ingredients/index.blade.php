@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showModal: false }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Bahan Baku & Persediaan</h1>
            <p class="text-sm text-[#575047] mt-0.5">Daftar bahan baku dapur dengan satuan dasar dan harga beli rata-rata bergerak (WAC).</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('stok.opname') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-semibold bg-white border border-[#D3C9BD] text-[#14110F] hover:bg-[#FAF8F5] shadow-sm">
                <span>Stok Opname Fisik</span>
            </a>
            <button @click="showModal = true"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold bg-[#C2410C] text-white hover:bg-[#9A3412] shadow-sm">
                <span>+ Tambah Bahan Baku</span>
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-sm">
        @if($ingredients->isEmpty())
            <div class="p-12 text-center text-[#9E9385] text-sm">
                Belum ada bahan baku terdaftar. Tambahkan bahan baku pertama Anda.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                        <tr>
                            <th class="px-3 py-2.5 rounded-l-lg">Kode / Nama Bahan</th>
                            <th class="px-3 py-2.5 text-center">Satuan Dasar</th>
                            <th class="px-3 py-2.5">Digunakan di Menu</th>
                            <th class="px-3 py-2.5 text-right">Stok Saat Ini</th>
                            <th class="px-3 py-2.5 text-right">Batas Minimum</th>
                            <th class="px-3 py-2.5 text-right">Harga Satuan (WAC)</th>
                            <th class="px-3 py-2.5 text-center rounded-r-lg">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E7E0D6]">
                        @foreach($ingredients as $ing)
                            <tr class="hover:bg-[#FAF8F5]">
                                <td class="px-3 py-3">
                                    <span class="font-bold text-[#14110F] block">{{ $ing->name }}</span>
                                    @if($ing->code)
                                        <span class="text-xs text-[#9E9385] font-mono">{{ $ing->code }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="text-xs px-2 py-0.5 rounded-md bg-[#FAF8F5] border border-[#D3C9BD] font-bold text-[#575047]">
                                        {{ $ing->base_unit }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    @php
                                        $menusUsing = $ing->recipeItems->map(fn($item) => $item->recipe?->menu)->filter()->unique('id');
                                    @endphp
                                    @if($menusUsing->isNotEmpty())
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @foreach($menusUsing->take(2) as $m)
                                                <span class="inline-block text-[11px] px-2 py-0.5 rounded-md bg-[#FAF8F5] border border-[#E7E0D6] text-[#575047] truncate max-w-[120px]" title="{{ $m->name }}">
                                                    {{ $m->name }}
                                                </span>
                                            @endforeach
                                            @if($menusUsing->count() > 2)
                                                <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-[#F4F0EA] text-[#575047] font-semibold self-center" title="{{ $menusUsing->pluck('name')->implode(', ') }}">
                                                    +{{ $menusUsing->count() - 2 }} menu
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-[#9E9385] italic">Belum terhubung</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums {{ $ing->isLowStock() ? 'text-[#DC2626]' : 'text-[#14110F]' }}">
                                    {{ (float) $ing->stock_qty }} {{ $ing->base_unit }}
                                </td>
                                <td class="px-3 py-3 text-right text-xs text-[#575047] tabular-nums">
                                    {{ (float) $ing->min_stock }} {{ $ing->base_unit }}
                                </td>
                                <td class="px-3 py-3 text-right text-xs font-medium tabular-nums text-[#047857]">
                                    {{ \App\Support\Money::format($ing->avg_cost) }} / {{ $ing->base_unit }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($ing->isLowStock())
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-[#FEF2F2] text-[#DC2626] font-bold">Menipis</span>
                                    @else
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-[#ECFDF5] text-[#047857] font-semibold">Aman</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $ingredients->links() }}
            </div>
        @endif
    </div>

    <!-- Create Ingredient Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div @click.away="showModal = false" class="bg-white w-full max-w-lg rounded-2xl border border-[#E7E0D6] shadow-2xl p-6">
            <h3 class="text-lg font-bold text-[#14110F] mb-4">Tambah Bahan Baku Baru</h3>

            <form action="{{ route('bahan.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Nama Bahan Baku</label>
                    <input type="text" name="name" required placeholder="Contoh: Daging Ayam, Beras, Minyak Goreng..."
                           class="w-full px-3 py-2 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Kode Bahan (Opsional)</label>
                        <input type="text" name="code" placeholder="ING-001"
                               class="w-full px-3 py-2 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Satuan Dasar</label>
                        <select name="base_unit" required
                                class="w-full px-3 py-2 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5]">
                            <option value="g">Gram (g)</option>
                            <option value="ml">Mililiter (ml)</option>
                            <option value="pcs">Pieces / Butir (pcs)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Batas Minimum</label>
                        <input type="number" step="0.01" name="min_stock" value="0"
                               class="w-full px-3 py-2 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Saldo Awal</label>
                        <input type="number" step="0.01" name="opening_qty" value="0"
                               class="w-full px-3 py-2 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Harga Satuan (Rp)</label>
                        <input type="number" name="opening_cost" value="0"
                               class="w-full px-3 py-2 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5]">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-[#E7E0D6]">
                    <button @click="showModal = false" type="button" class="px-4 py-2 rounded-xl border border-[#D3C9BD] text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#C2410C] text-white text-xs font-bold hover:bg-[#9A3412]">Simpan Bahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
