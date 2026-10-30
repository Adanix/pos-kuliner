<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Outlet;
use App\Models\RecipeItem;
use App\Models\Setting;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WizardController extends Controller
{
    public function index(Request $request)
    {
        $step = (int) $request->input('step', 1);

        $outlet = Outlet::first();
        $categories = Category::all();
        $menus = Menu::with('category')->get();
        $ingredients = Ingredient::all();
        $cashiers = User::where('role', UserRole::CASHIER)->get();

        return view('wizard.index', compact('step', 'outlet', 'categories', 'menus', 'ingredients', 'cashiers'));
    }

    public function saveStep(Request $request)
    {
        $step = (int) $request->input('step', 1);

        switch ($step) {
            case 1: // Outlet
                $validated = $request->validate([
                    'name' => ['required', 'string', 'max:255'],
                    'address' => ['nullable', 'string', 'max:500'],
                    'phone' => ['nullable', 'string', 'max:50'],
                ]);
                $outlet = Outlet::firstOrCreate([]);
                $outlet->update($validated);
                return redirect()->route('wizard.index', ['step' => 2]);

            case 2: // Kategori
                if ($name = $request->input('category_name')) {
                    Category::firstOrCreate(['name' => trim($name)], ['sort_order' => 1, 'is_active' => true]);
                }
                return redirect()->route('wizard.index', ['step' => 3]);

            case 3: // Menu
                if ($menuName = $request->input('menu_name')) {
                    $catId = $request->input('category_id');
                    $price = Money::parse($request->input('price', 0));
                    if ($catId && $price > 0) {
                        Menu::create([
                            'category_id' => $catId,
                            'name' => trim($menuName),
                            'price' => $price,
                            'is_active' => true,
                        ]);
                    }
                }
                return redirect()->route('wizard.index', ['step' => 4]);

            case 4: // Bahan Baku
                if ($ingName = $request->input('ingredient_name')) {
                    Ingredient::create([
                        'name' => trim($ingName),
                        'base_unit' => $request->input('base_unit', 'g'),
                        'min_stock' => (float) $request->input('min_stock', 0),
                        'stock_qty' => (float) $request->input('stock_qty', 0),
                        'avg_cost' => Money::parse($request->input('avg_cost', 0)),
                        'is_active' => true,
                    ]);
                }
                return redirect()->route('wizard.index', ['step' => 5]);

            case 5: // Resep
                if ($menuId = $request->input('menu_id')) {
                    $ingId = $request->input('ingredient_id');
                    $qty = (float) $request->input('qty_base', 0);
                    if ($ingId && $qty > 0) {
                        $menu = Menu::find($menuId);
                        $recipe = $menu->recipe()->firstOrCreate(['menu_id' => $menu->id]);
                        RecipeItem::updateOrCreate(
                            ['recipe_id' => $recipe->id, 'ingredient_id' => $ingId],
                            ['qty_base' => $qty]
                        );
                    }
                }
                return redirect()->route('wizard.index', ['step' => 6]);

            case 6: // Kasir
                if ($cashierEmail = $request->input('cashier_email')) {
                    User::create([
                        'name' => $request->input('cashier_name', 'Kasir 1'),
                        'email' => $cashierEmail,
                        'role' => UserRole::CASHIER,
                        'password' => Hash::make($request->input('cashier_password', 'kasir12345')),
                        'pin_hash' => Hash::make($request->input('cashier_pin', '1234')),
                        'is_active' => true,
                    ]);
                }
                Setting::set('wizard.completed', true, auth()->id());
                return redirect()->route('pos.index')->with('success', 'Setup awal outlet Anda telah selesai! Selamat bertransaksi.');
        }

        return redirect()->route('pos.index');
    }
}
