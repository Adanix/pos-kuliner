<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\StockMovement;
use App\Services\AuditService;
use App\Services\StockService;
use App\Support\Money;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function __construct(protected StockService $stockService) {}

    public function index(Request $request)
    {
        $query = StockMovement::with(['ingredient', 'user'])->latest('created_at');

        if ($ingId = $request->input('ingredient_id')) {
            $query->where('ingredient_id', $ingId);
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $movements = $query->paginate(25)->withQueryString();
        $ingredients = Ingredient::where('is_active', true)->orderBy('name')->get();

        return view('stock.index', compact('movements', 'ingredients'));
    }

    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'qty' => ['required', 'numeric', 'min:0.0001'],
            'purchase_cost' => ['required'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $ingredient = Ingredient::findOrFail($validated['ingredient_id']);
        $cost = Money::parse($validated['purchase_cost']);

        try {
            $this->stockService->recordPurchase(
                $ingredient,
                (float) $validated['qty'],
                (float) $cost,
                $validated['reason'] ?? null,
                auth()->id()
            );

            AuditService::log('purchase_stock', 'Ingredient', $ingredient->id, [
                'name' => $ingredient->name,
                'qty' => $validated['qty'],
                'cost' => $cost,
            ]);

            return redirect()->route('stok.index')->with('success', "Pembelian {$validated['qty']} {$ingredient->base_unit} {$ingredient->name} berhasil dicatat.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function waste(Request $request)
    {
        $validated = $request->validate([
            'ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'qty' => ['required', 'numeric', 'min:0.0001'],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        $ingredient = Ingredient::findOrFail($validated['ingredient_id']);

        try {
            $this->stockService->recordWaste(
                $ingredient,
                (float) $validated['qty'],
                $validated['reason'],
                auth()->id()
            );

            AuditService::log('waste_stock', 'Ingredient', $ingredient->id, [
                'name' => $ingredient->name,
                'qty' => $validated['qty'],
                'reason' => $validated['reason'],
            ]);

            return redirect()->route('stok.index')->with('success', "Waste {$validated['qty']} {$ingredient->base_unit} {$ingredient->name} berhasil dicatat.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function showOpname()
    {
        $ingredients = Ingredient::where('is_active', true)->orderBy('name')->get();
        return view('stock.opname', compact('ingredients'));
    }

    public function storeOpname(Request $request)
    {
        $validated = $request->validate([
            'counts' => ['required', 'array'],
            'counts.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'counts.*.physical_qty' => ['required', 'numeric', 'min:0'],
            'counts.*.reason' => ['nullable', 'string', 'max:255'],
        ]);

        $adjustedCount = 0;

        foreach ($validated['counts'] as $row) {
            $ingredient = Ingredient::find($row['ingredient_id']);
            if (! $ingredient) continue;

            $physicalQty = (float) $row['physical_qty'];
            $currentQty = (float) $ingredient->stock_qty;

            if (abs($physicalQty - $currentQty) > 0.0001) {
                $reason = ! empty($row['reason']) ? $row['reason'] : 'Penyesuaian stok opname fisik rutin';
                $this->stockService->recordOpname($ingredient, $physicalQty, $reason, auth()->id());
                $adjustedCount++;
            }
        }

        AuditService::log('stock_opname', 'Stock', null, ['adjusted_ingredients_count' => $adjustedCount]);

        return redirect()->route('stok.index')->with('success', "Stok opname selesai diproses. {$adjustedCount} bahan baku diperbarui.");
    }
}
