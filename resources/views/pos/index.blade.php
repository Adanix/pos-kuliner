@extends('layouts.app')

@section('content')
<div x-data="posApp()" x-init="initPos()" class="h-full flex flex-col -m-4 sm:-m-6 lg:-m-8">
    <!-- POS Top Control Strip -->
    <div class="no-print bg-white border-b border-[#E7E0D6] px-4 py-3 flex flex-wrap items-center justify-between gap-3 shrink-0">
        <!-- Search & Filter -->
        <div class="flex items-center gap-3 flex-1 min-w-[240px]">
            <div class="relative w-full max-w-md">
                <input type="text" x-model="searchQuery" placeholder="Cari nama menu..."
                       class="w-full pl-9 pr-4 py-2 rounded-xl border border-[#D3C9BD] focus:outline-none focus:ring-2 focus:ring-[#C2410C] text-sm bg-[#FAF8F5]">
                <svg class="w-4 h-4 text-[#9E9385] absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <!-- Shift & Channel Status -->
        <div class="flex items-center gap-2">
            @if($activeShift)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#ECFDF5] text-[#047857] border border-[#A7F3D0]">
                    <span class="w-2 h-2 rounded-full bg-[#047857] animate-pulse"></span>
                    <span>Kasir Aktif: {{ $activeShift->openedByUser?->name }}</span>
                </span>
            @else
                <a href="{{ route('pos.shift.index') }}" class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-[#FEF2F2] text-[#DC2626] border border-[#FECACA] animate-pulse">
                    <span>! Buka Sesi Kasir Dahulu</span>
                </a>
            @endif

            <select x-model="selectedChannel" class="text-xs font-semibold px-3 py-1.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5] focus:outline-none focus:ring-2 focus:ring-[#C2410C]">
                <option value="pos">Dine-in / Kasir POS</option>
                <option value="gofood">GoFood</option>
                <option value="shopeefood">ShopeeFood</option>
            </select>

            @if(auth()->user()?->isOwner())
                <a href="{{ route('menu.index') }}" title="Kelola Menu & Harga" class="hidden sm:inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold bg-[#FAF8F5] border border-[#D3C9BD] text-[#575047] hover:text-[#C2410C] hover:border-[#C2410C] transition">
                    <span>⚙️ Kelola Menu</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Category Horizontal Scroll Strip -->
    <div class="no-print bg-white border-b border-[#E7E0D6] px-4 py-2 overflow-x-auto flex items-center gap-2 shrink-0">
        <button @click="selectedCategory = null" type="button"
                :class="selectedCategory === null ? 'bg-[#C2410C] text-white font-semibold' : 'bg-[#F4F0EA] text-[#575047] hover:bg-[#E7E0D6]'"
                class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition">
            Semua Menu
        </button>
        <template x-for="cat in categories" :key="cat.id">
            <button @click="selectedCategory = cat.id" type="button"
                    :class="selectedCategory === cat.id ? 'bg-[#C2410C] text-white font-semibold' : 'bg-[#F4F0EA] text-[#575047] hover:bg-[#E7E0D6]'"
                    class="px-3.5 py-1.5 rounded-xl text-xs whitespace-nowrap transition"
                    x-text="cat.name">
            </button>
        </template>
    </div>

    <!-- Main POS Grid & Cart Panel -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Left: Menu Grid (Scrollable) -->
        <div class="flex-1 p-4 overflow-y-auto min-w-0">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 gap-3 sm:gap-4">
                <template x-for="menu in filteredMenus" :key="menu.id">
                    <button @click="addToCart(menu)" type="button"
                            class="bg-white rounded-2xl border border-[#E7E0D6] p-3 text-left hover:border-[#C2410C] hover:shadow-md transition flex flex-col justify-between group active:scale-[0.98]">
                        <div>
                            <div class="aspect-video w-full rounded-xl bg-[#F4F0EA] mb-2.5 overflow-hidden flex items-center justify-center text-[#9E9385]">
                                <template x-if="menu.image_path">
                                    <img :src="menu.image_path" class="w-full h-full object-cover" />
                                </template>
                                <template x-if="!menu.image_path">
                                    <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </template>
                            </div>
                            <span class="text-[10px] font-bold text-[#C2410C] uppercase tracking-wider block" x-text="menu.category_name"></span>
                            <h4 class="font-bold text-sm text-[#14110F] line-clamp-2 mt-0.5" x-text="menu.name"></h4>
                        </div>
                        <div class="mt-3 pt-2 border-t border-[#F4F0EA] flex items-center justify-between">
                            <span class="font-bold text-sm text-[#14110F] tabular-nums" x-text="menu.formatted_price"></span>
                            <span class="w-6 h-6 rounded-lg bg-[#FFF4EC] text-[#C2410C] flex items-center justify-center font-bold text-xs group-hover:bg-[#C2410C] group-hover:text-white transition">+</span>
                        </div>
                    </button>
                </template>
            </div>

            <!-- Empty Menu State -->
            <div x-show="filteredMenus.length === 0" class="p-12 text-center text-[#9E9385]">
                <p class="text-base font-semibold text-[#14110F]">Tidak ada menu yang sesuai.</p>
                <p class="text-xs text-[#575047] mt-1">Coba kata kunci pencarian lain atau pilih kategori berbeda.</p>
            </div>
        </div>

        <!-- Right: Fixed Desktop Cart Panel (>= 1280px) -->
        <div class="no-print hidden xl:flex w-96 bg-white border-l border-[#E7E0D6] flex-col shrink-0">
            <!-- Cart Header -->
            <div class="p-4 border-b border-[#E7E0D6] flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-base text-[#14110F]">Pesanan Baru</h3>
                    <span class="text-xs text-[#575047]" x-text="cart.length + ' item dipilih'"></span>
                </div>
                <button @click="clearCart()" x-show="cart.length > 0" type="button" class="text-xs font-semibold text-[#DC2626] hover:underline">
                    Kosongkan
                </button>
            </div>

            <!-- Cart Items List -->
            <div class="flex-1 p-4 overflow-y-auto space-y-3">
                <template x-for="(item, idx) in cart" :key="item.menu_id">
                    <div class="p-3 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6]">
                        <div class="flex justify-between items-start">
                            <div>
                                <h5 class="font-semibold text-sm text-[#14110F]" x-text="item.name"></h5>
                                <span class="text-xs text-[#575047] tabular-nums" x-text="formatRupiah(item.unit_price)"></span>
                            </div>
                            <span class="font-bold text-sm text-[#14110F] tabular-nums" x-text="formatRupiah(item.qty * item.unit_price)"></span>
                        </div>

                        <!-- Stepper & Notes -->
                        <div class="mt-2.5 pt-2 border-t border-[#E7E0D6] flex items-center justify-between">
                            <input type="text" x-model="item.note" placeholder="Catatan (opsional)..."
                                   class="text-xs px-2 py-1 rounded-lg border border-[#D3C9BD] w-36 bg-white focus:outline-none focus:ring-1 focus:ring-[#C2410C]">

                            <div class="flex items-center gap-1.5">
                                <button @click="decrementQty(idx)" type="button" class="w-8 h-8 rounded-lg bg-white border border-[#D3C9BD] flex items-center justify-center font-bold text-sm hover:bg-[#F4F0EA] active:scale-95">
                                    &minus;
                                </button>
                                <span class="w-7 text-center font-bold text-sm tabular-nums" x-text="item.qty"></span>
                                <button @click="incrementQty(idx)" type="button" class="w-8 h-8 rounded-lg bg-white border border-[#D3C9BD] flex items-center justify-center font-bold text-sm hover:bg-[#F4F0EA] active:scale-95">
                                    +
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="cart.length === 0" class="h-48 flex flex-col items-center justify-center text-center text-[#9E9385] text-xs">
                    <svg class="w-10 h-10 mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span>Keranjang kosong. Klik menu di samping untuk menambahkan.</span>
                </div>
            </div>

            <!-- Calculation Summary Footer -->
            <div class="p-4 border-t border-[#E7E0D6] bg-[#FAF8F5] space-y-2 text-xs">
                <div class="flex justify-between text-[#575047]">
                    <span>Subtotal</span>
                    <span class="font-semibold text-[#14110F] tabular-nums" x-text="formatRupiah(subtotal)"></span>
                </div>
                <div x-show="discountAmount > 0" class="flex justify-between text-[#047857]">
                    <span>Diskon</span>
                    <span class="font-semibold tabular-nums" x-text="'-' + formatRupiah(discountAmount)"></span>
                </div>
                <div x-show="taxAmount > 0" class="flex justify-between text-[#575047]">
                    <span>Pajak</span>
                    <span class="font-semibold text-[#14110F] tabular-nums" x-text="formatRupiah(taxAmount)"></span>
                </div>
                <div x-show="roundingAmount !== 0" class="flex justify-between text-[#575047]">
                    <span>Pembulatan Tunai</span>
                    <span class="font-semibold text-[#14110F] tabular-nums" x-text="formatRupiah(roundingAmount)"></span>
                </div>
                <div class="pt-2 border-t border-[#E7E0D6] flex justify-between items-baseline">
                    <span class="font-bold text-sm text-[#14110F]">Total Bayar</span>
                    <span class="font-bold text-xl text-[#C2410C] tabular-nums" x-text="formatRupiah(grandTotal)"></span>
                </div>

                <!-- Checkout Action -->
                <button @click="openPaymentModal()" :disabled="cart.length === 0 || isSubmitting" type="button"
                        class="w-full mt-2 py-3 rounded-xl font-bold text-white bg-[#C2410C] hover:bg-[#9A3412] disabled:opacity-40 disabled:cursor-not-allowed transition shadow-md flex items-center justify-center gap-2">
                    <span x-show="!isSubmitting">Lanjut ke Pembayaran &rarr;</span>
                    <span x-show="isSubmitting">Menyimpan Transaksi...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile / Tablet Sticky Action Bar (< 1280px) -->
    <div class="no-print xl:hidden sticky bottom-14 left-0 right-0 p-3 bg-white border-t border-[#E7E0D6] shadow-[0_-4px_12px_rgba(20,17,15,0.06)] z-30">
        <div class="flex items-center justify-between gap-3">
            <div>
                <span class="text-xs text-[#575047]" x-text="cart.length + ' item di keranjang'"></span>
                <div class="font-bold text-lg text-[#C2410C] tabular-nums" x-text="formatRupiah(grandTotal)"></div>
            </div>
            <button @click="showMobileCart = true" type="button"
                    class="px-5 py-2.5 rounded-xl font-bold text-sm text-white bg-[#C2410C] hover:bg-[#9A3412] shadow-sm">
                Lihat Pesanan &rarr;
            </button>
        </div>
    </div>

    <!-- Mobile Cart Bottom Sheet Modal -->
    <div x-show="showMobileCart" x-cloak class="no-print fixed inset-0 z-50 flex items-end justify-center bg-black/40 backdrop-blur-xs">
        <div @click.away="showMobileCart = false" class="bg-white w-full max-w-lg rounded-t-3xl max-h-[90dvh] flex flex-col shadow-2xl">
            <div class="p-4 border-b border-[#E7E0D6] flex items-center justify-between">
                <h3 class="font-bold text-base text-[#14110F]">Rincian Pesanan</h3>
                <button @click="showMobileCart = false" type="button" class="p-1 rounded-lg text-[#575047] hover:bg-[#FAF8F5]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 p-4 overflow-y-auto space-y-3">
                <template x-for="(item, idx) in cart" :key="item.menu_id">
                    <div class="p-3 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6]">
                        <div class="flex justify-between items-start">
                            <span class="font-semibold text-sm" x-text="item.name"></span>
                            <span class="font-bold text-sm tabular-nums" x-text="formatRupiah(item.qty * item.unit_price)"></span>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <input type="text" x-model="item.note" placeholder="Catatan..." class="text-xs px-2 py-1 rounded border border-[#D3C9BD] w-32 bg-white">
                            <div class="flex items-center gap-2">
                                <button @click="decrementQty(idx)" class="w-8 h-8 rounded bg-white border border-[#D3C9BD] font-bold">&minus;</button>
                                <span class="font-bold text-sm tabular-nums" x-text="item.qty"></span>
                                <button @click="incrementQty(idx)" class="w-8 h-8 rounded bg-white border border-[#D3C9BD] font-bold">+</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="p-4 border-t border-[#E7E0D6] bg-[#FAF8F5] space-y-2">
                <div class="flex justify-between font-bold text-base">
                    <span>Total</span>
                    <span class="text-[#C2410C] tabular-nums" x-text="formatRupiah(grandTotal)"></span>
                </div>
                <button @click="showMobileCart = false; openPaymentModal()" :disabled="cart.length === 0"
                        class="w-full py-3 rounded-xl font-bold text-white bg-[#C2410C] disabled:opacity-40">
                    Lanjut ke Pembayaran &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- Payment & Checkout Modal -->
    <div x-show="showPaymentModal" x-cloak class="no-print fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div @click.away="showPaymentModal = false" class="bg-white w-full max-w-md rounded-2xl border border-[#E7E0D6] shadow-2xl p-6 space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-[#E7E0D6]">
                <h3 class="font-bold text-lg text-[#14110F]">Pembayaran</h3>
                <button @click="showPaymentModal = false" class="text-[#575047] hover:text-[#14110F]">&times;</button>
            </div>

            <!-- Grand Total Display -->
            <div class="p-4 rounded-xl bg-[#FFF4EC] border border-[#FFE6D5] text-center">
                <span class="text-xs font-semibold text-[#575047] uppercase">Total Tagihan</span>
                <div class="text-3xl font-extrabold text-[#C2410C] mt-0.5 tabular-nums" x-text="formatRupiah(grandTotal)"></div>
            </div>

            <!-- Payment Methods -->
            <div>
                <label class="block text-xs font-semibold text-[#575047] uppercase mb-1.5">Metode Pembayaran</label>
                <div class="grid grid-cols-4 gap-2">
                    <button @click="paymentMethod = 'cash'; amountPaid = grandTotal" type="button"
                            :class="paymentMethod === 'cash' ? 'bg-[#C2410C] text-white font-bold' : 'bg-[#F4F0EA] text-[#575047] hover:bg-[#E7E0D6]'"
                            class="py-2.5 rounded-xl text-xs font-semibold transition">Tunai</button>
                    <button @click="paymentMethod = 'qris'; amountPaid = grandTotal" type="button"
                            :class="paymentMethod === 'qris' ? 'bg-[#C2410C] text-white font-bold' : 'bg-[#F4F0EA] text-[#575047] hover:bg-[#E7E0D6]'"
                            class="py-2.5 rounded-xl text-xs font-semibold transition">QRIS</button>
                    <button @click="paymentMethod = 'transfer'; amountPaid = grandTotal" type="button"
                            :class="paymentMethod === 'transfer' ? 'bg-[#C2410C] text-white font-bold' : 'bg-[#F4F0EA] text-[#575047] hover:bg-[#E7E0D6]'"
                            class="py-2.5 rounded-xl text-xs font-semibold transition">Transfer</button>
                    <button @click="paymentMethod = 'other'; amountPaid = grandTotal" type="button"
                            :class="paymentMethod === 'other' ? 'bg-[#C2410C] text-white font-bold' : 'bg-[#F4F0EA] text-[#575047] hover:bg-[#E7E0D6]'"
                            class="py-2.5 rounded-xl text-xs font-semibold transition">Lainnya</button>
                </div>
            </div>

            <!-- Cash Tendered Input (If cash) -->
            <div x-show="paymentMethod === 'cash'" class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Uang Diterima (Rp)</label>
                    <input type="number" x-model.number="amountPaid"
                           class="w-full text-lg font-bold px-4 py-2.5 rounded-xl border border-[#D3C9BD] focus:outline-none focus:ring-2 focus:ring-[#C2410C] tabular-nums">
                </div>

                <!-- Quick Cash Presets -->
                <div class="flex gap-2">
                    <button @click="amountPaid = grandTotal" type="button" class="flex-1 py-1.5 rounded-lg border border-[#D3C9BD] text-xs font-semibold hover:bg-[#FAF8F5]">Uang Pas</button>
                    <button @click="amountPaid = 50000" type="button" class="flex-1 py-1.5 rounded-lg border border-[#D3C9BD] text-xs font-semibold hover:bg-[#FAF8F5]">50.000</button>
                    <button @click="amountPaid = 100000" type="button" class="flex-1 py-1.5 rounded-lg border border-[#D3C9BD] text-xs font-semibold hover:bg-[#FAF8F5]">100.000</button>
                </div>

                <!-- Kembalian -->
                <div class="p-3 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6] flex justify-between items-center">
                    <span class="text-xs font-semibold text-[#575047]">Kembalian:</span>
                    <span class="font-extrabold text-lg tabular-nums"
                          :class="changeAmount >= 0 ? 'text-[#047857]' : 'text-[#DC2626]'"
                          x-text="changeAmount >= 0 ? formatRupiah(changeAmount) : 'Kurang ' + formatRupiah(Math.abs(changeAmount))">
                    </span>
                </div>
            </div>

            <!-- Submit Button -->
            <button @click="submitOrder()" :disabled="isSubmitting || (paymentMethod === 'cash' && changeAmount < 0)" type="button"
                    class="w-full py-3.5 rounded-xl font-bold text-white bg-[#047857] hover:bg-[#065f46] disabled:opacity-40 disabled:cursor-not-allowed transition shadow-md text-sm">
                <span x-show="!isSubmitting">Selesaikan & Cetak Struk</span>
                <span x-show="isSubmitting">Menyimpan Transaksi...</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function posApp() {
    return {
        categories: @json($categories),
        menus: @json($menus),
        selectedCategory: null,
        selectedChannel: 'pos',
        searchQuery: '',
        cart: [],
        discountAmount: 0,
        taxPercent: {{ (float) ($outlet->tax_percent ?? 0) }},
        servicePercent: {{ (float) ($outlet->service_charge_percent ?? 0) }},
        roundingMode: '{{ $outlet->rounding_mode ?? 'none' }}',
        paymentMethod: 'cash',
        amountPaid: 0,
        isSubmitting: false,
        showMobileCart: false,
        showPaymentModal: false,
        idempotencyKey: '',

        initPos() {
            // Restore cart draft from localStorage if available
            try {
                const saved = localStorage.getItem('ciora_cart_draft');
                if (saved) {
                    this.cart = JSON.parse(saved);
                }
            } catch (e) {}

            this.generateIdempotencyKey();

            // Auto-save cart to localStorage
            this.$watch('cart', (val) => {
                localStorage.setItem('ciora_cart_draft', JSON.stringify(val));
            });
        },

        generateIdempotencyKey() {
            this.idempotencyKey = 'pos-' + Date.now() + '-' + Math.random().toString(36).substring(2, 9);
        },

        get filteredMenus() {
            return this.menus.filter(m => {
                const matchCat = this.selectedCategory === null || m.category_id === this.selectedCategory;
                const matchSearch = this.searchQuery === '' || m.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                return matchCat && matchSearch;
            });
        },

        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.qty * item.unit_price), 0);
        },

        get taxAmount() {
            const base = Math.max(0, this.subtotal - this.discountAmount);
            return Math.round(base * (this.taxPercent / 100));
        },

        get roundingAmount() {
            if (this.paymentMethod === 'cash' && this.roundingMode === 'nearest_100') {
                const raw = this.subtotal - this.discountAmount + this.taxAmount;
                const rounded = Math.round(raw / 100) * 100;
                return rounded - raw;
            }
            return 0;
        },

        get grandTotal() {
            return Math.max(0, this.subtotal - this.discountAmount + this.taxAmount + this.roundingAmount);
        },

        get changeAmount() {
            return this.amountPaid - this.grandTotal;
        },

        addToCart(menu) {
            const existing = this.cart.find(i => i.menu_id === menu.id);
            if (existing) {
                if (existing.qty < 99) existing.qty++;
            } else {
                this.cart.push({
                    menu_id: menu.id,
                    name: menu.name,
                    unit_price: menu.price,
                    qty: 1,
                    note: '',
                });
            }
        },

        incrementQty(index) {
            if (this.cart[index].qty < 99) {
                this.cart[index].qty++;
            }
        },

        decrementQty(index) {
            if (this.cart[index].qty > 1) {
                this.cart[index].qty--;
            } else {
                this.cart.splice(index, 1);
            }
        },

        clearCart() {
            window.showConfirm(
                'Apakah Anda yakin ingin mengosongkan seluruh isi keranjang pesanan?',
                () => {
                    this.cart = [];
                    localStorage.removeItem('ciora_cart_draft');
                    this.generateIdempotencyKey();
                },
                'Kosongkan Keranjang',
                'Ya, Kosongkan'
            );
        },

        openPaymentModal() {
            this.amountPaid = this.grandTotal;
            this.showPaymentModal = true;
        },

        formatRupiah(num) {
            if (num === null || num === undefined) return 'Rp 0';
            const val = Math.round(num);
            return (val < 0 ? '-Rp ' : 'Rp ') + Math.abs(val).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        },

        async submitOrder() {
            if (this.cart.length === 0 || this.isSubmitting) return;

            this.isSubmitting = true;

            const payload = {
                channel: this.selectedChannel,
                payment_method: this.paymentMethod,
                amount_paid: this.amountPaid,
                discount_type: this.discountAmount > 0 ? 'fixed' : 'none',
                discount_value: this.discountAmount,
                idempotency_key: this.idempotencyKey,
                items: this.cart.map(i => ({
                    menu_id: i.menu_id,
                    qty: i.qty,
                    note: i.note || null,
                })),
            };

            try {
                const res = await fetch('{{ route('transactions.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Idempotency-Key': this.idempotencyKey,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    // Clear cart only after 201 response per PRD §6.2
                    this.cart = [];
                    localStorage.removeItem('ciora_cart_draft');
                    this.showPaymentModal = false;

                    // Open transaction receipt directly
                    window.location.href = '/transaksi/' + data.transaction.id + '/struk';
                } else {
                    window.showAlert(data.message || 'Terjadi kesalahan sistem.', 'Gagal Menyimpan Pesanan', 'error');
                    this.isSubmitting = false;
                }
            } catch (err) {
                window.showAlert(err.message, 'Koneksi Terputus / Error', 'error');
                this.isSubmitting = false;
            }
        }
    };
}
</script>
@endpush
@endsection
