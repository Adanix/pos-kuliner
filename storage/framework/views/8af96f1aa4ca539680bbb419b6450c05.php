<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? 'Ciora POS Kuliner'); ?></title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="bg-[#FAF8F5] text-[#14110F] min-h-screen flex flex-col font-sans antialiased">
    <!-- Top Header Navigation -->
    <header class="no-print bg-white border-b border-[#E7E0D6] sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="<?php echo e(auth()->user()?->isOwner() ? route('dashboard') : route('pos.index')); ?>" class="flex items-center gap-2">
                    <span class="w-9 h-9 rounded-xl bg-[#C2410C] text-white flex items-center justify-center font-bold text-lg shadow-sm">C</span>
                    <span class="font-bold text-lg tracking-tight text-[#14110F]">Ciora <span class="text-[#C2410C] font-semibold">POS</span></span>
                </a>
                <span class="hidden sm:inline-flex text-xs px-2.5 py-1 rounded-full font-medium bg-[#FFF4EC] text-[#C2410C] border border-[#FFE6D5]">
                    <?php echo e(auth()->user()?->isOwner() ? 'Pemilik' : 'Kasir'); ?>

                </span>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center gap-2 sm:gap-4">
                <a href="<?php echo e(route('pos.index')); ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold bg-[#C2410C] text-white hover:bg-[#9A3412] transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Kasir POS</span>
                </a>

                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" type="button" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-[#F4F0EA] transition">
                        <span class="w-8 h-8 rounded-full bg-[#E7E0D6] text-[#575047] font-semibold text-xs flex items-center justify-center">
                            <?php echo e(substr(auth()->user()?->name ?? 'U', 0, 1)); ?>

                        </span>
                        <span class="hidden md:block text-sm font-medium text-[#14110F]"><?php echo e(auth()->user()?->name); ?></span>
                        <svg class="w-4 h-4 text-[#575047]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-[#E7E0D6] py-1 z-50">
                        <div class="px-4 py-2 border-b border-[#E7E0D6] text-xs text-[#575047]">
                            <p class="font-medium text-[#14110F]"><?php echo e(auth()->user()?->name); ?></p>
                            <p class="truncate"><?php echo e(auth()->user()?->email); ?></p>
                        </div>
                        <a href="<?php echo e(route('pos.shift.index')); ?>" class="block px-4 py-2 text-sm text-[#14110F] hover:bg-[#FAF8F5]">Sesi Kasir (Shift)</a>
                        <?php if(auth()->user()?->isOwner()): ?>
                            <a href="<?php echo e(route('settings.edit')); ?>" class="block px-4 py-2 text-sm text-[#14110F] hover:bg-[#FAF8F5]">Pengaturan</a>
                        <?php endif; ?>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-[#DC2626] hover:bg-[#FEF2F2]">Keluar (Logout)</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex-1 flex max-w-7xl w-full mx-auto">
        <!-- Desktop Sidebar Navigation -->
        <aside class="no-print hidden lg:block w-60 border-r border-[#E7E0D6] bg-white py-6 px-4 shrink-0">
            <nav class="space-y-1 text-sm font-medium">
                <?php if(auth()->user()?->isOwner()): ?>
                    <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('dashboard') ? 'bg-[#FFF4EC] text-[#C2410C] font-semibold' : 'text-[#575047] hover:bg-[#FAF8F5] hover:text-[#14110F]'); ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Dashboard</span>
                    </a>
                <?php endif; ?>

                <a href="<?php echo e(route('pos.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('pos.*') ? 'bg-[#FFF4EC] text-[#C2410C] font-semibold' : 'text-[#575047] hover:bg-[#FAF8F5] hover:text-[#14110F]'); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Kasir POS</span>
                </a>

                <a href="<?php echo e(route('transactions.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('transactions.*') ? 'bg-[#FFF4EC] text-[#C2410C] font-semibold' : 'text-[#575047] hover:bg-[#FAF8F5] hover:text-[#14110F]'); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span>Riwayat Transaksi</span>
                </a>

                <a href="<?php echo e(route('impor.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('impor.*') ? 'bg-[#FFF4EC] text-[#C2410C] font-semibold' : 'text-[#575047] hover:bg-[#FAF8F5] hover:text-[#14110F]'); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Impor AI Online</span>
                </a>

                <a href="<?php echo e(route('pos.shift.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('pos.shift.*') ? 'bg-[#FFF4EC] text-[#C2410C] font-semibold' : 'text-[#575047] hover:bg-[#FAF8F5] hover:text-[#14110F]'); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Sesi Kasir (Shift)</span>
                </a>

                <?php if(auth()->user()?->isOwner()): ?>
                    <div class="pt-4 pb-2 px-3 text-[11px] font-bold tracking-wider text-[#9E9385] uppercase">Master Data & Dapur</div>

                    <a href="<?php echo e(route('menu.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('menu.*') ? 'bg-[#FFF4EC] text-[#C2410C] font-semibold' : 'text-[#575047] hover:bg-[#FAF8F5] hover:text-[#14110F]'); ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Menu & Harga</span>
                    </a>

                    <a href="<?php echo e(route('categories.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('categories.*') ? 'bg-[#FFF4EC] text-[#C2410C] font-semibold' : 'text-[#575047] hover:bg-[#FAF8F5] hover:text-[#14110F]'); ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span>Kategori</span>
                    </a>

                    <a href="<?php echo e(route('bahan.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('bahan.*') ? 'bg-[#FFF4EC] text-[#C2410C] font-semibold' : 'text-[#575047] hover:bg-[#FAF8F5] hover:text-[#14110F]'); ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>Bahan Baku</span>
                    </a>

                    <a href="<?php echo e(route('resep.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('resep.*') ? 'bg-[#FFF4EC] text-[#C2410C] font-semibold' : 'text-[#575047] hover:bg-[#FAF8F5] hover:text-[#14110F]'); ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        <span>Resep & HPP</span>
                    </a>

                    <a href="<?php echo e(route('stok.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('stok.*') ? 'bg-[#FFF4EC] text-[#C2410C] font-semibold' : 'text-[#575047] hover:bg-[#FAF8F5] hover:text-[#14110F]'); ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Pergerakan Stok</span>
                    </a>

                    <div class="pt-4 pb-2 px-3 text-[11px] font-bold tracking-wider text-[#9E9385] uppercase">Manajemen & Laporan</div>

                    <a href="<?php echo e(route('reports.index')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('reports.*') ? 'bg-[#FFF4EC] text-[#C2410C] font-semibold' : 'text-[#575047] hover:bg-[#FAF8F5] hover:text-[#14110F]'); ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Laporan Bisnis</span>
                    </a>

                    <a href="<?php echo e(route('settings.edit')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition <?php echo e(request()->routeIs('settings.*') ? 'bg-[#FFF4EC] text-[#C2410C] font-semibold' : 'text-[#575047] hover:bg-[#FAF8F5] hover:text-[#14110F]'); ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Pengaturan</span>
                    </a>
                <?php endif; ?>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 min-w-0 pb-24 lg:pb-8">
            <!-- Flash Message Alerts -->
            <?php if(session('success')): ?>
                <div class="no-print mb-6 p-4 rounded-xl bg-[#ECFDF5] border border-[#A7F3D0] text-[#047857] flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm font-medium"><?php echo e(session('success')); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="no-print mb-6 p-4 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-[#DC2626] flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm font-medium"><?php echo e(session('error')); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(session('warning')): ?>
                <div class="no-print mb-6 p-4 rounded-xl bg-[#FFFBEB] border border-[#FDE68A] text-[#B45309] flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span class="text-sm font-medium"><?php echo e(session('warning')); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="no-print mb-6 p-4 rounded-xl bg-[#FEF2F2] border border-[#FECACA] text-[#DC2626] shadow-sm">
                    <div class="font-semibold text-sm mb-1">Terdapat kesalahan pengisian data:</div>
                    <ul class="list-disc list-inside text-xs space-y-1">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($err); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php echo e($slot ?? ''); ?>

            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>

    <!-- Mobile Bottom Navigation (Viewport < 1024px) -->
    <nav class="no-print lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-[#E7E0D6] z-40 py-2 px-3 shadow-[0_-4px_12px_rgba(20,17,15,0.06)]">
        <div class="flex justify-around items-center">
            <?php if(auth()->user()?->isOwner()): ?>
                <a href="<?php echo e(route('dashboard')); ?>" class="flex flex-col items-center gap-1 py-1 px-2 text-xs <?php echo e(request()->routeIs('dashboard') ? 'text-[#C2410C] font-semibold' : 'text-[#575047]'); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Home</span>
                </a>
            <?php endif; ?>

            <a href="<?php echo e(route('pos.index')); ?>" class="flex flex-col items-center gap-1 py-1 px-2 text-xs <?php echo e(request()->routeIs('pos.*') ? 'text-[#C2410C] font-semibold' : 'text-[#575047]'); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Kasir</span>
            </a>

            <a href="<?php echo e(route('transactions.index')); ?>" class="flex flex-col items-center gap-1 py-1 px-2 text-xs <?php echo e(request()->routeIs('transactions.*') ? 'text-[#C2410C] font-semibold' : 'text-[#575047]'); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Riwayat</span>
            </a>

            <a href="<?php echo e(route('impor.index')); ?>" class="flex flex-col items-center gap-1 py-1 px-2 text-xs <?php echo e(request()->routeIs('impor.*') ? 'text-[#C2410C] font-semibold' : 'text-[#575047]'); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>AI Impor</span>
            </a>

            <?php if(auth()->user()?->isOwner()): ?>
                <a href="<?php echo e(route('reports.index')); ?>" class="flex flex-col items-center gap-1 py-1 px-2 text-xs <?php echo e(request()->routeIs('reports.*') ? 'text-[#C2410C] font-semibold' : 'text-[#575047]'); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Laporan</span>
                </a>
            <?php else: ?>
                <a href="<?php echo e(route('pos.shift.index')); ?>" class="flex flex-col items-center gap-1 py-1 px-2 text-xs <?php echo e(request()->routeIs('pos.shift.*') ? 'text-[#C2410C] font-semibold' : 'text-[#575047]'); ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Shift</span>
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/layouts/app.blade.php ENDPATH**/ ?>