<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Outlet;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\User;
use App\Services\SalesService;
use App\Services\ShiftService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VoidAndShiftTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $cashier;
    protected Menu $menu;
    protected Ingredient $ingredient;

    protected function setUp(): void
    {
        parent::setUp();

        Outlet::create([
            'name' => 'Outlet Test',
            'require_open_shift' => false,
            'allow_negative_stock' => true,
        ]);

        $this->owner = User::create([
            'name' => 'Owner',
            'email' => 'owner@shift.id',
            'role' => UserRole::OWNER,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $this->cashier = User::create([
            'name' => 'Kasir',
            'email' => 'kasir@shift.id',
            'role' => UserRole::CASHIER,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $cat = Category::create(['name' => 'Kategori', 'sort_order' => 1, 'is_active' => true]);
        $this->menu = Menu::create(['category_id' => $cat->id, 'name' => 'Kopi', 'price' => 15000, 'is_active' => true]);

        $this->ingredient = Ingredient::create([
            'name' => 'Biji Kopi',
            'base_unit' => 'g',
            'stock_qty' => 0,
            'min_stock' => 10,
            'avg_cost' => 0,
            'is_active' => true,
        ]);
        app(StockService::class)->recordOpening($this->ingredient, 100, 200, $this->owner->id);

        $recipe = Recipe::create(['menu_id' => $this->menu->id]);
        RecipeItem::create(['recipe_id' => $recipe->id, 'ingredient_id' => $this->ingredient->id, 'qty_base' => 15]);
    }

    public function test_void_requires_min_10_chars_and_reverses_stock_if_checked(): void
    {
        $salesService = app(SalesService::class);
        $tx = $salesService->storeTransaction([
            'channel' => 'pos',
            'payment_method' => 'cash',
            'amount_paid' => 15000,
            'items' => [['menu_id' => $this->menu->id, 'qty' => 1]],
        ], $this->cashier->id);

        $this->ingredient->refresh();
        $this->assertEquals(85, (float) $this->ingredient->stock_qty); // 100 - 15 = 85

        // Cashier cannot void (403)
        $this->actingAs($this->cashier)->post("/transaksi/{$tx->id}/void", [
            'reason' => 'Alasan pembatalan lebih dari sepuluh karakter',
        ])->assertStatus(403);

        // Owner voids with valid reason and return_stock = true
        $res = $this->actingAs($this->owner)->post("/transaksi/{$tx->id}/void", [
            'reason' => 'Pelanggan membatalkan pesanan karena salah klik',
            'return_stock' => '1',
        ]);

        $res->assertSessionHas('success');
        $tx->refresh();
        $this->assertEquals('voided', $tx->status->value);

        // Stock reversed back to 100
        $this->ingredient->refresh();
        $this->assertEquals(100, (float) $this->ingredient->stock_qty);
    }

    public function test_cashier_shift_open_close_and_difference(): void
    {
        $shiftService = app(ShiftService::class);

        // Open shift with Rp 100.000 float
        $session = $shiftService->openShift(100000, $this->cashier->id);
        $this->assertEquals('open', $session->status);
        $this->assertEquals(100000, $session->opening_float);

        // Perform cash sale of Rp 15.000
        app(SalesService::class)->storeTransaction([
            'channel' => 'pos',
            'payment_method' => 'cash',
            'amount_paid' => 15000,
            'items' => [['menu_id' => $this->menu->id, 'qty' => 1]],
        ], $this->cashier->id);

        // Expected cash should be 100.000 + 15.000 = 115.000
        $stats = $shiftService->calculateExpectedCash($session);
        $this->assertEquals(115000, $stats['expected_cash']);

        // Close shift with counted cash 113.000 -> Difference -2.000
        $closed = $shiftService->closeShift($session, 113000, 'Kurang Rp 2.000 karena salah kembalian', $this->cashier->id);
        $this->assertEquals('closed', $closed->status);
        $this->assertEquals(-2000, $closed->difference);
    }
}
