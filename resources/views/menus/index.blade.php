@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showModal: false, isEdit: false, editForm: {} }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Menu & Harga Master</h1>
            <p class="text-sm text-[#575047] mt-0.5">Satu harga master berlaku untuk semua kanal (POS, GoFood, ShopeeFood).</p>
        </div>
        <button @click="showModal = true; isEdit = false; editForm = { category_id: '{{ $categories->first()?->id }}', name: '', price: '', description: '', is_active: 1 }"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold bg-[#C2410C] text-white hover:bg-[#9A3412] shadow-sm">
            <span>+ Tambah Menu Baru</span>
        </button>
    </div>

    <!-- Menus Table -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-sm">
        @if($menus->isEmpty())
            <div class="p-12 text-center text-[#9E9385] text-sm">
                Belum ada menu terdaftar. Klik tombol Tambah Menu Baru untuk membuat menu pertama.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                        <tr>
                            <th class="px-3 py-2.5 rounded-l-lg">Nama Menu</th>
                            <th class="px-3 py-2.5">Kategori</th>
                            <th class="px-3 py-2.5 text-right">Harga Jual</th>
                            <th class="px-3 py-2.5 text-right">Estimasi HPP</th>
                            <th class="px-3 py-2.5 text-center">Status</th>
                            <th class="px-3 py-2.5 text-center rounded-r-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E7E0D6]">
                        @foreach($menus as $menu)
                            <tr class="hover:bg-[#FAF8F5]">
                                <td class="px-3 py-3">
                                    <span class="font-bold text-[#14110F] block">{{ $menu->name }}</span>
                                    @if($menu->description)
                                        <span class="text-xs text-[#575047] line-clamp-1">{{ $menu->description }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-[#FAF8F5] border border-[#D3C9BD] font-medium text-[#575047]">
                                        {{ $menu->category?->name ?? 'Umum' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-right font-bold text-[#14110F] tabular-nums">
                                    {{ \App\Support\Money::format($menu->price) }}
                                </td>
                                <td class="px-3 py-3 text-right text-xs tabular-nums">
                                    @if($menu->hpp_info['hpp_complete'] && $menu->hpp_info['unit_hpp'] !== null)
                                        <span class="font-semibold text-[#047857]">{{ \App\Support\Money::format($menu->hpp_info['unit_hpp']) }}</span>
                                    @else
                                        <a href="{{ route('resep.edit', $menu) }}" class="text-[#B45309] hover:underline italic">
                                            Belum dapat dihitung
                                        </a>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $menu->is_active ? 'bg-[#ECFDF5] text-[#047857]' : 'bg-[#FEF2F2] text-[#DC2626]' }}">
                                        {{ $menu->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="{{ route('resep.edit', $menu) }}" class="text-xs font-semibold text-[#C2410C] hover:underline">
                                            Resep
                                        </a>
                                        <button @click="showModal = true; isEdit = true; editForm = { id: {{ $menu->id }}, category_id: '{{ $menu->category_id }}', name: '{{ addslashes($menu->name) }}', price: '{{ $menu->price }}', description: '{{ addslashes($menu->description ?? '') }}', is_active: {{ $menu->is_active ? 1 : 0 }} }"
                                                class="text-xs font-semibold text-[#575047] hover:underline">
                                            Edit
                                        </button>
                                    </div>
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

    <!-- Create / Edit Menu Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div @click.away="showModal = false" class="bg-white w-full max-w-lg rounded-2xl border border-[#E7E0D6] shadow-2xl p-6">
            <h3 class="text-lg font-bold text-[#14110F] mb-4" x-text="isEdit ? 'Edit Menu' : 'Tambah Menu Baru'"></h3>

            <form :action="isEdit ? '/menu/' + editForm.id : '{{ route('menu.store') }}'" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Nama Menu</label>
                    <input type="text" name="name" x-model="editForm.name" required
                           class="w-full px-3 py-2 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Kategori</label>
                        <select name="category_id" x-model="editForm.category_id" required
                                class="w-full px-3 py-2 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5]">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Harga Jual (Rp)</label>
                        <input type="number" name="price" x-model="editForm.price" required min="0"
                               class="w-full px-3 py-2 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Deskripsi Singkat</label>
                    <textarea name="description" x-model="editForm.description" rows="2"
                              class="w-full px-3 py-2 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5]"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Foto Menu (Opsional)</label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full text-xs text-[#575047] file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#FFF4EC] file:text-[#C2410C]">
                </div>

                <template x-if="isEdit">
                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1" :checked="editForm.is_active == 1" class="rounded border-[#D3C9BD] text-[#C2410C]">
                        <label for="is_active" class="text-xs font-semibold text-[#14110F]">Menu Aktif (Tersedia untuk dijual)</label>
                    </div>
                </template>

                <div class="flex justify-end gap-2 pt-3 border-t border-[#E7E0D6]">
                    <button @click="showModal = false" type="button" class="px-4 py-2 rounded-xl border border-[#D3C9BD] text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#C2410C] text-white text-xs font-bold hover:bg-[#9A3412]">Simpan Menu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
