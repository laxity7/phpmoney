<?php

declare(strict_types=1);

namespace Laxity7\Money;

use Laxity7\Money\Currencies\AggregateCurrencies;
use Laxity7\Money\Exceptions\ConfigAlreadyConfiguredException;

final class MoneyConfig
{
    private static self $instance;

    /**
     * @param Currencies<int, Currency> $currencies
     */
    private function __construct(
        private readonly Currencies $currencies,
        /**
         * Check whether the currency is acceptable.
         * Does not allow you to create objects with non-existent currencies or currencies that do not match the type.
         * When creating a Currency object, it will be checked that the currency is specified in the list of acceptable currencies.
         * For fiat currencies, this is a list of fiat currencies, for cryptocurrencies - a list of cryptocurrencies.
         * For common currencies, a list of all currencies.
         * Enabled by default, but can be disabled if you need to create objects with any currencies
         * or if you want to use your own verification logic.
         * You can also manually check the currency for validity using the Currency::isAcceptable() method.
         */
        private readonly bool $checkAcceptable = true
    ) {
    }

    public static function isConfigured(): bool
    {
        return isset(self::$instance);
    }

    /**
     * @param Currencies<int, Currency> $currencies
     */
    public static function configure(Currencies $currencies, bool $checkAcceptable = true): MoneyConfig
    {
        if (isset(self::$instance)) {
            throw new ConfigAlreadyConfiguredException('MoneyConfig is already configured.');
        }

        self::$instance = new MoneyConfig($currencies, $checkAcceptable);

        return self::$instance;
    }

    public static function getConfig(): MoneyConfig
    {
        if (!isset(self::$instance)) {
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
}
