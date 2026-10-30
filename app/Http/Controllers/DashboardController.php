<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use App\Models\Ingredient;
use App\Models\Transaction;
use App\Services\ReportService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    public function index(Request $request)
    {
        [$start, $end] = $this->reportService->resolveDateRange('today');

        $summary = $this->reportService->getSalesSummary($start, $end);
        $channelStats = $this->reportService->getSalesByChannel($start, $end);
        $topMenus = array_slice($this->reportService->getSalesByMenu($start, $end), 0, 5);

        // Low stock alerts
        $lowStockIngredients = Ingredient::where('is_active', true)
            ->whereRaw('stock_qty <= min_stock')
            ->get();

        // Recent transactions
        $recentTransactions = Transaction::with(['items', 'user'])
            ->latest('occurred_at')
            ->take(5)
            ->get();

        // Active shift status
        $activeShift = CashSession::where('status', 'open')->with('openedByUser')->latest('opened_at')->first();

        return view('dashboard.index', compact(
            'summary',
            'channelStats',
            'topMenus',
            'lowStockIngredients',
            'recentTransactions',
            'activeShift'
        ));
    }
}
