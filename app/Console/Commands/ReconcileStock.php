<?php

namespace App\Console\Commands;

use App\Models\Ingredient;
use App\Services\StockService;
use Illuminate\Console\Command;

class ReconcileStock extends Command
{
    protected $signature = 'stock:reconcile {--fix : Perbaiki perbedaan saldo otomatis sesuai ledger}';
    protected $description = 'Bandingkan dan rekonsiliasi saldo stok bahan baku terhadap riwayat pergerakan ledger';

    public function handle(StockService $stockService): int
    {
        $fix = (bool) $this->option('fix');
        $ingredients = Ingredient::all();

        $this->info("=================================================");
        $this->info(" AUDIT REKONSILIASI STOK BAHAN BAKU (LEDGER)");
        $this->info("=================================================");

        $discrepancies = 0;

        foreach ($ingredients as $ing) {
            $result = $stockService->reconcile($ing, $fix);
            if (abs($result['difference']) > 0.0001) {
                $discrepancies++;
                $this->warn(sprintf(
                    "• [%s] %s | Cache: %s | Ledger: %s | Selisih: %s %s",
                    $ing->id,
                    $ing->name,
                    $result['cached_qty'],
                    $result['ledger_qty'],
                    $result['difference'],
                    $fix ? "-> DIPERBAIKI" : ""
                ));
            }
        }

        if ($discrepancies === 0) {
            $this->info("[✓] Semua saldo bahan baku cocok sempurna dengan ledger!");
        } else {
            $this->line("\nDitemukan {$discrepancies} bahan baku dengan selisih saldo.");
            if (! $fix) {
                $this->line("Gunakan opsi --fix untuk menyinkronkan saldo cache ke total ledger.");
            }
        }

        return Command::SUCCESS;
    }
}
