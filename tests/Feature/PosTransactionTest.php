<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PosTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Menu $menu;

    protected function setUp(): void
    {
        parent::setUp();

        Outlet::create([
            'name' => 'Outlet Test',
            'rounding_mode' => 'none',
            'require_open_shift' => false,
            'allow_negative_stock' => true,
        ]);

        $this->cashier = User::create([
            'name' => 'Kasir POS',
            'email' => 'kasir@pos.id',
            'role' => UserRole::CASHIER,
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $category = Category::create(['name' => 'Makanan Utama', 'sort_order' => 1, 'is_active' => true]);

        $this->menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Nasi Goreng Spesial',
            'price' => 25000,
            'is_active' => true,
        ]);
    }

    public function test_pos_transaction_creates_record_and_snapshots_price(): void
    {
        $payload = [
            'channel' => 'pos',
            'payment_method' => 'cash',
            'amount_paid' => 50000,
            'items' => [
                [
                    'menu_id' => $this->menu->id,
                    'qty' => 2,
                    'note' => 'Pedas sedang',
                ],
            ],
        ];

        $response = $this->actingAs($this->cashier)
            ->postJson('/transaksi', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('transactions', [
            'total' => 50000,
            'amount_paid' => 50000,
            'change_amount' => 0,
        ]);

        $this->assertDatabaseHas('transaction_items', [
            'menu_name' => 'Nasi Goreng Spesial',
            'qty' => 2,
            'unit_price' => 25000,
            'line_total' => 50000,
        ]);
    }

    public function test_idempotency_key_prevents_duplicate_transactions(): void
    {
        $payload = [
            'channel' => 'pos',
            'payment_method' => 'cash',
            'amount_paid' => 25000,
            'idempotency_key' => 'idem-test-uuid-12345',
            'items' => [
                [
                    'menu_id' => $this->menu->id,
                    'qty' => 1,
                ],
            ],
        ];

        // First attempt
        $res1 = $this->actingAs($this->cashier)->postJson('/transaksi', $payload);
        $res1->assertStatus(201);

        // Second attempt with exact same key
        $res2 = $this->actingAs($this->cashier)->postJson('/transaksi', $payload);
        $res2->assertStatus(201);

        // Ensure only 1 transaction exists in database
        $this->assertEquals(1, Transaction::where('idempotency_key', 'idem-test-uuid-12345')->count());
    }

    public function test_menu_price_change_preserves_historical_transaction_snapshot(): void
    {
        $payload = [
            'channel' => 'pos',
            'payment_method' => 'cash',
            'amount_paid' => 25000,
            'items' => [
                ['menu_id' => $this->menu->id, 'qty' => 1],
            ],
        ];

        $this->actingAs($this->cashier)->postJson('/transaksi', $payload)->assertStatus(201);

        // Owner updates menu price to 30000
        $this->menu->price = 30000;
        $this->menu->save();

        // Transaction history remains 25000
        $firstTx = Transaction::first();
        $item = $firstTx->items->first();
        $this->assertEquals(25000, $item->unit_price);
        $this->assertEquals(25000, $item->line_total);
    }
}
