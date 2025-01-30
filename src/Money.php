<?php

namespace Laxity7\Money;

use JsonSerializable;
use Laxity7\Money\Exceptions\InvalidArgumentException;
use Stringable;

/**
 * Class for proper handling of money in different currencies.
 *
 * Allows performing arithmetic operations with money.
 * Supports comparison operations.
 * Supports up to 8 decimal places; numbers resulting from operations with more decimals are rounded to 8 places.
 * Supports rounding up and down (for numbers with more than 8 decimal places).
 *
 * The class does not allow operations with different currencies.
 *
 * Example usage:
 *
 * $money1 = new Money(1.0, 'USD');
 * $money2 = new Money(2.0, 'USD');
 *
 * $money1->isSameCurrency($money2); // true
 * $money1->isEquals($money2); // false
 * $money1->isGreaterThan($money2); // false
 * $money1->isLessThan($money2); // true
 * $money1->isGreaterThanOrEquals($money2); // false
 * $money1->isLessThanOrEquals($money2); // true
 * $money1->plus($money2); // 3 USD
 * $money2->minus($money1); // 1 USD
 *
 * When calling the getAmount() method, it returns a string with trailing insignificant zeros removed after the decimal point.
 * The toString() method returns a string in the format "amount currency", e.g., "1 USD", "0.00000001 BTC".
 * @final
 */
class Money implements JsonSerializable, Stringable
{
    public const ROUND_HALF_UP = PHP_ROUND_HALF_UP;
    public const ROUND_HALF_DOWN = PHP_ROUND_HALF_DOWN;
    private const SCALE = PHP_FLOAT_DIG - 1;

    /** @var numeric-string */
    private readonly string $amount;
    private readonly Currency $currency;
    /** @var int<0, 14> */
    private readonly int $scale;

    /**
     * @param int|float|numeric-string $amount Amount of money
     * @param non-empty-string|Currency $currency Currency
     */
    final public function __construct(int|float|string $amount, string|Currency $currency)
    {
        $this->currency = $currency instanceof Currency ? $currency : new Currency($currency);
        $this->scale = $this->currency->getDecimalCount();
        $this->amount = $this->format($amount);
    }

    /**
     * Checks if the currency of two objects is the same.
     */
    final public function isSameCurrency(self $other): bool
    {
        return $this->currency->equals($other->currency);
    }

    /**
     * Checks if two objects are equal.
     */
    final public function isEquals(self $other): bool
    {
        return $this->isSameCurrency($other) && $this->compare($this->amount, $other->amount) === 0;
    }

    /**
     * Checks that two objects are not equal.
     */
    final public function isNotEquals(self $other): bool
    {
        return !$this->isSameCurrency($other) || $this->compare($this->amount, $other->amount) !== 0;
    }

    /**
     * Check if the current object is greater than the passed one.
     */
    final public function isGreaterThan(self $other): bool
    {
        return $this->isSameCurrency($other) && $this->compare($this->amount, $other->amount) === 1;
    }

    /**
     * Check if the current object is less than the passed one.
     */
    final public function isLessThan(self $other): bool
    {
        return $this->isSameCurrency($other) && $this->compare($this->amount, $other->amount) === -1;
    }

    /**
     * Check if the current object is greater than or equal to the passed one.
     */
    final public function isGreaterThanOrEquals(self $other): bool
    {
        return $this->isSameCurrency($other) && $this->compare($this->amount, $other->amount) >= 0;
    }

    /**
     * Check if the current object is less than or equal to the passed one.
     */
    final public function isLessThanOrEquals(self $other): bool
    {
        return $this->isSameCurrency($other) && $this->compare($this->amount, $other->amount) <= 0;
    }

    /**
     * Sum two objects.
     */
    final public function add(self $other): self
    {
        if (!$this->isSameCurrency($other)) {
            throw new InvalidArgumentException('Currencies must match.');
        }

        return $this->newInstance(bcadd($this->amount, $other->amount, $this->scale));
    }

    /**
     * Sum two objects.
     * Alias for the add method.
     */
    final public function plus(self $other): self
    {
        return $this->add($other);
    }

    /**
     * Subtract the passed object from the current one.
     */
    final public function subtract(self $other): self
    {
        if (!$this->isSameCurrency($other)) {
            throw new InvalidArgumentException('Currencies must match.');
        }

        return $this->newInstance(bcsub($this->amount, $other->amount, $this->scale));
    }

    /**
     * Subtract the passed object from the current one.
     * Alias for the subtract method.
     */
    final public function minus(self $other): self
    {
        return $this->subtract($other);
    }

    /**
     * Multiply the current object by the multiplier.
     *
     * @param int|float $multiplier Multiplier
     * @param self::ROUND_HALF_UP|self::ROUND_HALF_DOWN $roundingMode Rounding mode
     */
    final public function multiply(int|float $multiplier, int $roundingMode = self::ROUND_HALF_UP): self
    {
        $scale = max($this->scale, self::SCALE);
        $value = bcmul($this->amount, $this->format($multiplier, $scale), $scale + 1);
        $value = $this->round($value, $roundingMode);

        return $this->newInstance($value);
    }

