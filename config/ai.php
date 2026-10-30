<?php

return [
    'base_url' => env('CIORA_BASE_URL') ?: 'https://ciora.id/v1',
    'api_key' => env('CIORA_API_KEY', ''),
    'protocol' => env('CIORA_API_PROTOCOL') ?: 'openai', // openai | anthropic
    'vision_model' => env('CIORA_VISION_MODEL') ?: 'ciora-vision-free',
    'timeout' => (int) env('CIORA_TIMEOUT', 60),
    'max_retries' => (int) env('CIORA_MAX_RETRIES', 2),

    'max_file_mb' => (int) env('AI_IMPORT_MAX_FILE_MB', 8),
    'monthly_quota' => (int) env('AI_MONTHLY_QUOTA', 300),
    'retention_days' => (int) env('AI_SCREENSHOT_RETENTION_DAYS', 30),
    'delete_after_save' => filter_var(env('AI_DELETE_SCREENSHOT_AFTER_SAVE', false), FILTER_VALIDATE_BOOLEAN),
];
