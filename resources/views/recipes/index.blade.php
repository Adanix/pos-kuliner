@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Resep & Perhitungan HPP</h1>
            <p class="text-sm text-[#575047] mt-0.5">Tautkan menu dengan komposisi bahan baku untuk menghitung Harga Pokok Penjualan (HPP) otomatis.</p>
        </div>
    </div>

    <!-- Menus Recipe Table -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-sm">
        @if($menus->isEmpty())
            <div class="p-12 text-center text-[#9E9385] text-sm">
                Belum ada menu yang dibuat. Tambahkan menu terlebih dahulu.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                        <tr>
                            <th class="px-3 py-2.5 rounded-l-lg">Nama Menu</th>
                            <th class="px-3 py-2.5">Kategori</th>
                            <th class="px-3 py-2.5 text-right">Harga Jual</th>
                            <th class="px-3 py-2.5 text-center">Komposisi Bahan</th>
                            <th class="px-3 py-2.5 text-right">Estimasi HPP</th>
                            <th class="px-3 py-2.5 text-center rounded-r-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E7E0D6]">
                        @foreach($menus as $menu)
                            <tr class="hover:bg-[#FAF8F5]">
                                <td class="px-3 py-3 font-bold text-[#14110F]">{{ $menu->name }}</td>
                                <td class="px-3 py-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-[#FAF8F5] border border-[#D3C9BD] font-medium text-[#575047]">
                                        {{ $menu->category?->name ?? 'Umum' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-right font-semibold text-[#14110F] tabular-nums">
                                    {{ \App\Support\Money::format($menu->price) }}
                                </td>
                                <td class="px-3 py-3 text-center text-xs text-[#575047]">
                                    {{ $menu->recipe ? $menu->recipe->items->count() . ' bahan' : 'Belum ada resep' }}
                                </td>
                                <td class="px-3 py-3 text-right text-xs tabular-nums">
                                    @if($menu->hpp_info['hpp_complete'] && $menu->hpp_info['unit_hpp'] !== null)
                                        <span class="font-bold text-[#047857]">{{ \App\Support\Money::format($menu->hpp_info['unit_hpp']) }}</span>
                                    @else
                                        <span class="text-[#B45309] font-medium italic">Belum dapat dihitung</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <a href="{{ route('resep.edit', $menu) }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#C2410C] hover:underline">
                                        <span>Kelola Resep</span> &rarr;
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
