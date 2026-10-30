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
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StockAndHppTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Menu $menu;
    protected Ingredient $ayam;

    protected function setUp(): void
    {
        parent::setUp();

        Outlet::create([
            'name' => 'Outlet Test',
            'require_open_shift' => false,
            'allow_negative_stock' => false, // Disallow negative stock
        ]);

        $this->cashier = User::create([
            'name' => 'Kasir Test',
            'email' => 'kasir@stok.id',
            'role' => UserRole::CASHIER,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $category = Category::create(['name' => 'Makanan', 'sort_order' => 1, 'is_active' => true]);

        $this->menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Ayam Bakar',
            'price' => 20000,
            'is_active' => true,
        ]);

        // Ingredient: Ayam with stock 200g @ Rp 50/g
        $this->ayam = Ingredient::create([
            'name' => 'Daging Ayam',
            'base_unit' => 'g',
            'stock_qty' => 0,
            'min_stock' => 50,
            'avg_cost' => 0,
            'is_active' => true,
        ]);

        app(StockService::class)->recordOpening($this->ayam, 200, 50, $this->cashier->id);

        // Recipe: 1 portion of Ayam Bakar uses 150g Ayam
        $recipe = Recipe::create(['menu_id' => $this->menu->id]);
        RecipeItem::create([
            'recipe_id' => $recipe->id,
            'ingredient_id' => $this->ayam->id,
            'qty_base' => 150,
        ]);
    }

    public function test_insufficient_stock_blocks_transaction_when_disallowed(): void
    {
        // 2 portions require 300g ayam, but current stock is only 200g
        $payload = [
            'channel' => 'pos',
            'payment_method' => 'cash',
            'amount_paid' => 40000,
            'items' => [
                ['menu_id' => $this->menu->id, 'qty' => 2],
            ],
        ];

        $response = $this->actingAs($this->cashier)->postJson('/transaksi', $payload);

        $response->assertStatus(422);
        $this->ayam->refresh();
        $this->assertEquals(200, (float) $this->ayam->stock_qty); // Unchanged!
    }

    public function test_sufficient_stock_deducts_accurately_and_records_movement(): void
    {
        // 1 portion requires 150g ayam. 200g - 150g = 50g left
        $payload = [
            'channel' => 'pos',
            'payment_method' => 'cash',
            'amount_paid' => 20000,
            'items' => [
                ['menu_id' => $this->menu->id, 'qty' => 1],
            ],
        ];

        $response = $this->actingAs($this->cashier)->postJson('/transaksi', $payload);

        $response->assertStatus(201);
        $this->ayam->refresh();
        $this->assertEquals(50, (float) $this->ayam->stock_qty);

        $this->assertDatabaseHas('stock_movements', [
            'ingredient_id' => $this->ayam->id,
            'type' => 'usage',
            'qty_delta' => -150,
        ]);
    }

    public function test_menu_store_with_recipe_items_persists_recipe_and_calculates_hpp(): void
    {
        $owner = User::create([
            'name' => 'Owner Test',
            'email' => 'owner@stok.id',
            'role' => UserRole::OWNER,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $category = Category::first();

        // Create menu with recipe items directly
        $response = $this->actingAs($owner)->post('/menu', [
            'category_id' => $category->id,
            'name' => 'Ayam Penyet Sambal Ijo',
            'price' => '25000',
            'description' => 'Ayam penyet gurih pedas.',
            'has_recipe_builder' => 1,
            'recipe_items' => [
                [
                    'ingredient_id' => $this->ayam->id,
                    'qty_base' => 120, // 120g * Rp 50 = Rp 6.000 HPP
                ],
            ],
        ]);

        $response->assertRedirect('/menu');
        $createdMenu = Menu::where('name', 'Ayam Penyet Sambal Ijo')->first();
        $this->assertNotNull($createdMenu);
        $this->assertNotNull($createdMenu->recipe);
        $this->assertCount(1, $createdMenu->recipe->items);

        $hppService = app(\App\Services\HppService::class);
        $hppInfo = $hppService->calculateForMenu($createdMenu);
        $this->assertTrue($hppInfo['hpp_complete']);
        $this->assertEquals(6000, $hppInfo['unit_hpp']);

        // Update menu with recipe items
        $responseUpdate = $this->actingAs($owner)->put('/menu/' . $createdMenu->id, [
            'category_id' => $category->id,
            'name' => 'Ayam Penyet Sambal Ijo',
            'price' => '30000',
            'is_active' => 1,
            'has_recipe_builder' => 1,
            'recipe_items' => [
                [
                    'ingredient_id' => $this->ayam->id,
                    'qty_base' => 150, // 150g * Rp 50 = Rp 7.500 HPP
                ],
            ],
        ]);

        $responseUpdate->assertRedirect('/menu');
        $createdMenu->refresh();
        $this->assertEquals(30000, $createdMenu->price);
        $updatedHppInfo = $hppService->calculateForMenu($createdMenu);
        $this->assertEquals(7500, $updatedHppInfo['unit_hpp']);
    }

    public function test_recipes_index_displays_metrics_and_margin(): void
    {
        $owner = User::create([
            'name' => 'Owner Resep',
            'email' => 'owner2@stok.id',
            'role' => UserRole::OWNER,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->get('/resep');
        $response->assertStatus(200);
        $response->assertViewHas('metrics');
        $metrics = $response->viewData('metrics');
        $this->assertGreaterThanOrEqual(1, $metrics['total_menus']);
        $this->assertGreaterThanOrEqual(1, $metrics['with_recipe_count']);
    }

    public function test_ingredients_index_displays_used_in_menu(): void
    {
        $owner = User::create([
            'name' => 'Owner Bahan',
            'email' => 'owner3@stok.id',
            'role' => UserRole::OWNER,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->get('/bahan');
        $response->assertStatus(200);
        $response->assertSee('Digunakan di Menu');
        $response->assertSee($this->menu->name);
    }
}
