<?php

declare(strict_types=1);

namespace Laxity7\Money;

use IteratorAggregate;
use Laxity7\Money\Exceptions\UnknownCurrencyException;
use Traversable;

/**
 * Implement this to provide a list of currencies.
 */
interface Currencies extends IteratorAggregate
{
    /**
     * Checks whether a currency is available in the current context.
     */
    public function contains(Currency $currency): bool;

    /**
     * Returns the subunit for a currency.
     * @throws UnknownCurrencyException
     */
    public function getDecimalCount(Currency $currency): int;

    /**
     * @psalm-return Traversable<int|string, Currency>
     */
    public function getIterator(): Traversable;
}
