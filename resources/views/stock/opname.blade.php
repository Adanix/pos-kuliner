@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Stok Opname Cepat</h1>
            <p class="text-sm text-[#575047] mt-0.5">Masukkan hasil hitung fisik bahan baku di dapur untuk menyesuaikan saldo stok secara otomatis.</p>
        </div>
        <a href="{{ route('stok.index') }}" class="text-sm font-semibold text-[#575047] hover:underline">&larr; Riwayat Mutasi</a>
    </div>

    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm">
        @if($ingredients->isEmpty())
            <div class="p-8 text-center text-[#9E9385] text-sm">
                Belum ada bahan baku terdaftar. Tambahkan bahan baku terlebih dahulu.
            </div>
        @else
            <form method="POST" action="{{ route('stok.opname.store') }}" class="space-y-6">
                @csrf

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                            <tr>
                                <th class="px-3 py-2.5 rounded-l-lg">Bahan Baku</th>
                                <th class="px-3 py-2.5 text-center">Satuan</th>
                                <th class="px-3 py-2.5 text-right">Tercatat Sistem</th>
                                <th class="px-3 py-2.5 w-40 text-right">Hasil Fisik</th>
                                <th class="px-3 py-2.5 rounded-r-lg">Alasan Penyesuaian (Jika Ada Selisih)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E7E0D6]">
                            @foreach($ingredients as $idx => $ing)
                                <tr class="hover:bg-[#FAF8F5]">
                                    <td class="px-3 py-3">
                                        <input type="hidden" name="counts[{{ $idx }}][ingredient_id]" value="{{ $ing->id }}">
                                        <span class="font-bold text-[#14110F] block">{{ $ing->name }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-center text-xs text-[#575047]">{{ $ing->base_unit }}</td>
                                    <td class="px-3 py-3 text-right font-medium text-xs text-[#575047] tabular-nums">
                                        {{ (float) $ing->stock_qty }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <input type="number" step="0.0001" min="0" name="counts[{{ $idx }}][physical_qty]"
                                               value="{{ (float) $ing->stock_qty }}" required
                                               class="w-full text-right font-bold text-xs px-3 py-1.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5] focus:bg-white tabular-nums">
                                    </td>
                                    <td class="px-3 py-3">
                                        <input type="text" name="counts[{{ $idx }}][reason]" placeholder="Alasan jika ada selisih..."
                                               class="w-full text-xs px-3 py-1.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5] focus:bg-white">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-[#E7E0D6]">
                    <a href="{{ route('stok.index') }}" class="px-4 py-2.5 rounded-xl border border-[#D3C9BD] text-xs font-semibold">Batal</a>
                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin memproses penyesuaian stok opname ini?')"
                            class="px-6 py-2.5 rounded-xl bg-[#C2410C] text-white text-xs font-bold hover:bg-[#9A3412] shadow-sm">
                        Simpan Penyesuaian Stok
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
