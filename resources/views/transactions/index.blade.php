@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#14110F]">Riwayat Transaksi</h1>
            <p class="text-sm text-[#575047] mt-0.5">Daftar seluruh transaksi penjualan dari POS dan aplikasi online.</p>
        </div>
        <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold bg-[#C2410C] text-white hover:bg-[#9A3412] shadow-sm">
            <span>+ Transaksi Baru</span>
        </a>
    </div>

    <!-- Filters Strip -->
    <div class="bg-white p-4 rounded-2xl border border-[#E7E0D6] shadow-sm">
        <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-[#575047] mb-1">Cari No. / ID Pesanan</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="CIO-..."
                       class="w-full px-3 py-2 rounded-xl border border-[#D3C9BD] text-xs bg-[#FAF8F5]">
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#575047] mb-1">Kanal Penjualan</label>
                <select name="channel" class="w-full px-3 py-2 rounded-xl border border-[#D3C9BD] text-xs bg-[#FAF8F5]">
                    <option value="">Semua Kanal</option>
                    <option value="pos" {{ request('channel') === 'pos' ? 'selected' : '' }}>POS (Dine-in/Takeaway)</option>
                    <option value="gofood" {{ request('channel') === 'gofood' ? 'selected' : '' }}>GoFood</option>
                    <option value="shopeefood" {{ request('channel') === 'shopeefood' ? 'selected' : '' }}>ShopeeFood</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#575047] mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-[#D3C9BD] text-xs bg-[#FAF8F5]">
                    <option value="">Semua Status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>Dibatalkan (Void)</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 rounded-xl font-bold text-xs bg-[#14110F] text-white hover:bg-[#26221E] transition">
                    Filter
                </button>
                <a href="{{ route('transactions.index') }}" class="px-3 py-2 rounded-xl font-semibold text-xs border border-[#D3C9BD] text-[#575047] hover:bg-[#FAF8F5]">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Transactions List Table -->
    <div class="bg-white rounded-2xl border border-[#E7E0D6] p-5 shadow-sm">
        @if($transactions->isEmpty())
            <div class="p-12 text-center text-[#9E9385] text-sm">
                Belum ada transaksi yang sesuai dengan filter pencarian.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs font-semibold text-[#575047] uppercase bg-[#F4F0EA]">
                        <tr>
                            <th class="px-3 py-2.5 rounded-l-lg">No. Transaksi</th>
                            <th class="px-3 py-2.5">Waktu</th>
                            <th class="px-3 py-2.5">Kanal</th>
                            <th class="px-3 py-2.5">Kasir</th>
                            <th class="px-3 py-2.5">Metode</th>
                            <th class="px-3 py-2.5 text-right">Subtotal</th>
                            <th class="px-3 py-2.5 text-right">Total</th>
                            <th class="px-3 py-2.5 text-center">Status</th>
                            <th class="px-3 py-2.5 text-center rounded-r-lg">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E7E0D6]">
                        @foreach($transactions as $tx)
                            <tr class="hover:bg-[#FAF8F5]">
                                <td class="px-3 py-3">
                                    <span class="font-bold text-[#14110F]">{{ $tx->number }}</span>
                                    @if($tx->platform_order_id)
                                        <span class="block text-[11px] text-[#575047]">ID: {{ $tx->platform_order_id }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-xs text-[#575047]">
                                    {{ $tx->occurred_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-3 py-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $tx->channel->value === 'pos' ? 'bg-[#FFF4EC] text-[#C2410C]' : 'bg-[#EFF6FF] text-[#2563EB]' }}">
                                        {{ $tx->channel->label() }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-xs text-[#575047]">{{ $tx->user?->name ?? 'Kasir' }}</td>
                                <td class="px-3 py-3 text-xs font-medium">{{ $tx->payment_method->label() }}</td>
                                <td class="px-3 py-3 text-right text-xs tabular-nums text-[#575047]">{{ \App\Support\Money::format($tx->subtotal) }}</td>
                                <td class="px-3 py-3 text-right font-bold text-[#14110F] tabular-nums">{{ \App\Support\Money::format($tx->total) }}</td>
                                <td class="px-3 py-3 text-center">
                                    @if($tx->isCompleted())
                                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-[#ECFDF5] text-[#047857] font-semibold">Selesai</span>
                                    @else
                                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-[#FEF2F2] text-[#DC2626] font-semibold">Void</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('transactions.show', $tx) }}" class="text-xs font-semibold text-[#14110F] hover:text-[#C2410C]">
                                            Detail
                                        </a>
                                        <a href="{{ route('transactions.receipt', $tx) }}" target="_blank" class="text-xs font-semibold text-[#C2410C] hover:underline">
                                            Struk
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
