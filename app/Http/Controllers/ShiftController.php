<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use App\Services\ShiftService;
use App\Support\Money;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function __construct(protected ShiftService $shiftService) {}

    public function index()
    {
        $activeSession = $this->shiftService->getActiveSession();
        $stats = $activeSession ? $this->shiftService->calculateExpectedCash($activeSession) : null;

        $pastSessions = CashSession::with(['openedByUser', 'closedByUser'])
            ->latest('opened_at')
            ->take(15)
            ->get();

        return view('pos.shift', compact('activeSession', 'stats', 'pastSessions'));
    }

    public function open(Request $request)
    {
        $request->validate([
            'opening_float' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $session = $this->shiftService->openShift((int) $request->opening_float, auth()->id());
            return redirect()->route('pos.index')->with('success', 'Sesi kasir berhasil dibuka dengan modal awal ' . Money::format($session->opening_float));
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function close(Request $request)
    {
        $request->validate([
            'counted_cash' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $activeSession = $this->shiftService->getActiveSession();
        if (! $activeSession) {
            return back()->with('error', 'Tidak ada sesi kasir yang sedang aktif.');
        }

        try {
            $closed = $this->shiftService->closeShift($activeSession, (int) $request->counted_cash, $request->note, auth()->id());
            return redirect()->route('pos.shift.index')->with('success', 'Sesi kasir berhasil ditutup. Selisih: ' . Money::format($closed->difference));
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
