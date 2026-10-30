<?php

namespace App\Services;

use App\Enums\Channel;
use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use App\Models\CashSession;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\NumberSequence;
use App\Models\Outlet;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionItemIngredient;
use App\Support\Money;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesService
{
    public function __construct(
        protected StockService $stockService,
        protected HppService $hppService
    ) {}

    /**
     * Generate sequential transaction number: CIO-YYMMDD-NNNN with lock & retry.
     */
    public function generateTransactionNumber(): string
    {
        $now = Carbon::now('Asia/Jakarta');
        $period = $now->format('ymd'); // YYMMDD
        $scope = 'transaction';

        $seq = NumberSequence::where('scope', $scope)
            ->where('period', $period)
            ->lockForUpdate()
            ->first();

        if (! $seq) {
            $seq = NumberSequence::create([
                'scope' => $scope,
                'period' => $period,
                'last_value' => 0,
            ]);
        }

        $nextVal = $seq->last_value + 1;
        $seq->last_value = $nextVal;
        $seq->save();

        return sprintf('CIO-%s-%04d', $period, $nextVal);
    }

    /**
     * Create & store a complete transaction atomically.
     */
    public function storeTransaction(array $data, ?int $userId = null): Transaction
    {
        // 1. Idempotency Check
        if (! empty($data['idempotency_key'])) {
            $existing = Transaction::where('idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                return $existing;
            }
        }

        // 2. Validate Outlet & Settings
        $outlet = Outlet::first();
        $requireOpenShift = $outlet?->require_open_shift ?? true;
        $allowNegativeStock = $outlet?->allow_negative_stock ?? false;

        $channel = Channel::tryFrom($data['channel'] ?? 'pos') ?? Channel::POS;
        $paymentMethod = PaymentMethod::tryFrom($data['payment_method'] ?? 'cash') ?? PaymentMethod::CASH;

        // Verify active shift if required (for POS channel)
        $activeSession = null;
        if ($channel === Channel::POS && $requireOpenShift) {
            $activeSession = CashSession::where('status', 'open')->latest('opened_at')->first();
            if (! $activeSession) {
                throw ValidationException::withMessages([
                    'shift' => 'Buka kasir (shift) terlebih dahulu sebelum melakukan transaksi.',
                ]);
            }
        } elseif ($channel === Channel::POS) {
            $activeSession = CashSession::where('status', 'open')->latest('opened_at')->first();
        }

        return DB::transaction(function () use ($data, $userId, $outlet, $allowNegativeStock, $channel, $paymentMethod, $activeSession) {
            $itemsData = $data['items'] ?? [];
            if (empty($itemsData)) {
                throw new Exception("Transaksi harus memiliki minimal 1 item menu.");
            }

            // Extract menu IDs and lock recipes & ingredients
            $menuIds = array_column($itemsData, 'menu_id');
            $menus = Menu::with(['recipe.items.ingredient'])->whereIn('id', $menuIds)->get()->keyBy('id');

            // Collect required ingredients
            $requiredIngredients = [];
            foreach ($itemsData as $row) {
                $menu = $menus->get($row['menu_id']);
                if (! $menu || ! $menu->is_active) {
                    throw new Exception("Menu ID {$row['menu_id']} tidak ditemukan atau nonaktif.");
                }

                $qty = (int) ($row['qty'] ?? 1);
                if ($qty < 1 || $qty > 99) {
                    throw new Exception("Kuantitas item harus antara 1 sampai 99.");
                }

                if ($menu->recipe && $menu->recipe->items->isNotEmpty()) {
                    foreach ($menu->recipe->items as $rItem) {
                        $ingId = $rItem->ingredient_id;
                        $needed = (float) $rItem->qty_base * $qty;
                        $requiredIngredients[$ingId] = ($requiredIngredients[$ingId] ?? 0) + $needed;
                    }
                }
            }

            // Lock ingredients in ascending ID order to prevent deadlocks
            if (! empty($requiredIngredients)) {
                $ingIds = array_keys($requiredIngredients);
                sort($ingIds);
                $lockedIngredients = Ingredient::whereIn('id', $ingIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $shortages = [];
                foreach ($requiredIngredients as $ingId => $totalNeeded) {
                    $ing = $lockedIngredients->get($ingId);
                    if ($ing) {
                        $currentStock = (float) $ing->stock_qty;
                        if (! $allowNegativeStock && $currentStock < $totalNeeded) {
                            $shortages[] = [
                                'ingredient_id' => $ing->id,
                                'name' => $ing->name,
                                'current_stock' => $currentStock,
                                'required_stock' => $totalNeeded,
                                'unit' => $ing->base_unit,
                            ];
                        }
                    }
                }

                if (! empty($shortages)) {
                    throw ValidationException::withMessages([
                        'stock' => 'Stok bahan baku tidak mencukupi untuk pesanan ini.',
                        'shortages' => $shortages,
                    ]);
                }
            }

            // Calculate amounts in PHP strictly
            $subtotal = 0;
            $preparedItems = [];

            foreach ($itemsData as $row) {
                $menu = $menus->get($row['menu_id']);
                $qty = (int) $row['qty'];
                $unitPrice = (int) $menu->price; // Snapshot master price
                $lineDiscount = (int) ($row['line_discount'] ?? 0);
                $lineTotal = max(0, ($qty * $unitPrice) - $lineDiscount);
                $subtotal += $lineTotal;

                // Calculate HPP
                $hppResult = $this->hppService->calculateForMenu($menu);

                $preparedItems[] = [
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'line_discount' => $lineDiscount,
                    'line_total' => $lineTotal,
                    'unit_hpp' => $hppResult['unit_hpp'],
                    'hpp_complete' => $hppResult['hpp_complete'],
                    'note' => $row['note'] ?? null,
                    'ingredients' => $hppResult['ingredients'],
                ];
            }

            // Discounts
            $discountType = $data['discount_type'] ?? 'none';
            $discountValue = (int) ($data['discount_value'] ?? 0);
            $discountAmount = 0;

            if ($discountType === 'fixed') {
                $discountAmount = min($discountValue, $subtotal);
            } elseif ($discountType === 'percent') {
                $p = min(100, max(0, $discountValue));
                $discountAmount = (int) round(($subtotal * $p) / 100);
            }

            $base = max(0, $subtotal - $discountAmount);

            // Service & Tax
            $servicePercent = (float) ($outlet?->service_charge_percent ?? 0);
            $taxPercent = (float) ($outlet?->tax_percent ?? 0);

            $serviceCharge = (int) round(($base * $servicePercent) / 100);
            $taxAmount = (int) round((($base + $serviceCharge) * $taxPercent) / 100);

            $rawTotal = $base + $serviceCharge + $taxAmount;

            // Rounding (only for cash if nearest_100 is configured)
            $roundingAmount = 0;
            if ($paymentMethod === PaymentMethod::CASH && ($outlet?->rounding_mode === 'nearest_100')) {
                $rounded = Money::roundNearest100($rawTotal);
                $roundingAmount = $rounded - $rawTotal;
            }

            $total = $rawTotal + $roundingAmount;

            // Amount paid & change
            $amountPaid = (int) ($data['amount_paid'] ?? $total);
            if ($paymentMethod === PaymentMethod::CASH) {
                if ($amountPaid < $total) {
                    throw ValidationException::withMessages([
                        'amount_paid' => 'Uang yang diterima kurang dari total tagihan (' . Money::format($total) . ').',
                    ]);
                }
                $changeAmount = $amountPaid - $total;
            } else {
                $amountPaid = $total;
                $changeAmount = 0;
            }

            // Platform fee / commission
            $platformFee = 0;
            if ($channel === Channel::GOFOOD) {
                $commPercent = (float) Setting::get('report.commission.gofood', 0);
                $platformFee = (int) round(($base * $commPercent) / 100);
            } elseif ($channel === Channel::SHOPEEFOOD) {
                $commPercent = (float) Setting::get('report.commission.shopeefood', 0);
                $platformFee = (int) round(($base * $commPercent) / 100);
            }

            // Generate unique transaction number
            $number = $this->generateTransactionNumber();

            // Create Transaction record
            $transaction = Transaction::create([
                'number' => $number,
                'outlet_id' => $outlet?->id,
                'channel' => $channel,
                'status' => TransactionStatus::COMPLETED,
                'occurred_at' => Carbon::now('Asia/Jakarta'),
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'service_charge' => $serviceCharge,
                'tax_amount' => $taxAmount,
                'rounding_amount' => $roundingAmount,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'amount_paid' => $amountPaid,
                'change_amount' => $changeAmount,
                'platform_fee' => $platformFee,
                'platform_order_id' => $data['platform_order_id'] ?? null,
                'ai_import_id' => $data['ai_import_id'] ?? null,
                'cash_session_id' => $activeSession?->id,
                'user_id' => $userId,
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'note' => $data['note'] ?? null,
            ]);

            // Save items & snapshot ingredients & consume stock
            foreach ($preparedItems as $pItem) {
                $tItem = TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'menu_id' => $pItem['menu_id'],
                    'menu_name' => $pItem['menu_name'],
                    'qty' => $pItem['qty'],
                    'unit_price' => $pItem['unit_price'],
                    'line_discount' => $pItem['line_discount'],
                    'line_total' => $pItem['line_total'],
                    'unit_hpp' => $pItem['unit_hpp'],
                    'hpp_complete' => $pItem['hpp_complete'],
                    'note' => $pItem['note'],
                ]);

                // Record item ingredient snapshots
                foreach ($pItem['ingredients'] as $ingDetail) {
                    $totalQtyBase = $ingDetail['qty_base'] * $pItem['qty'];
                    $totalCost = (int) round($totalQtyBase * $ingDetail['unit_cost']);

                    TransactionItemIngredient::create([
                        'transaction_item_id' => $tItem->id,
                        'ingredient_id' => $ingDetail['ingredient_id'],
                        'ingredient_name' => $ingDetail['ingredient_name'],
                        'qty_base' => $totalQtyBase,
                        'unit_cost' => $ingDetail['unit_cost'],
                        'cost_total' => $totalCost,
                    ]);
                }
            }

            // Consume ingredient stocks in bulk via StockService
            foreach ($requiredIngredients as $ingId => $totalQty) {
                $ing = Ingredient::find($ingId);
                if ($ing) {
                    $this->stockService->consume($ing, $totalQty, 'transaction', $transaction->id, $userId);
                }
            }

            return $transaction;
        });
    }

    /**
     * Void a transaction (Owner only).
     */
    public function voidTransaction(Transaction $transaction, string $reason, bool $returnStock, ?int $userId = null): Transaction
    {
        if (strlen(trim($reason)) < 10) {
            throw new Exception("Alasan pembatalan (void) wajib diisi minimal 10 karakter.");
        }

        if ($transaction->isVoided()) {
            throw new Exception("Transaksi ini sudah berstatus void sebelumnya.");
        }

        return DB::transaction(function () use ($transaction, $reason, $returnStock, $userId) {
            $locked = Transaction::where('id', $transaction->id)->lockForUpdate()->firstOrFail();

            $locked->status = TransactionStatus::VOIDED;
            $locked->voided_at = Carbon::now('Asia/Jakarta');
            $locked->voided_by = $userId;
            $locked->void_reason = $reason;
            $locked->save();

            // Reverse stock if operator requested
            if ($returnStock) {
                // Read all item ingredients used
                $items = $locked->items()->with('itemIngredients.ingredient')->get();
                $ingredientReversals = [];

                foreach ($items as $item) {
                    foreach ($item->itemIngredients as $ingRow) {
                        $ingId = $ingRow->ingredient_id;
                        if ($ingId) {
                            $ingredientReversals[$ingId] = ($ingredientReversals[$ingId] ?? 0) + (float) $ingRow->qty_base;
                        }
                    }
                }

                foreach ($ingredientReversals as $ingId => $qty) {
                    $ingredient = Ingredient::find($ingId);
                    if ($ingredient) {
                        $this->stockService->reverse($ingredient, $qty, $locked->id, "Pengembalian void: {$reason}", $userId);
                    }
                }
            }

            return $locked;
        });
    }
}
