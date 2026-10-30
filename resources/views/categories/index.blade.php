@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Kategori Menu</h1>
            <p class="text-sm text-[#575047] mt-0.5">Kelola pengelompokan menu makanan dan minuman.</p>
        </div>
    </div>

    <!-- Create Category Card -->
    <div class="bg-white p-5 rounded-2xl border border-[#E7E0D6] shadow-sm">
        <h3 class="font-bold text-sm text-[#14110F] mb-3">Tambah Kategori Baru</h3>
        <form method="POST" action="{{ route('categories.store') }}" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <div class="flex-1">
                <input type="text" name="name" required placeholder="Nama kategori (e.g. Makanan Utama, Minuman, Cemilan)..."
                       class="w-full px-4 py-2.5 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5]">
            </div>
            <div class="w-28">
                <input type="number" name="sort_order" value="0" placeholder="Urutan"
                       class="w-full px-3 py-2.5 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5]">
            </div>
            <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-sm text-white bg-[#C2410C] hover:bg-[#9A3412] shadow-sm">
                Simpan
            </button>
        </form>
    </div>

    <!-- Category Table -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-sm">
        @if($categories->isEmpty())
            <div class="p-8 text-center text-[#9E9385] text-sm">
                Belum ada kategori menu. Buat kategori pertama di atas.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                        <tr>
                            <th class="px-3 py-2.5 rounded-l-lg">Urutan</th>
                            <th class="px-3 py-2.5">Nama Kategori</th>
                            <th class="px-3 py-2.5 text-center">Jumlah Menu</th>
                            <th class="px-3 py-2.5 text-center">Status</th>
                            <th class="px-3 py-2.5 text-right rounded-r-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E7E0D6]">
                        @foreach($categories as $cat)
                            <tr class="hover:bg-[#FAF8F5]">
                                <td class="px-3 py-3 text-xs font-bold text-[#575047]">{{ $cat->sort_order }}</td>
                                <td class="px-3 py-3 font-semibold text-[#14110F]">{{ $cat->name }}</td>
                                <td class="px-3 py-3 text-center">
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-[#FAF8F5] border border-[#D3C9BD] font-bold text-[#575047]">
                                        {{ $cat->menus_count }} menu
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $cat->is_active ? 'bg-[#ECFDF5] text-[#047857]' : 'bg-[#FEF2F2] text-[#DC2626]' }}">
                                        {{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <form method="POST" action="{{ route('categories.destroy', $cat) }}" class="inline" id="delete-cat-{{ $cat->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                onclick="window.showConfirm('Apakah Anda yakin ingin menghapus kategori \'{{ addslashes($cat->name) }}\'?', () => document.getElementById('delete-cat-{{ $cat->id }}').submit(), 'Hapus Kategori', 'Ya, Hapus')"
                                                class="text-xs font-semibold text-[#DC2626] hover:underline">
                                            Hapus
                                        </button>
                                    </form>
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
