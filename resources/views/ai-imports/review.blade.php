@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('impor.index') }}" class="text-xs font-semibold text-[#575047] hover:underline">&larr; Kembali ke Daftar Impor</a>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F] mt-1">Review Hasil Ekstraksi AI #{{ $aiImport->id }}</h1>
            <p class="text-xs text-[#575047]">Periksa kesesuaian menu dan total harga sebelum menyimpan sebagai transaksi resmi.</p>
        </div>

        <div class="flex items-center gap-2">
            @if($aiImport->status->value === 'saved' && $aiImport->transaction)
                <a href="{{ route('transactions.show', $aiImport->transaction) }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-[#047857] text-white">
                    Lihat Transaksi Tersimpan &rarr;
                </a>
            @endif
        </div>
    </div>

    @php
        $extracted = $aiImport->extracted ?? [];
        $items = $extracted['items'] ?? [];
        $flags = $extracted['review_flags'] ?? [];
        $warnings = $extracted['similarity_warnings'] ?? [];
    @endphp

    <!-- Alerts if Mismatch or Duplicate Warning -->
    @if(in_array('SUBTOTAL_MISMATCH', $flags))
        <div class="p-4 rounded-2xl bg-[#FFFBEB] border border-[#FDE68A] text-[#B45309] text-xs">
            <strong class="font-bold">Peringatan: Selisih Subtotal Item.</strong>
            Jumlah total per baris item tidak sama dengan subtotal pesanan di screenshot. Periksa kuantitas dan harga item.
        </div>
    @endif

    @if(in_array('TOTAL_MISMATCH', $flags))
        <div class="p-4 rounded-2xl bg-[#FFFBEB] border border-[#FDE68A] text-[#B45309] text-xs">
            <strong class="font-bold">Peringatan: Total Pesanan Berbeda.</strong>
            Total pesanan online mungkin mencakup ongkos kirim atau biaya jasa platform yang tidak masuk ke pendapatan outlet.
        </div>
    @endif

    @if(!empty($warnings))
        <div class="p-4 rounded-2xl bg-[#FFF4EC] border border-[#FFE6D5] text-[#C2410C] text-xs">
            <strong class="font-bold">Perhatian: Kemungkinan Pesanan Duplikat</strong>
            @foreach($warnings as $w)
                <p class="mt-0.5">• {{ $w['note'] }}</p>
            @endforeach
        </div>
    @endif

    <!-- Split Screen: Left Image, Right Review Form -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Column: Screenshot Image Viewer -->
        <div class="bg-white rounded-2xl border border-[#E7E0D6] p-4 shadow-sm flex flex-col">
            <h3 class="font-bold text-sm text-[#14110F] mb-3">Tangkapan Layar (Screenshot) Asli</h3>
            <div class="flex-1 bg-[#F4F0EA] rounded-xl overflow-hidden flex items-center justify-center p-2 min-h-[380px] max-h-[600px]">
                <img src="{{ route('impor.image', $aiImport) }}" alt="Screenshot Pesanan"
                     class="max-w-full max-h-[560px] object-contain rounded-lg shadow-sm">
            </div>
            <div class="mt-3 flex justify-between text-[11px] text-[#9E9385]">
                <span>Format: {{ strtoupper(pathinfo($aiImport->image_path, PATHINFO_EXTENSION)) }} ({{ round($aiImport->image_bytes / 1024) }} KB)</span>
                <span>SHA-256: {{ substr($aiImport->image_hash, 0, 16) }}...</span>
            </div>
        </div>

        <!-- Right Column: Verification & Mapping Form -->
        <div class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm flex flex-col justify-between">
            <form method="POST" action="{{ route('impor.save', $aiImport) }}" class="space-y-4">
                @csrf

                <div class="flex items-center justify-between pb-3 border-b border-[#E7E0D6]">
                    <h3 class="font-bold text-sm text-[#14110F]">Verifikasi & Pemetaan Menu</h3>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-[#FFF4EC] text-[#C2410C]">
                        {{ ucfirst($extracted['platform'] ?? 'Unknown') }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Kanal Penjualan</label>
                        <select name="channel" required class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                            <option value="gofood" {{ ($extracted['platform'] ?? '') === 'gofood' ? 'selected' : '' }}>GoFood</option>
                            <option value="shopeefood" {{ ($extracted['platform'] ?? '') === 'shopeefood' ? 'selected' : '' }}>ShopeeFood</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">ID Pesanan Platform</label>
                        <input type="text" name="platform_order_id" value="{{ $extracted['platform_order_id'] ?? '' }}"
                               class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5] font-mono">
                    </div>
                </div>

                <!-- Extracted Items List -->
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-2">Item Menu Terekstrak</label>
                    <div class="space-y-3 max-h-[300px] overflow-y-auto pr-1">
                        @foreach($items as $idx => $item)
                            <div class="p-3 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6] space-y-2">
                                <div class="flex justify-between items-start text-xs">
                                    <div>
                                        <input type="hidden" name="items[{{ $idx }}][source_name]" value="{{ $item['source_name'] }}">
                                        <span class="font-bold text-[#14110F]">{{ $item['source_name'] }}</span>
                                        @if(!empty($item['notes']))
                                            <span class="block text-[11px] text-[#575047] italic">"{{ $item['notes'] }}"</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs text-[#575047]">Qty:</span>
                                        <input type="number" name="items[{{ $idx }}][qty]" value="{{ $item['quantity'] }}" min="1" max="99" required
                                               class="w-14 text-center font-bold text-xs py-1 px-1.5 rounded-lg border border-[#D3C9BD] bg-white">
                                    </div>
                                </div>

                                <!-- Mapping dropdown -->
                                <div>
                                    <label class="block text-[10px] font-semibold text-[#575047] uppercase mb-0.5">Petakan ke Menu Master Outlet</label>
                                    <select name="items[{{ $idx }}][menu_id]" required class="w-full text-xs px-2.5 py-1.5 rounded-xl border border-[#D3C9BD] bg-white">
                                        <option value="">-- Pilih Menu Master --</option>
                                        @foreach($allMenus as $m)
                                            <option value="{{ $m->id }}" {{ ($item['matched_menu_id'] ?? null) == $m->id ? 'selected' : '' }}>
                                                {{ $m->name }} ({{ \App\Support\Money::format($m->price) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Discounts and Notes -->
                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-[#E7E0D6]">
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Diskon Pesanan (Rp)</label>
                        <input type="number" name="discount_amount" value="{{ $extracted['visible_discount'] ?? 0 }}" min="0"
                               class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5] tabular-nums">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Catatan Tambahan</label>
                        <input type="text" name="note" placeholder="Catatan pesanan..."
                               class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                    </div>
                </div>

                <!-- Save Action Button -->
                <div class="pt-4 border-t border-[#E7E0D6]">
                    <button type="submit" :disabled="{{ $aiImport->status->value === 'saved' ? 'true' : 'false' }}"
                            class="w-full py-3 rounded-xl font-bold text-white bg-[#047857] hover:bg-[#065f46] text-sm shadow-md transition disabled:opacity-40">
                        {{ $aiImport->status->value === 'saved' ? 'Sudah Tersimpan sebagai Transaksi' : 'Konfirmasi & Simpan Transaksi Resmi' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
