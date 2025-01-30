<?php

declare(strict_types=1);

namespace Laxity7\Money\Test\Stubs;

use ArrayIterator;
use Laxity7\Money\Currencies;
use Laxity7\Money\Currency;
use Traversable;

/**
 * @template-implements Currencies<int, Currency>
 */
final class CryptoCurrenciesStub implements Currencies
{
    private const CURRENCIES = [
        'BTC' => 'Bitcoin',
        'ETH' => 'Ethereum',
        'USDT' => 'Tether',
        'TON' => 'Telegram Open Network',
    ];

    public function contains(Currency $currency): bool
    {
        return isset(self::CURRENCIES[$currency->getCode()]);
    }

    public function getDecimalCount(Currency $currency): int
    {
        /** @phpstan-ignore return.type */
        return match ($currency->getCode()) {
            'ETH' => 18,
            'USDT' => 4,
            default => 8,
        };
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator(
            array_map(
                static fn($code) => new Currency($code),
                array_keys(self::CURRENCIES)
            )
        );
    }

    public function getName(Currency $currency): string
    {
        return self::CURRENCIES[$currency->getCode()];
    }
}
