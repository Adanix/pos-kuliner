@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Sesi Kasir (Shift)</h1>
            <p class="text-sm text-[#575047] mt-0.5">Kelola modal awal kasir dan rekonsiliasi uang fisik saat tutup kasir.</p>
        </div>
        <a href="{{ route('pos.index') }}" class="text-sm font-semibold text-[#C2410C] hover:underline">
            &larr; Kembali ke Kasir
        </a>
    </div>

    <!-- Active Shift Card or Open Shift Form -->
    @if($activeSession)
        <div class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-[#E7E0D6]">
                <div class="flex items-center gap-3">
                    <span class="w-3.5 h-3.5 rounded-full bg-[#047857] animate-pulse"></span>
                    <div>
                        <span class="text-xs font-bold text-[#047857] uppercase tracking-wider">Status: Sesi Kasir Aktif</span>
                        <h3 class="font-bold text-base text-[#14110F]">Dibuka oleh {{ $activeSession->openedByUser?->name }}</h3>
                    </div>
                </div>
                <div class="text-right text-xs text-[#575047]">
                    <span>Waktu Buka:</span>
                    <strong class="text-[#14110F] block">{{ $activeSession->opened_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</strong>
                </div>
            </div>

            <!-- Current Shift Live Financial Stats -->
            @if($stats)
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="p-4 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6]">
                        <span class="text-xs text-[#575047]">Modal Kas Awal</span>
                        <div class="text-lg font-bold text-[#14110F] mt-1 tabular-nums">{{ \App\Support\Money::format($stats['opening_float']) }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6]">
                        <span class="text-xs text-[#575047]">Penjualan Tunai</span>
                        <div class="text-lg font-bold text-[#047857] mt-1 tabular-nums">+{{ \App\Support\Money::format($stats['cash_sales']) }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6]">
                        <span class="text-xs text-[#575047]">Penjualan Non-Tunai</span>
                        <div class="text-lg font-bold text-[#2563EB] mt-1 tabular-nums">{{ \App\Support\Money::format($stats['non_cash_sales']) }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-[#FFF4EC] border border-[#FFE6D5]">
                        <span class="text-xs font-semibold text-[#C2410C]">Ekspektasi Kas Laci</span>
                        <div class="text-lg font-extrabold text-[#C2410C] mt-1 tabular-nums">{{ \App\Support\Money::format($stats['expected_cash']) }}</div>
                    </div>
                </div>
            @endif

            <!-- Form Tutup Kasir -->
            <form method="POST" action="{{ route('pos.shift.close') }}" class="p-5 rounded-xl bg-[#F4F0EA] border border-[#E7E0D6] space-y-4">
                @csrf
                <h4 class="font-bold text-sm text-[#14110F]">Tutup Sesi Kasir & Rekonsiliasi</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="counted_cash" class="block text-xs font-semibold text-[#575047] uppercase mb-1">Total Uang Fisik di Laci (Rp)</label>
                        <input type="number" id="counted_cash" name="counted_cash" required min="0"
                               class="w-full px-4 py-2.5 rounded-xl border border-[#D3C9BD] focus:ring-2 focus:ring-[#C2410C] bg-white font-bold text-sm">
                    </div>
                    <div>
                        <label for="note" class="block text-xs font-semibold text-[#575047] uppercase mb-1">Catatan (Wajib jika ada selisih)</label>
                        <input type="text" id="note" name="note" placeholder="Alasan jika ada lebih/kurang..."
                               class="w-full px-4 py-2.5 rounded-xl border border-[#D3C9BD] focus:ring-2 focus:ring-[#C2410C] bg-white text-sm">
                    </div>
                </div>

                <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menutup sesi kasir ini?')"
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl font-bold text-white bg-[#DC2626] hover:bg-[#b91c1c] text-sm shadow-sm transition">
                    Tutup Kasir & Finalisasi
                </button>
            </form>
        </div>
    @else
        <!-- Open Shift Form -->
        <div class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm max-w-lg mx-auto">
            <div class="text-center mb-6">
                <span class="w-12 h-12 rounded-2xl bg-[#FFF4EC] text-[#C2410C] inline-flex items-center justify-center font-bold text-2xl mb-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <h3 class="font-bold text-lg text-[#14110F]">Buka Sesi Kasir Baru</h3>
                <p class="text-xs text-[#575047] mt-1">Masukkan nominal modal kas awal (uang kembalian) di laci kasir.</p>
            </div>

            <form method="POST" action="{{ route('pos.shift.open') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="opening_float" class="block text-xs font-semibold text-[#575047] uppercase mb-1">Modal Kas Awal (Rp)</label>
                    <input type="number" id="opening_float" name="opening_float" value="100000" required min="0" autofocus
                           class="w-full text-center text-xl font-bold px-4 py-3 rounded-xl border border-[#D3C9BD] focus:ring-2 focus:ring-[#C2410C] bg-[#FAF8F5]">
                </div>

                <div class="flex gap-2">
                    <button type="button" onclick="document.getElementById('opening_float').value = 50000" class="flex-1 py-1.5 rounded-lg border border-[#D3C9BD] text-xs font-semibold hover:bg-[#FAF8F5]">50.000</button>
                    <button type="button" onclick="document.getElementById('opening_float').value = 100000" class="flex-1 py-1.5 rounded-lg border border-[#D3C9BD] text-xs font-semibold hover:bg-[#FAF8F5]">100.000</button>
                    <button type="button" onclick="document.getElementById('opening_float').value = 200000" class="flex-1 py-1.5 rounded-lg border border-[#D3C9BD] text-xs font-semibold hover:bg-[#FAF8F5]">200.000</button>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl font-bold text-white bg-[#C2410C] hover:bg-[#9A3412] text-sm shadow-md transition">
                    Mulai Sesi Kasir
                </button>
            </form>
        </div>
    @endif

    <!-- Past Shifts Table -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-sm">
        <h3 class="font-bold text-base text-[#14110F] mb-4">Riwayat Sesi Kasir Sebelumnya</h3>

        @if($pastSessions->isEmpty())
            <div class="p-8 text-center text-[#9E9385] text-xs bg-[#FAF8F5] rounded-xl border border-dashed border-[#D3C9BD]">
                Belum ada data sesi kasir terdahulu.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                        <tr>
                            <th class="px-3 py-2.5 rounded-l-lg">Dibuka Oleh</th>
                            <th class="px-3 py-2.5">Waktu Buka / Tutup</th>
                            <th class="px-3 py-2.5 text-right">Kas Awal</th>
                            <th class="px-3 py-2.5 text-right">Ekspektasi</th>
                            <th class="px-3 py-2.5 text-right">Fisik Laci</th>
                            <th class="px-3 py-2.5 text-right">Selisih</th>
                            <th class="px-3 py-2.5 rounded-r-lg">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E7E0D6]">
                        @foreach($pastSessions as $s)
                            <tr class="hover:bg-[#FAF8F5]">
                                <td class="px-3 py-3 font-semibold text-[#14110F]">{{ $s->openedByUser?->name }}</td>
                                <td class="px-3 py-3 text-xs text-[#575047]">
                                    {{ $s->opened_at->format('d/m H:i') }} - {{ $s->closed_at ? $s->closed_at->format('H:i') : '(Aktif)' }}
                                </td>
                                <td class="px-3 py-3 text-right tabular-nums">{{ \App\Support\Money::format($s->opening_float) }}</td>
                                <td class="px-3 py-3 text-right tabular-nums">{{ \App\Support\Money::format($s->expected_cash) }}</td>
                                <td class="px-3 py-3 text-right tabular-nums">{{ \App\Support\Money::format($s->counted_cash) }}</td>
                                <td class="px-3 py-3 text-right font-bold tabular-nums {{ ($s->difference ?? 0) < 0 ? 'text-[#DC2626]' : (($s->difference ?? 0) > 0 ? 'text-[#047857]' : 'text-[#575047]') }}">
                                    {{ \App\Support\Money::format($s->difference) }}
                                </td>
                                <td class="px-3 py-3 text-xs text-[#575047] max-w-xs truncate">{{ $s->note ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
