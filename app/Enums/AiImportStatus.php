<?php

namespace App\Enums;

enum AiImportStatus: string
{
    case QUEUED = 'queued';
    case PROCESSING = 'processing';
    case NEEDS_REVIEW = 'needs_review';
    case READY = 'ready';
    case SAVED = 'saved';
    case FAILED = 'failed';
    case DUPLICATE = 'duplicate';

    public function label(): string
    {
        return match($this) {
            self::QUEUED => 'Dalam Antrean',
            self::PROCESSING => 'Sedang Diproses AI',
            self::NEEDS_REVIEW => 'Perlu Ditinjau',
            self::READY => 'Siap Simpan',
            self::SAVED => 'Tersimpan',
            self::FAILED => 'Gagal Diekstrak',
            self::DUPLICATE => 'Duplikat Terdeteksi',
        };
    }
}
