<?php

declare(strict_types=1);

namespace Laxity7\Money\Currencies;

use AppendIterator;
use Laxity7\Money\Currencies;
use Laxity7\Money\Currency;
use Laxity7\Money\Exceptions\UnknownCurrencyException;
use Traversable;

/**
 * Aggregates several currency repositories.
 * @template-implements Currencies<int, Currency>
 */
final class AggregateCurrencies implements Currencies
{
    /** @var Currencies<int, Currency>[] */
    private readonly array $currencies;

    /**
     * @param Currencies<int, Currency> $currencies
     */
    public function __construct(Currencies ...$currencies)
    {
        $this->currencies = $currencies;
    }

    public function contains(Currency $currency): bool
    {
        foreach ($this->currencies as $currencies) {
            if ($currencies->contains($currency)) {
                return true;
            }
        }

        return false;
    }

    public function getDecimalCount(Currency $currency): int
    {
        foreach ($this->currencies as $currencies) {
            if ($currencies->contains($currency)) {
                return $currencies->getDecimalCount($currency);
            }
        }

        throw new UnknownCurrencyException('Cannot find currency ' . $currency->getCode());
    }

    /** {@inheritDoc} */
    public function getIterator(): Traversable
    {
        /** @psalm-var AppendIterator&Currencies<int, Currency>[] $iterator */
        $iterator = new AppendIterator();

        foreach ($this->currencies as $currencies) {
            $currencyIterator = $currencies->getIterator();
            /** @var \Iterator&Currencies<int, Currency> $currencyIterator */
            $iterator->append($currencyIterator);
        }

        return $iterator;
    }

    public function getName(Currency $currency): string
    {
        foreach ($this->currencies as $currencies) {
            if ($currencies->contains($currency)) {
                return $currencies->getName($currency);
            }
        }

        throw new UnknownCurrencyException('Cannot find currency ' . $currency->getCode());
    }
}
