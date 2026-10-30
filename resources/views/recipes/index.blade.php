@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Resep &amp; Perhitungan HPP Otomatis</h1>
            <p class="text-sm text-[#575047] mt-0.5">Tautkan menu dengan bahan baku untuk kalkulasi Harga Pokok Penjualan (HPP) dan margin keuntungan secara otomatis.</p>
        </div>
        <a href="{{ route('menu.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#FAF8F5] border border-[#D3C9BD] text-[#14110F] hover:bg-[#F4F0EA] transition">
            <svg class="w-4 h-4 text-[#C2410C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            <span>Kelola Daftar Menu</span>
        </a>
    </div>

    <!-- Summary Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Metric 1: Coverage -->
        <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#575047] uppercase">Cakupan Resep Menu</span>
                <span class="w-8 h-8 rounded-xl bg-[#FFF4EC] text-[#C2410C] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-[#14110F] tabular-nums">{{ $metrics['with_recipe_count'] }} / {{ $metrics['total_menus'] }}</span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $metrics['with_recipe_count'] === $metrics['total_menus'] ? 'bg-[#ECFDF5] text-[#047857]' : 'bg-[#FFFBEB] text-[#B45309]' }}">
                    {{ $metrics['total_menus'] > 0 ? round(($metrics['with_recipe_count'] / $metrics['total_menus']) * 100) : 0 }}% Terhubung
                </span>
            </div>
            <p class="text-xs text-[#575047] mt-1">Menu aktif yang sudah memiliki formula bahan baku.</p>
        </div>

        <!-- Metric 2: Average Margin -->
        <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#575047] uppercase">Rata-rata Margin Outlet</span>
                <span class="w-8 h-8 rounded-xl bg-[#ECFDF5] text-[#047857] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-[#047857] tabular-nums">{{ $metrics['avg_margin'] }}%</span>
                <span class="text-xs text-[#575047]">laba kotor / porsi</span>
            </div>
            <p class="text-xs text-[#575047] mt-1">Standar ideal bisnis kuliner: 50% &ndash; 65%.</p>
        </div>

        <!-- Metric 3: Needs Recipe -->
        <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#575047] uppercase">Menu Perlu Resep</span>
                <span class="w-8 h-8 rounded-xl {{ $metrics['need_recipe_count'] > 0 ? 'bg-[#FEF2F2] text-[#DC2626]' : 'bg-[#FAF8F5] text-[#9E9385]' }} flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-bold {{ $metrics['need_recipe_count'] > 0 ? 'text-[#DC2626]' : 'text-[#047857]' }} tabular-nums">{{ $metrics['need_recipe_count'] }}</span>
                <span class="text-xs text-[#575047]">menu belum terhubung</span>
            </div>
            <p class="text-xs text-[#575047] mt-1">{{ $metrics['need_recipe_count'] === 0 ? 'Semua menu aktif telah terhubung resep!' : 'Segera hubungkan bahan agar HPP akurat.' }}</p>
        </div>
    </div>

    <!-- Menus Recipe Table -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-sm">
        @if($menus->isEmpty())
            <div class="p-12 text-center text-[#9E9385] text-sm">
                Belum ada menu yang dibuat. Tambahkan menu terlebih dahulu di halaman Menu &amp; Harga Master.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                        <tr>
                            <th class="px-3 py-2.5 rounded-l-lg">Nama Menu</th>
                            <th class="px-3 py-2.5">Kategori</th>
                            <th class="px-3 py-2.5 text-right">Harga Jual</th>
                            <th class="px-3 py-2.5">Komposisi Bahan Baku (per Porsi)</th>
                            <th class="px-3 py-2.5 text-right">Estimasi HPP &amp; Margin</th>
                            <th class="px-3 py-2.5 text-center rounded-r-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E7E0D6]">
                        @foreach($menus as $menu)
                            <tr class="hover:bg-[#FAF8F5] transition">
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($menu->image_path)
                                            <img src="{{ $menu->image_path }}" class="w-10 h-9 object-cover rounded-lg border border-[#E7E0D6] shrink-0" alt="{{ $menu->name }}">
                                        @else
                                            <div class="w-10 h-9 rounded-lg bg-[#FAF8F5] border border-[#E7E0D6] flex items-center justify-center text-[#9E9385] shrink-0">
                                                <svg class="w-4 h-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                        <div>
                                            <span class="font-bold text-[#14110F] block leading-tight">{{ $menu->name }}</span>
                                            @if($menu->description)
                                                <span class="text-xs text-[#575047] line-clamp-1 mt-0.5">{{ $menu->description }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="text-xs px-2.5 py-1 rounded-full bg-[#FAF8F5] border border-[#D3C9BD] font-medium text-[#575047]">
                                        {{ $menu->category?->name ?? 'Umum' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-right font-bold text-[#14110F] tabular-nums">
                                    {{ \App\Support\Money::format($menu->price) }}
                                </td>
                                <td class="px-3 py-3">
                                    @if($menu->recipe && $menu->recipe->items->isNotEmpty())
                                        <div class="flex flex-wrap gap-1 max-w-sm">
                                            @foreach($menu->recipe->items->take(3) as $rItem)
                                                <span class="inline-flex items-center text-[11px] px-2 py-0.5 rounded-md bg-[#FAF8F5] border border-[#E7E0D6] text-[#575047]" title="{{ $rItem->ingredient?->name }}: {{ (float)$rItem->qty_base }} {{ $rItem->ingredient?->base_unit }}">
                                                    <span class="font-medium text-[#14110F] mr-1 truncate max-w-[100px]">{{ $rItem->ingredient?->name }}</span>
                                                    <span class="text-[#9E9385] font-semibold">{{ (float)$rItem->qty_base }}{{ $rItem->ingredient?->base_unit }}</span>
                                                </span>
                                            @endforeach
                                            @if($menu->recipe->items->count() > 3)
                                                <span class="text-[10px] px-1.5 py-0.5 rounded-md bg-[#F4F0EA] text-[#575047] font-semibold self-center" title="Total {{ $menu->recipe->items->count() }} jenis bahan baku">
                                                    +{{ $menu->recipe->items->count() - 3 }} bahan
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-[#B45309] italic font-medium">Belum ada resep</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-right text-xs tabular-nums">
                                    @if(isset($menu->hpp_info) && $menu->hpp_info['hpp_complete'] && $menu->hpp_info['unit_hpp'] !== null)
                                        <div class="flex flex-col items-end gap-1">
                                            <span class="font-bold text-[#047857]">{{ \App\Support\Money::format($menu->hpp_info['unit_hpp']) }}</span>
                                            @if(isset($menu->margin_percent))
                                                <span class="inline-flex items-center text-[10px] font-bold px-1.5 py-0.5 rounded-md
                                                    @if($menu->margin_percent >= 60) bg-[#ECFDF5] text-[#047857] border border-[#A7F3D0]
                                                    @elseif($menu->margin_percent >= 40) bg-[#FFFBEB] text-[#B45309] border border-[#FDE68A]
                                                    @else bg-[#FEF2F2] text-[#DC2626] border border-[#FECACA]
                                                    @endif">
                                                    Margin {{ $menu->margin_percent }}%
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[#B45309] font-medium italic">Belum dapat dihitung</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <a href="{{ route('resep.edit', $menu) }}" class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1.5 rounded-lg bg-[#FFF4EC] text-[#C2410C] hover:bg-[#FFE6D5] transition">
                                        <span>Kelola Resep</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $menus->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
