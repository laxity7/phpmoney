<?php

declare(strict_types=1);

namespace Laxity7\Money\Currencies;

use Laxity7\Money\Currency;
use Traversable;

class FiatCurrencies implements \Laxity7\Money\Currencies
{
    private array $currencies;

    /** @inheritDoc */
    public function contains(Currency $currency): bool
    {
        return isset($this->getCurrencies()[$currency->getCode()]);
    }

    /** @inheritDoc */
    public function getDecimalCount(Currency $currency): int
    {
        return 2;
    }

    /** @inheritDoc */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator(
            array_map(
                static fn($code) => new Currency($code),
                array_keys($this->getCurrencies())
            )
        );
    }

    /**
     * Returns a map of known currencies indexed by code.
     *
     * @return array<string, string> $currencies Where the key is the currency code and the value is the currency name.
     */
    private function getCurrencies(): array
    {
        $this->currencies ??= require __DIR__ . '/iso.php';

        return $this->currencies;
    }
}
