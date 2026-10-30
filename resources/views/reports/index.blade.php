@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'summary' }">
    <!-- Header & Period Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Laporan Bisnis & Keuangan</h1>
            <p class="text-sm text-[#575047] mt-0.5">Periode: {{ $start->translatedFormat('d M Y') }} &mdash; {{ $end->translatedFormat('d M Y') }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('reports.export', ['period' => $period, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#ECFDF5] text-[#047857] border border-[#A7F3D0] hover:bg-[#D1FAE5] transition shadow-2xs">
                <svg class="w-4 h-4 text-[#047857]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Unduh File Excel (.xlsx)</span>
            </a>
        </div>
    </div>

    <!-- Date Range Filter Strip -->
    <div class="bg-white p-4 rounded-2xl border border-[#E7E0D6] shadow-sm">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-1.5">
                <button type="submit" name="period" value="today"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold {{ $period === 'today' ? 'bg-[#C2410C] text-white' : 'bg-[#FAF8F5] text-[#575047] hover:bg-[#E7E0D6]' }}">
                    Hari Ini
                </button>
                <button type="submit" name="period" value="7_days"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold {{ $period === '7_days' ? 'bg-[#C2410C] text-white' : 'bg-[#FAF8F5] text-[#575047] hover:bg-[#E7E0D6]' }}">
                    7 Hari Terakhir
                </button>
                <button type="submit" name="period" value="30_days"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold {{ $period === '30_days' ? 'bg-[#C2410C] text-white' : 'bg-[#FAF8F5] text-[#575047] hover:bg-[#E7E0D6]' }}">
                    30 Hari Terakhir
                </button>
                <button type="submit" name="period" value="this_month"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold {{ $period === 'this_month' ? 'bg-[#C2410C] text-white' : 'bg-[#FAF8F5] text-[#575047] hover:bg-[#E7E0D6]' }}">
                    Bulan Ini
                </button>
            </div>

            <!-- Custom Date Range (Realtime - otomatis submit saat tanggal diubah) -->
            <div class="flex items-center gap-2">
                <input type="hidden" name="period" value="custom">
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       onchange="this.form.submit()"
                       class="text-xs px-2.5 py-1.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5] focus:outline-none focus:ring-2 focus:ring-[#C2410C]">
                <span class="text-xs text-[#575047]">s/d</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       onchange="this.form.submit()"
                       class="text-xs px-2.5 py-1.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5] focus:outline-none focus:ring-2 focus:ring-[#C2410C]">
            </div>
        </form>
    </div>

    <!-- Navigation Tabs for 6 Report Views -->
    <div class="border-b border-[#E7E0D6] flex space-x-6 text-sm font-semibold overflow-x-auto">
        <button @click="activeTab = 'summary'" :class="activeTab === 'summary' ? 'border-[#C2410C] text-[#C2410C]' : 'border-transparent text-[#575047] hover:text-[#14110F]'" class="py-3 border-b-2 whitespace-nowrap">
            1. Ringkasan Keuangan
        </button>
        <button @click="activeTab = 'channels'" :class="activeTab === 'channels' ? 'border-[#C2410C] text-[#C2410C]' : 'border-transparent text-[#575047] hover:text-[#14110F]'" class="py-3 border-b-2 whitespace-nowrap">
            2. Kanal Penjualan
        </button>
        <button @click="activeTab = 'menus'" :class="activeTab === 'menus' ? 'border-[#C2410C] text-[#C2410C]' : 'border-transparent text-[#575047] hover:text-[#14110F]'" class="py-3 border-b-2 whitespace-nowrap">
            3. Menu Terlaris
        </button>
        <button @click="activeTab = 'payments'" :class="activeTab === 'payments' ? 'border-[#C2410C] text-[#C2410C]' : 'border-transparent text-[#575047] hover:text-[#14110F]'" class="py-3 border-b-2 whitespace-nowrap">
            4. Metode Pembayaran
        </button>
        <button @click="activeTab = 'hpp'" :class="activeTab === 'hpp' ? 'border-[#C2410C] text-[#C2410C]' : 'border-transparent text-[#575047] hover:text-[#14110F]'" class="py-3 border-b-2 whitespace-nowrap">
            5. HPP & Laba Kotor
        </button>
        <button @click="activeTab = 'shifts'" :class="activeTab === 'shifts' ? 'border-[#C2410C] text-[#C2410C]' : 'border-transparent text-[#575047] hover:text-[#14110F]'" class="py-3 border-b-2 whitespace-nowrap">
            6. Sesi Kasir & Selisih
        </button>
    </div>

    <!-- Tab 1: Ringkasan Keuangan -->
    <div x-show="activeTab === 'summary'" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-[#E7E0D6] shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#575047]">Pendapatan Outlet</span>
                <div class="text-2xl font-bold text-[#14110F] mt-1 tabular-nums">{{ \App\Support\Money::format($summary['outlet_revenue']) }}</div>
                <div class="text-xs text-[#9E9385] mt-1">Omzet kotor: {{ \App\Support\Money::format($summary['gross_sales']) }}</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-[#E7E0D6] shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#575047]">HPP Terealisasi</span>
                <div class="text-2xl font-bold text-[#DC2626] mt-1 tabular-nums">{{ \App\Support\Money::format($summary['realized_hpp']) }}</div>
                <div class="text-xs text-[#9E9385] mt-1">Cakupan HPP: {{ $summary['hpp_coverage_percent'] }}% item</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-[#E7E0D6] shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#575047]">Laba Kotor Estimasi</span>
                <div class="text-2xl font-bold text-[#047857] mt-1 tabular-nums">{{ \App\Support\Money::format($summary['gross_profit']) }}</div>
                <div class="text-xs text-[#9E9385] mt-1">Margin Kotor: {{ $summary['margin_percent'] }}%</div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-[#E7E0D6] shadow-sm">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#575047]">Transaksi Selesai</span>
                <div class="text-2xl font-bold text-[#C2410C] mt-1 tabular-nums">{{ $summary['transaction_count'] }}</div>
                <div class="text-xs text-[#9E9385] mt-1">AOV: {{ \App\Support\Money::format($summary['aov']) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm space-y-3">
            <h3 class="font-bold text-base text-[#14110F] mb-4">Rincian Pos Keuangan</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between py-1 border-b border-[#FAF8F5]">
                    <span class="text-[#575047]">Omzet Kotor Penjualan (Gross Sales)</span>
                    <span class="font-semibold tabular-nums">{{ \App\Support\Money::format($summary['gross_sales']) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-[#FAF8F5]">
                    <span class="text-[#575047]">Pajak Restoran Terkumpul (Bukan Pendapatan)</span>
                    <span class="font-semibold tabular-nums">{{ \App\Support\Money::format($summary['tax_total']) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-[#FAF8F5]">
                    <span class="text-[#575047]">Biaya Layanan (Service Charge)</span>
                    <span class="font-semibold tabular-nums">{{ \App\Support\Money::format($summary['service_total']) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-[#FAF8F5]">
                    <span class="text-[#575047]">Selisih Pembulatan Tunai</span>
                    <span class="font-semibold tabular-nums">{{ \App\Support\Money::format($summary['rounding_total']) }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-[#FAF8F5]">
                    <span class="text-[#DC2626]">Estimasi Komisi Platform Online (GoFood / ShopeeFood)</span>
                    <span class="font-semibold text-[#DC2626] tabular-nums">-{{ \App\Support\Money::format($summary['platform_fee_total']) }}</span>
                </div>
                <div class="flex justify-between pt-2 font-bold text-base text-[#047857]">
                    <span>Pendapatan Bersih Estimasi (Setelah Komisi Platform)</span>
                    <span class="tabular-nums">{{ \App\Support\Money::format($summary['net_estimated_revenue']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 2: Kanal Penjualan -->
    <div x-show="activeTab === 'channels'" class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm">
        <h3 class="font-bold text-base text-[#14110F] mb-4">Performa Penjualan per Kanal</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($channels as $cKey => $cData)
                <div class="p-5 rounded-2xl bg-[#FAF8F5] border border-[#E7E0D6]">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-bold text-sm text-[#14110F]">{{ $cData['label'] }}</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-white border border-[#D3C9BD] font-bold text-[#575047]">{{ $cData['count'] }} Pesanan</span>
                    </div>
                    <div class="text-xl font-extrabold text-[#C2410C] tabular-nums mt-1">{{ \App\Support\Money::format($cData['revenue']) }}</div>
                    @if($cData['fee'] > 0)
                        <div class="text-xs text-[#DC2626] mt-2">Komisi platform: -{{ \App\Support\Money::format($cData['fee']) }}</div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Tab 3: Menu Terlaris -->
    <div x-show="activeTab === 'menus'" class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm">
        <h3 class="font-bold text-base text-[#14110F] mb-4">Penjualan per Item Menu</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                    <tr>
                        <th class="px-3 py-2.5 rounded-l-lg">Nama Menu</th>
                        <th class="px-3 py-2.5 text-center">Jumlah Transaksi</th>
                        <th class="px-3 py-2.5 text-center">Porsi Terjual</th>
                        <th class="px-3 py-2.5 text-right rounded-r-lg">Kontribusi Omzet</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E0D6]">
                    @foreach($menus as $m)
                        <tr class="hover:bg-[#FAF8F5]">
                            <td class="px-3 py-3 font-bold text-[#14110F]">{{ $m['menu_name'] }}</td>
                            <td class="px-3 py-3 text-center text-xs text-[#575047]">{{ $m['tx_count'] }}</td>
                            <td class="px-3 py-3 text-center font-bold text-[#C2410C]">{{ $m['total_qty'] }}</td>
                            <td class="px-3 py-3 text-right font-bold text-[#14110F] tabular-nums">{{ \App\Support\Money::format($m['total_sales']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 4: Metode Pembayaran -->
    <div x-show="activeTab === 'payments'" class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm">
        <h3 class="font-bold text-base text-[#14110F] mb-4">Rincian per Metode Pembayaran</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($paymentMethods as $pKey => $pData)
                <div class="p-4 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6]">
                    <span class="text-xs font-semibold text-[#575047] uppercase">{{ $pData['label'] }}</span>
                    <div class="text-xl font-bold text-[#14110F] mt-1 tabular-nums">{{ \App\Support\Money::format($pData['total']) }}</div>
                    <span class="text-xs text-[#9E9385] mt-1 block">{{ $pData['count'] }} transaksi</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Tab 5: HPP & Estimasi Laba Kotor -->
    <div x-show="activeTab === 'hpp'" class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm">
        <h3 class="font-bold text-base text-[#14110F] mb-4">Analisis HPP & Margin Laba Kotor per Menu</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                    <tr>
                        <th class="px-3 py-2.5 rounded-l-lg">Nama Menu</th>
                        <th class="px-3 py-2.5 text-center">Porsi</th>
                        <th class="px-3 py-2.5 text-right">Pendapatan</th>
                        <th class="px-3 py-2.5 text-right">Total HPP</th>
                        <th class="px-3 py-2.5 text-right">Laba Kotor</th>
                        <th class="px-3 py-2.5 text-right rounded-r-lg">Margin %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E0D6]">
                    @foreach($hppReport as $hr)
                        <tr class="hover:bg-[#FAF8F5]">
                            <td class="px-3 py-3 font-semibold text-[#14110F]">{{ $hr['menu_name'] }}</td>
                            <td class="px-3 py-3 text-center text-xs text-[#575047]">{{ $hr['total_qty'] }}</td>
                            <td class="px-3 py-3 text-right font-medium text-xs tabular-nums">{{ \App\Support\Money::format($hr['total_revenue']) }}</td>
                            <td class="px-3 py-3 text-right text-xs tabular-nums text-[#DC2626]">
                                {{ $hr['hpp_complete'] ? \App\Support\Money::format($hr['total_cost']) : 'Belum Lengkap' }}
                            </td>
                            <td class="px-3 py-3 text-right font-bold text-xs tabular-nums text-[#047857]">
                                {{ $hr['profit'] !== null ? \App\Support\Money::format($hr['profit']) : '-' }}
                            </td>
                            <td class="px-3 py-3 text-right font-bold text-xs tabular-nums">
                                {{ $hr['margin_percent'] !== null ? $hr['margin_percent'] . '%' : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 6: Sesi Kasir & Selisih -->
    <div x-show="activeTab === 'shifts'" class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm">
        <h3 class="font-bold text-base text-[#14110F] mb-4">Rekap Sesi Kasir & Audit Selisih Uang Laci</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                    <tr>
                        <th class="px-3 py-2.5 rounded-l-lg">Kasir</th>
                        <th class="px-3 py-2.5">Waktu</th>
                        <th class="px-3 py-2.5 text-right">Modal Awal</th>
                        <th class="px-3 py-2.5 text-right">Ekspektasi</th>
                        <th class="px-3 py-2.5 text-right">Uang Fisik</th>
                        <th class="px-3 py-2.5 text-right">Selisih</th>
                        <th class="px-3 py-2.5 rounded-r-lg">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E0D6]">
                    @foreach($cashSessions as $cs)
                        <tr class="hover:bg-[#FAF8F5]">
                            <td class="px-3 py-3 font-semibold text-[#14110F]">{{ $cs['opened_by_user']['name'] ?? 'Kasir' }}</td>
                            <td class="px-3 py-3 text-xs text-[#575047]">{{ \Illuminate\Support\Carbon::parse($cs['opened_at'])->format('d/m H:i') }}</td>
                            <td class="px-3 py-3 text-right tabular-nums text-xs">{{ \App\Support\Money::format($cs['opening_float']) }}</td>
                            <td class="px-3 py-3 text-right tabular-nums text-xs font-semibold">{{ \App\Support\Money::format($cs['expected_cash']) }}</td>
                            <td class="px-3 py-3 text-right tabular-nums text-xs font-semibold">{{ \App\Support\Money::format($cs['counted_cash']) }}</td>
                            <td class="px-3 py-3 text-right font-bold tabular-nums text-xs {{ ($cs['difference'] ?? 0) < 0 ? 'text-[#DC2626]' : (($cs['difference'] ?? 0) > 0 ? 'text-[#047857]' : 'text-[#575047]') }}">
                                {{ \App\Support\Money::format($cs['difference']) }}
                            </td>
                            <td class="px-3 py-3 text-xs text-[#575047] max-w-xs truncate">{{ $cs['note'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
