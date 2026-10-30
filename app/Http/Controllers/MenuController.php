<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use App\Services\AuditService;
use App\Services\HppService;
use App\Support\Money;
use Illuminate\Http\Request;
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

        // Compute HPP summary for each menu
        $menus->getCollection()->transform(function ($menu) {
            $hpp = $this->hppService->calculateForMenu($menu);
            $menu->hpp_info = $hpp;
            return $menu;
        });

        return view('menus.index', compact('menus', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'price' => ['required'],
            'description' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
        ]);

        $price = Money::parse($validated['price']);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'menus/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public', $filename);
            $imagePath = Storage::url($path);
        }

        $menu = Menu::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'price' => $price,
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'is_active' => true,
        ]);

        AuditService::log('create_menu', 'Menu', $menu->id, ['name' => $menu->name, 'price' => $price]);

        return redirect()->route('menu.index')->with('success', "Menu '{$menu->name}' berhasil ditambahkan.");
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
        ]);

        $price = Money::parse($validated['price']);
        $oldPrice = $menu->price;

        $imagePath = $menu->image_path;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'menus/' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public', $filename);
            $imagePath = Storage::url($path);
        }

        $menu->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'price' => $price,
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditService::log('update_menu', 'Menu', $menu->id, [
            'name' => $menu->name,
            'price' => $price,
            'old_price' => $oldPrice,
        ]);

        return redirect()->route('menu.index')->with('success', "Menu '{$menu->name}' berhasil diperbarui.");
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
