<?php

declare(strict_types=1);

namespace Laxity7\Money\Test;

use Laxity7\Money\Currency;
use Laxity7\Money\Exceptions\UnacceptableCurrencyException;
use Laxity7\Money\Exceptions\UnknownCurrencyException;
use Laxity7\Money\Test\Stubs\MoneyConfigStub;
use PHPUnit\Framework\TestCase;

final class CurrencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        MoneyConfigStub::configure(checkAcceptable: true);
    }

    public function testIsAcceptable(): void
    {
        $currency = new Currency('USD');
        self::assertTrue($currency->isAcceptable());

        $currency = new Currency('BTC');
        self::assertTrue($currency->isAcceptable());
    }

    public function testGetDecimalCount(): void
    {
        $currency = new Currency('USD');
        self::assertEquals(2, $currency->getDecimalCount());

        $currency = new Currency('BTC');
        self::assertEquals(8, $currency->getDecimalCount());

        $this->expectException(UnacceptableCurrencyException::class);
        $currency = new Currency('123');
        $currency->getDecimalCount();
    }

    public function testEqualsReturnsTrueWhenCurrencyIsTheSame(): void
    {
        $currency1 = new Currency('USD');
        $currency2 = new Currency('USD');

        self::assertTrue($currency1->equals($currency2));
    }

    public function testequalsReturnsFalseWhenCurrencyIsNotTheSame(): void
    {
        $currency1 = new Currency('USD');
        $currency2 = new Currency('EUR');

        self::assertFalse($currency1->equals($currency2));
    }

    public function testtoStringReturnsCurrencyCode(): void
    {
        $currency = new Currency('USD');

        self::assertEquals('USD', $currency->__toString());
        self::assertEquals('USD', $currency);
    }

    public function testjsonSerializeReturnsCurrencyCode(): void
    {
        $currency = new Currency('USD');

        self::assertEquals('USD', $currency->jsonSerialize());
    }

    public function testConstructThrowsExceptionWhenCurrencyCodeIsEmpty(): void
    {
        $this->expectException(UnknownCurrencyException::class);
        /** @phpstan-ignore-next-line */
        $currency = new Currency('');
    }

    public function testConstructThrowsExceptionWhenCurrencyCodeIsWrong(): void
    {
        $this->expectException(UnacceptableCurrencyException::class);
        $currency = new Currency('x');
    }

    public function testGetName(): void
    {
        $currency = new Currency('USD');
        self::assertEquals('US Dollar', $currency->getName());
    }
}
