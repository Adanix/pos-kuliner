<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Services\AuditService;
use App\Services\HppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    public function __construct(protected HppService $hppService) {}

    public function index()
    {
        $menus = Menu::with(['category', 'recipe.items.ingredient'])
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(20);

        $menus->getCollection()->transform(function ($menu) {
            $menu->hpp_info = $this->hppService->calculateForMenu($menu);
            return $menu;
        });

        return view('recipes.index', compact('menus'));
    }

    public function edit(Menu $menu)
    {
        $menu->load(['recipe.items.ingredient', 'category']);
        $allIngredients = Ingredient::where('is_active', true)->orderBy('name')->get();
        $hppInfo = $this->hppService->calculateForMenu($menu);

        return view('recipes.edit', compact('menu', 'allIngredients', 'hppInfo'));
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['nullable', 'array'],
            'items.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'items.*.qty_base' => ['required', 'numeric', 'min:0.0001'],
        ]);

        DB::transaction(function () use ($menu, $validated) {
            $recipe = $menu->recipe()->firstOrCreate(['menu_id' => $menu->id]);
            $recipe->notes = $validated['notes'] ?? null;
            $recipe->save();

            // Clear existing and replace
            $recipe->items()->delete();

            if (! empty($validated['items'])) {
                foreach ($validated['items'] as $row) {
                    RecipeItem::create([
                        'recipe_id' => $recipe->id,
                        'ingredient_id' => $row['ingredient_id'],
                        'qty_base' => (float) $row['qty_base'],
                    ]);
                }
            }
        });

        AuditService::log('update_recipe', 'Recipe', $menu->id, ['menu' => $menu->name]);

        return redirect()->route('resep.index')->with('success', "Resep untuk menu '{$menu->name}' berhasil diperbarui.");
    }
}
