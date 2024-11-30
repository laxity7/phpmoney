<?php

declare(strict_types=1);

namespace Laxity7\Money;

use Laxity7\Money\Exceptions\UnacceptableCurrencyException;
use Laxity7\Money\Exceptions\UnknownCurrencyException;

/**
 * Currency Value Object.
 *
 * Holds Currency specific data.
 */
class Currency implements \JsonSerializable, \Stringable
{
    /**
     * Currency code.
     * @var non-empty-string
     */
    private readonly string $code;

    /** @param non-empty-string $code */
    final public function __construct(string $code)
    {
        $code = trim(strtoupper($code));
        if ($code === '') {
            throw new UnknownCurrencyException('Currency code cannot be empty');
        }

        $this->code = $code;

        if (MoneyConfig::checkAcceptableEnabled() && !$this->isAcceptable()) {
            throw new UnacceptableCurrencyException(sprintf('"%s" is an unacceptable currency', $this->code));
        }
    }

    final public function isAcceptable(): bool
    {
        return MoneyConfig::getCurrencies()->contains($this);
    }

    /**
     * Returns the currency code.
     *
     * @psalm-return non-empty-string
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
        return MoneyConfig::getCurrencies()->getName($this);
    }

    /**
     * Returns the number of decimal places for this currency.
     * @return int<0, 14>
     */
    final public function getDecimalCount(): int
    {
        return MoneyConfig::getCurrencies()->getDecimalCount($this);
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
