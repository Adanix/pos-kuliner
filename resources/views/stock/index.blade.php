@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showPurchaseModal: false, showWasteModal: false }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Pergerakan Stok (Ledger)</h1>
            <p class="text-sm text-[#575047] mt-0.5">Catatan mutasi stok bahan baku bersifat kekal (immutable ledger).</p>
        </div>

        <div class="flex items-center gap-2">
            <button @click="showWasteModal = true" type="button"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-semibold bg-[#FEF2F2] text-[#DC2626] border border-[#FECACA] hover:bg-[#fee2e2] shadow-sm">
                <span>+ Catat Waste</span>
            </button>
            <button @click="showPurchaseModal = true" type="button"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold bg-[#047857] text-white hover:bg-[#065f46] shadow-sm">
                <span>+ Catat Pembelian</span>
            </button>
        </div>
    </div>

    <!-- Movement Filter Strip -->
    <div class="bg-white p-4 rounded-2xl border border-[#E7E0D6] shadow-sm">
        <form method="GET" action="{{ route('stok.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-semibold text-[#575047] mb-1">Bahan Baku</label>
                <select name="ingredient_id" class="w-full px-3 py-2 rounded-xl border border-[#D3C9BD] text-xs bg-[#FAF8F5]">
                    <option value="">Semua Bahan Baku</option>
                    @foreach($ingredients as $ing)
                        <option value="{{ $ing->id }}" {{ request('ingredient_id') == $ing->id ? 'selected' : '' }}>{{ $ing->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#575047] mb-1">Tipe Mutasi</label>
                <select name="type" class="w-full px-3 py-2 rounded-xl border border-[#D3C9BD] text-xs bg-[#FAF8F5]">
                    <option value="">Semua Tipe</option>
                    <option value="purchase" {{ request('type') === 'purchase' ? 'selected' : '' }}>Pembelian Masuk</option>
                    <option value="usage" {{ request('type') === 'usage' ? 'selected' : '' }}>Pemakaian Penjualan</option>
                    <option value="waste" {{ request('type') === 'waste' ? 'selected' : '' }}>Waste / Rusak</option>
                    <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Penyesuaian (Opname)</option>
                    <option value="reversal" {{ request('type') === 'reversal' ? 'selected' : '' }}>Pengembalian Void</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl font-bold text-xs bg-[#14110F] text-white hover:bg-[#26221E]">Filter</button>
                <a href="{{ route('stok.index') }}" class="px-3 py-2 rounded-xl font-semibold text-xs border border-[#D3C9BD] text-[#575047]">Reset</a>
            </div>
        </form>
    </div>

    <!-- Movements Table -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-sm">
        @if($movements->isEmpty())
            <div class="p-12 text-center text-[#9E9385] text-sm">
                Belum ada pergerakan stok bahan baku yang tercatat.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                        <tr>
                            <th class="px-3 py-2.5 rounded-l-lg">Waktu</th>
                            <th class="px-3 py-2.5">Bahan Baku</th>
                            <th class="px-3 py-2.5">Tipe Mutasi</th>
                            <th class="px-3 py-2.5 text-right">Perubahan Qty</th>
                            <th class="px-3 py-2.5 text-right">Harga Satuan</th>
                            <th class="px-3 py-2.5">Operator</th>
                            <th class="px-3 py-2.5 rounded-r-lg">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E7E0D6]">
                        @foreach($movements as $m)
                            <tr class="hover:bg-[#FAF8F5]">
                                <td class="px-3 py-3 text-xs text-[#575047] whitespace-nowrap">
                                    {{ $m->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-3 py-3 font-semibold text-[#14110F]">{{ $m->ingredient?->name }}</td>
                                <td class="px-3 py-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                        {{ $m->type->value === 'purchase' ? 'bg-[#ECFDF5] text-[#047857]' : ($m->type->value === 'waste' ? 'bg-[#FEF2F2] text-[#DC2626]' : 'bg-[#FAF8F5] text-[#575047] border border-[#D3C9BD]') }}">
                                        {{ $m->type->label() }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums {{ (float) $m->qty_delta > 0 ? 'text-[#047857]' : 'text-[#DC2626]' }}">
                                    {{ (float) $m->qty_delta > 0 ? '+' : '' }}{{ (float) $m->qty_delta }} {{ $m->ingredient?->base_unit }}
                                </td>
                                <td class="px-3 py-3 text-right text-xs tabular-nums text-[#575047]">
                                    {{ \App\Support\Money::format($m->unit_cost) }}
                                </td>
                                <td class="px-3 py-3 text-xs text-[#575047]">{{ $m->user?->name ?? 'Sistem' }}</td>
                                <td class="px-3 py-3 text-xs text-[#575047] max-w-xs truncate">{{ $m->reason ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Catat Pembelian -->
    <div x-show="showPurchaseModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div @click.away="showPurchaseModal = false" class="bg-white w-full max-w-md rounded-2xl border border-[#E7E0D6] shadow-2xl p-6">
            <h3 class="text-lg font-bold text-[#14110F] mb-4">Catat Pembelian Bahan Baku</h3>

            <form action="{{ route('stok.purchase') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Bahan Baku</label>
                    <select name="ingredient_id" required class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                        @foreach($ingredients as $ing)
                            <option value="{{ $ing->id }}">{{ $ing->name }} (Satuan: {{ $ing->base_unit }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Jumlah Masuk</label>
                        <input type="number" step="0.0001" min="0.0001" name="qty" required
                               class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5] tabular-nums">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Harga Beli Satuan (Rp)</label>
                        <input type="number" min="0" name="purchase_cost" required
                               class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5] tabular-nums">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Catatan / Supplier (Opsional)</label>
                    <input type="text" name="reason" placeholder="Pasar induk / Toko ABC..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-[#E7E0D6]">
                    <button @click="showPurchaseModal = false" type="button" class="px-4 py-2 rounded-xl border border-[#D3C9BD] text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#047857] text-white text-xs font-bold hover:bg-[#065f46]">Simpan Pembelian</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Catat Waste -->
    <div x-show="showWasteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div @click.away="showWasteModal = false" class="bg-white w-full max-w-md rounded-2xl border border-[#E7E0D6] shadow-2xl p-6">
            <h3 class="text-lg font-bold text-[#DC2626] mb-4">Catat Bahan Rusak / Terbuang (Waste)</h3>

            <form action="{{ route('stok.waste') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Bahan Baku</label>
                    <select name="ingredient_id" required class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                        @foreach($ingredients as $ing)
                            <option value="{{ $ing->id }}">{{ $ing->name }} (Satuan: {{ $ing->base_unit }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Jumlah Terbuang</label>
                    <input type="number" step="0.0001" min="0.0001" name="qty" required
                           class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5] tabular-nums">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Alasan Kerusakan (Wajib)</label>
                    <input type="text" name="reason" required minlength="5" placeholder="Kedaluwarsa / tumpah / busuk..."
                           class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-[#E7E0D6]">
                    <button @click="showWasteModal = false" type="button" class="px-4 py-2 rounded-xl border border-[#D3C9BD] text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#b91c1c]">Catat Waste</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
