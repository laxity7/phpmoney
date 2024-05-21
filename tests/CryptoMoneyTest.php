<?php

declare(strict_types=1);

namespace Laxity7\Money\Test;

use Laxity7\Money\CryptoCurrency;
use Laxity7\Money\CryptoMoney;
use Laxity7\Money\Currency;
use Laxity7\Money\Exceptions\UnacceptableCurrencyException;
use Laxity7\Money\FiatCurrency;
use Laxity7\Money\Test\Stubs\MoneyConfigStub;
use PHPUnit\Framework\TestCase;

class CryptoMoneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        MoneyConfigStub::configure();
    }

    public function testConstructCreatesInstanceWithCorrectAmountAndCurrency(): void
    {
        $amount = 100.0;
        $currency = new CryptoCurrency('BTC');

        $money = new CryptoMoney($amount, $currency);

        $this->assertEquals($amount, $money->getAmount());
    }

    public function testConstructCreatesInstanceWithCurrencyAsString(): void
    {
        $amount = 100.0;
        $currency = 'BTC';

        $money = new CryptoMoney($amount, $currency);

        $this->assertEquals($amount, $money->getAmount());
        $this->assertEquals($currency, $money->getCurrency());
    }

    public function testConstructThrowsExceptionWhenCurrencyAsStringIsNotFiatCurrency(): void
    {
        $this->expectException(UnacceptableCurrencyException::class);

        new CryptoMoney(100.0, 'USD');
    }

    public function testConstructThrowsExceptionWhenCurrencyAsCommonIsNotFiatCurrency(): void
    {
        $this->expectException(UnacceptableCurrencyException::class);

        new CryptoMoney(100.0, new Currency('USD'));
    }

    public function testConstructThrowsExceptionWhenCurrencyIsNotFiatCurrency(): void
    {
        $this->expectException(UnacceptableCurrencyException::class);

        new CryptoMoney(100.0, new FiatCurrency('USD'));
    }

    public function testGetAmount(): void
    {
        $btc = new CryptoCurrency('BTC');
        $money = new CryptoMoney(100.0, $btc);

        $this->assertSame('100', $money->getAmount());

        $money = new CryptoMoney(100.0999999, $btc);
        $this->assertSame('100.0999999', $money->getAmount());

        $money = new CryptoMoney(0.00000001, $btc);
        $this->assertSame('0.00000001', $money->getAmount());

        $money = new CryptoMoney(123, $btc);
        $this->assertSame('0.26973685', $money->divide(456)->getAmount());
    }
}
