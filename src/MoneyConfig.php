<?php

declare(strict_types=1);

namespace Laxity7\Money;

use Laxity7\Money\Currencies\AggregateCurrencies;
use Laxity7\Money\Exceptions\ConfigAlreadyConfiguredException;
use Laxity7\Money\Exceptions\ConfigNotConfiguredException;

final class MoneyConfig
{
    private static MoneyConfig $instance;
    private Currencies $fiatCurrencies;
    private Currencies $cryptoCurrencies;
    private AggregateCurrencies $currencies;

    /**
     * Check whether the currency is acceptable.
     * Does not allow you to create objects with non-existent currencies or currencies that do not match the type.
     * When creating a Currency object, it will be checked that the currency is specified in the list of acceptable currencies.
     * For fiat currencies, this is a list of fiat currencies, for cryptocurrencies - a list of cryptocurrencies.
     * For common currencies, a list of all currencies.
     * Enabled by default, but can be disabled if you need to create objects with any currencies
     * or if you want to use your own verification logic.
     * You can also manually check the currency for validity using the Currency::disallow() method.
     */
    private bool $checkAcceptable;

    private function __construct(Currencies $fiatCurrencies, Currencies $cryptoCurrencies, bool $checkAcceptable = true)
    {
        $this->fiatCurrencies = $fiatCurrencies;
        $this->cryptoCurrencies = $cryptoCurrencies;
        $this->currencies = new AggregateCurrencies([$fiatCurrencies, $cryptoCurrencies]);
        $this->checkAcceptable = $checkAcceptable;
    }

    public static function configure(Currencies $fiatCurrencies, Currencies $cryptoCurrencies, bool $checkAcceptable): MoneyConfig
    {
        if (isset(self::$instance)) {
            throw new ConfigAlreadyConfiguredException('MoneyConfig is already configured.');
        }

        self::$instance = new MoneyConfig($fiatCurrencies, $cryptoCurrencies, $checkAcceptable);

        return self::$instance;
    }

    public static function getConfig(): MoneyConfig
    {
        if (!isset(self::$instance)) {
            throw new ConfigNotConfiguredException('MoneyConfig is not configured.');
        }

        return self::$instance;
    }

    public static function getFiatCurrencies(): Currencies
    {
        return self::getConfig()->fiatCurrencies;
    }

    public static function getCryptoCurrencies(): Currencies
    {
        return self::getConfig()->cryptoCurrencies;
    }

    public static function getCurrencies(): Currencies
    {
        return self::getConfig()->currencies;
    }

    public static function isCheckAcceptable(): bool
    {
        return self::getConfig()->checkAcceptable;
    }
}
