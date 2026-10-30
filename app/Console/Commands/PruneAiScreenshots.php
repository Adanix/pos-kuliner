<?php

namespace App\Console\Commands;

use App\Models\AiImport;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PruneAiScreenshots extends Command
{
    protected $signature = 'ai:prune-screenshots {--days=}';
    protected $description = 'Bersihkan screenshot AI import yang telah melewati batas retensi privasi';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('ai.retention_days', 30));
        $cutoff = Carbon::now()->subDays($days);

        $expiredImports = AiImport::where('created_at', '<', $cutoff)->get();
        $prunedCount = 0;

        foreach ($expiredImports as $import) {
            if ($import->image_path && file_exists($import->image_path)) {
                @unlink($import->image_path);
                $import->image_path = '[PRUNED_PRIVACY_RETENTION]';
                $import->save();
                $prunedCount++;
            }
        }

        $this->info("Berhasil membersihkan {$prunedCount} file screenshot AI yang berusia > {$days} hari.");
        return Command::SUCCESS;
    }
}
