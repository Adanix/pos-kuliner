<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Demo — POS Kuliner</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FAF8F5] text-[#14110F] min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full py-6">
        <!-- Brand Header -->
        <div class="text-center mb-6">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 mb-3 group">
                <div class="inline-flex w-12 h-12 rounded-2xl bg-[#C2410C] text-white items-center justify-center font-bold text-2xl shadow-md group-hover:scale-105 transition-transform">
                    P
                </div>
            </a>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">POS Kuliner</h1>
            <p class="text-sm text-[#575047] mt-1">Sistem Kasir Pintar &amp; Otomasi Dapur Kuliner Modern</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl border border-[#E7E0D6] p-6 sm:p-8 shadow-sm">
            @if(session('success'))
                <div class="mb-4 p-3 rounded-xl bg-[#ECFDF5] border border-[#A7F3D0] text-[#047857] text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-[#DC2626] text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- 1-Click Quick Demo Login Section (Featured) -->
            <div class="space-y-3 mb-6">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#C2410C]">Pilih Akses Demo (1-Klik)</span>
                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-[#FFF4EC] text-[#C2410C] font-semibold border border-[#FFE6D5]">Langsung Masuk</span>
                </div>

                <!-- Tombol Demo Pemilik -->
                <form method="POST" action="{{ route('login.demo') }}">
                    @csrf
                    <input type="hidden" name="role" value="owner">
                    <button type="submit"
                            class="w-full p-4 rounded-xl text-left bg-gradient-to-r from-[#FFF4EC] to-white border-2 border-[#C2410C] hover:bg-[#FFE6D5] transition group shadow-2xs cursor-pointer flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-lg bg-[#C2410C] text-white flex items-center justify-center font-bold text-lg shadow-2xs group-hover:scale-105 transition-transform">
                                👑
                            </span>
                            <div>
                                <div class="font-bold text-[#14110F] text-sm flex items-center gap-1.5">
                                    <span>Masuk sebagai Pemilik (Owner)</span>
                                </div>
                                <div class="text-xs text-[#786E64] mt-0.5">Full Akses: Dashboard, Resep HPP, Stok &amp; Laporan</div>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-[#C2410C] group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>

                <!-- Tombol Demo Kasir -->
                <form method="POST" action="{{ route('login.demo') }}">
                    @csrf
                    <input type="hidden" name="role" value="cashier">
                    <button type="submit"
                            class="w-full p-4 rounded-xl text-left bg-[#FAF8F5] border border-[#D3C9BD] hover:bg-[#F4F0EA] hover:border-[#C2410C]/50 transition group shadow-2xs cursor-pointer flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-10 h-10 rounded-lg bg-[#E7E0D6] text-[#575047] flex items-center justify-center font-bold text-lg shadow-2xs group-hover:scale-105 transition-transform">
                                💳
                            </span>
                            <div>
                                <div class="font-bold text-[#14110F] text-sm">
                                    <span>Masuk sebagai Kasir (Cashier)</span>
                                </div>
                                <div class="text-xs text-[#786E64] mt-0.5">Akses Operasional: Kasir POS, Shift &amp; Cetak Struk</div>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-[#575047] group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>
            </div>

            <!-- Divider with Collapsible Manual Login -->
            <div x-data="{ showManual: false }" class="pt-2 border-t border-[#E7E0D6]">
                <div class="text-center">
                    <button type="button" @click="showManual = !showManual"
                            class="text-xs font-semibold text-[#786E64] hover:text-[#14110F] transition py-1 inline-flex items-center gap-1.5 cursor-pointer">
                        <span>Atau masuk manual dengan email &amp; kata sandi</span>
                        <svg class="w-3.5 h-3.5 transition-transform" :class="showManual ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </div>

                <form x-show="showManual" x-cloak method="POST" action="{{ route('login') }}" class="space-y-3 mt-4 pt-3 border-t border-dashed border-[#E7E0D6]">
                    @csrf
                    <div>
                        <label for="email" class="block text-xs font-semibold text-[#14110F] mb-1">Email Akun</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="w-full px-3.5 py-2 rounded-xl border border-[#D3C9BD] focus:outline-none focus:ring-2 focus:ring-[#C2410C] text-sm bg-white"
                               placeholder="nama@outlet.id">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-[#14110F] mb-1">Kata Sandi</label>
                        <input type="password" id="password" name="password"
                               class="w-full px-3.5 py-2 rounded-xl border border-[#D3C9BD] focus:outline-none focus:ring-2 focus:ring-[#C2410C] text-sm bg-white"
                               placeholder="••••••••">
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 px-4 rounded-xl font-semibold text-white bg-[#575047] hover:bg-[#14110F] transition text-xs shadow-2xs mt-2">
                        Masuk Manual
                    </button>
                </form>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="flex items-center justify-center gap-4 mt-6 text-xs text-[#575047]">
            <a href="{{ route('home') }}" class="hover:text-[#C2410C] transition flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Halaman Beranda</span>
            </a>
            <span>•</span>
            <a href="{{ route('docs') }}" class="hover:text-[#C2410C] transition flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>Dokumentasi Fitur</span>
            </a>
        </div>

        <div class="text-center mt-4 text-xs text-[#9E9385]">
            &copy; 2026 POS Kuliner · Satu Outlet, Tiga Kanal (POS, GoFood, ShopeeFood)
        </div>
    </div>
</body>
</html>
