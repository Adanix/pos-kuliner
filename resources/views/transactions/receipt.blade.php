<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $receipt['transaction_number'] }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            @page {
                size: 58mm auto;
                margin: 0;
            }
            body {
                width: 58mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                color: #000 !important;
                font-family: 'Courier New', Courier, monospace !important;
                font-size: 9pt !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="bg-[#FAF8F5] text-black min-h-screen py-6 flex flex-col items-center">
    <!-- Top Action Buttons -->
    <div class="no-print mb-6 flex items-center gap-3">
        <button onclick="window.print()" class="px-5 py-2.5 rounded-xl font-bold text-white bg-[#C2410C] hover:bg-[#9A3412] text-sm shadow-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak Struk (58mm)</span>
        </button>
        <a href="{{ route('pos.index') }}" class="px-4 py-2.5 rounded-xl font-semibold bg-white border border-[#D3C9BD] text-[#14110F] text-sm hover:bg-[#FAF8F5]">
            Kembali ke Kasir
        </a>
    </div>

    <!-- 58mm Thermal Printable Receipt Container -->
    <div class="receipt-container bg-white border border-[#E7E0D6] shadow-md p-4 w-[58mm] font-mono text-[9pt] leading-tight text-black">
        <!-- Header -->
        <div class="text-center pb-2 border-b border-dashed border-gray-400">
            <h2 class="font-bold text-[11pt] uppercase">{{ $receipt['outlet_name'] }}</h2>
            @if(!empty($receipt['outlet_address']))
                <p class="text-[8pt] text-gray-700 mt-0.5">{{ $receipt['outlet_address'] }}</p>
            @endif
            @if(!empty($receipt['outlet_phone']))
                <p class="text-[8pt] text-gray-700">Telp: {{ $receipt['outlet_phone'] }}</p>
            @endif
        </div>

        <!-- Meta -->
        <div class="py-2 border-b border-dashed border-gray-400 text-[8pt] space-y-0.5">
            <div class="flex justify-between">
                <span>No: {{ $receipt['transaction_number'] }}</span>
                <span>{{ $receipt['channel'] }}</span>
            </div>
            <div class="flex justify-between">
                <span>{{ $receipt['date_time'] }}</span>
                <span>Kasir: {{ $receipt['cashier_name'] }}</span>
            </div>
            @if($receipt['is_voided'])
                <div class="text-center font-bold text-red-600 mt-1">*** DIBATALKAN (VOID) ***</div>
            @endif
        </div>

        <!-- Itemized List -->
        <div class="py-2 border-b border-dashed border-gray-400 space-y-1.5">
            @foreach($receipt['items'] as $item)
                <div>
                    <div class="font-bold">{{ $item['name'] }}</div>
                    <div class="flex justify-between text-[8pt]">
                        <span>{{ $item['qty'] }} x {{ $item['unit_price'] }}</span>
                        <span>{{ $item['line_total'] }}</span>
                    </div>
                    @if($item['note'])
                        <div class="text-[7pt] text-gray-600 italic">({{ $item['note'] }})</div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Financial Totals -->
        <div class="py-2 border-b border-dashed border-gray-400 space-y-0.5 text-[8pt]">
            <div class="flex justify-between">
                <span>Subtotal</span>
                <span>{{ $receipt['subtotal'] }}</span>
            </div>
            @if($receipt['discount_amount'])
                <div class="flex justify-between">
                    <span>Diskon</span>
                    <span>-{{ $receipt['discount_amount'] }}</span>
                </div>
            @endif
            @if($receipt['service_charge'])
                <div class="flex justify-between">
                    <span>Layanan</span>
                    <span>{{ $receipt['service_charge'] }}</span>
                </div>
            @endif
            @if($receipt['tax_amount'])
                <div class="flex justify-between">
                    <span>Pajak</span>
                    <span>{{ $receipt['tax_amount'] }}</span>
                </div>
            @endif
            @if($receipt['rounding_amount'])
                <div class="flex justify-between">
                    <span>Pembulatan</span>
                    <span>{{ $receipt['rounding_amount'] }}</span>
                </div>
            @endif
            <div class="flex justify-between font-bold text-[10pt] pt-1 border-t border-dotted border-gray-300">
                <span>TOTAL</span>
                <span>{{ $receipt['total'] }}</span>
            </div>
            <div class="flex justify-between pt-1">
                <span>Bayar ({{ $receipt['payment_method'] }})</span>
                <span>{{ $receipt['amount_paid'] }}</span>
            </div>
            <div class="flex justify-between font-bold">
                <span>Kembalian</span>
                <span>{{ $receipt['change_amount'] }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center pt-3 text-[8pt] space-y-1">
            <p>{{ $receipt['receipt_footer'] }}</p>
            <p class="text-[7pt] text-gray-500">ciora.id · POS Kuliner</p>
        </div>
    </div>
</body>
</html>
