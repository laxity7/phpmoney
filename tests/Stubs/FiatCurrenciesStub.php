<?php

declare(strict_types=1);

namespace Laxity7\Money\Test\Stubs;

use Laxity7\Money\Currencies;
use Laxity7\Money\Currency;
use Traversable;

final class FiatCurrenciesStub implements Currencies
{
    private const CURRENCIES = [
        'USD' => 'US Dollar',
        'EUR' => 'Euro',
        'JPY' => 'Japanese Yen',
        'GBP' => 'British Pound Sterling',
    ];

    public function contains(Currency $currency): bool
    {
        return isset(self::CURRENCIES[$currency->getCode()]);
    }

    public function getDecimalCount(Currency $currency): int
    {
        return 2;
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator(
            array_map(
                static fn($code) => new Currency($code),
                array_keys(self::CURRENCIES)
            )
        );
    }
}
