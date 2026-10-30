<?php

namespace App\Services;

use App\Enums\Channel;
use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\CashSession;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Resolve date range filter into Carbon instances in Asia/Jakarta.
     */
    public function resolveDateRange(?string $period = 'today', ?string $startDate = null, ?string $endDate = null): array
    {
        $now = Carbon::now('Asia/Jakarta');

        switch ($period) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case '7_days':
                $start = $now->copy()->subDays(6)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case '30_days':
                $start = $now->copy()->subDays(29)->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'this_month':
                $start = $now->copy()->startOfMonth()->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'custom':
                $start = $startDate ? Carbon::parse($startDate, 'Asia/Jakarta')->startOfDay() : $now->copy()->startOfDay();
                $end = $endDate ? Carbon::parse($endDate, 'Asia/Jakarta')->endOfDay() : $now->copy()->endOfDay();
                break;
            default:
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
        }

        return [$start, $end];
    }

    /**
     * Report 1: Core Sales & Revenue Summary (Used by Dashboard & Reports).
     */
    public function getSalesSummary(Carbon $start, Carbon $end): array
    {
        $transactions = Transaction::whereBetween('occurred_at', [$start, $end])
            ->where('status', TransactionStatus::COMPLETED)
            ->with('items')
            ->get();

        $txCount = $transactions->count();
        $grossSales = $transactions->sum(fn ($tx) => $tx->subtotal - $tx->discount_amount);
        $taxTotal = $transactions->sum('tax_amount');
        $serviceTotal = $transactions->sum('service_charge');
        $roundingTotal = $transactions->sum('rounding_amount');
        $totalCollected = $transactions->sum('total');
        $platformFeeTotal = $transactions->sum('platform_fee');

        $outletRevenue = $totalCollected - $taxTotal;
        $netEstimatedRevenue = $outletRevenue - $platformFeeTotal;

        // HPP Calculation
        $realizedHpp = 0;
        $totalItemsCount = 0;
        $coveredItemsCount = 0;

        foreach ($transactions as $tx) {
            foreach ($tx->items as $item) {
                $totalItemsCount++;
                if ($item->hpp_complete && $item->unit_hpp !== null) {
                    $coveredItemsCount++;
                    $realizedHpp += ($item->unit_hpp * $item->qty);
                }
            }
        }

        $grossProfit = $outletRevenue - $realizedHpp;
        $marginPercent = $outletRevenue > 0 ? round(($grossProfit / $outletRevenue) * 100, 1) : 0;
        $aov = $txCount > 0 ? (int) round($outletRevenue / $txCount) : 0;

        return [
            'transaction_count' => $txCount,
            'gross_sales' => $grossSales,
            'tax_total' => $taxTotal,
            'service_total' => $serviceTotal,
            'rounding_total' => $roundingTotal,
            'total_collected' => $totalCollected,
            'platform_fee_total' => $platformFeeTotal,
            'outlet_revenue' => $outletRevenue,
            'net_estimated_revenue' => $netEstimatedRevenue,
            'realized_hpp' => $realizedHpp,
            'gross_profit' => $grossProfit,
            'margin_percent' => $marginPercent,
            'aov' => $aov,
            'total_items_count' => $totalItemsCount,
            'covered_items_count' => $coveredItemsCount,
            'hpp_coverage_percent' => $totalItemsCount > 0 ? round(($coveredItemsCount / $totalItemsCount) * 100, 1) : 0,
        ];
    }

    /**
     * Report 2: Sales by Channel.
     */
    public function getSalesByChannel(Carbon $start, Carbon $end): array
    {
        $transactions = Transaction::whereBetween('occurred_at', [$start, $end])
            ->where('status', TransactionStatus::COMPLETED)
            ->get();

        $channels = [
            Channel::POS->value => ['label' => Channel::POS->label(), 'count' => 0, 'revenue' => 0, 'fee' => 0],
            Channel::GOFOOD->value => ['label' => Channel::GOFOOD->label(), 'count' => 0, 'revenue' => 0, 'fee' => 0],
            Channel::SHOPEEFOOD->value => ['label' => Channel::SHOPEEFOOD->label(), 'count' => 0, 'revenue' => 0, 'fee' => 0],
        ];

        foreach ($transactions as $tx) {
            $key = $tx->channel->value;
            if (isset($channels[$key])) {
                $channels[$key]['count']++;
                $channels[$key]['revenue'] += ($tx->total - $tx->tax_amount);
                $channels[$key]['fee'] += $tx->platform_fee;
            }
        }

        return $channels;
    }

    /**
     * Report 3: Sales by Menu Item.
     */
    public function getSalesByMenu(Carbon $start, Carbon $end): array
    {
        return TransactionItem::whereHas('transaction', function ($q) use ($start, $end) {
            $q->whereBetween('occurred_at', [$start, $end])
              ->where('status', TransactionStatus::COMPLETED);
        })
        ->select(
            'menu_name',
            DB::raw('SUM(qty) as total_qty'),
            DB::raw('SUM(line_total) as total_sales'),
            DB::raw('SUM(CASE WHEN hpp_complete = 1 THEN unit_hpp * qty ELSE 0 END) as total_hpp'),
            DB::raw('COUNT(DISTINCT transaction_id) as tx_count')
        )
        ->groupBy('menu_name')
        ->orderByDesc('total_qty')
        ->get()
        ->toArray();
    }

    /**
     * Report 4: Sales by Payment Method.
     */
    public function getSalesByPaymentMethod(Carbon $start, Carbon $end): array
    {
        $transactions = Transaction::whereBetween('occurred_at', [$start, $end])
            ->where('status', TransactionStatus::COMPLETED)
            ->get();

        $methods = [
            PaymentMethod::CASH->value => ['label' => 'Tunai', 'count' => 0, 'total' => 0],
            PaymentMethod::QRIS->value => ['label' => 'QRIS', 'count' => 0, 'total' => 0],
            PaymentMethod::TRANSFER->value => ['label' => 'Transfer Bank', 'count' => 0, 'total' => 0],
            PaymentMethod::OTHER->value => ['label' => 'Lainnya', 'count' => 0, 'total' => 0],
        ];

        foreach ($transactions as $tx) {
            $key = $tx->payment_method->value;
            if (isset($methods[$key])) {
                $methods[$key]['count']++;
                $methods[$key]['total'] += $tx->total;
            }
        }

        return $methods;
    }

    /**
     * Report 5: HPP & Profit Analysis.
     */
    public function getHppReport(Carbon $start, Carbon $end): array
    {
        $items = TransactionItem::whereHas('transaction', function ($q) use ($start, $end) {
            $q->whereBetween('occurred_at', [$start, $end])
              ->where('status', TransactionStatus::COMPLETED);
        })
        ->select(
            'menu_name',
            'unit_price',
            'unit_hpp',
            'hpp_complete',
            DB::raw('SUM(qty) as total_qty'),
            DB::raw('SUM(line_total) as total_revenue'),
            DB::raw('SUM(CASE WHEN hpp_complete = 1 THEN unit_hpp * qty ELSE 0 END) as total_cost')
        )
        ->groupBy('menu_name', 'unit_price', 'unit_hpp', 'hpp_complete')
        ->orderBy('menu_name')
        ->get();

        return $items->map(function ($row) {
            $revenue = (int) $row->total_revenue;
            $cost = (int) $row->total_cost;
            $profit = $row->hpp_complete ? ($revenue - $cost) : null;
            $margin = ($row->hpp_complete && $revenue > 0) ? round(($profit / $revenue) * 100, 1) : null;

            return [
                'menu_name' => $row->menu_name,
                'total_qty' => (int) $row->total_qty,
                'total_revenue' => $revenue,
                'total_cost' => $cost,
                'profit' => $profit,
                'margin_percent' => $margin,
                'hpp_complete' => (bool) $row->hpp_complete,
            ];
        })->toArray();
    }

    /**
     * Report 6: Stock Movements.
     */
    public function getStockMovementsReport(Carbon $start, Carbon $end, ?int $ingredientId = null, ?string $type = null): array
    {
        $query = StockMovement::whereBetween('created_at', [$start, $end])
            ->with(['ingredient', 'user'])
            ->latest('created_at');

        if ($ingredientId) {
            $query->where('ingredient_id', $ingredientId);
        }
        if ($type) {
            $query->where('type', $type);
        }

        return $query->get()->toArray();
    }

    /**
     * Report 7: Cash Sessions & Variances.
     */
    public function getCashSessionsReport(Carbon $start, Carbon $end): array
    {
        return CashSession::whereBetween('opened_at', [$start, $end])
            ->with(['openedByUser', 'closedByUser'])
            ->latest('opened_at')
            ->get()
            ->toArray();
    }
}
