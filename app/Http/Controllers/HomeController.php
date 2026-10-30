<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Outlet;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function landing()
    {
        $outlet = Outlet::first();
        $menuCount = Menu::where('is_active', true)->count();
        $categoryCount = Category::where('is_active', true)->count();

        return view('landing', compact('outlet', 'menuCount', 'categoryCount'));
    }

    public function docs()
    {
        $outlet = Outlet::first();
        return view('docs', compact('outlet'));
    }
}
