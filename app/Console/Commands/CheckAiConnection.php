<?php

namespace App\Console\Commands;

use App\Services\Ai\CioraClient;
use Illuminate\Console\Command;

class CheckAiConnection extends Command
{
    protected $signature = 'ai:check';
    protected $description = 'Uji konektivitas dan kesiapan vision model AI Ciora tanpa membocorkan API Key';

    public function handle(CioraClient $client): int
    {
        $this->info("=================================================");
        $this->info(" ADANIX CIORA AI VISION CONNECTIVITY CHECK");
        $this->info("=================================================");

        $baseUrl = config('ai.base_url');
        $protocol = config('ai.protocol');
        $model = config('ai.vision_model');
        $configured = $client->isConfigured();

        $this->line("• Base URL   : {$baseUrl}");
        $this->line("• Protocol   : {$protocol}");
        $this->line("• Model      : {$model}");
        $this->line("• API Key    : " . ($configured ? "[TERPASANG - REDACTED]" : "[BELUM DIATUR - SIMULASI/MOCK AKTIF]"));

        if (! $configured) {
            $this->warn("\n[!] Perhatian: CIORA_API_KEY belum disetel di .env.");
            $this->line("    Sistem beroperasi dalam mode aman: simulasi vision parser aktif.");
            $this->line("    Pengujian unit & fungsionalitas UI tetap dapat diuji secara penuh.");
            return Command::SUCCESS;
        }

        $this->line("\nMenguji permintaan vision dengan gambar uji...");
        $startTime = microtime(true);

        try {
            // Create a small 1x1 test gif image in temp
            $tmpFile = tempnam(sys_get_temp_dir(), 'ai_test') . '.gif';
            file_put_contents($tmpFile, base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'));

            $result = $client->extractFromImage($tmpFile);
            @unlink($tmpFile);

            $duration = round((microtime(true) - $startTime) * 1000);
            $this->info("[✓] Berhasil terhubung ke Ciora AI Vision! Latensi: {$duration} ms.");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("[X] Gagal terhubung: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
