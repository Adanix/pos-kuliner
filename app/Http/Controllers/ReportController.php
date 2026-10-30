<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ReportService;
use App\Support\Money;
use App\Support\SimpleXlsxWriter;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    public function index(Request $request)
    {
        $period = $request->input('period', 'today');
        [$start, $end] = $this->reportService->resolveDateRange($period, $request->input('start_date'), $request->input('end_date'));

        $summary = $this->reportService->getSalesSummary($start, $end);
        $channels = $this->reportService->getSalesByChannel($start, $end);
        $menus = $this->reportService->getSalesByMenu($start, $end);
        $paymentMethods = $this->reportService->getSalesByPaymentMethod($start, $end);
        $hppReport = $this->reportService->getHppReport($start, $end);
        $cashSessions = $this->reportService->getCashSessionsReport($start, $end);

        return view('reports.index', compact(
            'period',
            'start',
            'end',
            'summary',
            'channels',
            'menus',
            'paymentMethods',
            'hppReport',
            'cashSessions'
        ));
    }

    public function export(Request $request)
    {
        $period = $request->input('period', 'this_month');
        [$start, $end] = $this->reportService->resolveDateRange($period, $request->input('start_date'), $request->input('end_date'));

        $transactions = Transaction::whereBetween('occurred_at', [$start, $end])
            ->with(['items', 'user'])
            ->orderBy('occurred_at')
            ->get();

        $filename = 'laporan-transaksi-kuliner-' . $start->format('Ymd') . '-' . $end->format('Ymd') . '.xlsx';

        $headers = [
            'Nomor Transaksi',
            'Tanggal & Waktu',
            'Kanal Penjualan',
            'Status Nota',
            'Metode Bayar',
            'Kasir Bertugas',
            'Subtotal (Rp)',
            'Diskon (Rp)',
            'Pajak (Rp)',
            'Service (Rp)',
            'Pembulatan (Rp)',
            'Total Bersih (Rp)',
            'ID Order Platform',
            'Catatan Pesanan',
        ];

        $numericColumns = [6, 7, 8, 9, 10, 11]; // Numeric currency columns

        $rows = [];
        foreach ($transactions as $tx) {
            $rows[] = [
                $tx->number,
                $tx->occurred_at ? $tx->occurred_at->format('Y-m-d H:i:s') : '',
                $tx->channel->label(),
                $tx->status->label(),
                $tx->payment_method->label(),
                $tx->user?->name ?? 'Kasir',
                $tx->subtotal,
                $tx->discount_amount,
                $tx->tax_amount,
                $tx->service_charge,
                $tx->rounding_amount,
                $tx->total,
                $tx->platform_order_id ?? '-',
                $tx->note ?? '-',
            ];
        }

        $filePath = SimpleXlsxWriter::create($headers, $rows, $numericColumns);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
