<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\AuditService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('menus')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        AuditService::log('create_category', 'Category', $category->id, ['name' => $category->name]);

        return redirect()->route('categories.index')->with('success', "Kategori '{$category->name}' berhasil ditambahkan.");
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditService::log('update_category', 'Category', $category->id, ['name' => $category->name]);

        return redirect()->route('categories.index')->with('success', "Kategori '{$category->name}' berhasil diperbarui.");
    }

    public function destroy(Category $category)
    {
        $menuCount = $category->menus()->count();
        if ($menuCount > 0) {
            return back()->with('error', "Kategori tidak dapat dihapus karena masih digunakan oleh {$menuCount} menu.");
        }

        $name = $category->name;
        $category->delete();

        AuditService::log('delete_category', 'Category', null, ['name' => $name]);

        return redirect()->route('categories.index')->with('success', "Kategori '{$name}' berhasil dihapus.");
    }
}