    /**
     * Divide the current object by the divisor.
     *
     * @param int|float $divisor Divisor
     * @param self::ROUND_HALF_UP|self::ROUND_HALF_DOWN $roundingMode Rounding mode
     */
    final public function divide(int|float $divisor, int $roundingMode = self::ROUND_HALF_UP): self
    {
        $scale = max($this->scale, self::SCALE);
        $value = bcdiv($this->amount, $this->format($divisor, $scale), $scale + 1);
        $value = $this->round($value, $roundingMode);

        return $this->newInstance($value);
    }

    /**
     * Check if the current object is zero.
     */
    final public function isZero(): bool
    {
        return $this->compare($this->amount, '0') === 0;
    }

    /**
     * Check if the number is positive.
     */
    final public function isPositive(): bool
    {
        return $this->compare($this->amount, '0') === 1;
    }

    /**
     * Check if the number is negative.
     */
    final public function isNegative(): bool
    {
        return $this->compare($this->amount, '0') === -1;
    }

    /**
     * Get a mount.
     * @return numeric-string
     */
    final public function getAmount(bool $trimZero = true): string
    {
        if (!$trimZero) {
            return $this->amount;
        }

        if ($this->isZero()) {
            return '0';
        }

        if (!str_contains($this->amount, '.')) {
            return $this->amount;
        }

        $amount = rtrim(rtrim($this->amount, '0'), '.');

        /** @var numeric-string */
        return $amount;
    }

    /**
     * Get a currency.
     */
    final public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * Get an object as a string in the format "amount currency".
     */
    final public function toString(bool $trimZero = true): string
    {
        return $this->getAmount($trimZero) . ' ' . $this->getCurrency()->getCode();
    }

    final public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @return array{amount: string, currency: non-empty-string}
     */
    final public function jsonSerialize(): array
    {
        return [
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency()->getCode(),
        ];
    }

    /**
     * @param numeric-string $value
     * @return int<0, max>
     */
    private function getFractionalCount(string $value): int
    {
        return strlen($this->getFractionalPart($value));
    }

    /**
     * @param numeric-string $value
     * @return string
     */
    private function getFractionalPart(string $value): string
    {
        return explode('.', rtrim($value, '0'))[1] ?? '';
    }

    /**
     * @param numeric-string $value
     * @return string
     */
    private function getMinimalFractionalUnit(string $value): string
    {
        $fractionCount = $this->getFractionalCount($value);
        if ($fractionCount === 0) {
            return '0';
        }

        $unit = sprintf('0.%s1', str_repeat('0', min($fractionCount - 1, $this->scale + 1)));
        if ($value[0] === '-') {
            $unit = '-' . $unit;
        }

        return $unit;
    }

    /**
     * @param numeric-string $value
     */
    private function getLastNumber(string $value): int
    {
        $fraction = $this->getFractionalPart($value);
        $pos = $this->scale + 2;
        $lastChar = $fraction[$pos] ?? '';

        if ($lastChar === '0') {
            // if last char is 0, then we need to get the last non-zero char
            for ($i = $pos - 1; $i >= 0; $i--) {
                if ($fraction[$i] !== '0') {
                    $lastChar = $fraction[$i];
                    break;
                }
            }
        }

        if ($lastChar === '') {
            $lastChar = substr($fraction, -1);
        }

        return (int)$lastChar;
    }

    /**
     * @param numeric-string $number
     * @return numeric-string
     */
    private function roundUp(string $number): string
    {
        if ($this->scale === 0) {
            $result = bcadd($number, '0.01', 2);
            return $this->format($result, $this->scale);
        }

        /** @var numeric-string $adjustment */
        $adjustment = $this->getMinimalFractionalUnit($number);
        $result = bcadd($number, $adjustment, self::SCALE);

        return $this->format($result, $this->getFractionalCount($adjustment) - 1);
    }

    /**
     * @param numeric-string $number
     * @return numeric-string
     */
    private function roundDown(string $number): string
    {
        return bcadd($number, '0', $this->scale);
    }

    /**
     * @param numeric-string $value
     * @param positive-int $roundingMode
     * @return numeric-string
     */
    private function round(string $value, int $roundingMode): string
    {
        if ($this->getFractionalCount($value) <= $this->scale && $this->scale <= self::SCALE) {
            return $value;
        }

        $lastChar = $this->getLastNumber($value);
        if ($lastChar === 5) {
            switch ($roundingMode) {
                case PHP_ROUND_HALF_UP:
                    $value = $this->roundUp($value);
                    break;
                case PHP_ROUND_HALF_DOWN:
                    $value = $this->roundDown($value);
                    break;
            }
        } elseif ($lastChar > 5) {
            $value = $this->roundUp($value);
        }

        return $this->roundDown($value);
    }

    /**
     * @param int|float|numeric-string $amount
     * @return numeric-string
     */
    private function format(int|float|string $amount, ?int $scale = null): string
    {
        $scale ??= $this->scale;

        $formatted = number_format((float)$amount, $scale, '.', '');
        if (!is_numeric($formatted)) {
            throw new InvalidArgumentException('Amount must be numeric.');
        }

        /** @var numeric-string $formatted */
        return $formatted;
    }

    /**
     * @param numeric-string $amount
     */
    private function newInstance(string $amount): self
    {
        return new self($amount, $this->currency);
    }

    /**
     * @param numeric-string $amount
     * @param numeric-string $other
     */
    private function compare(string $amount, string $other): int
    {
        return bccomp($amount, $other, $this->scale);
    }
}
