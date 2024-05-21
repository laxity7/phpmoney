<?php

declare(strict_types=1);

namespace Laxity7\Money\Test;

use Laxity7\Money\Currency;
use Laxity7\Money\Exceptions\UnknownCurrencyException;
use Laxity7\Money\Test\Stubs\MoneyConfigStub;
use PHPUnit\Framework\TestCase;

final class CurrencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        MoneyConfigStub::configure();
    }

    public function testIsAcceptable(): void
    {
        $currency = new Currency('USD');
        $this->assertTrue($currency->isAcceptable());

        $currency = new Currency('BTC');
        $this->assertTrue($currency->isAcceptable());
    }

    public function testGetDecimalCount(): void
    {
        $currency = new Currency('USD');
        $this->assertEquals(2, $currency->getDecimalCount());

        $currency = new Currency('BTC');
        $this->assertEquals(8, $currency->getDecimalCount());

        $this->expectException(UnknownCurrencyException::class);
        $currency = new Currency('123');
        $currency->getDecimalCount();
    }

    public function testEqualsReturnsTrueWhenCurrencyIsTheSame(): void
    {
        $currency1 = new Currency('USD');
        $currency2 = new Currency('USD');

        $this->assertTrue($currency1->equals($currency2));
    }

    public function testequalsReturnsFalseWhenCurrencyIsNotTheSame(): void
    {
        $currency1 = new Currency('USD');
        $currency2 = new Currency('EUR');

        $this->assertFalse($currency1->equals($currency2));
    }

    public function testtoStringReturnsCurrencyCode(): void
    {
        $currency = new Currency('USD');

        $this->assertEquals('USD', $currency->__toString());
        $this->assertEquals('USD', $currency);
    }

    public function testjsonSerializeReturnsCurrencyCode(): void
    {
        $currency = new Currency('USD');

        $this->assertEquals('USD', $currency->jsonSerialize());
    }
}
