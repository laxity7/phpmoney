<?php

declare(strict_types=1);

namespace Laxity7\Money;

use Laxity7\Money\Currencies\AggregateCurrencies;

final class MoneyConfig
{
    private static ?self $instance = null;

    /**
     * @param Currencies<int, Currency> $currencies
     */
    private function __construct(
        private readonly Currencies $currencies,
        private readonly bool $checkAcceptable = true
    ) {
    }

    public static function isConfigured(): bool
    {
        return self::$instance !== null;
    }

    /**
     * Configures the global currency registry. Can be called multiple times; each call replaces the previous config.
     *
     * @param Currencies<int, Currency> $currencies
     */
    public static function configure(Currencies $currencies, bool $checkAcceptable = true): MoneyConfig
    {
        self::$instance = new MoneyConfig($currencies, $checkAcceptable);

        return self::$instance;
    }

    public static function getConfig(): MoneyConfig
    {
        if (self::$instance === null) {
            $defaultCurrencies = new AggregateCurrencies(
                new \Laxity7\Money\Currencies\Currencies(require __DIR__ . '/Currencies/fiatCurrencies.php'),
                new \Laxity7\Money\Currencies\Currencies(require __DIR__ . '/Currencies/top100cryptoCurrencies.php')
            );

            return self::configure($defaultCurrencies, true);
        }

        return self::$instance;
    }

    /**
     * @return Currencies<int, Currency>
     */
    public static function getCurrencies(): Currencies
    {
        return self::getConfig()->currencies;
    }

    public static function checkAcceptableEnabled(): bool
    {
        return self::getConfig()->checkAcceptable;
    }

    /**
     * Resets the configuration to unconfigured state.
     *
     * @internal
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
