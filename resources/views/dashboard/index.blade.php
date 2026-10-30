@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Dashboard Bisnis</h1>
            <p class="text-sm text-[#575047] mt-0.5">Ringkasan operasional dan keuangan hari ini ({{ now()->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}).</p>
        </div>

        <div class="flex items-center gap-2">
            @if(! $activeShift)
                <a href="{{ route('pos.shift.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-semibold bg-[#B45309] text-white hover:bg-[#92400E] transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    <span>Buka Sesi Kasir</span>
                </a>
            @endif
            <a href="{{ route('impor.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-semibold bg-white border border-[#D3C9BD] text-[#14110F] hover:bg-[#FAF8F5] transition shadow-sm">
                <svg class="w-4 h-4 text-[#C2410C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Impor Screenshot</span>
            </a>
            <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold bg-[#C2410C] text-white hover:bg-[#9A3412] transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Kasir Baru</span>
            </a>
        </div>
    </div>

    <!-- Active Shift Status Card -->
    @if($activeShift)
        <div class="p-4 rounded-2xl bg-white border border-[#E7E0D6] flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-3 h-3 rounded-full bg-[#047857] animate-pulse"></div>
                <div>
                    <span class="text-xs font-semibold text-[#047857] uppercase tracking-wider">Sesi Kasir Aktif</span>
                    <p class="text-sm font-medium text-[#14110F]">Dibuka oleh <strong class="font-semibold">{{ $activeShift->openedByUser?->name }}</strong> jam {{ $activeShift->opened_at->format('H:i') }} (Modal Kas: {{ \App\Support\Money::format($activeShift->opening_float) }})</p>
                </div>
            </div>
            <a href="{{ route('pos.shift.index') }}" class="text-sm font-semibold text-[#C2410C] hover:underline shrink-0">
                Kelola / Tutup Kasir &rarr;
            </a>
        </div>
    @endif

    <!-- Low Stock Alert Banner -->
    @if($lowStockIngredients->isNotEmpty())
        <div class="p-4 rounded-2xl bg-[#FFFBEB] border border-[#FDE68A] text-[#B45309] shadow-sm">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 shrink-0 mt-0.5 text-[#B45309]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold">Peringatan: {{ $lowStockIngredients->count() }} Bahan Baku Menipis</h3>
                    <div class="mt-1 flex flex-wrap gap-2 text-xs">
                        @foreach($lowStockIngredients as $ing)
                            <span class="px-2 py-0.5 rounded-md bg-white/80 border border-[#FDE68A] font-medium">
                                {{ $ing->name }}: <strong>{{ (float) $ing->stock_qty }} {{ $ing->base_unit }}</strong> (Min: {{ (float) $ing->min_stock }})
                            </span>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('stok.index') }}" class="text-xs font-semibold text-[#B45309] underline shrink-0 mt-0.5">
                    Catat Pembelian &rarr;
                </a>
            </div>
        </div>
    @endif

    <!-- Core KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Pendapatan Outlet -->
        <div class="bg-white p-5 rounded-2xl border border-[#E7E0D6] shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#575047]">Pendapatan Outlet</span>
            <div class="text-2xl font-bold tracking-tight text-[#14110F] mt-1 tabular-nums">
                {{ \App\Support\Money::format($summary['outlet_revenue']) }}
            </div>
            <div class="text-xs text-[#9E9385] mt-1">
                Omzet kotor: {{ \App\Support\Money::format($summary['gross_sales']) }}
            </div>
        </div>

        <!-- Laba Kotor Estimasi -->
        <div class="bg-white p-5 rounded-2xl border border-[#E7E0D6] shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#575047]">Laba Kotor Estimasi</span>
            <div class="text-2xl font-bold tracking-tight text-[#047857] mt-1 tabular-nums">
                {{ \App\Support\Money::format($summary['gross_profit']) }}
            </div>
            <div class="text-xs text-[#9E9385] mt-1">
                Margin: {{ $summary['margin_percent'] }}% (HPP: {{ \App\Support\Money::format($summary['realizedHpp'] ?? $summary['realized_hpp']) }})
            </div>
        </div>

        <!-- Total Transaksi -->
        <div class="bg-white p-5 rounded-2xl border border-[#E7E0D6] shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#575047]">Total Transaksi</span>
            <div class="text-2xl font-bold tracking-tight text-[#14110F] mt-1 tabular-nums">
                {{ $summary['transaction_count'] }} <span class="text-sm font-normal text-[#575047]">pesanan</span>
            </div>
            <div class="text-xs text-[#9E9385] mt-1">
                AOV: {{ \App\Support\Money::format($summary['aov']) }}
            </div>
        </div>

        <!-- HPP Coverage -->
        <div class="bg-white p-5 rounded-2xl border border-[#E7E0D6] shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-[#575047]">Cakupan HPP Resep</span>
            <div class="text-2xl font-bold tracking-tight text-[#C2410C] mt-1 tabular-nums">
                {{ $summary['hpp_coverage_percent'] }}%
            </div>
            <div class="text-xs text-[#9E9385] mt-1">
                {{ $summary['covered_items_count'] }} dari {{ $summary['total_items_count'] }} item terhitung
            </div>
        </div>
    </div>

    <!-- Grid Section: Channels & Top Menus -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sales per Channel -->
        <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-sm">
            <h3 class="text-base font-bold text-[#14110F] mb-4">Penjualan per Kanal</h3>
            <div class="space-y-4">
                @foreach($channelStats as $chKey => $chData)
                    <div class="p-3.5 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6]">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-semibold text-sm text-[#14110F]">{{ $chData['label'] }}</span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-white border border-[#D3C9BD] text-[#575047]">
                                {{ $chData['count'] }} tx
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-xs text-[#575047]">
                            <span>Pendapatan:</span>
                            <span class="font-semibold text-[#14110F]">{{ \App\Support\Money::format($chData['revenue']) }}</span>
                        </div>
                        @if($chData['fee'] > 0)
                            <div class="flex justify-between items-center text-xs text-[#DC2626] mt-0.5">
                                <span>Komisi platform:</span>
                                <span>-{{ \App\Support\Money::format($chData['fee']) }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Top Selling Menus -->
        <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-[#14110F]">Menu Terlaris Hari Ini</h3>
                <a href="{{ route('reports.index') }}" class="text-xs font-semibold text-[#C2410C] hover:underline">
                    Lihat Semua Laporan &rarr;
                </a>
            </div>

            @if(empty($topMenus))
                <div class="p-8 text-center text-[#9E9385] text-sm bg-[#FAF8F5] rounded-xl border border-dashed border-[#D3C9BD]">
                    Belum ada menu terjual hari ini. Mulai transaksi pertama melalui Kasir POS.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                            <tr>
                                <th class="px-3 py-2.5 rounded-l-lg">Nama Menu</th>
                                <th class="px-3 py-2.5 text-center">Terjual</th>
                                <th class="px-3 py-2.5 text-right rounded-r-lg">Total Penjualan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E7E0D6]">
                            @foreach($topMenus as $tm)
                                <tr class="hover:bg-[#FAF8F5]">
                                    <td class="px-3 py-3 font-semibold text-[#14110F]">{{ $tm['menu_name'] }}</td>
                                    <td class="px-3 py-3 text-center font-bold text-[#C2410C]">{{ $tm['total_qty'] }}</td>
                                    <td class="px-3 py-3 text-right font-medium text-[#14110F] tabular-nums">{{ \App\Support\Money::format($tm['total_sales']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-[#14110F]">Transaksi Terakhir</h3>
            <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-[#C2410C] hover:underline">
                Buka Riwayat Lengkap &rarr;
            </a>
        </div>

        @if($recentTransactions->isEmpty())
            <div class="p-8 text-center text-[#9E9385] text-sm bg-[#FAF8F5] rounded-xl border border-dashed border-[#D3C9BD]">
                Belum ada transaksi tersimpan.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                        <tr>
                            <th class="px-3 py-2.5 rounded-l-lg">No. Transaksi</th>
                            <th class="px-3 py-2.5">Waktu</th>
                            <th class="px-3 py-2.5">Kanal</th>
                            <th class="px-3 py-2.5">Metode</th>
                            <th class="px-3 py-2.5 text-right">Total</th>
                            <th class="px-3 py-2.5 text-center rounded-r-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E7E0D6]">
                        @foreach($recentTransactions as $tx)
                            <tr class="hover:bg-[#FAF8F5]">
                                <td class="px-3 py-3">
                                    <span class="font-bold text-[#14110F]">{{ $tx->number }}</span>
                                    @if($tx->isVoided())
                                        <span class="ml-2 text-[10px] px-2 py-0.5 rounded-full bg-[#FEF2F2] text-[#DC2626] font-bold">VOID</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-xs text-[#575047]">{{ $tx->occurred_at->format('H:i') }}</td>
                                <td class="px-3 py-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $tx->channel->value === 'pos' ? 'bg-[#FFF4EC] text-[#C2410C]' : 'bg-[#EFF6FF] text-[#2563EB]' }}">
                                        {{ $tx->channel->label() }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-xs font-medium">{{ $tx->payment_method->label() }}</td>
                                <td class="px-3 py-3 text-right font-bold text-[#14110F] tabular-nums">{{ \App\Support\Money::format($tx->total) }}</td>
                                <td class="px-3 py-3 text-center">
                                    <a href="{{ route('transactions.show', $tx) }}" class="text-xs font-semibold text-[#C2410C] hover:underline">
                                        Detail &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
