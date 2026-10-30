@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Transaksi {{ $transaction->number }}</h1>
                @if($transaction->isCompleted())
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-[#ECFDF5] text-[#047857] font-semibold border border-[#A7F3D0]">Selesai</span>
                @else
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-[#FEF2F2] text-[#DC2626] font-semibold border border-[#FECACA]">Dibatalkan (Void)</span>
                @endif
            </div>
            <p class="text-xs text-[#575047] mt-0.5">Waktu transaksi: {{ $transaction->occurred_at->timezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('transactions.receipt', $transaction) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold bg-white border border-[#D3C9BD] text-[#14110F] hover:bg-[#FAF8F5] transition shadow-sm">
                <svg class="w-4 h-4 text-[#C2410C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Struk 58mm</span>
            </a>

            @if(auth()->user()?->isOwner() && $transaction->isCompleted())
                <div x-data="{ showVoidModal: false }">
                    <button @click="showVoidModal = true" type="button"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold bg-[#FEF2F2] text-[#DC2626] border border-[#FECACA] hover:bg-[#fee2e2] transition shadow-sm">
                        <span>Batalkan (Void)</span>
                    </button>

                    <!-- Void Confirmation Modal -->
                    <div x-show="showVoidModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
                        <div @click.away="showVoidModal = false" class="bg-white w-full max-w-md rounded-2xl border border-[#E7E0D6] shadow-2xl p-6 text-left">
                            <h3 class="text-lg font-bold text-[#DC2626] mb-2">Batalkan Transaksi (Void)</h3>
                            <p class="text-xs text-[#575047] mb-4">Transaksi yang dibatalkan tidak akan dihitung dalam omzet dan laba. Tindakan ini tidak dapat diurungkan.</p>

                            <form method="POST" action="{{ route('transactions.void', $transaction) }}" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-[#14110F] mb-1">Alasan Pembatalan (Min. 10 karakter)</label>
                                    <textarea name="reason" required minlength="10" rows="3"
                                              class="w-full p-2.5 rounded-xl border border-[#D3C9BD] text-xs focus:ring-2 focus:ring-[#DC2626]"
                                              placeholder="Contoh: Pelanggan salah pesan dan makanan belum dimasak..."></textarea>
                                </div>

                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="return_stock" name="return_stock" value="1" class="rounded border-[#D3C9BD] text-[#C2410C]">
                                    <label for="return_stock" class="text-xs font-medium text-[#14110F]">Kembalikan stok bahan baku ke gudang</label>
                                </div>

                                <div class="flex justify-end gap-2 pt-2">
                                    <button @click="showVoidModal = false" type="button" class="px-4 py-2 rounded-xl border border-[#D3C9BD] text-xs font-semibold">Batal</button>
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#DC2626] text-white text-xs font-bold hover:bg-[#b91c1c]">Konfirmasi Void</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Void Info Banner if Voided -->
    @if($transaction->isVoided())
        <div class="p-4 rounded-2xl bg-[#FEF2F2] border border-[#FECACA] text-[#DC2626] text-xs">
            <strong class="font-bold">Transaksi ini telah dibatalkan (VOID)</strong> pada {{ $transaction->voided_at?->format('d/m/Y H:i') }} oleh {{ $transaction->voidedByUser?->name ?? 'Owner' }}.
            <p class="mt-1">Alasan: <em>"{{ $transaction->void_reason }}"</em></p>
        </div>
    @endif

    <!-- Transaction Summary & Meta -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm">
        <h3 class="font-bold text-base text-[#14110F] mb-4">Informasi Transaksi</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-[#575047] block">Kanal Penjualan</span>
                <strong class="font-bold text-sm text-[#14110F]">{{ $transaction->channel->label() }}</strong>
            </div>
            <div>
                <span class="text-[#575047] block">Metode Pembayaran</span>
                <strong class="font-bold text-sm text-[#14110F]">{{ $transaction->payment_method->label() }}</strong>
            </div>
            <div>
                <span class="text-[#575047] block">Kasir / Operator</span>
                <strong class="font-bold text-sm text-[#14110F]">{{ $transaction->user?->name ?? 'Kasir' }}</strong>
            </div>
            <div>
                <span class="text-[#575047] block">ID Platform</span>
                <strong class="font-bold text-sm text-[#14110F]">{{ $transaction->platform_order_id ?? '-' }}</strong>
            </div>
        </div>
    </div>

    <!-- Items List Table -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-6 shadow-sm">
        <h3 class="font-bold text-base text-[#14110F] mb-4">Item Menu yang Dipesan</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                    <tr>
                        <th class="px-3 py-2.5 rounded-l-lg">Item Menu</th>
                        <th class="px-3 py-2.5 text-center">Qty</th>
                        <th class="px-3 py-2.5 text-right">Harga Satuan</th>
                        @if(auth()->user()?->isOwner())
                            <th class="px-3 py-2.5 text-right">HPP Snapshot</th>
                        @endif
                        <th class="px-3 py-2.5 text-right rounded-r-lg">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7E0D6]">
                    @foreach($transaction->items as $item)
                        <tr class="hover:bg-[#FAF8F5]">
                            <td class="px-3 py-3">
                                <span class="font-bold text-[#14110F]">{{ $item->menu_name }}</span>
                                @if($item->note)
                                    <span class="block text-xs text-[#575047]">Catatan: {{ $item->note }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center font-bold text-[#C2410C]">{{ $item->qty }}</td>
                            <td class="px-3 py-3 text-right tabular-nums text-xs text-[#575047]">{{ \App\Support\Money::format($item->unit_price) }}</td>
                            @if(auth()->user()?->isOwner())
                                <td class="px-3 py-3 text-right tabular-nums text-xs font-medium">
                                    @if($item->hpp_complete && $item->unit_hpp !== null)
                                        <span class="text-[#047857]">{{ \App\Support\Money::format($item->unit_hpp) }}</span>
                                    @else
                                        <span class="text-[#9E9385] italic">Belum dapat dihitung</span>
                                    @endif
                                </td>
                            @endif
                            <td class="px-3 py-3 text-right font-bold text-[#14110F] tabular-nums">{{ \App\Support\Money::format($item->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Financial Totals Breakdown -->
        <div class="mt-6 pt-4 border-t border-[#E7E0D6] flex justify-end">
            <div class="w-full sm:w-72 space-y-2 text-xs">
                <div class="flex justify-between text-[#575047]">
                    <span>Subtotal Menu</span>
                    <span class="font-semibold text-[#14110F] tabular-nums">{{ \App\Support\Money::format($transaction->subtotal) }}</span>
                </div>
                @if($transaction->discount_amount > 0)
                    <div class="flex justify-between text-[#047857]">
                        <span>Diskon Transaksi</span>
                        <span class="font-semibold tabular-nums">-{{ \App\Support\Money::format($transaction->discount_amount) }}</span>
                    </div>
                @endif
                @if($transaction->service_charge > 0)
                    <div class="flex justify-between text-[#575047]">
                        <span>Biaya Layanan (Service)</span>
                        <span class="font-semibold text-[#14110F] tabular-nums">{{ \App\Support\Money::format($transaction->service_charge) }}</span>
                    </div>
                @endif
                @if($transaction->tax_amount > 0)
                    <div class="flex justify-between text-[#575047]">
                        <span>Pajak Restoran</span>
                        <span class="font-semibold text-[#14110F] tabular-nums">{{ \App\Support\Money::format($transaction->tax_amount) }}</span>
                    </div>
                @endif
                @if($transaction->rounding_amount != 0)
                    <div class="flex justify-between text-[#575047]">
                        <span>Pembulatan Kasir</span>
                        <span class="font-semibold text-[#14110F] tabular-nums">{{ \App\Support\Money::format($transaction->rounding_amount) }}</span>
                    </div>
                @endif
                <div class="pt-2 border-t border-[#E7E0D6] flex justify-between font-bold text-base text-[#14110F]">
                    <span>Total Pembayaran</span>
                    <span class="text-[#C2410C] tabular-nums">{{ \App\Support\Money::format($transaction->total) }}</span>
                </div>
                <div class="flex justify-between text-[#575047] pt-1">
                    <span>Uang Diterima</span>
                    <span class="font-medium tabular-nums">{{ \App\Support\Money::format($transaction->amount_paid) }}</span>
                </div>
                <div class="flex justify-between text-[#047857] font-semibold">
                    <span>Kembalian</span>
                    <span class="tabular-nums">{{ \App\Support\Money::format($transaction->change_amount) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
