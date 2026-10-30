@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Stepper Indicator -->
    <div class="text-center mb-6">
        <span class="text-xs font-bold uppercase tracking-wider text-[#C2410C]">Setup Wizard Outlet Baru</span>
        <h1 class="text-2xl font-bold tracking-tight text-[#14110F] mt-1">Langkah {{ $step }} dari 6</h1>
        <div class="flex gap-2 justify-center mt-3">
            @for($i = 1; $i <= 6; $i++)
                <div class="h-1.5 w-10 rounded-full {{ $i <= $step ? 'bg-[#C2410C]' : 'bg-[#E7E0D6]' }}"></div>
            @endfor
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-6 sm:p-8 shadow-sm">
        <form method="POST" action="{{ route('wizard.save') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="step" value="{{ $step }}">

            @if($step === 1)
                <!-- Step 1: Profil Outlet -->
                <h3 class="font-bold text-lg text-[#14110F]">1. Profil Outlet Anda</h3>
                <p class="text-xs text-[#575047] mb-4">Informasi ini akan dicetak pada bagian atas struk kasir.</p>

                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Nama Outlet / Warung</label>
                    <input type="text" name="name" value="{{ $outlet->name ?? 'Kuliner Ciora' }}" required
                           class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" value="{{ $outlet->phone ?? '' }}" placeholder="08..."
                           class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Alamat Outlet</label>
                    <input type="text" name="address" value="{{ $outlet->address ?? '' }}" placeholder="Jl. Raya..."
                           class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>

            @elseif($step === 2)
                <!-- Step 2: Kategori -->
                <h3 class="font-bold text-lg text-[#14110F]">2. Buat Kategori Pertama</h3>
                <p class="text-xs text-[#575047] mb-4">Kategori mengelompokkan menu agar kasir mudah mencari produk.</p>

                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Nama Kategori</label>
                    <input type="text" name="category_name" placeholder="Contoh: Makanan Utama, Minuman Dingin, Snack..." required
                           class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>

            @elseif($step === 3)
                <!-- Step 3: Menu -->
                <h3 class="font-bold text-lg text-[#14110F]">3. Tambah Menu Pertama</h3>
                <p class="text-xs text-[#575047] mb-4">Masukkan produk yang Anda jual beserta harga jualnya.</p>

                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Pilih Kategori</label>
                    <select name="category_id" required class="w-full text-sm px-3 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Nama Menu</label>
                    <input type="text" name="menu_name" placeholder="Contoh: Nasi Goreng Spesial" required
                           class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Harga Jual (Rp)</label>
                    <input type="number" name="price" placeholder="25000" min="0" required
                           class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5] tabular-nums">
                </div>

            @elseif($step === 4)
                <!-- Step 4: Bahan Baku -->
                <h3 class="font-bold text-lg text-[#14110F]">4. Tambah Bahan Baku (Persediaan)</h3>
                <p class="text-xs text-[#575047] mb-4">Bahan baku yang dipakai untuk membuat masakan/minuman Anda.</p>

                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Nama Bahan</label>
                    <input type="text" name="ingredient_name" placeholder="Contoh: Daging Ayam, Beras, Telur..." required
                           class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Satuan Dasar</label>
                        <select name="base_unit" required class="w-full text-sm px-3 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                            <option value="g">Gram (g)</option>
                            <option value="ml">Mililiter (ml)</option>
                            <option value="pcs">Pieces / Butir (pcs)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Saldo Awal</label>
                        <input type="number" step="0.01" name="stock_qty" value="1000" required
                               class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                    </div>
                </div>

            @elseif($step === 5)
                <!-- Step 5: Resep -->
                <h3 class="font-bold text-lg text-[#14110F]">5. Tautkan Resep ke Menu</h3>
                <p class="text-xs text-[#575047] mb-4">Tentukan berapa banyak bahan yang terpakai untuk satu porsi.</p>

                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Menu</label>
                    <select name="menu_id" required class="w-full text-sm px-3 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                        @foreach($menus as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Bahan Baku</label>
                        <select name="ingredient_id" required class="w-full text-sm px-3 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                            @foreach($ingredients as $ing)
                                <option value="{{ $ing->id }}">{{ $ing->name }} ({{ $ing->base_unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Takaran per Porsi</label>
                        <input type="number" step="0.0001" min="0.0001" name="qty_base" value="100" required
                               class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                    </div>
                </div>

            @elseif($step === 6)
                <!-- Step 6: Akun Kasir / Selesai -->
                <h3 class="font-bold text-lg text-[#14110F]">6. Buat Akun Kasir (Opsional)</h3>
                <p class="text-xs text-[#575047] mb-4">Kasir dapat melakukan transaksi kasir dan shift tanpa akses ke laporan keuangan atau biaya HPP.</p>

                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Nama Kasir</label>
                    <input type="text" name="cashier_name" value="Kasir 1"
                           class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Email Kasir</label>
                    <input type="email" name="cashier_email" placeholder="kasir@outlet.id"
                           class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Kata Sandi</label>
                        <input type="password" name="cashier_password" value="kasir12345"
                               class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">PIN 4 Digit</label>
                        <input type="password" name="cashier_pin" value="1234" maxlength="4"
                               class="w-full text-sm px-4 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                    </div>
                </div>
            @endif

            <div class="flex justify-between items-center pt-6 border-t border-[#E7E0D6]">
                @if($step > 1)
                    <a href="{{ route('wizard.index', ['step' => $step - 1]) }}" class="text-xs font-semibold text-[#575047] hover:underline">
                        &larr; Langkah Sebelumnya
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-[#9E9385] hover:underline">
                        Lewati Wizard
                    </a>
                @endif

                <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-white bg-[#C2410C] hover:bg-[#9A3412] text-sm shadow-sm">
                    {{ $step === 6 ? 'Selesai & Buka Kasir' : 'Lanjut Langkah Berikutnya →' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
