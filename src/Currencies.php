<?php

declare(strict_types=1);

namespace Laxity7\Money;

use IteratorAggregate;
use Laxity7\Money\Exceptions\UnknownCurrencyException;
use Traversable;

/**
 * @template TKey of int
 * @template-covariant TValue of Currency
 * @template-extends IteratorAggregate<TKey, TValue>
 */
interface Currencies extends IteratorAggregate
{
    /**
     * Checks whether a currency is available in the current context.
     */
    public function contains(Currency $currency): bool;

    /**
     * Returns the subunit for a currency.
     * @return int<0, 14>
     * @throws UnknownCurrencyException
     */
    public function getDecimalCount(Currency $currency): int;

    /**
     * Returns the name of a currency.
     * @return non-empty-string
     * @throws UnknownCurrencyException
     */
    public function getName(Currency $currency): string;

    /**
     * @return Traversable<int, Currency>
     */
    public function getIterator(): Traversable;
}
