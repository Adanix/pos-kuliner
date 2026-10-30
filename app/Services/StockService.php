<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Ingredient;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Record initial opening stock.
     */
    public function recordOpening(Ingredient $ingredient, float $qty, float $unitCost, ?int $userId = null): StockMovement
    {
        return DB::transaction(function () use ($ingredient, $qty, $unitCost, $userId) {
            $locked = Ingredient::where('id', $ingredient->id)->lockForUpdate()->firstOrFail();

            $valueDelta = (int) round($qty * $unitCost);

            $movement = StockMovement::create([
                'ingredient_id' => $locked->id,
                'type' => StockMovementType::OPENING,
                'qty_delta' => $qty,
                'unit_cost' => $unitCost,
                'value_delta' => $valueDelta,
                'reason' => 'Saldo awal bahan baku',
                'user_id' => $userId,
            ]);

            $locked->stock_qty = $qty;
            $locked->avg_cost = $unitCost;
            $locked->save();

            return $movement;
        });
    }

    /**
     * Record a purchase with Weighted Average Cost (WAC) recalculation.
     */
    public function recordPurchase(Ingredient $ingredient, float $qty, float $purchaseCost, ?string $reason = null, ?int $userId = null): StockMovement
    {
        if ($qty <= 0) {
            throw new Exception("Kuantitas pembelian harus lebih besar dari 0.");
        }
        if ($purchaseCost < 0) {
            throw new Exception("Harga beli tidak boleh negatif.");
        }

        return DB::transaction(function () use ($ingredient, $qty, $purchaseCost, $reason, $userId) {
            $locked = Ingredient::where('id', $ingredient->id)->lockForUpdate()->firstOrFail();

            $currentQty = (float) $locked->stock_qty;
            $currentAvg = (float) $locked->avg_cost;

            // Recalculate WAC
            if ($currentQty <= 0) {
                $newAvg = $purchaseCost;
            } else {
                $newTotalValue = ($currentQty * $currentAvg) + ($qty * $purchaseCost);
                $newTotalQty = $currentQty + $qty;
                $newAvg = $newTotalQty > 0 ? ($newTotalValue / $newTotalQty) : $purchaseCost;
            }

            $valueDelta = (int) round($qty * $purchaseCost);

            $movement = StockMovement::create([
                'ingredient_id' => $locked->id,
                'type' => StockMovementType::PURCHASE,
                'qty_delta' => $qty,
                'unit_cost' => $purchaseCost,
                'value_delta' => $valueDelta,
                'reason' => $reason ?? 'Pembelian bahan baku',
                'user_id' => $userId,
            ]);

            $locked->stock_qty = $currentQty + $qty;
            $locked->avg_cost = $newAvg;
            $locked->save();

            return $movement;
        });
    }

    /**
     * Consume ingredient stock for sales/usage.
     */
    public function consume(Ingredient $ingredient, float $qty, ?string $refType = 'transaction', ?int $refId = null, ?int $userId = null): StockMovement
    {
        if ($qty <= 0) {
            throw new Exception("Kuantitas pemakaian harus lebih besar dari 0.");
        }

        $locked = Ingredient::where('id', $ingredient->id)->lockForUpdate()->firstOrFail();
        $unitCost = (float) $locked->avg_cost;
        $valueDelta = - (int) round($qty * $unitCost);

        $movement = StockMovement::create([
            'ingredient_id' => $locked->id,
            'type' => StockMovementType::USAGE,
            'qty_delta' => - $qty,
            'unit_cost' => $unitCost,
            'value_delta' => $valueDelta,
            'ref_type' => $refType,
            'ref_id' => $refId,
            'reason' => 'Pemakaian penjualan pesanan',
            'user_id' => $userId,
        ]);

        $locked->stock_qty = (float) $locked->stock_qty - $qty;
        $locked->save();

        return $movement;
    }

    /**
     * Reverse consumed stock on transaction void.
     */
    public function reverse(Ingredient $ingredient, float $qty, ?int $refId = null, ?string $reason = null, ?int $userId = null): StockMovement
    {
        $locked = Ingredient::where('id', $ingredient->id)->lockForUpdate()->firstOrFail();
        $unitCost = (float) $locked->avg_cost;
        $valueDelta = (int) round($qty * $unitCost);

        $movement = StockMovement::create([
            'ingredient_id' => $locked->id,
            'type' => StockMovementType::REVERSAL,
            'qty_delta' => $qty,
            'unit_cost' => $unitCost,
            'value_delta' => $valueDelta,
            'ref_type' => 'transaction_void',
            'ref_id' => $refId,
            'reason' => $reason ?? 'Pengembalian stok transaksi void',
            'user_id' => $userId,
        ]);

        $locked->stock_qty = (float) $locked->stock_qty + $qty;
        $locked->save();

        return $movement;
    }

    /**
     * Record waste / spoiled ingredient.
     */
    public function recordWaste(Ingredient $ingredient, float $qty, string $reason, ?int $userId = null): StockMovement
    {
        if ($qty <= 0) {
            throw new Exception("Kuantitas waste harus lebih besar dari 0.");
        }

        return DB::transaction(function () use ($ingredient, $qty, $reason, $userId) {
            $locked = Ingredient::where('id', $ingredient->id)->lockForUpdate()->firstOrFail();
            $unitCost = (float) $locked->avg_cost;
            $valueDelta = - (int) round($qty * $unitCost);

            $movement = StockMovement::create([
                'ingredient_id' => $locked->id,
                'type' => StockMovementType::WASTE,
                'qty_delta' => - $qty,
                'unit_cost' => $unitCost,
                'value_delta' => $valueDelta,
                'reason' => $reason,
                'user_id' => $userId,
            ]);

            $locked->stock_qty = (float) $locked->stock_qty - $qty;
            $locked->save();

            return $movement;
        });
    }

    /**
     * Record stock opname (physical count adjustment).
     */
    public function recordOpname(Ingredient $ingredient, float $physicalQty, string $reason, ?int $userId = null): ?StockMovement
    {
        if (trim($reason) === '') {
            throw new Exception("Alasan penyesuaian stok fisik wajib diisi.");
        }

        return DB::transaction(function () use ($ingredient, $physicalQty, $reason, $userId) {
            $locked = Ingredient::where('id', $ingredient->id)->lockForUpdate()->firstOrFail();
            $currentQty = (float) $locked->stock_qty;
            $delta = $physicalQty - $currentQty;

            if (abs($delta) < 0.0001) {
                return null; // Tidak ada selisih
            }

            $unitCost = (float) $locked->avg_cost;
            $valueDelta = (int) round($delta * $unitCost);

            $movement = StockMovement::create([
                'ingredient_id' => $locked->id,
                'type' => StockMovementType::ADJUSTMENT,
                'qty_delta' => $delta,
                'unit_cost' => $unitCost,
                'value_delta' => $valueDelta,
                'reason' => "Opname fisik: Tercatat {$currentQty}, Dihitung {$physicalQty}. Alasan: {$reason}",
                'user_id' => $userId,
            ]);

            $locked->stock_qty = $physicalQty;
            $locked->save();

            return $movement;
        });
    }

    /**
     * Reconcile cached stock_qty against sum of ledger movements.
     */
    public function reconcile(Ingredient $ingredient, bool $fix = false): array
    {
        $cached = (float) $ingredient->stock_qty;
        $ledger = (float) StockMovement::where('ingredient_id', $ingredient->id)->sum('qty_delta');
        $difference = $cached - $ledger;

        if ($fix && abs($difference) > 0.0001) {
            $ingredient->stock_qty = $ledger;
            $ingredient->save();
        }

        return [
            'ingredient_id' => $ingredient->id,
            'name' => $ingredient->name,
            'cached_qty' => $cached,
            'ledger_qty' => $ledger,
            'difference' => $difference,
            'fixed' => $fix,
        ];
    }
}
