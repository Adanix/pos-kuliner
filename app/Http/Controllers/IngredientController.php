<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Services\AuditService;
use App\Services\StockService;
use App\Support\Money;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function __construct(protected StockService $stockService) {}

    public function index(Request $request)
    {
        $query = Ingredient::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }
        if ($request->boolean('low_stock')) {
            $query->whereRaw('stock_qty <= min_stock');
        }

        $ingredients = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('ingredients.index', compact('ingredients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50', 'unique:ingredients,code'],
            'base_unit' => ['required', 'string', 'in:g,ml,pcs'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'opening_qty' => ['nullable', 'numeric', 'min:0'],
            'opening_cost' => ['nullable'],
        ]);

        $ingredient = Ingredient::create([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'base_unit' => $validated['base_unit'],
            'min_stock' => $validated['min_stock'] ?? 0,
            'stock_qty' => 0,
            'avg_cost' => 0,
            'is_active' => true,
        ]);

        $openingQty = (float) ($validated['opening_qty'] ?? 0);
        $openingCost = Money::parse($validated['opening_cost'] ?? 0);

        if ($openingQty > 0) {
            $this->stockService->recordOpening($ingredient, $openingQty, (float) $openingCost, auth()->id());
        }

        AuditService::log('create_ingredient', 'Ingredient', $ingredient->id, [
            'name' => $ingredient->name,
            'unit' => $ingredient->base_unit,
            'opening_qty' => $openingQty,
        ]);

        return redirect()->route('bahan.index')->with('success', "Bahan baku '{$ingredient->name}' berhasil ditambahkan.");
    }

    public function update(Request $request, Ingredient $bahan)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:50', 'unique:ingredients,code,' . $bahan->id],
            'base_unit' => ['required', 'string', 'in:g,ml,pcs'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $bahan->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'base_unit' => $validated['base_unit'],
            'min_stock' => $validated['min_stock'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditService::log('update_ingredient', 'Ingredient', $bahan->id, ['name' => $bahan->name]);

        return redirect()->route('bahan.index')->with('success', "Bahan baku '{$bahan->name}' berhasil diperbarui.");
    }

    public function show(Request $request, Ingredient $bahan)
    {
        if ($request->wantsJson()) {
            return response()->json($bahan);
        }

        return redirect()->route('bahan.index');
    }

    public function create()
    {
        return redirect()->route('bahan.index');
    }

    public function edit(Ingredient $bahan)
    {
        return redirect()->route('bahan.index');
    }
}
