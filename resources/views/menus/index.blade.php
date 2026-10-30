@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ showModal: false, isEdit: false, editForm: {}, deleteModal: { open: false, menuId: null, menuName: '' } }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Menu &amp; Harga Master</h1>
            <p class="text-sm text-[#575047] mt-0.5">Kelola hidangan, harga jual, dan formula resep dapur untuk semua kanal penjualan.</p>
        </div>
        <button @click="showModal = true; isEdit = false; editForm = { category_id: '{{ $categories->first()?->id }}', name: '', price: '', description: '', is_active: 1 }"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#C2410C] text-white hover:bg-[#9A3412] shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>+ Tambah Menu Baru</span>
        </button>
    </div>

    <!-- Menus Table -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-sm">
        @if($menus->isEmpty())
            <div class="p-12 text-center text-[#9E9385] text-sm">
                Belum ada menu terdaftar. Klik tombol <strong>+ Tambah Menu Baru</strong> untuk membuat menu pertama.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                        <tr>
                            <th class="px-3 py-2.5 rounded-l-lg">Hidangan / Menu</th>
                            <th class="px-3 py-2.5">Kategori</th>
                            <th class="px-3 py-2.5 text-right">Harga Jual</th>
                            <th class="px-3 py-2.5 text-right">Estimasi HPP</th>
                            <th class="px-3 py-2.5 text-center">Status</th>
                            <th class="px-3 py-2.5 text-center rounded-r-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E7E0D6]">
                        @foreach($menus as $menu)
                            <tr class="hover:bg-[#FAF8F5] transition">
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($menu->image_path)
                                            <img src="{{ $menu->image_path }}" class="w-12 h-10 object-cover rounded-lg border border-[#E7E0D6] shadow-2xs shrink-0" alt="{{ $menu->name }}">
                                        @else
                                            <div class="w-12 h-10 rounded-lg bg-[#FAF8F5] border border-[#E7E0D6] flex items-center justify-center text-[#9E9385] shrink-0">
                                                <svg class="w-5 h-5 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
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
                                <td class="px-3 py-3 text-right text-xs tabular-nums">
                                    @if(isset($menu->hpp_info) && $menu->hpp_info['hpp_complete'] && $menu->hpp_info['unit_hpp'] !== null)
                                        <span class="font-bold text-[#047857]">{{ \App\Support\Money::format($menu->hpp_info['unit_hpp']) }}</span>
                                    @else
                                        <a href="{{ route('resep.edit', $menu) }}" class="text-[#B45309] hover:underline italic font-medium">
                                            Atur Resep ↗
                                        </a>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold {{ $menu->is_active ? 'bg-[#ECFDF5] text-[#047857]' : 'bg-[#FEF2F2] text-[#DC2626]' }}">
                                        {{ $menu->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('resep.edit', $menu) }}" title="Atur Resep & HPP"
                                           class="text-xs font-semibold px-2.5 py-1 rounded-lg text-[#C2410C] bg-[#FFF4EC] hover:bg-[#FFE6D5] transition">
                                            Resep
                                        </a>
                                        <button @click="showModal = true; isEdit = true; editForm = { id: {{ $menu->id }}, category_id: '{{ $menu->category_id }}', name: '{{ addslashes($menu->name) }}', price: '{{ $menu->price }}', description: '{{ addslashes($menu->description ?? '') }}', is_active: {{ $menu->is_active ? 1 : 0 }} }"
                                                class="text-xs font-semibold px-2.5 py-1 rounded-lg text-[#575047] bg-[#FAF8F5] border border-[#D3C9BD] hover:bg-[#F4F0EA] transition">
                                            Edit
                                        </button>
                                        <button type="button"
                                                @click="deleteModal.open = true; deleteModal.menuId = {{ $menu->id }}; deleteModal.menuName = '{{ addslashes($menu->name) }}'"
                                                class="text-xs font-semibold px-2.5 py-1 rounded-lg text-[#DC2626] bg-[#FEF2F2] hover:bg-[#FEE2E2] transition">
                                            Hapus
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
                           class="w-full px-3.5 py-2.5 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5] focus:outline-none focus:ring-2 focus:ring-[#C2410C]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Kategori</label>
                        <select name="category_id" x-model="editForm.category_id" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5] focus:outline-none focus:ring-2 focus:ring-[#C2410C]">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Harga Jual (Rp)</label>
                        <input type="number" name="price" x-model="editForm.price" required min="0" step="500"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5] focus:outline-none focus:ring-2 focus:ring-[#C2410C]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Deskripsi Singkat</label>
                    <textarea name="description" x-model="editForm.description" rows="2"
                              class="w-full px-3.5 py-2.5 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5] focus:outline-none focus:ring-2 focus:ring-[#C2410C]"
                              placeholder="Keterangan rasa, porsi, atau keunikan hidangan..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Upload Foto Menu Baru (Opsional)</label>
                    <input type="file" name="image" accept="image/*"
                           class="w-full text-xs text-[#575047] file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#FFF4EC] file:text-[#C2410C] hover:file:bg-[#FFE6D5] cursor-pointer">
                </div>

                <template x-if="isEdit">
                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1" :checked="editForm.is_active == 1" class="rounded border-[#D3C9BD] text-[#C2410C] focus:ring-[#C2410C]">
                        <label for="is_active" class="text-xs font-semibold text-[#14110F]">Menu Aktif (Tersedia untuk dijual di kasir)</label>
                    </div>
                </template>

                <div class="flex justify-end gap-2 pt-3 border-t border-[#E7E0D6]">
                    <button @click="showModal = false" type="button" class="px-4 py-2 rounded-xl border border-[#D3C9BD] text-xs font-semibold text-[#575047] hover:bg-[#FAF8F5]">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#C2410C] text-white text-xs font-bold hover:bg-[#9A3412] shadow-sm">Simpan Menu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete / Deactivate Confirmation Modal (Custom Tailwind) -->
    <div x-show="deleteModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div @click.away="deleteModal.open = false" class="bg-white w-full max-w-md rounded-2xl border border-[#E7E0D6] shadow-2xl p-6">
            <div class="flex items-center gap-3 text-[#DC2626] mb-3">
                <span class="w-10 h-10 rounded-xl bg-[#FEF2F2] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </span>
                <div>
                    <h3 class="text-base font-bold text-[#14110F]">Hapus / Nonaktifkan Menu</h3>
                    <p class="text-xs text-[#575047]">Tindakan ini akan menonaktifkan menu dari kasir.</p>
                </div>
            </div>
            <p class="text-xs text-[#575047] leading-relaxed mb-6">
                Apakah Anda yakin ingin menonaktifkan menu <strong class="text-[#14110F]" x-text="deleteModal.menuName"></strong>? Menu ini tidak akan lagi muncul pada katalog kasir POS.
            </p>
            <form :action="'/menu/' + deleteModal.menuId" method="POST" class="flex justify-end gap-2">
                @csrf
                @method('DELETE')
                <button @click="deleteModal.open = false" type="button" class="px-4 py-2 rounded-xl border border-[#D3C9BD] text-xs font-semibold text-[#575047] hover:bg-[#FAF8F5]">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#B91C1C]">Ya, Hapus Menu</button>
            </form>
        </div>
    </div>
</div>
@endsection
