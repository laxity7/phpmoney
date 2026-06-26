<?php

declare(strict_types=1);

namespace Laxity7\Money\Test;

use Laxity7\Money\Currencies\AggregateCurrencies;
use Laxity7\Money\MoneyConfig;
use Laxity7\Money\Test\Stubs\CryptoCurrenciesStub;
use Laxity7\Money\Test\Stubs\FiatCurrenciesStub;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Laxity7\Money\MoneyConfig
 */
final class MoneyConfigTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        MoneyConfig::reset();
    }

    public function testIsNotConfigured(): void
    {
        self::assertFalse(MoneyConfig::isConfigured());
    }

    public function testIsConfigured(): void
    {
        MoneyConfig::configure(new FiatCurrenciesStub(), true);

        self::assertTrue(MoneyConfig::isConfigured());
    }

    public function testConfigureReturnsSelf(): void
    {
        $config = MoneyConfig::configure(new FiatCurrenciesStub(), true);

        self::assertInstanceOf(MoneyConfig::class, $config);
    }

    public function testConfigureCanReconfigure(): void
    {
        MoneyConfig::configure(new FiatCurrenciesStub(), true);
        MoneyConfig::configure(new AggregateCurrencies(new FiatCurrenciesStub(), new CryptoCurrenciesStub()), false);

        self::assertInstanceOf(AggregateCurrencies::class, MoneyConfig::getCurrencies());
        self::assertFalse(MoneyConfig::checkAcceptableEnabled());
    }

    public function testGetCurrencies(): void
    {
        MoneyConfig::configure(new FiatCurrenciesStub(), true);

        self::assertInstanceOf(FiatCurrenciesStub::class, MoneyConfig::getCurrencies());
    }

    public function testGetConfigUsesDefaultCurrenciesWhenNotConfigured(): void
    {
        self::assertInstanceOf(AggregateCurrencies::class, MoneyConfig::getCurrencies());
    }
}
