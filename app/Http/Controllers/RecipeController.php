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
        $allActiveMenus = Menu::with(['recipe.items.ingredient'])
            ->where('is_active', true)
            ->get();

        $totalMenus = $allActiveMenus->count();
        $withRecipeCount = 0;
        $margins = [];

        foreach ($allActiveMenus as $m) {
            $hpp = $this->hppService->calculateForMenu($m);
            $hasValidRecipe = ! empty($m->recipe) && $m->recipe->items->isNotEmpty();
            if ($hasValidRecipe) {
                $withRecipeCount++;
            }
            if ($hpp['hpp_complete'] && $hpp['unit_hpp'] !== null && $m->price > 0) {
                $margins[] = (($m->price - $hpp['unit_hpp']) / $m->price) * 100;
            }
        }

        $avgMargin = ! empty($margins) ? round(array_sum($margins) / count($margins), 1) : 0;
        $needRecipeCount = $totalMenus - $withRecipeCount;

        $menus = Menu::with(['category', 'recipe.items.ingredient'])
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(20);

        $menus->getCollection()->transform(function ($menu) {
            $hpp = $this->hppService->calculateForMenu($menu);
            $menu->hpp_info = $hpp;
            if ($hpp['hpp_complete'] && $hpp['unit_hpp'] !== null && $menu->price > 0) {
                $menu->margin_percent = round((($menu->price - $hpp['unit_hpp']) / $menu->price) * 100, 1);
            } else {
                $menu->margin_percent = null;
            }
            return $menu;
        });

        $metrics = [
            'total_menus' => $totalMenus,
            'with_recipe_count' => $withRecipeCount,
            'avg_margin' => $avgMargin,
            'need_recipe_count' => $needRecipeCount,
        ];

        return view('recipes.index', compact('menus', 'metrics'));
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
