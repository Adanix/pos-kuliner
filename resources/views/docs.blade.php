<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi Fitur — POS Kuliner</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FAF8F5] text-[#14110F] min-h-screen flex flex-col font-sans antialiased selection:bg-[#FFE6D5] selection:text-[#C2410C]">

    <!-- Header / Navbar -->
    <header class="bg-white border-b border-[#E7E0D6] sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-[#C2410C] text-white flex items-center justify-center font-bold text-lg shadow-sm">
                    P
                </span>
                <span class="font-bold text-lg tracking-tight text-[#14110F]">POS <span class="text-[#C2410C]">Kuliner</span></span>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="text-sm font-medium text-[#575047] hover:text-[#C2410C] transition px-3 py-1.5">
                    Beranda
                </a>
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold bg-[#C2410C] text-white hover:bg-[#9A3412] transition shadow-sm">
                    <span>Akses Demo</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </header>

    <!-- Docs Layout -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-1">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Sidebar Navigation (Desktop) -->
            <aside class="hidden lg:block lg:col-span-3 sticky top-24 self-start space-y-6">
                <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-2xs">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#9E9385] mb-3">Daftar Modul Fitur</h3>
                    <nav class="space-y-1 text-sm font-medium">
                        <a href="#ringkasan" class="block px-3 py-2 rounded-lg text-[#575047] hover:bg-[#FAF8F5] hover:text-[#C2410C] transition">1. Ringkasan Sistem</a>
                        <a href="#pos-kasir" class="block px-3 py-2 rounded-lg text-[#575047] hover:bg-[#FAF8F5] hover:text-[#C2410C] transition">2. Kasir POS &amp; Transaksi</a>
                        <a href="#ai-impor" class="block px-3 py-2 rounded-lg text-[#575047] hover:bg-[#FAF8F5] hover:text-[#C2410C] transition">3. AI Impor Pesanan Online</a>
                        <a href="#resep-hpp" class="block px-3 py-2 rounded-lg text-[#575047] hover:bg-[#FAF8F5] hover:text-[#C2410C] transition">4. Resep Dapur &amp; HPP Otomatis</a>
                        <a href="#stok-wac" class="block px-3 py-2 rounded-lg text-[#575047] hover:bg-[#FAF8F5] hover:text-[#C2410C] transition">5. Buku Besar Stok (WAC)</a>
                        <a href="#sesi-shift" class="block px-3 py-2 rounded-lg text-[#575047] hover:bg-[#FAF8F5] hover:text-[#C2410C] transition">6. Sesi Kasir &amp; Pergantian Shift</a>
                        <a href="#laporan-keuangan" class="block px-3 py-2 rounded-lg text-[#575047] hover:bg-[#FAF8F5] hover:text-[#C2410C] transition">7. Laporan Penjualan &amp; Laba</a>
                        <a href="#keamanan-role" class="block px-3 py-2 rounded-lg text-[#575047] hover:bg-[#FAF8F5] hover:text-[#C2410C] transition">8. Hak Akses &amp; PIN Void</a>
                        <a href="#catatan-pengembangan" class="block px-3 py-2 rounded-lg text-[#C2410C] bg-[#FFF4EC] font-semibold transition mt-2">Catatan Pengembangan</a>
                    </nav>
                </div>

                <div class="bg-[#FAF8F5] rounded-2xl border border-[#E7E0D6] p-5">
                    <p class="text-xs text-[#575047] leading-relaxed">
                        Perlu mencoba alur kasir sekarang? Gunakan fitur 1-klik login di halaman demo.
                    </p>
                    <a href="{{ route('login') }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-[#C2410C] hover:underline">
                        <span>Buka Halaman Demo</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </aside>

            <!-- Main Documentation Content -->
            <main class="lg:col-span-9 space-y-12">

                <!-- Header Title -->
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-[#FFF4EC] text-[#C2410C] border border-[#FFE6D5] mb-3">
                        Dokumentasi Resmi POS Kuliner v2.0
                    </div>
                    <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-[#14110F]">
                        Panduan Lengkap Fitur &amp; Alur Operasional POS Kuliner
                    </h1>
                    <p class="mt-3 text-base text-[#575047] leading-relaxed">
                        Dokumentasi ini menjelaskan secara menyeluruh seluruh fitur, alur kerja bisnis, logika perhitungan finansial, dan mekanisme kontrol stok yang tertanam di dalam aplikasi POS Kuliner.
                    </p>
                </div>

                <!-- Section 1: Ringkasan Sistem -->
                <section id="ringkasan" class="bg-white rounded-2xl border border-[#E7E0D6] p-6 sm:p-8 shadow-2xs scroll-mt-24">
                    <h2 class="text-xl font-bold text-[#14110F] mb-3 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-[#C2410C] text-white flex items-center justify-center text-xs">1</span>
                        Ringkasan Arsitektur &amp; Tujuan Sistem
                    </h2>
                    <p class="text-sm text-[#575047] leading-relaxed mb-4">
                        POS Kuliner dirancang khusus untuk memenuhi dinamika operasional industri kuliner (Food &amp; Beverage) di Indonesia, mulai dari stand makanan, kafe, warung makan, hingga resto kasual. Sistem ini menyatukan tiga pilar penting operasional kuliner:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-[#575047]">
                        <div class="p-4 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6]">
                            <h4 class="font-bold text-[#14110F] text-sm mb-1">⚡ Kecepatan Kasir</h4>
                            Operasi cepat tanpa lag, antarmuka layar sentuh ramah tablet/smartphone, split bill, serta cetak struk termal standar kasir.
                        </div>
                        <div class="p-4 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6]">
                            <h4 class="font-bold text-[#14110F] text-sm mb-1">🤖 AI Vision Online</h4>
                            Impor pesanan platform pesan antar (GoFood &amp; ShopeeFood) cukup dengan upload screenshot tanpa perlu integrasi API mahal.
                        </div>
                        <div class="p-4 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6]">
                            <h4 class="font-bold text-[#14110F] text-sm mb-1">📦 Akurasi HPP &amp; Stok</h4>
                            Kartu stok perpetual menggunakan metode Weighted Average Cost (WAC) untuk mengetahui laba kotor riil setiap porsi hidangan.
                        </div>
                    </div>
                </section>

                <!-- Section 2: Kasir POS & Transaksi -->
                <section id="pos-kasir" class="bg-white rounded-2xl border border-[#E7E0D6] p-6 sm:p-8 shadow-2xs scroll-mt-24">
                    <h2 class="text-xl font-bold text-[#14110F] mb-3 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-[#C2410C] text-white flex items-center justify-center text-xs">2</span>
                        Kasir POS (Point of Sale) &amp; Transaksi
                    </h2>
                    <div class="text-sm text-[#575047] space-y-3 leading-relaxed">
                        <p>
                            Modul kasir dirancang dengan alur kerja ergonomis agar kasir dapat memproses antrean pelanggan dalam hitungan detik.
                        </p>
                        <ul class="list-disc list-inside space-y-1.5 ml-1">
                            <li><strong class="text-[#14110F]">Kanal Penjualan Kasir:</strong> Mendukung transaksi <em>Dine-in (Makan di Tempat)</em> dan <em>Takeaway (Bawa Pulang)</em>.</li>
                            <li><strong class="text-[#14110F]">Katalog Menu Interaktif:</strong> Menu dikelompokkan berdasarkan kategori, dilengkapi foto hidangan, status stok realtime, serta pencarian instan nama menu.</li>
                            <li><strong class="text-[#14110F]">Keranjang &amp; Modifikasi Pesanan:</strong> Mengubah jumlah porsi secara dinamis, menambahkan catatan khusus tiap hidangan (misal: "tanpa cabai", "es sedikit").</li>
                            <li><strong class="text-[#14110F]">Metode Pembayaran:</strong> Mendukung Tunai (Cash) dengan kalkulator uang diterima &amp; kembalian, QRIS / Transfer Bank, serta Kartu Debit.</li>
                            <li><strong class="text-[#14110F]">Cetak Struk Termal:</strong> Format struk ramah printer kasir termal 58mm atau 80mm dengan informasi detail pesanan, pajak, diskon, dan nomor struk unik.</li>
                        </ul>
                    </div>
                </section>

                <!-- Section 3: AI Impor Pesanan Online -->
                <section id="ai-impor" class="bg-white rounded-2xl border border-[#E7E0D6] p-6 sm:p-8 shadow-2xs scroll-mt-24">
                    <h2 class="text-xl font-bold text-[#14110F] mb-3 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-[#C2410C] text-white flex items-center justify-center text-xs">3</span>
                        AI Vision Impor Pesanan Online (GoFood &amp; ShopeeFood)
                    </h2>
                    <div class="text-sm text-[#575047] space-y-3 leading-relaxed">
                        <p>
                            Mengatasi kendala umum pemilik resto yang harus mengetik ulang pesanan dari aplikasi merchant online secara manual ke sistem kasir.
                        </p>
                        <div class="p-4 rounded-xl bg-[#FFF9F5] border border-[#FFE6D5]">
                            <h4 class="font-semibold text-[#C2410C] mb-1">Alur Kerja Otomasi AI:</h4>
                            <ol class="list-decimal list-inside space-y-1 text-xs">
                                <li>Kasir mengambil tangkapan layar (screenshot) struk atau rincian order di aplikasi GoBiz atau Shopee Partner.</li>
                                <li>Unggah file gambar ke menu <strong>Impor AI Online</strong>.</li>
                                <li>Model AI Vision menganalisis gambar dan mengekstrak nomor order, kanal (GoFood/ShopeeFood), daftar menu, jumlah porsi, nominal kotor, dan komisi merchant.</li>
                                <li>Layar konfirmasi (Review) memungkinkan staf memverifikasi data sebelum transaksi tersimpan ke buku besar &amp; stok dapur otomatis terpotong.</li>
                            </ol>
                        </div>
                    </div>
                </section>

                <!-- Section 4: Resep Dapur & HPP Otomatis -->
                <section id="resep-hpp" class="bg-white rounded-2xl border border-[#E7E0D6] p-6 sm:p-8 shadow-2xs scroll-mt-24">
                    <h2 class="text-xl font-bold text-[#14110F] mb-3 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-[#C2410C] text-white flex items-center justify-center text-xs">4</span>
                        Resep Dapur &amp; Perhitungan HPP Otomatis
                    </h2>
                    <div class="text-sm text-[#575047] space-y-3 leading-relaxed">
                        <p>
                            Kunci keuntungan bisnis kuliner terletak pada kepastian Harga Pokok Penjualan (HPP). POS Kuliner menghubungkan menu jual dengan komposisi bahan baku (Bill of Materials):
                        </p>
                        <ul class="list-disc list-inside space-y-1.5 ml-1">
                            <li><strong class="text-[#14110F]">Gramatur Presisi:</strong> Masukkan takaran bahan baku per porsi (contoh: 150 gr daging ayam, 20 ml minyak goreng, 1 set box takeaway).</li>
                            <li><strong class="text-[#14110F]">Kalkulasi HPP Dinamis:</strong> Sistem mengalikan takaran resep dengan harga beli rata-rata bahan baku berjalan.</li>
                            <li><strong class="text-[#14110F]">Indikator Margin Laba:</strong> Setiap kali terjadi fluktuasi harga bahan di pasar, margin laba kotor menu akan otomatis terhitung kembali tanpa perlu kalkulasi manual.</li>
                        </ul>
                    </div>
                </section>

                <!-- Section 5: Buku Besar Stok (WAC) -->
                <section id="stok-wac" class="bg-white rounded-2xl border border-[#E7E0D6] p-6 sm:p-8 shadow-2xs scroll-mt-24">
                    <h2 class="text-xl font-bold text-[#14110F] mb-3 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-[#C2410C] text-white flex items-center justify-center text-xs">5</span>
                        Buku Besar Stok &amp; Metode Biaya Rata-Rata (WAC)
                    </h2>
                    <div class="text-sm text-[#575047] space-y-3 leading-relaxed">
                        <p>
                            Stok di POS Kuliner dicatat dengan prinsip <em>perpetual inventory ledger</em> yang tidak dapat diubah di masa lalu (immutable) untuk menjamin integritas data audit:
                        </p>
                        <ul class="list-disc list-inside space-y-1.5 ml-1">
                            <li><strong class="text-[#14110F]">Metode Weighted Average Cost (WAC):</strong> Setiap pembelian bahan baku baru akan memperbarui harga pokok rata-rata tertimbang per satuan unit.</li>
                            <li><strong class="text-[#14110F]">Pencatatan Pembelian:</strong> Mencatat stok masuk disertai faktur pembelian, harga beli satuan, dan nama pemasok.</li>
                            <li><strong class="text-[#14110F]">Pencatatan Bahan Rusak / Basi (Waste):</strong> Bahan yang kedaluwarsa atau tumpah dapat dicatat secara transparan dengan alasan spesifik agar tidak menjadi kebocoran modal.</li>
                            <li><strong class="text-[#14110F]">Stock Opname Fisik:</strong> Fitur penyesuaian berkala untuk menyamakan saldo fisik di rak dapur dengan saldo di sistem.</li>
                        </ul>
                    </div>
                </section>

                <!-- Section 6: Sesi Kasir & Shift -->
                <section id="sesi-shift" class="bg-white rounded-2xl border border-[#E7E0D6] p-6 sm:p-8 shadow-2xs scroll-mt-24">
                    <h2 class="text-xl font-bold text-[#14110F] mb-3 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-[#C2410C] text-white flex items-center justify-center text-xs">6</span>
                        Sesi Kasir &amp; Pergantian Shift (Cash Drawer Control)
                    </h2>
                    <div class="text-sm text-[#575047] space-y-3 leading-relaxed">
                        <p>
                            Mencegah kecurangan uang tunai dan mempermudah rekonsiliasi kas saat pertukaran shift kerja:
                        </p>
                        <ul class="list-disc list-inside space-y-1.5 ml-1">
                            <li><strong class="text-[#14110F]">Buka Kasir:</strong> Kasir wajib menginput modal kembalian awal (misal: Rp 200.000) sebelum dapat memproses transaksi baru.</li>
                            <li><strong class="text-[#14110F]">Pelacakan Arus Kas Masuk:</strong> Transaksi tunai tercatat otomatis menambah nilai kas yang wajib ada di laci.</li>
                            <li><strong class="text-[#14110F]">Tutup Kasir &amp; Rekonsiliasi:</strong> Di akhir shift, kasir memasukkan jumlah fisik uang tunai aktual. Sistem langsung menampilkan apakah kas sesuai, surplus, atau defisit.</li>
                        </ul>
                    </div>
                </section>

                <!-- Section 7: Laporan Keuangan & Laba -->
                <section id="laporan-keuangan" class="bg-white rounded-2xl border border-[#E7E0D6] p-6 sm:p-8 shadow-2xs scroll-mt-24">
                    <h2 class="text-xl font-bold text-[#14110F] mb-3 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-[#C2410C] text-white flex items-center justify-center text-xs">7</span>
                        Laporan Penjualan &amp; Analisis Finansial
                    </h2>
                    <div class="text-sm text-[#575047] space-y-3 leading-relaxed">
                        <p>
                            Menyajikan visibilitas total bagi pemilik usaha untuk mengambil keputusan strategis:
                        </p>
                        <ul class="list-disc list-inside space-y-1.5 ml-1">
                            <li><strong class="text-[#14110F]">Ringkasan Pendapatan:</strong> Omzet kotor, total diskon, biaya komisi online, dan pendapatan bersih per rentang tanggal.</li>
                            <li><strong class="text-[#14110F]">Laba Kotor (Gross Profit):</strong> Pendapatan bersih dikurangi total HPP bahan baku yang terpakai.</li>
                            <li><strong class="text-[#14110F]">Performa Kanal:</strong> Perbandingan kontribusi penjualan dari kasir lokal, GoFood, dan ShopeeFood.</li>
                            <li><strong class="text-[#14110F]">Menu Terlaris:</strong> Grafik hidangan yang paling banyak terjual untuk membantu perencanaan belanja dapur.</li>
                            <li><strong class="text-[#14110F]">Ekspor Format CSV:</strong> Mendukung pengunduhan data transaksi terperinci untuk diimpor ke Excel atau software akuntansi.</li>
                        </ul>
                    </div>
                </section>

                <!-- Section 8: Keamanan Role & PIN Void -->
                <section id="keamanan-role" class="bg-white rounded-2xl border border-[#E7E0D6] p-6 sm:p-8 shadow-2xs scroll-mt-24">
                    <h2 class="text-xl font-bold text-[#14110F] mb-3 flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-[#C2410C] text-white flex items-center justify-center text-xs">8</span>
                        Hak Akses Multi-Role &amp; Keamanan Otorisasi Void
                    </h2>
                    <div class="text-sm text-[#575047] space-y-3 leading-relaxed">
                        <p>
                            Sistem keamanan bertingkat menjaga operasional dari potensi kebocoran finansial:
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs mt-3">
                            <div class="p-4 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6]">
                                <h4 class="font-bold text-[#14110F] text-sm mb-1">Peran Pemilik (Owner)</h4>
                                Akses penuh ke seluruh menu, analisis laporan keuangan, pengaturan resep &amp; master bahan baku, kelola pengguna staf, dan persetujuan pembatalan nota.
                            </div>
                            <div class="p-4 rounded-xl bg-[#FAF8F5] border border-[#E7E0D6]">
                                <h4 class="font-bold text-[#14110F] text-sm mb-1">Peran Kasir (Cashier)</h4>
                                Hanya berfokus pada melayani transaksi kasir, input shift, dan melihat riwayat nota. Dibatasi dari menu pengaturan laba, resep, dan perubahan harga.
                            </div>
                        </div>
                        <p class="pt-2">
                            <strong class="text-[#14110F]">Prosedur Void Transaksi:</strong> Kasir tidak diizinkan membatalkan struk yang sudah dicetak tanpa memasukkan 6 digit PIN otorisasi Pemilik. Setiap nota yang dibatalkan akan mengembalikan saldo stok bahan baku ke gudang secara otomatis dan mencatat alasan pembatalan ke log audit.
                        </p>
                    </div>
                </section>

                <!-- Bottom Distinctive Note: Extensibility Notice (User Mandatory Requirement) -->
                <section id="catatan-pengembangan" class="rounded-2xl bg-gradient-to-r from-[#FFF4EC] to-[#FFF9F5] border-2 border-[#C2410C]/30 p-6 sm:p-8 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-[#C2410C] text-white flex items-center justify-center shrink-0 shadow-sm mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-[#C2410C] tracking-tight">Catatan Pengembangan Aplikasi:</h3>
                            <p class="mt-1 text-sm font-medium text-[#14110F] leading-relaxed">
                                <strong>web apps ini masih bisa di kembangkan sesuai kebutuhan</strong>
                            </p>
                            <p class="mt-2 text-xs text-[#575047] leading-relaxed">
                                Arsitektur kode POS Kuliner dibangun secara modular dengan standar Laravel terbaru, relasi basis data yang rapi, serta API endpoints yang siap diintegrasikan dengan modul tambahan lainnya—seperti printer thermal bluetooth ESC/POS, integrasi webhook payment gateway (Midtrans/Xendit), multi-outlet central kitchen, integrasi API GrabFood, program loyalitas pelanggan (member points), hingga integrasi sistem akuntansi lanjutan.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Bottom Back / Demo Link -->
                <div class="pt-6 border-t border-[#E7E0D6] flex flex-col sm:flex-row items-center justify-between gap-4">
                    <a href="{{ route('home') }}" class="text-sm font-semibold text-[#575047] hover:text-[#C2410C] transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Kembali ke Halaman Beranda</span>
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-[#C2410C] text-white hover:bg-[#9A3412] transition shadow-sm">
                        <span>Coba Demo Aplikasi Sekarang</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-auto bg-white border-t border-[#E7E0D6] py-6 text-xs text-[#575047]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-md bg-[#C2410C] text-white flex items-center justify-center font-bold text-xs">P</span>
                <span class="font-bold text-[#14110F]">POS Kuliner</span>
                <span>· Dokumentasi &amp; Panduan Pengguna</span>
            </div>
            <p>&copy; 2026 POS Kuliner. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
