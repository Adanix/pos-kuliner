<?php

namespace Database\Seeders;

use App\Enums\Channel;
use App\Enums\PaymentMethod;
use App\Enums\StockMovementType;
use App\Enums\TransactionStatus;
use App\Enums\UserRole;
use App\Models\AiImport;
use App\Models\AuditLog;
use App\Models\CashSession;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuAlias;
use App\Models\Outlet;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionItemIngredient;
use App\Models\User;
use App\Services\HppService;
use App\Services\Ai\MenuMatcher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure Outlet
        $outlet = Outlet::first();
        if (! $outlet) {
            $outlet = new Outlet();
        }
        $outlet->name = 'POS Kuliner Nusantara';
        $outlet->address = 'Jl. Veteran No. 45, Gambir, Jakarta Pusat';
        $outlet->phone = '0812-3456-7890';
        $outlet->receipt_footer = 'Terima kasih atas kunjungan Anda! Follow IG @poskuliner.demo';
        $outlet->tax_percent = 0;
        $outlet->service_charge_percent = 0;
        $outlet->rounding_mode = 'none';
        $outlet->require_open_shift = true;
        $outlet->allow_negative_stock = false;
        $outlet->save();

        // 2. Ensure Users (Owner & Cashier)
        $owner = User::firstOrCreate(
            ['role' => UserRole::OWNER],
            [
                'name' => 'Pemilik POS Kuliner',
                'email' => 'owner@kuliner.ramadadawah.my.id',
                'password' => Hash::make('password123'),
                'pin_hash' => Hash::make('123456'),
                'is_active' => true,
            ]
        );
        if (! $owner->pin_hash) {
            $owner->pin_hash = Hash::make('123456');
            $owner->save();
        }

        $cashier = User::firstOrCreate(
            ['role' => UserRole::CASHIER],
            [
                'name' => 'Kasir Demo',
                'email' => 'kasir@kuliner.ramadadawah.my.id',
                'password' => Hash::make('password123'),
                'pin_hash' => null,
                'is_active' => true,
            ]
        );

        // 3. Clean operational tables before seeding dummy data
        Schema::disableForeignKeyConstraints();
        TransactionItemIngredient::truncate();
        TransactionItem::truncate();
        Transaction::truncate();
        AiImport::truncate();
        CashSession::truncate();
        StockMovement::truncate();
        RecipeItem::truncate();
        Recipe::truncate();
        MenuAlias::truncate();
        Menu::truncate();
        Ingredient::truncate();
        Category::truncate();
        AuditLog::truncate();
        Schema::enableForeignKeyConstraints();

        // 4. Categories (5 Categories)
        $categoriesData = [
            ['name' => 'Makanan Utama', 'sort_order' => 1, 'is_active' => true],
            ['name' => 'Camilan & Gorengan', 'sort_order' => 2, 'is_active' => true],
            ['name' => 'Minuman Segar', 'sort_order' => 3, 'is_active' => true],
            ['name' => 'Kopi & Teh', 'sort_order' => 4, 'is_active' => true],
            ['name' => 'Paket Nasi Hemat', 'sort_order' => 5, 'is_active' => true],
        ];

        $categories = [];
        foreach ($categoriesData as $cData) {
            $categories[$cData['name']] = Category::create($cData);
        }

        // 5. Ingredients (18 Ingredients) with Opening Stock Ledger
        $ingredientsData = [
            ['code' => 'ING-AYM', 'name' => 'Ayam Karkas Potong Segar', 'base_unit' => 'g', 'stock_qty' => 30000, 'min_stock' => 5000, 'avg_cost' => 38.00],
            ['code' => 'ING-BBK', 'name' => 'Daging Bebek Karkas', 'base_unit' => 'g', 'stock_qty' => 15000, 'min_stock' => 3000, 'avg_cost' => 55.00],
            ['code' => 'ING-SPI', 'name' => 'Daging Sapi Brisket Rawon', 'base_unit' => 'g', 'stock_qty' => 12000, 'min_stock' => 2000, 'avg_cost' => 125.00],
            ['code' => 'ING-BRS', 'name' => 'Beras Ramos Premium', 'base_unit' => 'g', 'stock_qty' => 60000, 'min_stock' => 10000, 'avg_cost' => 14.00],
            ['code' => 'ING-TLR', 'name' => 'Telur Ayam Ras Segar', 'base_unit' => 'pcs', 'stock_qty' => 350, 'min_stock' => 50, 'avg_cost' => 2000.00],
            ['code' => 'ING-MYK', 'name' => 'Minyak Goreng Sawit', 'base_unit' => 'ml', 'stock_qty' => 40000, 'min_stock' => 5000, 'avg_cost' => 17.50],
            ['code' => 'ING-SMB', 'name' => 'Bumbu Sambal Bawang Ulek', 'base_unit' => 'g', 'stock_qty' => 8000, 'min_stock' => 1000, 'avg_cost' => 35.00],
            ['code' => 'ING-MIE', 'name' => 'Mie Telur Spesial Dapur', 'base_unit' => 'g', 'stock_qty' => 15000, 'min_stock' => 2000, 'avg_cost' => 22.00],
            ['code' => 'ING-THU', 'name' => 'Tahu Putih Tradisional', 'base_unit' => 'pcs', 'stock_qty' => 200, 'min_stock' => 30, 'avg_cost' => 1000.00],
            ['code' => 'ING-TMP', 'name' => 'Tempe Daun Segar', 'base_unit' => 'pcs', 'stock_qty' => 150, 'min_stock' => 20, 'avg_cost' => 1500.00],
            ['code' => 'ING-KNT', 'name' => 'Kentang Impor Potong', 'base_unit' => 'g', 'stock_qty' => 10000, 'min_stock' => 2000, 'avg_cost' => 28.00],
            ['code' => 'ING-BMB', 'name' => 'Bumbu Kuah Soto & Santan', 'base_unit' => 'ml', 'stock_qty' => 15000, 'min_stock' => 2000, 'avg_cost' => 20.00],
            ['code' => 'ING-TEH', 'name' => 'Daun Teh Melati Wangi', 'base_unit' => 'g', 'stock_qty' => 5000, 'min_stock' => 500, 'avg_cost' => 35.00],
            ['code' => 'ING-KOP', 'name' => 'Biji Kopi Espresso Blend', 'base_unit' => 'g', 'stock_qty' => 6000, 'min_stock' => 500, 'avg_cost' => 180.00],
            ['code' => 'ING-SSU', 'name' => 'Susu UHT Full Cream', 'base_unit' => 'ml', 'stock_qty' => 20000, 'min_stock' => 3000, 'avg_cost' => 20.00],
            ['code' => 'ING-ARN', 'name' => 'Gula Aren Cair Organik', 'base_unit' => 'ml', 'stock_qty' => 10000, 'min_stock' => 2000, 'avg_cost' => 32.00],
            ['code' => 'ING-JRK', 'name' => 'Jeruk Nipis Peras Segar', 'base_unit' => 'g', 'stock_qty' => 1500, 'min_stock' => 2000, 'avg_cost' => 25.00], // Low stock alert!
            ['code' => 'ING-PCK', 'name' => 'Lunch Box & Cup Takeaway', 'base_unit' => 'pcs', 'stock_qty' => 600, 'min_stock' => 100, 'avg_cost' => 900.00],
        ];

        $ingredients = [];
        $openingTime = Carbon::now('Asia/Jakarta')->subDays(7)->setTime(8, 0, 0);

        foreach ($ingredientsData as $iData) {
            $ing = Ingredient::create([
                'code' => $iData['code'],
                'name' => $iData['name'],
                'base_unit' => $iData['base_unit'],
                'stock_qty' => $iData['stock_qty'],
                'min_stock' => $iData['min_stock'],
                'avg_cost' => $iData['avg_cost'],
                'is_active' => true,
            ]);
            $ingredients[$iData['code']] = $ing;

            // Record initial opening stock ledger
            StockMovement::create([
                'ingredient_id' => $ing->id,
                'type' => StockMovementType::OPENING,
                'qty_delta' => $iData['stock_qty'],
                'unit_cost' => $iData['avg_cost'],
                'value_delta' => (int) round($iData['stock_qty'] * $iData['avg_cost']),
                'reason' => 'Saldo awal bahan baku operasional',
                'user_id' => $owner->id,
                'created_at' => $openingTime,
            ]);
        }

        // Additional realistic stock movements: 1 purchase & 1 waste
        StockMovement::create([
            'ingredient_id' => $ingredients['ING-BRS']->id,
            'type' => StockMovementType::PURCHASE,
            'qty_delta' => 20000,
            'unit_cost' => 14.00,
            'value_delta' => 280000,
            'reason' => 'Pembelian beras pasar induk (Faktur PO-8821)',
            'user_id' => $owner->id,
            'created_at' => Carbon::now('Asia/Jakarta')->subDays(3)->setTime(10, 30, 0),
        ]);

        StockMovement::create([
            'ingredient_id' => $ingredients['ING-THU']->id,
            'type' => StockMovementType::WASTE,
            'qty_delta' => -10,
            'unit_cost' => 1000.00,
            'value_delta' => -10000,
            'reason' => 'Tahu asam / basi saat penyimpanan kulkas',
            'user_id' => $owner->id,
            'created_at' => Carbon::now('Asia/Jakarta')->subDays(2)->setTime(16, 0, 0),
        ]);

        // 6. Menus (15 Menus) & Recipes
        $menusData = [
            // Makanan Utama
            [
                'category' => 'Makanan Utama',
                'name' => 'Ayam Goreng Kremes Sambal Bawang',
                'description' => 'Ayam goreng gurih dengan taburan kremes renyah dan sambal bawang pedas khas Jawa.',
                'price' => 25000,
                'recipe' => [
                    'ING-AYM' => 200, // 200g
                    'ING-SMB' => 30,  // 30g
                    'ING-MYK' => 40,  // 40ml
                    'ING-PCK' => 1,   // 1pcs
                ],
            ],
            [
                'category' => 'Makanan Utama',
                'name' => 'Ayam Bakar Kecap Pedas',
                'description' => 'Ayam bakar dengan bumbu kecap rempah pilihan, manis pedas meresap sempurna.',
                'price' => 26000,
                'recipe' => [
                    'ING-AYM' => 200,
                    'ING-SMB' => 25,
                    'ING-MYK' => 20,
                    'ING-PCK' => 1,
                ],
            ],
            [
                'category' => 'Makanan Utama',
                'name' => 'Bebek Goreng Gurih Krispi',
                'description' => 'Daging bebek empuk dengan kulit garing berbumbu laos rempah dan lalapan.',
                'price' => 36000,
                'recipe' => [
                    'ING-BBK' => 250,
                    'ING-SMB' => 30,
                    'ING-MYK' => 50,
                    'ING-PCK' => 1,
                ],
            ],
            [
                'category' => 'Makanan Utama',
                'name' => 'Nasi Goreng Spesial Telur Ceplok',
                'description' => 'Nasi goreng harum wajan panas dengan suwiran ayam, bakso, dan telur mata sapi.',
                'price' => 22000,
                'recipe' => [
                    'ING-BRS' => 180,
                    'ING-TLR' => 1,
                    'ING-MYK' => 30,
                    'ING-PCK' => 1,
                ],
            ],
            [
                'category' => 'Makanan Utama',
                'name' => 'Mie Goreng Jawa Spesial',
                'description' => 'Mie telur kenyal dengan bumbu kemiri manis gurih, sayuran, dan telur orak-arik.',
                'price' => 20000,
                'recipe' => [
                    'ING-MIE' => 120,
                    'ING-TLR' => 1,
                    'ING-MYK' => 25,
                    'ING-PCK' => 1,
                ],
            ],
            [
                'category' => 'Makanan Utama',
                'name' => 'Soto Betawi Daging Sapi Kuah Santan',
                'description' => 'Potongan daging sapi empuk dengan kuah santan gurih berempah dan emping melinjo.',
                'price' => 38000,
                'recipe' => [
                    'ING-SPI' => 120,
                    'ING-BMB' => 200,
                    'ING-PCK' => 1,
                ],
            ],

            // Camilan & Gorengan
            [
                'category' => 'Camilan & Gorengan',
                'name' => 'Tahu Crispy Cabe Garam',
                'description' => 'Tahu sutra potong dadu digoreng krispi dengan taburan bawang putih dan cabai rawit.',
                'price' => 15000,
                'recipe' => [
                    'ING-THU' => 4,
                    'ING-MYK' => 40,
                    'ING-SMB' => 15,
                ],
            ],
            [
                'category' => 'Camilan & Gorengan',
                'name' => 'Tempe Mendoan Gurih (4 Pcs)',
                'description' => 'Tempe daun goreng setengah matang tepung rempah disajikan dengan sambal kecap rawit.',
                'price' => 14000,
                'recipe' => [
                    'ING-TMP' => 4,
                    'ING-MYK' => 40,
                    'ING-SMB' => 15,
                ],
            ],
            [
                'category' => 'Camilan & Gorengan',
                'name' => 'Kentang Goreng Saus Keju',
                'description' => 'Kentang stik goreng renyah disiram saus keju creamy gurih.',
                'price' => 18000,
                'recipe' => [
                    'ING-KNT' => 150,
                    'ING-MYK' => 40,
                    'ING-PCK' => 1,
                ],
            ],

            // Minuman Segar
            [
                'category' => 'Minuman Segar',
                'name' => 'Es Teh Manis Jumbo',
                'description' => 'Teh seduh melati segar porsi jumbo pelepas dahaga.',
                'price' => 6000,
                'recipe' => [
                    'ING-TEH' => 15,
                    'ING-PCK' => 1,
                ],
            ],
            [
                'category' => 'Minuman Segar',
                'name' => 'Es Jeruk Nipis Madu',
                'description' => 'Perasan jeruk nipis asli dipadukan dengan gula aren dan madu segar.',
                'price' => 12000,
                'recipe' => [
                    'ING-JRK' => 80,
                    'ING-ARN' => 30,
                    'ING-PCK' => 1,
                ],
            ],

            // Kopi & Teh
            [
                'category' => 'Kopi & Teh',
                'name' => 'Es Kopi Susu Gula Aren',
                'description' => 'Espresso robusta mantap dipadukan dengan susu segar creamy dan gula aren cair.',
                'price' => 18000,
                'recipe' => [
                    'ING-KOP' => 18,
                    'ING-SSU' => 120,
                    'ING-ARN' => 30,
                    'ING-PCK' => 1,
                ],
            ],
            [
                'category' => 'Kopi & Teh',
                'name' => 'Teh Tarik Dingin',
                'description' => 'Teh kental berpadu susu ditarik hingga berbusa lembut khas peranakan.',
                'price' => 14000,
                'recipe' => [
                    'ING-TEH' => 20,
                    'ING-SSU' => 80,
                    'ING-PCK' => 1,
                ],
            ],

            // Paket Nasi Hemat
            [
                'category' => 'Paket Nasi Hemat',
                'name' => 'Paket Nasi Ayam Kremes + Es Teh',
                'description' => 'Nasi putih pulen, ayam goreng kremes sambal bawang, lalapan, plus Es Teh Manis Jumbo.',
                'price' => 28000,
                'recipe' => [
                    'ING-AYM' => 200,
                    'ING-BRS' => 180,
                    'ING-SMB' => 30,
                    'ING-MYK' => 40,
                    'ING-TEH' => 15,
                    'ING-PCK' => 2,
                ],
            ],
            [
                'category' => 'Paket Nasi Hemat',
                'name' => 'Paket Nasi Bebek Gurih + Es Jeruk',
                'description' => 'Nasi putih pulen, bebek goreng gurih renyah, sambal spesial, plus Es Jeruk Nipis Madu.',
                'price' => 42000,
                'recipe' => [
                    'ING-BBK' => 250,
                    'ING-BRS' => 180,
                    'ING-SMB' => 30,
                    'ING-MYK' => 50,
                    'ING-JRK' => 80,
                    'ING-ARN' => 30,
                    'ING-PCK' => 2,
                ],
            ],
        ];

        $menus = [];
        $hppService = app(HppService::class);

        foreach ($menusData as $mData) {
            $cat = $categories[$mData['category']];
            $menu = Menu::create([
                'category_id' => $cat->id,
                'name' => $mData['name'],
                'description' => $mData['description'],
                'price' => $mData['price'],
                'image_path' => null,
                'is_active' => true,
            ]);
            $menus[$mData['name']] = $menu;

            // Create Recipe
            $recipe = Recipe::create([
                'menu_id' => $menu->id,
                'notes' => 'Standar takaran resep dapur outlet ' . $mData['name'],
            ]);

            foreach ($mData['recipe'] as $ingCode => $qtyBase) {
                $ing = $ingredients[$ingCode];
                RecipeItem::create([
                    'recipe_id' => $recipe->id,
                    'ingredient_id' => $ing->id,
                    'qty_base' => $qtyBase,
                ]);
            }
        }

        // 7. Menu Aliases (For AI Vision Matching)
        $aliasesData = [
            ['menu' => 'Ayam Goreng Kremes Sambal Bawang', 'source_name' => 'Ayam Kremes Sambal Bawang Pedas', 'channel' => 'gofood'],
            ['menu' => 'Paket Nasi Ayam Kremes + Es Teh', 'source_name' => 'Paket Hemat Ayam Kremes + Es Teh', 'channel' => 'gofood'],
            ['menu' => 'Es Kopi Susu Gula Aren', 'source_name' => 'Kopi Susu Aren Special 500ml', 'channel' => 'shopeefood'],
            ['menu' => 'Nasi Goreng Spesial Telur Ceplok', 'source_name' => 'Nasgor Spesial Telor', 'channel' => 'shopeefood'],
        ];

        $menuMatcher = app(MenuMatcher::class);
        foreach ($aliasesData as $alias) {
            $targetMenu = $menus[$alias['menu']];
            MenuAlias::create([
                'menu_id' => $targetMenu->id,
                'source_name' => $alias['source_name'],
                'normalized_name' => $menuMatcher->normalize($alias['source_name']),
                'channel' => $alias['channel'],
            ]);
        }

        // 8. Cash Sessions (Shifts)
        // Shift Kemarin (Closed)
        $yesterday = Carbon::yesterday('Asia/Jakarta');
        $closedShift = CashSession::create([
            'outlet_id' => $outlet->id,
            'opened_by' => $cashier->id,
            'opened_at' => $yesterday->copy()->setTime(9, 0, 0),
            'opening_float' => 200000,
            'closed_by' => $cashier->id,
            'closed_at' => $yesterday->copy()->setTime(21, 30, 0),
            'counted_cash' => 1840000,
            'expected_cash' => 1840000,
            'difference' => 0,
            'note' => 'Shift kemarin berjalan tertib dan lancar. Uang fisik di laci pas.',
            'status' => 'closed',
        ]);

        // Shift Hari Ini (Active / Open)
        $today = Carbon::today('Asia/Jakarta');
        $activeShift = CashSession::create([
            'outlet_id' => $outlet->id,
            'opened_by' => $cashier->id,
            'opened_at' => $today->copy()->setTime(9, 0, 0),
            'opening_float' => 200000,
            'status' => 'open',
            'note' => 'Shift aktif kasir demo hari ini.',
        ]);

        // 9. Historical Transactions (Past 6 Days & Yesterday)
        $txSequence = 1;
        $seedTransaction = function (
            string $number,
            Carbon $time,
            string $channel,
            string $status,
            string $paymentMethod,
            array $itemsWithQty,
            ?CashSession $shift = null,
            int $discountAmount = 0,
            int $platformFee = 0,
            ?string $platformOrderId = null,
            ?string $voidReason = null
        ) use ($menus, $hppService, $outlet, $cashier, $owner) {
            $subtotal = 0;
            $itemsPayload = [];

            foreach ($itemsWithQty as $menuName => $qty) {
                $menu = $menus[$menuName];
                $lineTotal = $menu->price * $qty;
                $subtotal += $lineTotal;

                $hppInfo = $hppService->calculateForMenu($menu);

                $itemsPayload[] = [
                    'menu' => $menu,
                    'qty' => $qty,
                    'unit_price' => $menu->price,
                    'line_total' => $lineTotal,
                    'unit_hpp' => $hppInfo['unit_hpp'] ?? 0,
                    'hpp_complete' => $hppInfo['hpp_complete'] ?? false,
                    'ingredients' => $hppInfo['ingredients'] ?? [],
                ];
            }

            $total = max(0, $subtotal - $discountAmount);
            $amountPaid = $paymentMethod === 'cash' ? (int) (ceil($total / 10000) * 10000) : $total;
            if ($amountPaid < $total) {
                $amountPaid = $total;
            }
            $changeAmount = $amountPaid - $total;

            $tx = Transaction::create([
                'number' => $number,
                'outlet_id' => $outlet->id,
                'channel' => $channel,
                'status' => $status,
                'occurred_at' => $time,
                'subtotal' => $subtotal,
                'discount_type' => $discountAmount > 0 ? 'fixed' : 'none',
                'discount_value' => $discountAmount,
                'discount_amount' => $discountAmount,
                'service_charge' => 0,
                'tax_amount' => 0,
                'rounding_amount' => 0,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'amount_paid' => $amountPaid,
                'change_amount' => $changeAmount,
                'platform_fee' => $platformFee,
                'platform_order_id' => $platformOrderId,
                'cash_session_id' => $shift?->id,
                'user_id' => $cashier->id,
                'idempotency_key' => 'SEED-' . $number,
                'note' => $channel === 'pos' ? 'Pesanan Kasir' : 'Pesanan Online ' . strtoupper($channel),
                'voided_at' => $status === TransactionStatus::VOIDED->value ? $time->copy()->addMinutes(15) : null,
                'voided_by' => $status === TransactionStatus::VOIDED->value ? $owner->id : null,
                'void_reason' => $voidReason,
                'created_at' => $time,
                'updated_at' => $time,
            ]);

            foreach ($itemsPayload as $item) {
                $txItem = TransactionItem::create([
                    'transaction_id' => $tx->id,
                    'menu_id' => $item['menu']->id,
                    'menu_name' => $item['menu']->name,
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'line_discount' => 0,
                    'line_total' => $item['line_total'],
                    'unit_hpp' => $item['unit_hpp'],
                    'hpp_complete' => $item['hpp_complete'],
                ]);

                foreach ($item['ingredients'] as $ingDetail) {
                    $ingQtyTotal = (float) $ingDetail['qty_base'] * $item['qty'];
                    $ingCostTotal = (int) round($ingQtyTotal * $ingDetail['unit_cost']);

                    TransactionItemIngredient::create([
                        'transaction_item_id' => $txItem->id,
                        'ingredient_id' => $ingDetail['ingredient_id'],
                        'ingredient_name' => $ingDetail['ingredient_name'],
                        'qty_base' => $ingQtyTotal,
                        'unit_cost' => $ingDetail['unit_cost'],
                        'cost_total' => $ingCostTotal,
                    ]);
                }
            }

            return $tx;
        };

        // --- Transaksi 6 hari lalu sampai 2 hari lalu ---
        for ($daysAgo = 6; $daysAgo >= 2; $daysAgo--) {
            $dayDate = Carbon::today('Asia/Jakarta')->subDays($daysAgo);
            $dayCode = $dayDate->format('ymd');

            // 2 POS Cash
            $seedTransaction(
                sprintf('CIO-%s-%04d', $dayCode, 1),
                $dayDate->copy()->setTime(11, 30, 0),
                'pos',
                'completed',
                'cash',
                ['Paket Nasi Ayam Kremes + Es Teh' => 2, 'Tahu Crispy Cabe Garam' => 1]
            );

            // 1 POS QRIS
            $seedTransaction(
                sprintf('CIO-%s-%04d', $dayCode, 2),
                $dayDate->copy()->setTime(13, 15, 0),
                'pos',
                'completed',
                'qris',
                ['Ayam Bakar Kecap Pedas' => 2, 'Es Teh Manis Jumbo' => 2]
            );

            // 1 GoFood
            $seedTransaction(
                sprintf('CIO-%s-%04d', $dayCode, 3),
                $dayDate->copy()->setTime(18, 45, 0),
                'gofood',
                'completed',
                'other',
                ['Bebek Goreng Gurih Krispi' => 2, 'Es Jeruk Nipis Madu' => 2],
                null,
                0,
                19200,
                sprintf('GF-%s-001', $dayCode)
            );
        }

        // --- Transaksi Kemarin (Sesi shift closed kemarin) ---
        $yesterdayCode = $yesterday->format('ymd');

        $seedTransaction(
            sprintf('CIO-%s-%04d', $yesterdayCode, 1),
            $yesterday->copy()->setTime(11, 15, 0),
            'pos',
            'completed',
            'cash',
            ['Nasi Goreng Spesial Telur Ceplok' => 2, 'Es Teh Manis Jumbo' => 2],
            $closedShift
        );

        $seedTransaction(
            sprintf('CIO-%s-%04d', $yesterdayCode, 2),
            $yesterday->copy()->setTime(12, 30, 0),
            'pos',
            'completed',
            'cash',
            ['Paket Nasi Bebek Gurih + Es Jeruk' => 1, 'Tempe Mendoan Gurih (4 Pcs)' => 1],
            $closedShift
        );

        // 1 Voided transaction kemarin
        $voidedTx = $seedTransaction(
            sprintf('CIO-%s-%04d', $yesterdayCode, 3),
            $yesterday->copy()->setTime(13, 40, 0),
            'pos',
            'voided',
            'cash',
            ['Soto Betawi Daging Sapi Kuah Santan' => 1, 'Es Jeruk Nipis Madu' => 1],
            $closedShift,
            0,
            0,
            null,
            'Pelanggan membatalkan pesanan karena salah pilih paket'
        );

        // Audit Log for the voided transaction
        AuditLog::create([
            'user_id' => $owner->id,
            'action' => 'void_transaction',
            'entity_type' => 'transaction',
            'entity_id' => $voidedTx->id,
            'summary' => [
                'transaction_number' => $voidedTx->number,
                'total' => $voidedTx->total,
                'channel' => 'pos',
            ],
            'reason' => 'Otorisasi PIN Pemilik: Pelanggan membatalkan pesanan karena salah pilih paket',
            'ip' => '127.0.0.1',
            'created_at' => $yesterday->copy()->setTime(13, 55, 0),
        ]);

        $seedTransaction(
            sprintf('CIO-%s-%04d', $yesterdayCode, 4),
            $yesterday->copy()->setTime(18, 20, 0),
            'shopeefood',
            'completed',
            'other',
            ['Mie Goreng Jawa Spesial' => 2, 'Es Kopi Susu Gula Aren' => 2],
            null,
            0,
            15200,
            sprintf('SPF-%s-0881', $yesterdayCode)
        );

        // --- Transaksi Hari Ini (Sesi shift aktif hari ini) ---
        $todayCode = $today->format('ymd');

        // Transaksi 1: POS Tunai
        $seedTransaction(
            sprintf('CIO-%s-%04d', $todayCode, 1),
            $today->copy()->setTime(10, 15, 0),
            'pos',
            'completed',
            'cash',
            ['Ayam Goreng Kremes Sambal Bawang' => 1, 'Es Teh Manis Jumbo' => 1],
            $activeShift
        );

        // Transaksi 2: POS QRIS
        $seedTransaction(
            sprintf('CIO-%s-%04d', $todayCode, 2),
            $today->copy()->setTime(11, 20, 0),
            'pos',
            'completed',
            'qris',
            ['Paket Nasi Ayam Kremes + Es Teh' => 2, 'Tahu Crispy Cabe Garam' => 1],
            $activeShift
        );

        // Transaksi 3: POS Tunai
        $seedTransaction(
            sprintf('CIO-%s-%04d', $todayCode, 3),
            $today->copy()->setTime(12, 05, 0),
            'pos',
            'completed',
            'cash',
            ['Nasi Goreng Spesial Telur Ceplok' => 1, 'Es Jeruk Nipis Madu' => 1],
            $activeShift
        );

        // Transaksi 4: GoFood Online
        $seedTransaction(
            sprintf('CIO-%s-%04d', $todayCode, 4),
            $today->copy()->setTime(12, 45, 0),
            'gofood',
            'completed',
            'other',
            ['Paket Nasi Bebek Gurih + Es Jeruk' => 1],
            null,
            0,
            8400,
            sprintf('GF-%s-0102', $todayCode)
        );

        // Transaksi 5: ShopeeFood Online (Linked to Saved AI Import)
        $txShopeeToday = $seedTransaction(
            sprintf('CIO-%s-%04d', $todayCode, 5),
            $today->copy()->setTime(13, 10, 0),
            'shopeefood',
            'completed',
            'other',
            ['Soto Betawi Daging Sapi Kuah Santan' => 1, 'Es Teh Manis Jumbo' => 1],
            null,
            0,
            8800,
            sprintf('SPF-%s-0551', $todayCode)
        );

        // 10. Sample AI Imports
        $aiDir = storage_path('app/private/ai-imports/' . $today->format('Y') . '/' . $today->format('m'));
        if (! File::exists($aiDir)) {
            File::makeDirectory($aiDir, 0755, true, true);
        }

        // Minimal valid 1x1 JPEG dummy image binary
        $dummyJpg = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
        
        $gfImagePath = $aiDir . '/sample_gofood_' . $todayCode . '.jpg';
        $spfImagePath = $aiDir . '/sample_shopeefood_' . $todayCode . '.jpg';
        File::put($gfImagePath, $dummyJpg);
        File::put($spfImagePath, $dummyJpg);

        // AI Import 1: GoFood (Status: READY / Siap Review Kasir)
        AiImport::create([
            'user_id' => $cashier->id,
            'channel_selected' => 'auto',
            'channel_detected' => 'gofood',
            'image_path' => $gfImagePath,
            'image_hash' => hash('sha256', 'sample_gofood_' . $todayCode),
            'image_bytes' => strlen($dummyJpg),
            'status' => 'ready',
            'platform_order_id' => sprintf('GF-%s-0774', $todayCode),
            'extracted' => [
                'channel' => 'gofood',
                'order_id' => sprintf('GF-%s-0774', $todayCode),
                'items' => [
                    [
                        'name' => 'Ayam Kremes Sambal Bawang Pedas',
                        'qty' => 2,
                        'price' => 25000,
                        'matched_menu_id' => $menus['Ayam Goreng Kremes Sambal Bawang']->id,
                        'matched_menu_name' => 'Ayam Goreng Kremes Sambal Bawang',
                    ],
                    [
                        'name' => 'Es Teh Manis Jumbo',
                        'qty' => 2,
                        'price' => 6000,
                        'matched_menu_id' => $menus['Es Teh Manis Jumbo']->id,
                        'matched_menu_name' => 'Es Teh Manis Jumbo',
                    ],
                ],
                'subtotal' => 62000,
                'commission_percent' => 20,
                'commission_amount' => 12400,
                'net_amount' => 49600,
            ],
            'schema_version' => '1.0',
            'prompt_version' => '1.0',
            'model' => 'gemini-1.5-flash',
            'tokens_in' => 1240,
            'tokens_out' => 310,
            'latency_ms' => 1340,
            'created_at' => $today->copy()->setTime(13, 20, 0),
        ]);

        // AI Import 2: ShopeeFood (Status: SAVED / Sudah tersimpan ke Transaksi 5)
        AiImport::create([
            'user_id' => $cashier->id,
            'channel_selected' => 'shopeefood',
            'channel_detected' => 'shopeefood',
            'image_path' => $spfImagePath,
            'image_hash' => hash('sha256', 'sample_shopeefood_' . $todayCode),
            'image_bytes' => strlen($dummyJpg),
            'status' => 'saved',
            'platform_order_id' => sprintf('SPF-%s-0551', $todayCode),
            'extracted' => [
                'channel' => 'shopeefood',
                'order_id' => sprintf('SPF-%s-0551', $todayCode),
                'items' => [
                    [
                        'name' => 'Soto Betawi Daging Sapi Kuah Santan',
                        'qty' => 1,
                        'price' => 38000,
                        'matched_menu_id' => $menus['Soto Betawi Daging Sapi Kuah Santan']->id,
                        'matched_menu_name' => 'Soto Betawi Daging Sapi Kuah Santan',
                    ],
                    [
                        'name' => 'Es Teh Manis Jumbo',
                        'qty' => 1,
                        'price' => 6000,
                        'matched_menu_id' => $menus['Es Teh Manis Jumbo']->id,
                        'matched_menu_name' => 'Es Teh Manis Jumbo',
                    ],
                ],
                'subtotal' => 44000,
                'commission_percent' => 20,
                'commission_amount' => 8800,
                'net_amount' => 35200,
            ],
            'schema_version' => '1.0',
            'prompt_version' => '1.0',
            'model' => 'gemini-1.5-flash',
            'tokens_in' => 1180,
            'tokens_out' => 295,
            'latency_ms' => 1210,
            'transaction_id' => $txShopeeToday->id,
            'reviewed_by' => $cashier->id,
            'reviewed_at' => $today->copy()->setTime(13, 10, 0),
            'created_at' => $today->copy()->setTime(13, 05, 0),
        ]);

        // Link transaction to this ai_import
        $txShopeeToday->ai_import_id = 2;
        $txShopeeToday->save();
    }
}
