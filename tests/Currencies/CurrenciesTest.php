<?php

declare(strict_types=1);

namespace Laxity7\Money\Test\Currencies;

use Laxity7\Money\Currencies\Currencies;
use Laxity7\Money\Currency;
use Laxity7\Money\Exceptions\UnknownCurrencyException;
use Laxity7\Money\Test\Stubs\MoneyConfigStub;
use PHPUnit\Framework\TestCase;

final class CurrenciesTest extends TestCase
{
    private Currencies $currencies;

    protected function setUp(): void
    {
        parent::setUp();
        $this->currencies = new Currencies([
            ['name' => 'US Dollar', 'symbol' => 'USD', 'scale' => 2],
            ['name' => 'Euro', 'symbol' => 'EUR', 'scale' => 2],
            ['name' => 'Japanese Yen', 'symbol' => 'JPY', 'scale' => 2],
            ['name' => 'British Pound Sterling', 'symbol' => 'GBP', 'scale' => 2],
        ]);
        MoneyConfigStub::configure($this->currencies);
    }

    public function testContains(): void
    {
        self::assertTrue($this->currencies->contains(new Currency('USD')));

        self::assertFalse($this->currencies->contains(new Currency('XXX')));
    }

    public function testGetDecimalCountReturns(): void
    {
        self::assertEquals(2, $this->currencies->getDecimalCount(new Currency('USD')));

        $this->expectException(UnknownCurrencyException::class);
        $this->currencies->getDecimalCount(new Currency('XXX'));
    }

    public function testGetIterator(): void
    {
        $iterator = $this->currencies->getIterator();
        self::assertCount(4, $iterator);
    }

    public function testGetName(): void
    {
        self::assertEquals('US Dollar', $this->currencies->getName(new Currency('USD')));
    }
}
