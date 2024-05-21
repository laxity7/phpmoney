<?php

declare(strict_types=1);

namespace Laxity7\Money\Test\Stubs;

use Laxity7\Money\Exceptions\ConfigAlreadyConfiguredException;
use Laxity7\Money\MoneyConfig;

class MoneyConfigStub
{
    public static function configure(): void
    {
        try {
            MoneyConfig::configure(
                new FiatCurrenciesStub(),
                new CryptoCurrenciesStub(),
                false
            );
        } catch (ConfigAlreadyConfiguredException $e) {
            // Do nothing
        }
    }
}
