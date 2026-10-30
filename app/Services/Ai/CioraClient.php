<?php

namespace App\Services\Ai;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CioraClient
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $protocol;
    protected string $model;
    protected int $timeout;
    protected int $maxRetries;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('ai.base_url', 'https://ciora.id/v1'), '/');
        $this->apiKey = config('ai.api_key', '');
        $this->protocol = config('ai.protocol', 'anthropic');
        $this->model = config('ai.vision_model', 'claude-3-5-sonnet-20241022');
        $this->timeout = config('ai.timeout', 60);
        $this->maxRetries = config('ai.max_retries', 2);
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Send screenshot to vision model and receive JSON string output.
     */
    public function extractFromImage(string $imagePath): array
    {
        if (! file_exists($imagePath)) {
            throw new Exception("File gambar tidak ditemukan.", 404);
        }

        $imageBytes = filesize($imagePath);
        $mime = mime_content_type($imagePath);
        $base64 = base64_encode(file_get_contents($imagePath));

        // If no API key configured, use intelligent mock extractor based on filename or fallback
        if (! $this->isConfigured()) {
            return $this->mockExtraction($imagePath);
        }

        $startTime = microtime(true);

        try {
            if ($this->protocol === 'openai') {
                $response = $this->callOpenAiProtocol($base64, $mime);
            } else {
                $response = $this->callAnthropicProtocol($base64, $mime);
            }

            $latency = (int) round((microtime(true) - $startTime) * 1000);
            $response['latency_ms'] = $latency;
            $response['model'] = $this->model;
            return $response;
        } catch (\Throwable $e) {
            Log::error('Ciora vision API error: ' . $e->getMessage());
            throw new Exception("Layanan AI Vision sedang tidak dapat dihubungi: " . $e->getMessage(), 503);
        }
    }

    protected function callAnthropicProtocol(string $base64, string $mime): array
    {
        $prompt = "Kamu mengekstrak data pesanan makanan dari screenshot GoFood atau ShopeeFood. Balas HANYA JSON murni tanpa markdown/backticks. Skema: {\"platform\": \"gofood|shopeefood|unknown\", \"platform_order_id\": string|null, \"ordered_at\": string|null, \"items\": [{\"source_name\": string, \"quantity\": int, \"visible_unit_price\": int, \"visible_line_total\": int, \"notes\": string|null, \"unreadable_fields\": []}], \"visible_subtotal\": int, \"visible_discount\": int, \"visible_order_total\": int, \"visible_other_fees\": int, \"unreadable_fields\": []}. DILARANG mengekstrak nama, telepon, atau alamat pelanggan.";

        $payload = [
            'model' => $this->model,
            'max_tokens' => 2048,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $mime,
                                'data' => $base64,
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => $prompt,
                        ],
                    ],
                ],
            ],
        ];

        $res = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])
        ->timeout($this->timeout)
        ->retry($this->maxRetries, 1000)
        ->post("{$this->baseUrl}/messages", $payload);

        if (! $res->successful()) {
            throw new Exception("HTTP {$res->status()}: {$res->body()}");
        }

        $body = $res->json();
        $text = $body['content'][0]['text'] ?? '';
        $tokensIn = $body['usage']['input_tokens'] ?? null;
        $tokensOut = $body['usage']['output_tokens'] ?? null;

        return [
            'raw_json' => $this->cleanJsonString($text),
            'tokens_in' => $tokensIn,
            'tokens_out' => $tokensOut,
        ];
    }

    protected function callOpenAiProtocol(string $base64, string $mime): array
    {
        $prompt = 'Ekstrak data pesanan GoFood/ShopeeFood dari gambar. Jangan sertakan teks penjelasan atau penalaran panjang, balas HANYA format JSON murni. Skema: {"platform": "gofood|shopeefood|unknown", "platform_order_id": string|null, "ordered_at": string|null, "items": [{"source_name": string, "quantity": int, "visible_unit_price": int, "visible_line_total": int, "notes": string|null, "unreadable_fields": []}], "visible_subtotal": int, "visible_discount": int, "visible_order_total": int, "visible_other_fees": int, "unreadable_fields": []}. DILARANG mengekstrak data pribadi pelanggan seperti nama penerima, nomor HP, atau alamat rumah.';

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $prompt],
                        [
                            'type' => 'image_url',
                            'image_url' => ['url' => "data:{$mime};base64,{$base64}"],
                        ],
                    ],
                ],
            ],
            'max_tokens' => 4096,
        ];

        $res = Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->retry($this->maxRetries, 1000)
            ->post("{$this->baseUrl}/chat/completions", $payload);

        if (! $res->successful()) {
            throw new Exception("HTTP {$res->status()}: {$res->body()}");
        }

        $body = $res->json();
        $text = $body['choices'][0]['message']['content'] ?? '';
        $tokensIn = $body['usage']['prompt_tokens'] ?? null;
        $tokensOut = $body['usage']['completion_tokens'] ?? null;

        return [
            'raw_json' => $this->cleanJsonString($text),
            'tokens_in' => $tokensIn,
            'tokens_out' => $tokensOut,
        ];
    }

    protected function cleanJsonString(string $text): string
    {
        $text = trim($text);
        if (str_starts_with($text, '```json')) {
            $text = substr($text, 7);
        } elseif (str_starts_with($text, '```')) {
            $text = substr($text, 3);
        }
        if (str_ends_with($text, '```')) {
            $text = substr($text, 0, -3);
        }
        $text = trim($text);

        // Extract valid JSON block between first '{' and last '}'
        $firstBrace = strpos($text, '{');
        $lastBrace = strrpos($text, '}');
        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $text = substr($text, $firstBrace, $lastBrace - $firstBrace + 1);
        }

        return trim($text);
    }

    /**
     * Fallback mock simulation for development and testing without paid API keys.
     */
    protected function mockExtraction(string $imagePath): array
    {
        // Safe mock sample that respects all PRD rules
        $filename = basename($imagePath);
        $orderId = 'GF-' . substr(md5($filename), 0, 8);

        $mockData = [
            'platform' => 'gofood',
            'platform_order_id' => $orderId,
            'ordered_at' => now()->toIso8601String(),
            'items' => [
                [
                    'source_name' => 'Nasi Goreng Spesial',
                    'quantity' => 2,
                    'visible_unit_price' => 25000,
                    'visible_line_total' => 50000,
                    'notes' => 'Pedas sedang',
                    'unreadable_fields' => [],
                ],
                [
                    'source_name' => 'Es Teh Manis',
                    'quantity' => 2,
                    'visible_unit_price' => 6000,
                    'visible_line_total' => 12000,
                    'notes' => null,
                    'unreadable_fields' => [],
                ],
            ],
            'visible_subtotal' => 62000,
            'visible_discount' => 5000,
            'visible_order_total' => 57000,
            'visible_other_fees' => 0,
            'unreadable_fields' => [],
        ];

        return [
            'raw_json' => json_encode($mockData),
            'tokens_in' => 450,
            'tokens_out' => 180,
            'latency_ms' => 420,
            'model' => 'mock-ciora-vision-v1',
        ];
    }
}
