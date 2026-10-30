<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\CashSession;
use App\Models\Outlet;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ShiftService
{
    /**
     * Get currently active open session.
     */
    public function getActiveSession(): ?CashSession
    {
        return CashSession::where('status', 'open')
            ->with(['openedByUser'])
            ->latest('opened_at')
            ->first();
    }

    /**
     * Open a new cashier shift.
     */
    public function openShift(int $openingFloat, int $userId): CashSession
    {
        if ($this->getActiveSession()) {
            throw new Exception("Sesi kasir sebelumnya masih aktif. Tutup sesi terlebih dahulu sebelum membuka sesi baru.");
        }

        if ($openingFloat < 0) {
            throw new Exception("Modal kas awal tidak boleh bernilai negatif.");
        }

        $outlet = Outlet::first();

        return CashSession::create([
            'outlet_id' => $outlet?->id,
            'opened_by' => $userId,
            'opened_at' => Carbon::now('Asia/Jakarta'),
            'opening_float' => $openingFloat,
            'status' => 'open',
        ]);
    }

    /**
     * Calculate live expected cash for a session.
     */
    public function calculateExpectedCash(CashSession $session): array
    {
        $transactions = Transaction::where('cash_session_id', $session->id)->get();

        $completedTransactions = $transactions->where('status', TransactionStatus::COMPLETED);
        $voidedTransactions = $transactions->where('status', TransactionStatus::VOIDED);

        $cashCompleted = $completedTransactions->where('payment_method', PaymentMethod::CASH)->sum('total');
        $nonCashCompleted = $completedTransactions->where('payment_method', '!=', PaymentMethod::CASH)->sum('total');

        $expectedCash = $session->opening_float + $cashCompleted;

        return [
            'opening_float' => $session->opening_float,
            'total_transactions' => $completedTransactions->count(),
            'void_transactions' => $voidedTransactions->count(),
            'cash_sales' => $cashCompleted,
            'non_cash_sales' => $nonCashCompleted,
            'expected_cash' => $expectedCash,
        ];
    }

    /**
     * Close the cashier shift.
     */
    public function closeShift(CashSession $session, int $countedCash, ?string $note, int $userId): CashSession
    {
        if ($session->status !== 'open') {
            throw new Exception("Sesi kasir ini sudah ditutup sebelumnya.");
        }

        $stats = $this->calculateExpectedCash($session);
        $expected = $stats['expected_cash'];
        $difference = $countedCash - $expected;

        if ($difference !== 0 && (empty($note) || strlen(trim($note)) < 5)) {
            throw new Exception("Terdapat selisih kas (" . ($difference > 0 ? "+{$difference}" : "{$difference}") . "). Wajib menyertakan catatan penjelasan.");
        }

        return DB::transaction(function () use ($session, $countedCash, $expected, $difference, $note, $userId) {
            $session->closed_by = $userId;
            $session->closed_at = Carbon::now('Asia/Jakarta');
            $session->counted_cash = $countedCash;
            $session->expected_cash = $expected;
            $session->difference = $difference;
            $session->note = $note;
            $session->status = 'closed';
            $session->save();

            return $session;
        });
    }
}
