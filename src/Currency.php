<?php

declare(strict_types=1);

namespace Laxity7\Money;

use Laxity7\Money\Exceptions\UnknownCurrencyException;

/**
 * Currency Value Object.
 *
 * Holds Currency specific data.
 *
 * @psalm-immutable
 */
class Currency implements \JsonSerializable, \Stringable
{
    /**
     * Currency code.
     *
     * @psalm-var non-empty-string
     */
    private string $code;

    /** @psalm-param non-empty-string $code */
    final public function __construct(string $code)
    {
        $code = trim(strtoupper($code));
        if (empty($code)) {
            throw new UnknownCurrencyException('Currency code cannot be empty');
        }

        $this->code = $code;

        if (MoneyConfig::isCheckAcceptable() && !$this->isAcceptable()) {
            throw new UnknownCurrencyException(sprintf('"%s" is an unacceptable currency', $this->code));
        }
    }

    public function isAcceptable(): bool
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
