<?php

declare(strict_types=1);

namespace Laxity7\Money\Test;

use Laxity7\Money\Currencies;
use Laxity7\Money\Currencies\AggregateCurrencies;
use Laxity7\Money\MoneyConfig;
use Laxity7\Money\Test\Stubs\CryptoCurrenciesStub;
use Laxity7\Money\Test\Stubs\FiatCurrenciesStub;
use PHPUnit\Framework\TestCase;

final class MoneyConfigTest extends TestCase
{
    private static Currencies $fiatCurrencies;
    private static Currencies $cryptoCurrencies;

    protected function setUp(): void
    {
        self::$fiatCurrencies ??= new FiatCurrenciesStub();
        self::$cryptoCurrencies ??= new CryptoCurrenciesStub();
    }

    public function testGetConfigThrowsExceptionWhenNotConfigured(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('MoneyConfig is not configured.');

        MoneyConfig::getConfig();
    }

    public function testConfigureCreatesSingletonInstance(): void
    {
        $config = MoneyConfig::configure(self::$fiatCurrencies, self::$cryptoCurrencies, true);

        $this->assertInstanceOf(MoneyConfig::class, $config);
    }

    /**
     * @depends testConfigureCreatesSingletonInstance
     */
    public function testConfigureThrowsExceptionWhenAlreadyConfigured(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('MoneyConfig is already configured.');

        MoneyConfig::configure(self::$fiatCurrencies, self::$cryptoCurrencies, true);
    }

    /**
     * @depends testConfigureCreatesSingletonInstance
     */
    public function testGetFiatCurrencies(): void
    {
        $this->assertSame(self::$fiatCurrencies, MoneyConfig::getFiatCurrencies());
    }

    /**
     * @depends testConfigureCreatesSingletonInstance
     */
    public function testGetCryptoCurrencies(): void
    {
        $this->assertSame(self::$cryptoCurrencies, MoneyConfig::getCryptoCurrencies());
    }

    /**
     * @depends testConfigureCreatesSingletonInstance
     */
    public function testGetCurrencies(): void
    {
        $this->assertInstanceOf(AggregateCurrencies::class, MoneyConfig::getCurrencies());
    }

}
