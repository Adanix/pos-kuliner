<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_money_formatting(): void
    {
        $this->assertEquals('Rp 0', Money::format(0));
        $this->assertEquals('Rp 0', Money::format(null));
        $this->assertEquals('Rp 12.500', Money::format(12500));
        $this->assertEquals('Rp 1.500.000', Money::format(1500000));
        $this->assertEquals('-Rp 2.000', Money::format(-2000));
    }

    public function test_money_parsing(): void
    {
        $this->assertEquals(0, Money::parse(''));
        $this->assertEquals(0, Money::parse(null));
        $this->assertEquals(12500, Money::parse(12500));
        $this->assertEquals(12500, Money::parse('12500'));
        $this->assertEquals(12500, Money::parse('12.500'));
        $this->assertEquals(12500, Money::parse('Rp 12.500'));
        $this->assertEquals(250000, Money::parse('Rp. 250.000'));
    }

    public function test_money_round_nearest_100(): void
    {
        $this->assertEquals(12500, Money::roundNearest100(12500));
        $this->assertEquals(12500, Money::roundNearest100(12540));
        $this->assertEquals(12600, Money::roundNearest100(12550));
        $this->assertEquals(12600, Money::roundNearest100(12580));
    }
}
