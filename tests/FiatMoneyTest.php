<?php

declare(strict_types=1);

namespace Laxity7\Money\Test;

use Laxity7\Money\CryptoCurrency;
use Laxity7\Money\Currency;
use Laxity7\Money\Exceptions\UnacceptableCurrencyException;
use Laxity7\Money\FiatCurrency;
use Laxity7\Money\FiatMoney;
use Laxity7\Money\Test\Stubs\MoneyConfigStub;
use PHPUnit\Framework\TestCase;

final class FiatMoneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        MoneyConfigStub::configure();
    }

    public function testConstructCreatesInstanceWithCorrectAmountAndCurrency(): void
    {
        $amount = 100.0;
        $currency = new FiatCurrency('USD');

        $fiatMoney = new FiatMoney($amount, $currency);

        $this->assertEquals($amount, $fiatMoney->getAmount());
    }

    public function testConstructCreatesInstanceWithCurrencyAsString(): void
    {
        $amount = 100.0;
        $currency = 'USD';

        $fiatMoney = new FiatMoney($amount, $currency);

        $this->assertEquals($amount, $fiatMoney->getAmount());
        $this->assertEquals($currency, $fiatMoney->getCurrency());
    }

    public function testConstructThrowsExceptionWhenCurrencyAsStringIsNotFiatCurrency(): void
    {
        $this->expectException(UnacceptableCurrencyException::class);

        new FiatMoney(100.0, 'BTC');
    }

    public function testConstructThrowsExceptionWhenCurrencyAsCommonIsNotFiatCurrency(): void
    {
        $this->expectException(UnacceptableCurrencyException::class);

        new FiatMoney(100.0, new Currency('BTC'));
    }

    public function testConstructThrowsExceptionWhenCurrencyIsNotFiatCurrency(): void
    {
        $this->expectException(UnacceptableCurrencyException::class);

        new FiatMoney(100.0, new CryptoCurrency('BTC'));
    }

    public function testGetAmount(): void
    {
        $usd = new FiatCurrency('USD');
        $fiatMoney = new FiatMoney(100.0, $usd);

        $this->assertSame('100', $fiatMoney->getAmount());

        $fiatMoney = new FiatMoney(100.0999999, $usd);
        $this->assertSame('100.1', $fiatMoney->getAmount());

        $fiatMoney = new FiatMoney(0.00000001, $usd);
        $this->assertSame('0', $fiatMoney->getAmount());

        $fiatMoney = new FiatMoney(123, $usd);
        $this->assertSame('0.27', $fiatMoney->divide(456)->getAmount());
    }
}
