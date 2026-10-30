<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ReportService;
use App\Support\Money;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function export(Request $request): StreamedResponse
    {
        $period = $request->input('period', 'this_month');
        [$start, $end] = $this->reportService->resolveDateRange($period, $request->input('start_date'), $request->input('end_date'));

        $transactions = Transaction::whereBetween('occurred_at', [$start, $end])
            ->with(['items', 'user'])
            ->orderBy('occurred_at')
            ->get();

        $filename = 'transaksi-kuliner-' . $start->format('Ymd') . '-' . $end->format('Ymd') . '.csv';

        $response = new StreamedResponse(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Nomor Transaksi',
                'Tanggal',
                'Kanal',
                'Status',
                'Metode Bayar',
                'Kasir',
                'Subtotal (Rp)',
                'Diskon (Rp)',
                'Pajak (Rp)',
                'Service (Rp)',
                'Pembulatan (Rp)',
                'Total (Rp)',
                'ID Pesanan Platform',
                'Catatan',
            ]);

            foreach ($transactions as $tx) {
                fputcsv($handle, [
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
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }
}
