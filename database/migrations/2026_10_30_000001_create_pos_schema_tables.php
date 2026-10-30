<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add columns to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('cashier')->after('email');
            $table->string('pin_hash')->nullable()->after('password');
            $table->boolean('is_active')->default(true)->after('pin_hash');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });

        // 2. Outlets
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('receipt_footer')->nullable();
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('service_charge_percent', 5, 2)->default(0);
            $table->string('rounding_mode', 20)->default('none'); // 'none', 'nearest_100'
            $table->boolean('require_open_shift')->default(true);
            $table->boolean('allow_negative_stock')->default(false);
            $table->timestamps();
        });

        // 3. Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        // 4. Menus
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price'); // BIGINT integer rupiah
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'category_id']);
            $table->index('name');
        });

        // 5. Ingredients (Bahan Baku)
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 50)->nullable()->unique();
            $table->string('base_unit', 20); // 'g', 'ml', 'pcs'
            $table->decimal('stock_qty', 14, 4)->default(0);
            $table->decimal('min_stock', 14, 4)->default(0);
            $table->decimal('avg_cost', 18, 6)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });

        // 6. Recipes
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->unique()->constrained('menus')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 7. Recipe Items
        Schema::create('recipe_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->decimal('qty_base', 14, 4); // quantity in base unit
            $table->timestamps();

            $table->unique(['recipe_id', 'ingredient_id']);
        });

        // 8. Cash Sessions (Shifts)
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->nullable()->constrained('outlets')->nullOnDelete();
            $table->foreignId('opened_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('opened_at');
            $table->unsignedBigInteger('opening_float'); // kas awal
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->bigInteger('counted_cash')->nullable(); // fisik laci
            $table->bigInteger('expected_cash')->nullable(); // sistem
            $table->bigInteger('difference')->nullable(); // selisih
            $table->text('note')->nullable();
            $table->string('status', 20)->default('open'); // 'open', 'closed'
            $table->timestamps();

            $table->index(['outlet_id', 'status']);
        });

        // 9. Stock Movements (Immutable Ledger)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->restrictOnDelete();
            $table->string('type', 30); // opening, purchase, usage, waste, adjustment, reversal
            $table->decimal('qty_delta', 14, 4); // signed delta
            $table->decimal('unit_cost', 18, 6)->nullable();
            $table->bigInteger('value_delta')->nullable();
            $table->string('ref_type', 50)->nullable(); // e.g. transaction, opname, purchase
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['ingredient_id', 'created_at']);
        });

        // 10. AI Imports
        Schema::create('ai_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('channel_selected', 20)->nullable();
            $table->string('channel_detected', 20)->nullable();
            $table->string('image_path');
            $table->char('image_hash', 64)->index();
            $table->unsignedInteger('image_bytes');
            $table->string('status', 30)->default('queued'); // queued, processing, needs_review, ready, saved, failed, duplicate
            $table->string('platform_order_id', 100)->nullable()->index();
            $table->json('extracted')->nullable();
            $table->string('schema_version', 20)->nullable();
            $table->string('prompt_version', 20)->nullable();
            $table->string('model', 100)->nullable();
            $table->unsignedInteger('tokens_in')->nullable();
            $table->unsignedInteger('tokens_out')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('duplicate_of_import_id')->nullable()->constrained('ai_imports')->nullOnDelete();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('retention_delete_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // 11. Transactions
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();
            $table->foreignId('outlet_id')->nullable()->constrained('outlets')->nullOnDelete();
            $table->string('channel', 20)->default('pos'); // pos, gofood, shopeefood
            $table->string('status', 20)->default('completed'); // completed, voided
            $table->timestamp('occurred_at');
            $table->unsignedBigInteger('subtotal');
            $table->string('discount_type', 20)->default('none'); // none, fixed, percent
            $table->unsignedBigInteger('discount_value')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('service_charge')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->bigInteger('rounding_amount')->default(0);
            $table->unsignedBigInteger('total');
            $table->string('payment_method', 20)->default('cash'); // cash, qris, transfer, other
            $table->unsignedBigInteger('amount_paid')->default(0);
            $table->bigInteger('change_amount')->default(0);
            $table->unsignedBigInteger('platform_fee')->default(0);
            $table->string('platform_order_id', 100)->nullable();
            $table->foreignId('ai_import_id')->nullable()->constrained('ai_imports')->nullOnDelete();
            $table->foreignId('cash_session_id')->nullable()->constrained('cash_sessions')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->text('note')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('void_reason')->nullable();
            $table->timestamps();

            $table->unique(['channel', 'platform_order_id']);
            $table->index(['occurred_at', 'channel', 'status']);
        });

        // Link ai_imports.transaction_id to transactions
        Schema::table('ai_imports', function (Blueprint $table) {
            $table->foreign('transaction_id')->references('id')->on('transactions')->nullOnDelete();
        });

        // 12. Transaction Items
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignId('menu_id')->nullable()->constrained('menus')->nullOnDelete();
            $table->string('menu_name', 150);
            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('line_discount')->default(0);
            $table->unsignedBigInteger('line_total');
            $table->unsignedBigInteger('unit_hpp')->nullable();
            $table->boolean('hpp_complete')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('transaction_id');
            $table->index('menu_id');
        });

        // 13. Transaction Item Ingredients
        Schema::create('transaction_item_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_item_id')->constrained('transaction_items')->cascadeOnDelete();
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients')->nullOnDelete();
            $table->string('ingredient_name', 150);
            $table->decimal('qty_base', 14, 4);
            $table->decimal('unit_cost', 18, 6)->default(0);
            $table->unsignedBigInteger('cost_total')->default(0);
            $table->timestamps();

            $table->index('transaction_item_id');
        });

        // 14. Menu Aliases
        Schema::create('menu_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->string('source_name', 150);
            $table->string('normalized_name', 150);
            $table->string('channel', 20)->nullable();
            $table->timestamps();

            $table->unique(['channel', 'normalized_name']);
        });

        // 15. Number Sequences
        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 50);
            $table->char('period', 6); // YYMMDD
            $table->unsignedInteger('last_value')->default(0);
            $table->timestamps();

            $table->unique(['scope', 'period']);
        });

        // 16. Settings
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->json('value');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        // 17. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50);
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('summary')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('number_sequences');
        Schema::dropIfExists('menu_aliases');
        Schema::dropIfExists('transaction_item_ingredients');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('ai_imports');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('cash_sessions');
        Schema::dropIfExists('recipe_items');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('ingredients');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('outlets');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'pin_hash', 'is_active', 'last_login_at']);
        });
    }
};
