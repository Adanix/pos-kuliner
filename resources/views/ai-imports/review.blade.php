@extends('layouts.app')

@section('content')
@php
    $extracted = $aiImport->normalized_extracted;
    $items = $extracted['items'] ?? [];
    $flags = $extracted['review_flags'] ?? [];
    $warnings = $extracted['similarity_warnings'] ?? [];
    $currentChannel = strtolower($extracted['channel'] ?? ($aiImport->channel_detected ?? $aiImport->channel_selected ?? 'gofood'));
    $isSaved = $aiImport->status->value === 'saved';
@endphp

<div class="space-y-6" x-data="aiReviewEditor({{ json_encode($items) }}, {{ json_encode($allMenus) }}, '{{ $currentChannel }}', {{ (int) ($extracted['visible_discount'] ?? 0) }})">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('impor.index') }}" class="text-xs font-semibold text-[#575047] hover:underline inline-flex items-center gap-1">
                <span>&larr;</span> Kembali ke Daftar Impor
            </a>
            <div class="flex items-center gap-2 mt-1">
                <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Review Ekstraksi AI #{{ $aiImport->id }}</h1>
                @php
                    $statusClass = match($aiImport->status->value) {
                        'saved' => 'bg-[#ECFDF5] text-[#047857] border-[#A7F3D0]',
                        'needs_review', 'ready' => 'bg-[#FFFBEB] text-[#B45309] border-[#FDE68A]',
                        'duplicate' => 'bg-[#FFF4EC] text-[#C2410C] border-[#FFE6D5]',
                        'failed' => 'bg-[#FEF2F2] text-[#DC2626] border-[#FECACA]',
                        default => 'bg-[#FAF8F5] text-[#575047] border-[#D3C9BD]',
                    };
                @endphp
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold border {{ $statusClass }}">
                    {{ $aiImport->status->label() }}
                </span>
            </div>
            <p class="text-xs text-[#575047] mt-0.5">Periksa kesesuaian menu struk dengan katalog master outlet sebelum menyimpan sebagai transaksi resmi.</p>
        </div>

        <div class="flex items-center gap-2">
            @if($isSaved && $aiImport->transaction)
                <a href="{{ route('transactions.show', $aiImport->transaction) }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-[#047857] hover:bg-[#065f46] text-white shadow-sm flex items-center gap-1.5 transition">
                    <span>Lihat Transaksi Tersimpan</span>
                    <span>&rarr;</span>
                </a>
            @else
                <!-- Tombol Ekstrak Ulang dengan Ciora AI -->
                <form method="POST" action="{{ route('impor.retry', $aiImport) }}" onsubmit="return confirm('Proses ulang gambar struk ini dengan Ciora AI?')">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-[#FFF4EC] text-[#C2410C] hover:bg-[#FFE6D5] border border-[#FFE6D5] shadow-2xs flex items-center gap-1.5 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Ekstrak Ulang AI</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Error Banner if Import Failed -->
    @if($aiImport->status->value === 'failed')
        <div class="p-4 rounded-2xl bg-[#FEF2F2] border border-[#FECACA] text-[#DC2626] text-xs space-y-1">
            <div class="flex items-center gap-2 font-bold text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Ekstraksi AI Mengalami Kendala</span>
            </div>
            <p>{{ $aiImport->error_message ?: 'AI Vision tidak dapat menyelesaikan ekstraksi teks pada struk ini secara otomatis.' }}</p>
            <p class="text-[11px] text-[#786E64] mt-2">
                Anda dapat mengklik tombol <strong>"Ekstrak Ulang AI"</strong> di atas, atau mengisi daftar menu dan ID pesanan secara manual melalui formulir di bawah.
            </p>
        </div>
    @endif

    <!-- Similarity Warnings -->
    @if(!empty($warnings))
        <div class="p-4 rounded-2xl bg-[#FFF4EC] border border-[#FFE6D5] text-[#C2410C] text-xs">
            <strong class="font-bold flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Peringatan Kemungkinan Pesanan Duplikat</span>
            </strong>
            @foreach($warnings as $w)
                <p class="mt-1 pl-5.5">• {{ $w['note'] }} ({{ $w['transaction_number'] ?? '' }})</p>
            @endforeach
        </div>
    @endif

    <!-- Split Screen Layout: Left (Sticky Screenshot Viewer), Right (Interactive Form) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Left: Image Viewer (5 Cols) -->
        <div class="lg:col-span-5 bg-white rounded-2xl border border-[#E7E0D6] p-4 shadow-sm lg:sticky lg:top-4 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-[#E7E0D6]">
                <div>
                    <h3 class="font-bold text-sm text-[#14110F]">Tangkapan Layar Struk</h3>
                    <p class="text-[11px] text-[#786E64]">Gunakan tombol zoom untuk memperbesar teks struk</p>
                </div>
                <!-- Zoom Controls -->
                <div class="flex items-center gap-1 bg-[#FAF8F5] p-1 rounded-xl border border-[#D3C9BD]">
                    <button type="button" @click="zoomOut()" title="Perkecil"
                            class="w-7 h-7 rounded-lg bg-white hover:bg-[#F4F0EA] text-xs font-bold text-[#575047] flex items-center justify-center border border-[#E7E0D6] cursor-pointer">
                        -
                    </button>
                    <span class="text-[11px] font-mono px-1.5 font-bold text-[#14110F]" x-text="Math.round(zoomLevel * 100) + '%'"></span>
                    <button type="button" @click="zoomIn()" title="Perbesar"
                            class="w-7 h-7 rounded-lg bg-white hover:bg-[#F4F0EA] text-xs font-bold text-[#575047] flex items-center justify-center border border-[#E7E0D6] cursor-pointer">
                        +
                    </button>
                    <button type="button" @click="resetZoom()" title="Reset"
                            class="px-2 h-7 rounded-lg bg-white hover:bg-[#F4F0EA] text-[10px] font-semibold text-[#575047] flex items-center justify-center border border-[#E7E0D6] cursor-pointer">
                        Reset
                    </button>
                    <button type="button" @click="showLightbox = true" title="Layar Penuh"
                            class="w-7 h-7 rounded-lg bg-[#C2410C] text-white hover:bg-[#9A3412] flex items-center justify-center text-xs cursor-pointer shadow-2xs">
                        ⛶
                    </button>
                </div>
            </div>

            <!-- Scrollable Image Canvas -->
            <div class="bg-[#F4F0EA] rounded-xl overflow-hidden flex items-center justify-center min-h-[400px] max-h-[620px] p-2 relative select-none">
                <div class="overflow-auto max-h-[600px] w-full flex items-center justify-center p-2">
                    <img src="{{ route('impor.image', $aiImport) }}" alt="Screenshot Pesanan"
                         :style="'transform: scale(' + zoomLevel + '); transform-origin: top center; transition: transform 0.15s ease-out;'"
                         class="max-w-full rounded-lg shadow-sm border border-[#D3C9BD]">
                </div>
            </div>

            <!-- Image Metadata Badge -->
            <div class="flex items-center justify-between text-[11px] text-[#786E64] pt-1">
                <span>Model AI: <strong>{{ $aiImport->model ?: 'ciora-vision-free' }}</strong></span>
                <span>Latency: <strong>{{ $aiImport->latency_ms ? $aiImport->latency_ms . ' ms' : '-' }}</strong></span>
            </div>
        </div>

        <!-- Right: Interactive Review & Mapping Form (7 Cols) -->
        <div class="lg:col-span-7 bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm">
            <form method="POST" action="{{ route('impor.save', $aiImport) }}" class="space-y-5">
                @csrf

                <!-- Platform & Order ID Row -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pb-4 border-b border-[#E7E0D6]">
                    <div class="sm:col-span-5">
                        <label class="block text-xs font-bold text-[#575047] uppercase mb-1.5">Kanal Penjualan</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="channel" value="gofood" x-model="channel" class="peer sr-only">
                                <div class="p-2.5 rounded-xl border text-center text-xs font-bold transition peer-checked:border-[#00AA13] peer-checked:bg-[#E8F8EC] peer-checked:text-[#00880F] border-[#D3C9BD] bg-[#FAF8F5] text-[#575047]">
                                    🟢 GoFood
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="channel" value="shopeefood" x-model="channel" class="peer sr-only">
                                <div class="p-2.5 rounded-xl border text-center text-xs font-bold transition peer-checked:border-[#EE4D2D] peer-checked:bg-[#FFF2EE] peer-checked:text-[#EE4D2D] border-[#D3C9BD] bg-[#FAF8F5] text-[#575047]">
                                    🟠 ShopeeFood
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="sm:col-span-7">
                        <label class="block text-xs font-bold text-[#575047] uppercase mb-1.5">ID Pesanan Platform</label>
                        <div class="relative">
                            <input type="text" name="platform_order_id" x-model="orderId"
                                   placeholder="Contoh: GF-261030-0774"
                                   class="w-full text-xs font-mono font-bold px-3 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5] focus:outline-none focus:ring-2 focus:ring-[#C2410C]">
                            <button type="button" @click="copyOrderId()"
                                    class="absolute right-2 top-2 px-2 py-1 rounded-lg bg-white border border-[#D3C9BD] text-[10px] font-semibold text-[#575047] hover:bg-[#F4F0EA] transition cursor-pointer">
                                <span x-text="copied ? '✓ Disalin' : 'Salin'"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Items Section Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-sm text-[#14110F]">Item Menu Terekstrak</h4>
                        <p class="text-xs text-[#575047]">Pastikan setiap baris telah dipetakan ke Menu Master outlet yang sesuai.</p>
                    </div>
                    <button type="button" @click="addNewItem()"
                            class="px-3 py-1.5 rounded-xl bg-[#FFF4EC] text-[#C2410C] hover:bg-[#FFE6D5] border border-[#FFE6D5] text-xs font-bold transition flex items-center gap-1 cursor-pointer">
                        <span>+ Tambah Baris Menu</span>
                    </button>
                </div>

                <!-- Extracted Items List -->
                <div class="space-y-3">
                    <template x-for="(item, index) in itemsList" :key="index">
                        <div class="p-3.5 rounded-2xl border transition space-y-2.5"
                             :class="item.menu_id ? 'border-[#E7E0D6] bg-[#FAF8F5]' : 'border-[#FDE68A] bg-[#FFFBEB]'">
                            
                            <!-- Header Row: Name & Quantity -->
                            <div class="flex items-start justify-between gap-3 text-xs">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <input type="hidden" :name="'items[' + index + '][source_name]'" :value="item.source_name">
                                        <span class="font-bold text-sm text-[#14110F]" x-text="item.source_name || 'Item Tanpa Nama'"></span>
                                        <template x-if="item.menu_id">
                                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-[#ECFDF5] text-[#047857] border border-[#A7F3D0]">
                                                ✓ Terpetakan
                                            </span>
                                        </template>
                                        <template x-if="!item.menu_id">
                                            <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-[#FFFBEB] text-[#B45309] border border-[#FDE68A] animate-pulse">
                                                ! Pilih Menu
                                            </span>
                                        </template>
                                    </div>
                                    <template x-if="item.notes">
                                        <div class="text-[11px] text-[#575047] italic mt-0.5" x-text="'Catatan: ' + item.notes"></div>
                                    </template>
                                </div>

                                <!-- Quantity Controls -->
                                <div class="flex items-center gap-1.5 shrink-0 bg-white p-1 rounded-xl border border-[#D3C9BD]">
                                    <button type="button" @click="decrementQty(index)"
                                            class="w-6 h-6 rounded-lg bg-[#FAF8F5] hover:bg-[#E7E0D6] text-xs font-bold text-[#14110F] flex items-center justify-center cursor-pointer">
                                        -
                                    </button>
                                    <input type="number" :name="'items[' + index + '][qty]'" x-model.number="item.qty" min="1" max="99" required
                                           class="w-10 text-center font-bold text-xs py-0.5 border-0 focus:ring-0 text-[#14110F]">
                                    <button type="button" @click="incrementQty(index)"
                                            class="w-6 h-6 rounded-lg bg-[#FAF8F5] hover:bg-[#E7E0D6] text-xs font-bold text-[#14110F] flex items-center justify-center cursor-pointer">
                                        +
                                    </button>
                                </div>

                                <!-- Remove Item Button -->
                                <button type="button" @click="removeItem(index)" title="Hapus baris ini"
                                        class="p-1 rounded-lg text-[#DC2626] hover:bg-[#FEF2F2] transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>

                            <!-- Menu Mapping Selector -->
                            <div>
                                <label class="block text-[11px] font-bold text-[#575047] uppercase mb-1">Hubungkan ke Menu Master Outlet</label>
                                <select :name="'items[' + index + '][menu_id]'" x-model="item.menu_id" required
                                        class="w-full text-xs font-medium px-3 py-2 rounded-xl border border-[#D3C9BD] bg-white focus:outline-none focus:ring-2 focus:ring-[#C2410C]">
                                    <option value="">-- Pilih Menu Master --</option>
                                    <template x-for="m in allMenus" :key="m.id">
                                        <option :value="m.id" :selected="item.menu_id == m.id"
                                                x-text="m.name + ' (Rp ' + Number(m.price).toLocaleString('id-ID') + ')'">
                                        </option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </template>

                    <template x-if="itemsList.length === 0">
                        <div class="p-8 text-center text-[#9E9385] text-xs border border-dashed border-[#D3C9BD] rounded-2xl bg-[#FAF8F5]">
                            Belum ada item yang terdaftar. Klik <strong>+ Tambah Baris Menu</strong> untuk menambahkan.
                        </div>
                    </template>
                </div>

                <!-- Financial Summary Card (Realtime Calculated) -->
                <div class="p-4 rounded-2xl bg-[#FAF8F5] border border-[#E7E0D6] space-y-3">
                    <h5 class="text-xs font-bold text-[#575047] uppercase tracking-wider">Perhitungan Nilai Pesanan</h5>
                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between items-center text-[#575047]">
                            <span>Subtotal Menu Terpilih:</span>
                            <span class="font-bold text-[#14110F] text-sm" x-text="'Rp ' + calculatedSubtotal.toLocaleString('id-ID')"></span>
                        </div>
                        <div class="flex justify-between items-center text-[#575047]">
                            <span>Diskon Pesanan:</span>
                            <div class="flex items-center gap-1">
                                <span>Rp</span>
                                <input type="number" name="discount_amount" x-model.number="discountAmount" min="0"
                                       class="w-24 text-right px-2 py-1 rounded-lg border border-[#D3C9BD] bg-white text-xs font-bold text-[#DC2626]">
                            </div>
                        </div>
                        <div class="pt-2 border-t border-[#E7E0D6] flex justify-between items-center text-sm font-bold text-[#14110F]">
                            <span>Total Penerimaan Bersih:</span>
                            <span class="text-base text-[#047857]" x-text="'Rp ' + calculatedNetTotal.toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </div>

                <!-- Catatan Tambahan -->
                <div>
                    <label class="block text-xs font-bold text-[#575047] uppercase mb-1">Catatan Transaksi (Opsional)</label>
                    <input type="text" name="note" placeholder="Catatan khusus transaksi online..."
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5] focus:outline-none focus:ring-2 focus:ring-[#C2410C]">
                </div>

                <!-- Primary Action Button -->
                <div class="pt-2">
                    <button type="submit" :disabled="{{ $isSaved ? 'true' : 'false' }} || itemsList.length === 0"
                            class="w-full py-3.5 px-6 rounded-xl font-bold text-white bg-[#047857] hover:bg-[#065f46] text-sm shadow-md transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ $isSaved ? 'Pesanan Sudah Tersimpan sebagai Transaksi' : 'Konfirmasi & Simpan Transaksi Resmi' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lightbox Modal for Fullscreen Image View -->
    <div x-show="showLightbox" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-xs" @click.self="showLightbox = false">
        <div class="relative max-w-4xl max-h-[90vh] bg-white rounded-2xl overflow-hidden p-2 shadow-2xl flex flex-col">
            <div class="flex justify-between items-center p-2 border-b border-[#E7E0D6]">
                <span class="text-xs font-bold text-[#14110F]">Tampilan Penuh Screenshot</span>
                <button type="button" @click="showLightbox = false" class="text-xs font-bold text-[#575047] hover:text-black px-2 py-1 rounded bg-[#FAF8F5] cursor-pointer">Tutup ✕</button>
            </div>
            <div class="overflow-auto p-2 flex items-center justify-center">
                <img src="{{ route('impor.image', $aiImport) }}" alt="Screenshot Full" class="max-w-full max-h-[80vh] object-contain rounded-lg">
            </div>
        </div>
    </div>
</div>

<script>
function aiReviewEditor(initialItems, allMenus, defaultChannel, initialDiscount) {
    // Normalisasi item awal
    const formattedItems = (initialItems || []).map(item => {
        return {
            source_name: item.source_name || item.name || 'Item Tanpa Nama',
            qty: Number(item.quantity || item.qty || 1),
            price: Number(item.visible_unit_price || item.price || 0),
            menu_id: item.matched_menu_id || item.menu_id || '',
            notes: item.notes || null,
        };
    });

    return {
        itemsList: formattedItems,
        allMenus: allMenus || [],
        channel: defaultChannel || 'gofood',
        orderId: '{{ $extracted['platform_order_id'] ?? ($aiImport->platform_order_id ?? '') }}',
        discountAmount: Number(initialDiscount || 0),
        zoomLevel: 1.0,
        showLightbox: false,
        copied: false,

        zoomIn() {
            if (this.zoomLevel < 3.0) this.zoomLevel = Number((this.zoomLevel + 0.25).toFixed(2));
        },
        zoomOut() {
            if (this.zoomLevel > 0.5) this.zoomLevel = Number((this.zoomLevel - 0.25).toFixed(2));
        },
        resetZoom() {
            this.zoomLevel = 1.0;
        },

        copyOrderId() {
            if (!this.orderId) return;
            navigator.clipboard.writeText(this.orderId).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            });
        },

        incrementQty(index) {
            if (this.itemsList[index]) {
                this.itemsList[index].qty = Math.min(99, this.itemsList[index].qty + 1);
            }
        },
        decrementQty(index) {
            if (this.itemsList[index] && this.itemsList[index].qty > 1) {
                this.itemsList[index].qty--;
            }
        },
        removeItem(index) {
            this.itemsList.splice(index, 1);
        },
        addNewItem() {
            this.itemsList.push({
                source_name: 'Item Tambahan',
                qty: 1,
                price: 0,
                menu_id: '',
                notes: null,
            });
        },

        get calculatedSubtotal() {
            return this.itemsList.reduce((sum, item) => {
                const menu = this.allMenus.find(m => m.id == item.menu_id);
                const price = menu ? Number(menu.price) : Number(item.price || 0);
                return sum + (price * Number(item.qty || 1));
            }, 0);
        },

        get calculatedNetTotal() {
            return Math.max(0, this.calculatedSubtotal - Number(this.discountAmount || 0));
        }
    };
}
</script>
@endsection
