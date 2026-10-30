<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Services\AuditService;
use App\Services\HppService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    public function __construct(protected HppService $hppService) {}

    public function index(Request $request)
    {
        $query = Menu::with(['category', 'recipe.items.ingredient']);

        if ($catId = $request->input('category_id')) {
            $query->where('category_id', $catId);
        }
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $menus = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $allIngredients = Ingredient::where('is_active', true)->orderBy('name')->get();

        // Compute HPP summary for each menu
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

        return view('menus.index', compact('menus', 'categories', 'allIngredients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'price' => ['required'],
            'description' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'recipe_items' => ['nullable', 'array'],
            'recipe_items.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'recipe_items.*.qty_base' => ['required', 'numeric', 'min:0.0001'],
        ]);

        $price = Money::parse($validated['price']);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('menus', $filename, 'public');
            $imagePath = '/storage/' . $path;
        }

        $menu = DB::transaction(function () use ($validated, $price, $imagePath) {
            $menu = Menu::create([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'price' => $price,
                'description' => $validated['description'] ?? null,
                'image_path' => $imagePath,
                'is_active' => true,
            ]);

            if (! empty($validated['recipe_items'])) {
                $recipe = Recipe::create(['menu_id' => $menu->id]);
                foreach ($validated['recipe_items'] as $itemRow) {
                    RecipeItem::create([
                        'recipe_id' => $recipe->id,
                        'ingredient_id' => $itemRow['ingredient_id'],
                        'qty_base' => (float) $itemRow['qty_base'],
                    ]);
                }
            }

            return $menu;
        });

        AuditService::log('create_menu', 'Menu', $menu->id, ['name' => $menu->name, 'price' => $price]);

        return redirect()->route('menu.index')->with('success', "Menu '{$menu->name}' beserta resep berhasil ditambahkan.");
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'price' => ['required'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            'recipe_items' => ['nullable', 'array'],
            'recipe_items.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'recipe_items.*.qty_base' => ['required', 'numeric', 'min:0.0001'],
        ]);

        $price = Money::parse($validated['price']);
        $oldPrice = $menu->price;

        $imagePath = $menu->image_path;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('menus', $filename, 'public');
            $imagePath = '/storage/' . $path;
        }

        DB::transaction(function () use ($menu, $validated, $price, $imagePath, $request) {
            $menu->update([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'price' => $price,
                'description' => $validated['description'] ?? null,
                'image_path' => $imagePath,
                'is_active' => $request->boolean('is_active'),
            ]);

            if ($request->has('has_recipe_builder') || $request->has('recipe_items')) {
                $recipe = $menu->recipe()->firstOrCreate(['menu_id' => $menu->id]);
                $recipe->items()->delete();

                if (! empty($validated['recipe_items'])) {
                    foreach ($validated['recipe_items'] as $itemRow) {
                        RecipeItem::create([
                            'recipe_id' => $recipe->id,
                            'ingredient_id' => $itemRow['ingredient_id'],
                            'qty_base' => (float) $itemRow['qty_base'],
                        ]);
                    }
                }
            }
        });

        AuditService::log('update_menu', 'Menu', $menu->id, [
            'name' => $menu->name,
            'price' => $price,
            'old_price' => $oldPrice,
        ]);

        return redirect()->route('menu.index')->with('success', "Menu '{$menu->name}' berhasil diperbarui.");
    }

    public function show(Request $request, Menu $menu)
    {
        if ($request->wantsJson()) {
            return response()->json($menu->load(['category', 'recipe.items.ingredient']));
        }

        return redirect()->route('menu.index');
    }

    public function create()
    {
        return redirect()->route('menu.index');
    }

    public function edit(Menu $menu)
    {
        return redirect()->route('menu.index');
    }

    public function destroy(Menu $menu)
    {
        $name = $menu->name;
        // Softly set inactive to preserve historical integrity, or delete if no sales
        $menu->is_active = false;
        $menu->save();

        AuditService::log('deactivate_menu', 'Menu', $menu->id, ['name' => $name]);

        return redirect()->route('menu.index')->with('success', "Menu '{$name}' telah dinonaktifkan.");
    }
}
