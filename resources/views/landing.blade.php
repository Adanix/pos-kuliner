<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Kuliner — Sistem Kasir Pintar & Otomasi Dapur Kuliner Modern</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FAF8F5] text-[#14110F] min-h-screen flex flex-col font-sans antialiased selection:bg-[#FFE6D5] selection:text-[#C2410C]">

    <!-- Header / Navbar -->
    <header class="bg-white/90 backdrop-blur-md border-b border-[#E7E0D6] sticky top-0 z-40 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <span class="w-10 h-10 rounded-xl bg-[#C2410C] text-white flex items-center justify-center font-bold text-xl shadow-sm group-hover:scale-105 transition-transform">
                    P
                </span>
                <div class="flex flex-col">
                    <span class="font-bold text-xl tracking-tight text-[#14110F]">POS <span class="text-[#C2410C]">Kuliner</span></span>
                    <span class="text-[11px] text-[#786E64] font-medium leading-none">Smart Cashier & Kitchen</span>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-[#575047]">
                <a href="#fitur" class="hover:text-[#C2410C] transition">Fitur Utama</a>
                <a href="#demo" class="hover:text-[#C2410C] transition">Pilihan Demo</a>
                <a href="{{ route('docs') }}" class="hover:text-[#C2410C] transition flex items-center gap-1.5">
                    <span>Dokumentasi</span>
                    <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full bg-[#FFF4EC] text-[#C2410C] border border-[#FFE6D5]">Lengkap</span>
                </a>
            </nav>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ auth()->user()->isOwner() ? route('dashboard') : route('pos.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#C2410C] text-white hover:bg-[#9A3412] transition shadow-sm">
                        <span>Buka Aplikasi</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('docs') }}"
                       class="hidden sm:inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium text-[#575047] hover:bg-[#F4F0EA] transition">
                        Buku Panduan
                    </a>
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold bg-[#C2410C] text-white hover:bg-[#9A3412] transition shadow-sm hover:shadow">
                        <span>Coba Demo</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative overflow-hidden py-16 sm:py-24 border-b border-[#E7E0D6] bg-gradient-to-b from-white to-[#FAF8F5]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-[#FFF4EC] text-[#C2410C] border border-[#FFE6D5] mb-6">
                    <span class="w-2 h-2 rounded-full bg-[#C2410C] animate-pulse"></span>
                    <span>Versi Demo Siap Diuji · 3 Kanal Penjualan Terpadu</span>
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-[#14110F] leading-[1.15]">
                    Sistem Kasir Pintar &amp; Otomasi Dapur Kuliner Modern
                </h1>

                <p class="mt-6 text-lg sm:text-xl text-[#575047] leading-relaxed">
                    Kelola transaksi <strong class="text-[#14110F]">Kasir Langsung (Dine-in/Takeaway)</strong> serta impor otomatis pesanan dari <strong class="text-[#14110F]">GoFood &amp; ShopeeFood</strong> via AI Vision. Lengkap dengan kartu stok HPP, resep dinamis, dan laporan keuangan akurat.
                </p>

                <!-- Action CTAs -->
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4">
                    <a href="{{ route('login') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-semibold text-white bg-[#C2410C] hover:bg-[#9A3412] transition shadow-md shadow-orange-500/10 text-base">
                        <span>Mulai Akses Demo (1-Klik)</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="{{ route('docs') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl font-semibold text-[#14110F] bg-white border border-[#D3C9BD] hover:bg-[#F4F0EA] transition text-base">
                        <svg class="w-5 h-5 text-[#C2410C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Dokumentasi Fitur</span>
                    </a>
                </div>

                <!-- Channel pills -->
                <div class="mt-10 pt-8 border-t border-[#E7E0D6] flex flex-wrap items-center justify-center gap-4 text-xs font-semibold text-[#575047]">
                    <span class="text-[#786E64] font-medium">Kanal Penjualan Didukung:</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-[#E7E0D6] shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-[#C2410C]"></span> Kasir Dine-in / Takeaway
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-[#E7E0D6] shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-[#00AA13]"></span> GoFood Online
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-[#E7E0D6] shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-[#EE4D2D]"></span> ShopeeFood Online
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Fitur Utama Section -->
    <section id="fitur" class="py-16 sm:py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-xs font-bold uppercase tracking-wider text-[#C2410C] mb-2">Fitur Unggulan</h2>
            <p class="text-3xl font-bold tracking-tight text-[#14110F]">Semua yang Anda butuhkan untuk mengelola outlet kuliner</p>
            <p class="text-[#575047] mt-3">Dibangun khusus untuk kecepatan operasi kasir, akurasi stok dapur, dan kejelasan laporan laba bersih.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Feature 1 -->
            <div class="bg-white p-7 rounded-2xl border border-[#E7E0D6] shadow-sm hover:border-[#C2410C]/40 transition group">
                <div class="w-12 h-12 rounded-xl bg-[#FFF4EC] text-[#C2410C] flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-[#14110F] mb-2">Kasir Layar Sentuh Cepat</h3>
                <p class="text-sm text-[#575047] leading-relaxed">
                    Filter menu kategori instan, pencarian cepat, split bill, perhitungan diskon &amp; pajak otomatis, serta cetak struk termal standar 58mm/80mm.
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-white p-7 rounded-2xl border border-[#E7E0D6] shadow-sm hover:border-[#C2410C]/40 transition group">
                <div class="w-12 h-12 rounded-xl bg-[#FFF4EC] text-[#C2410C] flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-[#14110F] mb-2">AI Impor Pesanan Online</h3>
                <p class="text-sm text-[#575047] leading-relaxed">
                    Unggah tangkapan layar struk atau pesanan GoFood &amp; ShopeeFood. AI Vision mengekstrak item pesanan, total rupiah, dan komisi platform tanpa input manual.
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="bg-white p-7 rounded-2xl border border-[#E7E0D6] shadow-sm hover:border-[#C2410C]/40 transition group">
                <div class="w-12 h-12 rounded-xl bg-[#FFF4EC] text-[#C2410C] flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-[#14110F] mb-2">Resep Dapur &amp; HPP Otomatis</h3>
                <p class="text-sm text-[#575047] leading-relaxed">
                    Setiap menu terhubung langsung ke formula gramatur bahan baku. HPP dihitung otomatis sehingga margin laba kotor selalu terlihat transparan.
                </p>
            </div>

            <!-- Feature 4 -->
            <div class="bg-white p-7 rounded-2xl border border-[#E7E0D6] shadow-sm hover:border-[#C2410C]/40 transition group">
                <div class="w-12 h-12 rounded-xl bg-[#FFF4EC] text-[#C2410C] flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-[#14110F] mb-2">Buku Besar Stok (Metode WAC)</h3>
                <p class="text-sm text-[#575047] leading-relaxed">
                    Buku mutasi stok bahan baku (pembelian, penjualan, bahan terbuang/waste, opname) dengan sistem rata-rata tertimbang (Weighted Average Cost).
                </p>
            </div>

            <!-- Feature 5 -->
            <div class="bg-white p-7 rounded-2xl border border-[#E7E0D6] shadow-sm hover:border-[#C2410C]/40 transition group">
                <div class="w-12 h-12 rounded-xl bg-[#FFF4EC] text-[#C2410C] flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-[#14110F] mb-2">Sesi Kasir &amp; Pergantian Shift</h3>
                <p class="text-sm text-[#575047] leading-relaxed">
                    Kontrol arus kas ketat dengan pencatatan kas modal awal, rekonsiliasi kas riil saat tutup shift, dan deteksi selisih uang kas otomatis.
                </p>
            </div>

            <!-- Feature 6 -->
            <div class="bg-white p-7 rounded-2xl border border-[#E7E0D6] shadow-sm hover:border-[#C2410C]/40 transition group">
                <div class="w-12 h-12 rounded-xl bg-[#FFF4EC] text-[#C2410C] flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-[#14110F] mb-2">Keamanan Role &amp; PIN Void</h3>
                <p class="text-sm text-[#575047] leading-relaxed">
                    Pemisahan hak akses yang ketat antara Kasir dan Pemilik. Pembatalan transaksi (Void) tidak bisa dilakukan sembarangan dan wajib otorisasi PIN Owner.
                </p>
            </div>
        </div>
    </section>

    <!-- Pilihan Demo Interaktif Section -->
    <section id="demo" class="py-16 bg-[#FFF9F5] border-y border-[#FFE6D5]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#FFE6D5] text-[#C2410C] mb-2">
                    ⚡ Coba Langsung Tanpa Ketik Password
                </span>
                <h2 class="text-3xl font-bold tracking-tight text-[#14110F]">Pilih Akun Demo &amp; Uji Coba Sekarang</h2>
                <p class="text-[#575047] mt-2">Klik tombol di bawah untuk langsung merasakan alur kerja Pemilik maupun Kasir.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <!-- Card 1: Demo Pemilik -->
                <div class="bg-white rounded-2xl border-2 border-[#C2410C] p-8 shadow-md relative flex flex-col justify-between">
                    <div class="absolute -top-3.5 right-6 px-3 py-1 bg-[#C2410C] text-white text-xs font-bold rounded-full uppercase tracking-wider">
                        Full Akses
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <span class="w-12 h-12 rounded-xl bg-[#FFF4EC] text-[#C2410C] flex items-center justify-center font-bold text-xl">
                                👑
                            </span>
                            <div>
                                <h3 class="text-xl font-bold text-[#14110F]">Akun Pemilik (Owner)</h3>
                                <p class="text-xs text-[#786E64]">owner@kuliner.ramadadawah.my.id</p>
                            </div>
                        </div>

                        <p class="text-sm text-[#575047] mb-6">
                            Cocok untuk melihat metrik omzet, margin kotor, mengatur resep &amp; HPP bahan baku, stok opname, dan laporan keuangan komprehensif.
                        </p>

                        <ul class="text-xs text-[#575047] space-y-2 mb-8">
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Dashboard Ringkasan Penjualan &amp; Laba</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Manajemen Resep &amp; Kalkulasi HPP</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Pembelian Bahan &amp; Opname Stok Fisik</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Otorisasi Void Struk &amp; Ekspor CSV</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('login.demo') }}">
                        @csrf
                        <input type="hidden" name="role" value="owner">
                        <button type="submit" class="w-full py-3.5 px-4 rounded-xl font-semibold text-white bg-[#C2410C] hover:bg-[#9A3412] transition shadow-sm text-sm flex items-center justify-center gap-2 cursor-pointer">
                            <span>⚡ Masuk sebagai Pemilik</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>
                </div>

                <!-- Card 2: Demo Kasir -->
                <div class="bg-white rounded-2xl border border-[#E7E0D6] p-8 shadow-sm hover:border-[#D3C9BD] transition relative flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <span class="w-12 h-12 rounded-xl bg-[#F4F0EA] text-[#575047] flex items-center justify-center font-bold text-xl">
                                💳
                            </span>
                            <div>
                                <h3 class="text-xl font-bold text-[#14110F]">Akun Kasir (Cashier)</h3>
                                <p class="text-xs text-[#786E64]">kasir@kuliner.ramadadawah.my.id</p>
                            </div>
                        </div>

                        <p class="text-sm text-[#575047] mb-6">
                            Fokus pada alur kerja operasional meja kasir, melayani pesanan pelanggan dengan cepat, dan cetak struk tanpa gangguan menu manajerial.
                        </p>

                        <ul class="text-xs text-[#575047] space-y-2 mb-8">
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Antarmuka Kasir Layar Sentuh &amp; Keranjang Belanja</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Input Tunai, QRIS, &amp; Kartu Debit</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Cetak Struk Belanja Termal 58mm/80mm</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Buka &amp; Tutup Shift Kasir</li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('login.demo') }}">
                        @csrf
                        <input type="hidden" name="role" value="cashier">
                        <button type="submit" class="w-full py-3.5 px-4 rounded-xl font-semibold text-[#14110F] bg-[#FAF8F5] hover:bg-[#F4F0EA] border border-[#D3C9BD] transition shadow-2xs text-sm flex items-center justify-center gap-2 cursor-pointer">
                            <span>⚡ Masuk sebagai Kasir</span>
                            <svg class="w-4 h-4 text-[#575047]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-8 text-center text-xs text-[#786E64]">
                Atau kunjungi <a href="{{ route('login') }}" class="text-[#C2410C] font-semibold underline hover:text-[#9A3412]">Halaman Login</a> untuk masuk dengan email &amp; kata sandi kustom.
            </div>
        </div>
    </section>

    <!-- Footer Banner Call to Action -->
    <section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-[#14110F] text-white p-8 sm:p-12 relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="max-w-xl">
                <span class="text-xs font-bold tracking-wider uppercase text-[#FB923C] mb-2 block">Dukungan Dokumentasi Lengkap</span>
                <h3 class="text-2xl sm:text-3xl font-bold tracking-tight">Pelajari Arsitektur &amp; Fitur POS Kuliner</h3>
                <p class="text-sm text-[#A89F91] mt-2 leading-relaxed">
                    Lihat panduan lengkap cara kerja sistem kasir, rumus HPP resep dapur, kartu stok perpetual WAC, hingga impor AI vision.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto shrink-0">
                <a href="{{ route('docs') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold bg-[#C2410C] hover:bg-[#9A3412] text-white transition text-sm">
                    <span>Baca Dokumentasi</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold bg-white/10 hover:bg-white/20 text-white transition text-sm">
                    <span>Uji Coba Sekarang</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="mt-auto bg-white border-t border-[#E7E0D6] py-8 text-sm text-[#575047]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="w-7 h-7 rounded-lg bg-[#C2410C] text-white flex items-center justify-center font-bold text-sm">P</span>
                <span class="font-bold text-[#14110F]">POS Kuliner</span>
                <span class="text-xs text-[#786E64]">· Smart Cashier &amp; Kitchen Management</span>
            </div>
            <div class="flex items-center gap-6 text-xs">
                <a href="{{ route('home') }}" class="hover:text-[#C2410C] transition">Beranda</a>
                <a href="{{ route('docs') }}" class="hover:text-[#C2410C] transition">Dokumentasi Fitur</a>
                <a href="{{ route('login') }}" class="hover:text-[#C2410C] transition">Masuk Demo</a>
            </div>
            <p class="text-xs text-[#786E64]">&copy; 2026 POS Kuliner. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
