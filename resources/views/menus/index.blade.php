@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    showModal: false,
    isEdit: false,
    editForm: {
        id: null,
        category_id: '{{ $categories->first()?->id }}',
        name: '',
        price: '',
        description: '',
        is_active: 1,
        recipe_items: []
    },
    allIngredients: {{ Js::from($allIngredients->map(fn($i) => [
        'id' => $i->id,
        'name' => $i->name,
        'base_unit' => $i->base_unit,
        'avg_cost' => (float)$i->avg_cost
    ])) }},
    deleteModal: { open: false, menuId: null, menuName: '' },

    openCreate() {
        this.isEdit = false;
        this.editForm = {
            id: null,
            category_id: '{{ $categories->first()?->id }}',
            name: '',
            price: '',
            description: '',
            is_active: 1,
            recipe_items: []
        };
        this.showModal = true;
    },

    openEdit(menuData) {
        this.isEdit = true;
        this.editForm = {
            id: menuData.id,
            category_id: menuData.category_id,
            name: menuData.name,
            price: menuData.price,
            description: menuData.description || '',
            is_active: menuData.is_active ? 1 : 0,
            recipe_items: (menuData.recipe_items || []).map(item => ({
                ingredient_id: item.ingredient_id,
                qty_base: parseFloat(item.qty_base) || 1
            }))
        };
        this.showModal = true;
    },

    addRecipeItem() {
        if (this.allIngredients.length === 0) return;
        this.editForm.recipe_items.push({
            ingredient_id: this.allIngredients[0].id,
            qty_base: 1
        });
    },

    removeRecipeItem(index) {
        this.editForm.recipe_items.splice(index, 1);
    },

    getIngredient(id) {
        return this.allIngredients.find(i => i.id == id);
    },

    getLineCost(item) {
        const ing = this.getIngredient(item.ingredient_id);
        if (!ing) return 0;
        return (parseFloat(item.qty_base) || 0) * (parseFloat(ing.avg_cost) || 0);
    },

    calculateHpp() {
        if (!this.editForm.recipe_items || this.editForm.recipe_items.length === 0) return 0;
        return this.editForm.recipe_items.reduce((sum, item) => sum + this.getLineCost(item), 0);
    },

    calculateGrossProfit() {
        const price = parseFloat(this.editForm.price) || 0;
        const hpp = this.calculateHpp();
        return Math.max(0, price - hpp);
    },

    calculateMarginPercent() {
        const price = parseFloat(this.editForm.price) || 0;
        const hpp = this.calculateHpp();
        if (price <= 0 || hpp <= 0) return null;
        const profit = price - hpp;
        return ((profit / price) * 100).toFixed(1);
    },

    formatRupiah(num) {
        if (!num || isNaN(num)) return 'Rp 0';
        const val = Math.round(num);
        return 'Rp ' + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Menu &amp; Harga Master</h1>
            <p class="text-sm text-[#575047] mt-0.5">Kelola hidangan, harga jual, dan formula resep dapur untuk menghitung estimasi HPP otomatis.</p>
        </div>
        <button @click="openCreate()"
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
                            <th class="px-3 py-2.5 text-right">Estimasi HPP &amp; Margin</th>
                            <th class="px-3 py-2.5 text-center">Status</th>
                            <th class="px-3 py-2.5 text-center rounded-r-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E7E0D6]">
                        @foreach($menus as $menu)
                            @php
                                $menuData = [
                                    'id' => $menu->id,
                                    'category_id' => $menu->category_id,
                                    'name' => $menu->name,
                                    'price' => $menu->price,
                                    'description' => $menu->description ?? '',
                                    'is_active' => $menu->is_active ? 1 : 0,
                                    'recipe_items' => $menu->recipe ? $menu->recipe->items->map(fn($item) => [
                                        'ingredient_id' => $item->ingredient_id,
                                        'qty_base' => (float)$item->qty_base
                                    ])->values() : []
                                ];
                            @endphp
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
                                        <button type="button" @click="openEdit({{ Js::from($menuData) }})"
                                                class="inline-flex items-center gap-1 text-xs font-semibold text-[#B45309] hover:text-[#92400E] bg-[#FFFBEB] hover:bg-[#FEF3C7] px-2.5 py-1 rounded-lg border border-[#FDE68A] transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            <span>Atur Resep</span>
                                        </button>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold {{ $menu->is_active ? 'bg-[#ECFDF5] text-[#047857]' : 'bg-[#FEF2F2] text-[#DC2626]' }}">
                                        {{ $menu->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button @click="openEdit({{ Js::from($menuData) }})"
                                                class="text-xs font-semibold px-2.5 py-1 rounded-lg text-[#C2410C] bg-[#FFF4EC] hover:bg-[#FFE6D5] transition">
                                            Edit &amp; Resep
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

    <!-- Create / Edit Menu Modal with Integrated Recipe Builder -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs overflow-y-auto">
        <div @click.away="showModal = false" class="bg-white w-full max-w-2xl rounded-2xl border border-[#E7E0D6] shadow-2xl p-6 my-8 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between pb-3 border-b border-[#E7E0D6] mb-4">
                <div>
                    <h3 class="text-lg font-bold text-[#14110F]" x-text="isEdit ? 'Edit Menu & Formula Resep' : 'Tambah Menu Baru & Formula Resep'"></h3>
                    <p class="text-xs text-[#575047] mt-0.5">Informasi hidangan dan formula bahan baku untuk estimasi HPP otomatis.</p>
                </div>
                <button type="button" @click="showModal = false" class="p-1 rounded-lg text-[#9E9385] hover:text-[#14110F] hover:bg-[#FAF8F5]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="isEdit ? '/menu/' + editForm.id : '{{ route('menu.store') }}'" method="POST" enctype="multipart/form-data" class="space-y-5 overflow-y-auto pr-1">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <input type="hidden" name="has_recipe_builder" value="1">

                <!-- Basic Info Grid -->
                <div class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Nama Menu</label>
                        <input type="text" name="name" x-model="editForm.name" required
                               placeholder="Contoh: Es Teh Tawar Melati"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5] focus:outline-none focus:ring-2 focus:ring-[#C2410C]">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                            <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Harga Jual Kasir (Rp)</label>
                            <input type="number" name="price" x-model="editForm.price" required min="0" step="500"
                                   placeholder="15000"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5] focus:outline-none focus:ring-2 focus:ring-[#C2410C]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Deskripsi Singkat (Opsional)</label>
                        <textarea name="description" x-model="editForm.description" rows="2"
                                  class="w-full px-3.5 py-2 rounded-xl border border-[#D3C9BD] text-sm bg-[#FAF8F5] focus:outline-none focus:ring-2 focus:ring-[#C2410C]"
                                  placeholder="Keterangan rasa, porsi, atau keunikan hidangan..."></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Upload Foto Menu (Opsional)</label>
                        <input type="file" name="image" accept="image/*"
                               class="w-full text-xs text-[#575047] file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#FFF4EC] file:text-[#C2410C] hover:file:bg-[#FFE6D5] cursor-pointer">
                    </div>
                </div>

                <!-- Recipe / Bahan Baku Section -->
                <div class="pt-4 border-t border-[#E7E0D6] space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-[#14110F] uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#C2410C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                <span>Formula Bahan Baku &amp; Estimasi HPP</span>
                            </h4>
                            <p class="text-[11px] text-[#575047] mt-0.5">Tentukan bahan per 1 porsi hidangan untuk kalkulasi biaya modal otomatis.</p>
                        </div>
                        <button type="button" @click="addRecipeItem()"
                                class="inline-flex items-center gap-1 text-xs font-bold text-[#C2410C] hover:text-[#9A3412] bg-[#FFF4EC] hover:bg-[#FFE6D5] px-2.5 py-1.5 rounded-lg border border-[#FFE6D5] transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>+ Tambah Bahan</span>
                        </button>
                    </div>

                    <!-- Recipe Items List -->
                    <div class="space-y-2.5">
                        <template x-for="(item, index) in editForm.recipe_items" :key="index">
                            <div class="p-2.5 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6] flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                                <!-- Ingredient select -->
                                <div class="flex-1">
                                    <label class="block text-[10px] font-semibold text-[#575047] uppercase mb-0.5">Bahan Baku</label>
                                    <select :name="'recipe_items[' + index + '][ingredient_id]'" x-model="item.ingredient_id" required
                                            class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-[#D3C9BD] bg-white focus:outline-none focus:ring-1 focus:ring-[#C2410C]">
                                        <template x-for="ing in allIngredients" :key="ing.id">
                                            <option :value="ing.id" :selected="item.ingredient_id == ing.id"
                                                    x-text="ing.name + ' (' + ing.base_unit + ') • ' + formatRupiah(ing.avg_cost) + '/' + ing.base_unit"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Qty base input -->
                                <div class="w-full sm:w-28">
                                    <label class="block text-[10px] font-semibold text-[#575047] uppercase mb-0.5">
                                        Takaran (<span x-text="getIngredient(item.ingredient_id)?.base_unit || '-'"></span>)
                                    </label>
                                    <input type="number" step="0.0001" min="0.0001" :name="'recipe_items[' + index + '][qty_base]'" x-model.number="item.qty_base" required
                                           placeholder="1"
                                           class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-[#D3C9BD] bg-white tabular-nums focus:outline-none focus:ring-1 focus:ring-[#C2410C]">
                                </div>

                                <!-- Subtotal line cost -->
                                <div class="w-full sm:w-28 text-right sm:self-center">
                                    <span class="block text-[10px] text-[#575047] uppercase">Biaya Porsi</span>
                                    <span class="font-bold text-xs text-[#14110F] tabular-nums" x-text="formatRupiah(getLineCost(item))"></span>
                                </div>

                                <!-- Delete button -->
                                <div class="sm:self-center pt-1 sm:pt-4">
                                    <button type="button" @click="removeRecipeItem(index)" class="p-1.5 text-[#DC2626] hover:bg-[#FEF2F2] rounded-lg transition" title="Hapus bahan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div x-show="editForm.recipe_items.length === 0" class="p-4 text-center text-[#9E9385] text-xs bg-[#FAF8F5] rounded-xl border border-dashed border-[#D3C9BD]">
                            Belum ada bahan baku ditautkan. Klik <button type="button" @click="addRecipeItem()" class="text-[#C2410C] font-semibold underline">+ Tambah Bahan</button> untuk menghubungkan bahan baku dan menghitung HPP secara otomatis.
                        </div>
                    </div>

                    <!-- Live Realtime Calculation Banner -->
                    <div class="p-3.5 rounded-xl bg-gradient-to-r from-[#FAF8F5] to-[#F4F0EA] border border-[#E7E0D6] grid grid-cols-3 gap-2 text-center">
                        <div>
                            <span class="block text-[10px] uppercase font-semibold text-[#575047]">Estimasi HPP</span>
                            <span class="text-sm font-bold text-[#047857] tabular-nums" x-text="formatRupiah(calculateHpp())"></span>
                        </div>
                        <div>
                            <span class="block text-[10px] uppercase font-semibold text-[#575047]">Laba Kotor / Porsi</span>
                            <span class="text-sm font-bold text-[#14110F] tabular-nums" x-text="formatRupiah(calculateGrossProfit())"></span>
                        </div>
                        <div>
                            <span class="block text-[10px] uppercase font-semibold text-[#575047]">Margin Keuntungan</span>
                            <span class="text-xs font-bold inline-block px-2 py-0.5 rounded-full mt-0.5"
                                  :class="{
                                      'bg-[#ECFDF5] text-[#047857] border border-[#A7F3D0]': calculateMarginPercent() >= 60,
                                      'bg-[#FFFBEB] text-[#B45309] border border-[#FDE68A]': calculateMarginPercent() >= 40 && calculateMarginPercent() < 60,
                                      'bg-[#FEF2F2] text-[#DC2626] border border-[#FECACA]': calculateMarginPercent() !== null && calculateMarginPercent() < 40,
                                      'bg-[#F4F0EA] text-[#9E9385]': calculateMarginPercent() === null
                                  }"
                                  x-text="calculateMarginPercent() !== null ? calculateMarginPercent() + '%' : '-'">
                            </span>
                        </div>
                    </div>
                </div>

                <template x-if="isEdit">
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="is_active" name="is_active" value="1" :checked="editForm.is_active == 1" class="rounded border-[#D3C9BD] text-[#C2410C] focus:ring-[#C2410C]">
                        <label for="is_active" class="text-xs font-semibold text-[#14110F]">Menu Aktif (Tersedia untuk dijual di kasir)</label>
                    </div>
                </template>

                <div class="flex justify-end gap-2 pt-4 border-t border-[#E7E0D6]">
                    <button @click="showModal = false" type="button" class="px-4 py-2 rounded-xl border border-[#D3C9BD] text-xs font-semibold text-[#575047] hover:bg-[#FAF8F5]">Batal</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#C2410C] text-white text-xs font-bold hover:bg-[#9A3412] shadow-sm">Simpan Menu &amp; Resep</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete / Deactivate Confirmation Modal -->
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
