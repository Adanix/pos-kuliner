<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Ciora POS Kuliner</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FAF8F5] text-[#14110F] min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-12 h-12 rounded-2xl bg-[#C2410C] text-white items-center justify-center font-bold text-2xl shadow-md mb-3">
                C
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Ciora POS Kuliner</h1>
            <p class="text-sm text-[#575047] mt-1">Sistem Kasir & Otomasi Impor Pesanan Online</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl border border-[#E7E0D6] p-6 sm:p-8 shadow-sm">
            @if(session('success'))
                <div class="mb-4 p-3 rounded-xl bg-[#ECFDF5] border border-[#A7F3D0] text-[#047857] text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-[#DC2626] text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-semibold text-[#14110F] mb-1">Email Akun</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 rounded-xl border border-[#D3C9BD] focus:outline-none focus:ring-2 focus:ring-[#C2410C] focus:border-transparent text-sm bg-white"
                           placeholder="nama@outlet.id">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-[#14110F] mb-1">Kata Sandi</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2.5 rounded-xl border border-[#D3C9BD] focus:outline-none focus:ring-2 focus:ring-[#C2410C] focus:border-transparent text-sm bg-white"
                           placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-[#575047] cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-[#D3C9BD] text-[#C2410C] focus:ring-[#C2410C]">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full py-3 px-4 rounded-xl font-semibold text-white bg-[#C2410C] hover:bg-[#9A3412] transition shadow-sm text-sm mt-2">
                    Masuk ke Sistem
                </button>
            </form>
        </div>

        <div class="text-center mt-6 text-xs text-[#9E9385]">
            &copy; 2026 Ciora POS · Satu Outlet, Tiga Kanal (POS, GoFood, ShopeeFood)
        </div>
    </div>
</body>
</html>
