<?php

declare(strict_types=1);

namespace Laxity7\Money\Test\Currencies;

use Laxity7\Money\Currencies\AggregateCurrencies;
use Laxity7\Money\Currency;
use Laxity7\Money\Exceptions\UnknownCurrencyException;
use Laxity7\Money\Test\Stubs\CryptoCurrenciesStub;
use Laxity7\Money\Test\Stubs\FiatCurrenciesStub;
use Laxity7\Money\Test\Stubs\MoneyConfigStub;
use PHPUnit\Framework\TestCase;

class AggregateCurrenciesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        MoneyConfigStub::configure();
    }

    public function testContainsReturnsTrue(): void
    {
        $currency = new Currency('USD');
        $currencies1 = new FiatCurrenciesStub();
        $currencies2 = new CryptoCurrenciesStub();

        $aggregateCurrencies = new AggregateCurrencies([$currencies1, $currencies2]);

        $this->assertTrue($aggregateCurrencies->contains($currency));

        $currency = new Currency('BTC');

        $this->assertTrue($aggregateCurrencies->contains($currency));
    }

    public function testContainsReturnsFalse(): void
    {
        $currency = new Currency('XXX');
        $currencies1 = new FiatCurrenciesStub();
        $currencies2 = new CryptoCurrenciesStub();

        $aggregateCurrencies = new AggregateCurrencies([$currencies1, $currencies2]);

        $this->assertFalse($aggregateCurrencies->contains($currency));
    }

    public function testGetDecimalCountReturns(): void
    {
        $currency = new Currency('USD');
        $currencies1 = new FiatCurrenciesStub();
        $currencies2 = new CryptoCurrenciesStub();

        $aggregateCurrencies = new AggregateCurrencies([$currencies1, $currencies2]);

        $this->assertEquals(2, $aggregateCurrencies->getDecimalCount($currency));

        $currency = new Currency('BTC');
        $this->assertEquals(8, $aggregateCurrencies->getDecimalCount($currency));
    }

    public function getDecimalCountThrowsExceptionWhenCurrencyIsInNoRepository(): void
    {
        $currency = new Currency('XXX');
        $currencies1 = new FiatCurrenciesStub();
        $currencies2 = new CryptoCurrenciesStub();

        $aggregateCurrencies = new AggregateCurrencies([$currencies1, $currencies2]);

        $this->expectException(UnknownCurrencyException::class);

        $aggregateCurrencies->getDecimalCount($currency);
    }

    public function testGetIteratorReturns(): void
    {
        $currencies1 = new FiatCurrenciesStub();
        $currencies2 = new CryptoCurrenciesStub();

        $aggregateCurrencies = new AggregateCurrencies([$currencies1, $currencies2]);

        $this->assertCount(8, $aggregateCurrencies);
        $this->assertContainsOnlyInstancesOf(Currency::class, $aggregateCurrencies->getIterator());
    }
}
