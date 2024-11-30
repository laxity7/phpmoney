<?php

declare(strict_types=1);

namespace Laxity7\Money\Test\Stubs;

use Laxity7\Money\Currencies\AggregateCurrencies;
use Laxity7\Money\Currencies\Currencies;
use Laxity7\Money\Exceptions\ConfigAlreadyConfiguredException;
use Laxity7\Money\MoneyConfig;

class MoneyConfigStub
{
    public static function configure(?Currencies $currencies = null, bool $checkAcceptable = false): void
    {
        try {
            MoneyConfig::configure(
                $currencies ?? new AggregateCurrencies(
                new FiatCurrenciesStub(),
                new CryptoCurrenciesStub()
            ),
                $checkAcceptable
            );
        } catch (ConfigAlreadyConfiguredException) {
            // Do nothing
        }
    }
}
