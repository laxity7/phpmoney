<?php

declare(strict_types=1);

namespace Laxity7\Money;

use Laxity7\Money\Exceptions\UnacceptableCurrencyException;
use Laxity7\Money\Exceptions\UnknownCurrencyException;

/**
 * Currency Value Object.
 *
 * Holds currency-specific data: code, name, and decimal precision.
 * By default resolves metadata from the global MoneyConfig registry.
 * An explicit registry can be injected for isolated contexts or testing.
 */
class Currency implements \JsonSerializable, \Stringable
{
    /**
     * @var non-empty-string
     */
    private readonly string $code;

    /** @var Currencies<int, Currency> */
    private readonly Currencies $registry;

    /**
     * @param non-empty-string $code
     * @param Currencies<int, Currency>|null $registry When null, uses the global MoneyConfig registry.
     */
    final public function __construct(string $code, ?Currencies $registry = null)
    {
        $code = trim(strtoupper($code));
        if ($code === '') {
            throw new UnknownCurrencyException('Currency code cannot be empty');
        }

        $this->code = $code;
        $this->registry = $registry ?? MoneyConfig::getCurrencies();

        // acceptability check only applies when using the global registry
        if ($registry === null && MoneyConfig::checkAcceptableEnabled() && !$this->isAcceptable()) {
            throw new UnacceptableCurrencyException(sprintf('"%s" is an unacceptable currency', $this->code));
        }
    }

    final public function isAcceptable(): bool
    {
        return $this->registry->contains($this);
    }

    /**
     * Returns the currency code.
     *
     * @return non-empty-string
     */
    final public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Returns the currency name.
     */
    final public function getName(): string
    {
        return $this->registry->getName($this);
    }

    /**
     * Returns the number of decimal places for this currency.
     *
     * @return int<0, 14>
     */
    final public function getDecimalCount(): int
    {
        return $this->registry->getDecimalCount($this);
    }

    /**
     * Checks whether this currency is the same as another.
     */
    final public function equals(self $other): bool
    {
        return $this->code === $other->code;
    }

    final public function __toString(): string
    {
        return $this->code;
    }

    final public function jsonSerialize(): string
    {
        return $this->code;
    }
}
