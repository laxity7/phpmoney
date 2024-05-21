<?php

declare(strict_types=1);

namespace Laxity7\Money\Currencies;

use Laxity7\Money\Currency;
use Traversable;

class Currencies implements \Laxity7\Money\Currencies
{
    private array $currencies;

    /** @var array<string, string> */
    public function __construct(array $currencies)
    {
        $this->currencies = $currencies;
    }

    /**
     * Checks whether a currency is available in the current context.
     *
     * @return bool
     */
    public function contains(Currency $currency): bool
    {
        return isset($this->getCurrencies()[$currency->getCode()]);
    }

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
        self::$currencies ??= $this->loadCurrencies();

        return self::$currencies;
    }

    public function getDecimalCount(Currency $currency): int
    {
        return 2;
    }

    private function loadCurrencies()
    {
    }
}
