<?php

namespace Tests\Unit;

use App\Services\Ai\MenuMatcher;
use PHPUnit\Framework\TestCase;

class MenuMatcherTest extends TestCase
{
    public function test_normalization_removes_promos_and_punctuation(): void
    {
        $matcher = new MenuMatcher();

        $this->assertEquals('nasi goreng spesial', $matcher->normalize('PROMO Nasi Goreng Spesial!'));
        $this->assertEquals('es teh manis', $matcher->normalize('Diskon Es Teh Manis 5k'));
        $this->assertEquals('ayam bakar madu', $matcher->normalize('Paket Hemat Ayam Bakar Madu Rp 25.000'));
    }
}
