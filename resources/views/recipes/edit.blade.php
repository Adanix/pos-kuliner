@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="recipeEditor()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('resep.index') }}" class="text-xs font-semibold text-[#575047] hover:underline">&larr; Kembali ke Daftar Resep</a>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F] mt-1">Resep: {{ $menu->name }}</h1>
            <p class="text-sm text-[#575047]">Harga Jual: <strong class="text-[#14110F] font-bold">{{ \App\Support\Money::format($menu->price) }}</strong></p>
        </div>

        <!-- Calculated HPP Display Card -->
        <div class="p-3.5 rounded-2xl bg-[#FFF4EC] border border-[#FFE6D5] text-right">
            <span class="text-xs text-[#575047] uppercase font-semibold">Estimasi HPP per Porsi</span>
            <div class="text-xl font-extrabold text-[#047857] tabular-nums" x-text="formatRupiah(estimatedTotalHpp)"></div>
        </div>
    </div>

    <!-- Recipe Items Form -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm">
        <form method="POST" action="{{ route('resep.update', $menu) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="flex items-center justify-between">
                <h3 class="font-bold text-sm text-[#14110F]">Komposisi Bahan Baku (per Porsi)</h3>
                <button @click="addItem()" type="button" class="text-xs font-bold text-[#C2410C] hover:underline flex items-center gap-1">
                    <span>+ Tambah Baris Bahan</span>
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(row, idx) in items" :key="idx">
                    <div class="p-3 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6] flex flex-col sm:flex-row items-start sm:items-center gap-3">
                        <!-- Ingredient Selection -->
                        <div class="flex-1 w-full sm:w-auto">
                            <label class="block text-[10px] font-semibold text-[#575047] uppercase mb-1">Bahan Baku</label>
                            <select :name="'items[' + idx + '][ingredient_id]'" x-model="row.ingredient_id" @change="onIngredientChange(idx)" required
                                    class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-white">
                                <option value="">-- Pilih Bahan Baku --</option>
                                <template x-for="ing in allIngredients" :key="ing.id">
                                    <option :value="ing.id" x-text="ing.name + ' (' + ing.base_unit + ')'" :selected="row.ingredient_id == ing.id"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Qty Base -->
                        <div class="w-full sm:w-36">
                            <label class="block text-[10px] font-semibold text-[#575047] uppercase mb-1">
                                Takaran (<span x-text="row.unit || '-'"></span>)
                            </label>
                            <input type="number" step="0.0001" min="0.0001" :name="'items[' + idx + '][qty_base]'" x-model.number="row.qty_base" required
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-[#D3C9BD] bg-white tabular-nums">
                        </div>

                        <!-- Unit Cost & Subtotal Info -->
                        <div class="w-full sm:w-36 text-right">
                            <span class="block text-[10px] text-[#575047] uppercase">Biaya Bahan</span>
                            <span class="font-bold text-xs text-[#14110F] tabular-nums" x-text="formatRupiah(row.qty_base * row.avg_cost)"></span>
                        </div>

                        <!-- Remove Button -->
                        <div class="pt-5 sm:pt-4">
                            <button @click="removeItem(idx)" type="button" class="p-1.5 text-[#DC2626] hover:bg-[#FEF2F2] rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </template>

                <div x-show="items.length === 0" class="p-8 text-center text-[#9E9385] text-xs bg-[#FAF8F5] rounded-xl border border-dashed border-[#D3C9BD]">
                    Belum ada bahan baku di resep ini. Klik "+ Tambah Baris Bahan" di atas.
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Catatan Resep (Opsional)</label>
                <textarea name="notes" rows="2" class="w-full p-2.5 rounded-xl border border-[#D3C9BD] text-xs bg-[#FAF8F5]">{{ $menu->recipe?->notes }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-[#E7E0D6]">
                <a href="{{ route('resep.index') }}" class="px-4 py-2.5 rounded-xl border border-[#D3C9BD] text-xs font-semibold">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#C2410C] text-white text-xs font-bold hover:bg-[#9A3412] shadow-sm">
                    Simpan Resep & HPP
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function recipeEditor() {
    return {
        allIngredients: @json($allIngredients),
        items: [
            @if($menu->recipe && $menu->recipe->items->isNotEmpty())
                @foreach($menu->recipe->items as $rItem)
                {
                    ingredient_id: {{ $rItem->ingredient_id }},
                    qty_base: {{ (float) $rItem->qty_base }},
                    unit: '{{ $rItem->ingredient?->base_unit }}',
                    avg_cost: {{ (float) ($rItem->ingredient?->avg_cost ?? 0) }},
                },
                @endforeach
            @endif
        ],

        addItem() {
            this.items.push({
                ingredient_id: '',
                qty_base: 1,
                unit: '-',
                avg_cost: 0,
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        onIngredientChange(index) {
            const row = this.items[index];
            const ing = this.allIngredients.find(i => i.id == row.ingredient_id);
            if (ing) {
                row.unit = ing.base_unit;
                row.avg_cost = parseFloat(ing.avg_cost) || 0;
            } else {
                row.unit = '-';
                row.avg_cost = 0;
            }
        },

        get estimatedTotalHpp() {
            return this.items.reduce((sum, item) => sum + ((parseFloat(item.qty_base) || 0) * (parseFloat(item.avg_cost) || 0)), 0);
        },

        formatRupiah(num) {
            if (!num) return 'Rp 0';
            const val = Math.round(num);
            return 'Rp ' + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
    };
}
</script>
@endpush
@endsection
