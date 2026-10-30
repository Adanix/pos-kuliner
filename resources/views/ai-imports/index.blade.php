@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">AI Impor Pesanan Online</h1>
            <p class="text-sm text-[#575047] mt-0.5">Ekstrak otomatis pesanan makanan dari tangkapan layar (screenshot) GoFood dan ShopeeFood.</p>
        </div>

        <!-- Quota Badge -->
        <div class="px-4 py-2 rounded-2xl bg-white border border-[#E7E0D6] shadow-sm flex items-center gap-3">
            <span class="text-xs text-[#575047] uppercase font-semibold">Kuota AI Bulanan:</span>
            <span class="font-extrabold text-sm text-[#C2410C] tabular-nums">{{ $monthlyUsage }} / {{ $monthlyQuota }}</span>
        </div>
    </div>

    <!-- Upload Card -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm">
        <h3 class="font-bold text-base text-[#14110F] mb-3">Unggah Screenshot Baru</h3>
        <p class="text-xs text-[#575047] mb-4">Pastikan gambar menampilkan detail item, kuantitas, dan total harga pesanan secara jelas.</p>

        <form method="POST" action="{{ route('impor.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">File Gambar Screenshot (JPG, PNG, WebP &le; 8MB)</label>
                    <input type="file" name="screenshot" accept="image/jpeg,image/png,image/webp" required
                           class="w-full text-xs text-[#575047] file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#FFF4EC] file:text-[#C2410C] hover:file:bg-[#ffe6d5] border border-[#D3C9BD] rounded-xl p-1 bg-[#FAF8F5]">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#575047] uppercase mb-1">Pilihan Kanal Aplikasi</label>
                    <select name="channel" class="w-full text-xs px-3 py-2.5 rounded-xl border border-[#D3C9BD] bg-[#FAF8F5]">
                        <option value="auto">Deteksi Otomatis</option>
                        <option value="gofood">GoFood</option>
                        <option value="shopeefood">ShopeeFood</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-white bg-[#C2410C] hover:bg-[#9A3412] text-sm shadow-md transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <span>Unggah & Ekstrak dengan AI</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Import History Table -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-sm">
        <h3 class="font-bold text-base text-[#14110F] mb-4">Riwayat Impor Screenshot</h3>

        @if($imports->isEmpty())
            <div class="p-12 text-center text-[#9E9385] text-sm">
                Belum ada screenshot yang diimpor. Unggah screenshot pesanan pertama di atas.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                        <tr>
                            <th class="px-3 py-2.5 rounded-l-lg">Waktu</th>
                            <th class="px-3 py-2.5">Kanal</th>
                            <th class="px-3 py-2.5">ID Platform</th>
                            <th class="px-3 py-2.5">Operator</th>
                            <th class="px-3 py-2.5 text-center">Status AI</th>
                            <th class="px-3 py-2.5 text-center rounded-r-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E7E0D6]">
                        @foreach($imports as $imp)
                            <tr class="hover:bg-[#FAF8F5]">
                                <td class="px-3 py-3 text-xs text-[#575047]">
                                    {{ $imp->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-3 py-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ ($imp->channel_detected ?? $imp->channel_selected) === 'shopeefood' ? 'bg-[#FFFBEB] text-[#B45309]' : 'bg-[#EFF6FF] text-[#2563EB]' }}">
                                        {{ ucfirst($imp->channel_detected ?? $imp->channel_selected ?? 'Auto') }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-xs font-mono font-bold text-[#14110F]">
                                    {{ $imp->platform_order_id ?? '-' }}
                                </td>
                                <td class="px-3 py-3 text-xs text-[#575047]">{{ $imp->user?->name ?? 'Kasir' }}</td>
                                <td class="px-3 py-3 text-center">
                                    @php
                                        $statusClass = match($imp->status->value) {
                                            'saved' => 'bg-[#ECFDF5] text-[#047857]',
                                            'needs_review', 'ready' => 'bg-[#FFFBEB] text-[#B45309]',
                                            'duplicate' => 'bg-[#FFF4EC] text-[#C2410C]',
                                            'failed' => 'bg-[#FEF2F2] text-[#DC2626]',
                                            default => 'bg-[#FAF8F5] text-[#575047] border border-[#D3C9BD]',
                                        };
                                    @endphp
                                    <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold {{ $statusClass }}">
                                        {{ $imp->status->label() }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($imp->status->value === 'saved' && $imp->transaction)
                                        <a href="{{ route('transactions.show', $imp->transaction) }}" class="text-xs font-bold text-[#047857] hover:underline">
                                            Lihat Transaksi &rarr;
                                        </a>
                                    @else
                                        <a href="{{ route('impor.review', $imp) }}" class="text-xs font-bold text-[#C2410C] hover:underline">
                                            Review Hasil &rarr;
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $imports->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
