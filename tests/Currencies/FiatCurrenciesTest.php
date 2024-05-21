<?php

declare(strict_types=1);

namespace Laxity7\Money\Test\Currencies;

use Laxity7\Money\Currencies\FiatCurrencies;
use Laxity7\Money\Currency;
use Laxity7\Money\Test\Stubs\MoneyConfigStub;
use PHPUnit\Framework\TestCase;

final class FiatCurrenciesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        MoneyConfigStub::configure();
    }

    public function testContains(): void
    {
        $currencies = new FiatCurrencies();

        $this->assertTrue($currencies->contains(new Currency('PHP')));

        $this->assertFalse($currencies->contains(new Currency('XXX')));
    }

    public function testGetDecimalCountReturns(): void
    {
        $currencies = new FiatCurrencies();

        $this->assertEquals(2, $currencies->getDecimalCount(new Currency('USD')));

        // This is a custom currency, so it should return 2
        $this->assertEquals(2, $currencies->getDecimalCount(new Currency('XXX')));
    }
}
