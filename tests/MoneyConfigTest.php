<?php

declare(strict_types=1);

namespace Laxity7\Money\Test;

use Laxity7\Money\Exceptions\ConfigAlreadyConfiguredException;
use Laxity7\Money\MoneyConfig;
use Laxity7\Money\Test\Stubs\FiatCurrenciesStub;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Laxity7\Money\MoneyConfig
 */
final class MoneyConfigTest extends TestCase
{
    /**
     * @covers \Laxity7\Money\MoneyConfig::configure()
     */
    public function testConfigureCreatesSingletonInstance(): void
    {
        $config = MoneyConfig::configure(new FiatCurrenciesStub(), true);

        self::assertInstanceOf(MoneyConfig::class, $config);
    }

    /**
     * @covers \Laxity7\Money\MoneyConfig::configure()
     */
    public function testConfigureThrowsExceptionWhenAlreadyConfigured(): void
    {
        MoneyConfig::configure(new FiatCurrenciesStub(), true);

        $this->expectException(ConfigAlreadyConfiguredException::class);
        $this->expectExceptionMessage('MoneyConfig is already configured.');

        MoneyConfig::configure(new FiatCurrenciesStub(), true);
    }

    /**
     * @covers \Laxity7\Money\MoneyConfig::getCurrencies()
     */
    public function testGetCurrencies(): void
    {
        MoneyConfig::configure(new FiatCurrenciesStub(), true);
        self::assertInstanceOf(FiatCurrenciesStub::class, MoneyConfig::getCurrencies());
    }
}
