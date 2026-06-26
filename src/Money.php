<?php

declare(strict_types=1);

namespace Laxity7\Money;

use JsonSerializable;
use Laxity7\Money\Exceptions\InvalidArgumentException;
use Stringable;

/**
 * Class for proper handling of money in different currencies.
 *
 * Allows performing arithmetic operations with money.
 * Supports comparison operations.
 * Supports up to 14 decimal places; numbers resulting from operations with more decimals are rounded.
 * Supports rounding up and down.
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
     * @param int|float|numeric-string $amount Amount of money. For large or precision-critical values pass a numeric string to avoid float precision loss.
     * @param non-empty-string|Currency $currency Currency code or Currency object
     * @param Currencies<int, Currency>|null $currencies Currency registry. When null, uses the global MoneyConfig registry.
     */
    final public function __construct(int|float|string $amount, string|Currency $currency, ?Currencies $currencies = null)
    {
        $this->currency = $currency instanceof Currency ? $currency : new Currency($currency, $currencies);
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
     *
     * @throws InvalidArgumentException if currencies do not match
     */
    final public function isEquals(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->compare($this->amount, $other->amount) === 0;
    }

    /**
     * Checks that two objects are not equal.
     *
     * @throws InvalidArgumentException if currencies do not match
     */
    final public function isNotEquals(self $other): bool
    {
        return !$this->isEquals($other);
    }

    /**
     * Check if the current object is greater than the passed one.
     *
     * @throws InvalidArgumentException if currencies do not match
     */
    final public function isGreaterThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->compare($this->amount, $other->amount) === 1;
    }

    /**
     * Check if the current object is less than the passed one.
     *
     * @throws InvalidArgumentException if currencies do not match
     */
    final public function isLessThan(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->compare($this->amount, $other->amount) === -1;
    }

    /**
     * Check if the current object is greater than or equal to the passed one.
     *
     * @throws InvalidArgumentException if currencies do not match
     */
    final public function isGreaterThanOrEquals(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->compare($this->amount, $other->amount) >= 0;
    }

    /**
     * Check if the current object is less than or equal to the passed one.
     *
     * @throws InvalidArgumentException if currencies do not match
     */
    final public function isLessThanOrEquals(self $other): bool
    {
        $this->assertSameCurrency($other);

        return $this->compare($this->amount, $other->amount) <= 0;
    }

    /**
     * Sum two objects.
     *
     * @throws InvalidArgumentException if currencies do not match
     */
    final public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return $this->newInstance(bcadd($this->amount, $other->amount, $this->scale));
    }

    /**
     * Sum two objects.
     * Alias for the add method.
     *
     * @throws InvalidArgumentException if currencies do not match
     */
    final public function plus(self $other): self
    {
        return $this->add($other);
    }

    /**
     * Subtract the passed object from the current one.
     *
     * @throws InvalidArgumentException if currencies do not match
     */
    final public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        return $this->newInstance(bcsub($this->amount, $other->amount, $this->scale));
    }

    /**
     * Subtract the passed object from the current one.
     * Alias for the subtract method.
     *
     * @throws InvalidArgumentException if currencies do not match
     */
    final public function minus(self $other): self
    {
        return $this->subtract($other);
    }

    /**
     * Multiply the current object by the multiplier.
     *
     * @param int|float|numeric-string $multiplier Multiplier
     * @param self::ROUND_HALF_UP|self::ROUND_HALF_DOWN $roundingMode Rounding mode
     */
    final public function multiply(int|float|string $multiplier, int $roundingMode = self::ROUND_HALF_UP): self
    {
        $scale = max($this->scale, self::SCALE);
        $value = bcmul($this->amount, $this->format($multiplier, $scale), $scale + 1);
        $value = $this->round($value, $roundingMode);

        return $this->newInstance($value);
    }

    /**
     * Divide the current object by the divisor.
     *
     * @param int|float|numeric-string $divisor Divisor
     * @param self::ROUND_HALF_UP|self::ROUND_HALF_DOWN $roundingMode Rounding mode
     *
     * @throws InvalidArgumentException if divisor is zero
     */
    final public function divide(int|float|string $divisor, int $roundingMode = self::ROUND_HALF_UP): self
    {
        $scale = max($this->scale, self::SCALE);
        $formattedDivisor = $this->format($divisor, $scale);

        if (bccomp($formattedDivisor, '0', $scale) === 0) {
            throw new InvalidArgumentException('Division by zero.');
        }

        $value = bcdiv($this->amount, $formattedDivisor, $scale + 1);
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
     * Returns the absolute value.
     */
    final public function abs(): self
    {
        if ($this->isNegative()) {
            return $this->negate();
        }

        return $this;
    }

    /**
     * Returns the negated value (positive becomes negative and vice versa).
     */
    final public function negate(): self
    {
        return $this->newInstance(bcmul($this->amount, '-1', $this->scale));
    }

    /**
     * Distributes the amount according to the given ratios without losing a minimum unit.
     *
     * The remainder (if any) is assigned one minimum unit at a time to the first parts.
     * The sum of the returned parts always equals the original amount.
     *
     * Example: allocate(1, 1, 1) on $10.00 USD → [$3.34, $3.33, $3.33]
     *
     * @param int ...$ratios
     * @return self[]
     * @throws InvalidArgumentException if no ratios given, any ratio is negative, or all ratios are zero
     */
    final public function allocate(int ...$ratios): array
    {
        if (count($ratios) === 0) {
            throw new InvalidArgumentException('At least one ratio is required.');
        }
        foreach ($ratios as $ratio) {
            if ($ratio < 0) {
                throw new InvalidArgumentException('Ratios must not be negative.');
            }
        }

        $total = (string)array_sum($ratios);
        if (bccomp($total, '0', 0) === 0) {
            throw new InvalidArgumentException('Ratios must not all be zero.');
        }

        /** @var numeric-string $unit */
        $unit = $this->scale === 0 ? '1' : '0.' . str_repeat('0', $this->scale - 1) . '1'; // @phpstan-ignore varTag.nativeType
        $isNegative = $this->isNegative();
        /** @var numeric-string $signedUnit */
        $signedUnit = $isNegative ? '-' . $unit : $unit;

        $shares = [];
        $allocated = bcadd('0', '0', $this->scale);

        foreach ($ratios as $ratio) {
            // truncate toward zero to avoid over-allocation
            $share = bcadd(
                bcdiv(bcmul($this->amount, (string)$ratio, $this->scale + 4), $total, $this->scale + 4),
                '0',
                $this->scale
            );
            $shares[] = $share;
            $allocated = bcadd($allocated, $share, $this->scale);
        }

        $remainder = bcsub($this->amount, $allocated, $this->scale);
        $direction = $isNegative ? -1 : 1;
        $count = count($shares);
        $i = 0;

        while (bccomp($remainder, '0', $this->scale) === $direction) {
            $shares[$i % $count] = bcadd($shares[$i % $count], $signedUnit, $this->scale);
            $remainder = bcsub($remainder, $signedUnit, $this->scale);
            $i++;
        }

        /** @var array<int, numeric-string> $shares */
        return array_map(fn(string $amount) => $this->newInstance($amount), $shares);
    }

    /**
     * Splits the amount into $n equal parts without losing a minimum unit.
     *
     * Shorthand for allocate(1, 1, ..., 1).
     *
     * @return self[]
     * @throws InvalidArgumentException if $n is not positive
     */
    final public function split(int $n): array
    {
        if ($n <= 0) {
            throw new InvalidArgumentException('Split count must be greater than zero.');
        }

        return $this->allocate(...array_fill(0, $n, 1));
    }

    /**
     * Get amount.
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
     * @throws InvalidArgumentException if currencies do not match
     */
    private function assertSameCurrency(self $other): void
    {
        if (!$this->isSameCurrency($other)) {
            throw new InvalidArgumentException('Currencies must match.');
        }
    }

    /**
     * Rounds a numeric string to the given scale using half-up rounding (BCMath-safe, no float cast).
     *
     * @param numeric-string $number
     * @return numeric-string
     */
    private function bcRound(string $number, int $scale): string
    {
        $dotPos = strpos($number, '.');
        $fractionalLength = $dotPos !== false ? strlen($number) - $dotPos - 1 : 0;

        if ($fractionalLength <= $scale) {
            /** @var numeric-string */
            return bcadd($number, '0', $scale);
        }

        $isNegative = $number[0] === '-';
        /** @var numeric-string $rounder */
        $rounder = '0.' . str_repeat('0', $scale) . '5'; // @phpstan-ignore varTag.nativeType

        /** @var numeric-string $result */
        $result = $isNegative
            ? bcsub($number, $rounder, $scale)
            : bcadd($number, $rounder, $scale);

        return $result;
    }

    /**
     * Rounds a numeric string to the currency scale using the given mode (BCMath-safe, no float cast).
     *
     * Both modes round to the nearest unit of the currency scale; they differ only on an exact
     * half: ROUND_HALF_UP rounds away from zero, ROUND_HALF_DOWN rounds toward zero.
     *
     * @param numeric-string $value
     * @param self::ROUND_HALF_UP|self::ROUND_HALF_DOWN $roundingMode
     * @return numeric-string
     */
    private function round(string $value, int $roundingMode): string
    {
        // Truncate toward zero at the target scale; the remainder is the part beyond it.
        $truncated = bcadd($value, '0', $this->scale);
        $remainder = bcsub($value, $truncated, self::SCALE + 2);

        // Half of one unit at the target scale (e.g. "0.005" for scale 2, "0.5" for scale 0).
        /** @var numeric-string $half */
        $half = '0.' . str_repeat('0', $this->scale) . '5'; // @phpstan-ignore varTag.nativeType

        $cmp = bccomp(ltrim($remainder, '-'), $half, self::SCALE + 2);
        $roundAwayFromZero = $cmp === 1 || ($cmp === 0 && $roundingMode === self::ROUND_HALF_UP);

        if ($roundAwayFromZero) {
            /** @var numeric-string $unit */
            $unit = $this->scale === 0 ? '1' : '0.' . str_repeat('0', $this->scale - 1) . '1'; // @phpstan-ignore varTag.nativeType
            $truncated = $value[0] === '-'
                ? bcsub($truncated, $unit, $this->scale)
                : bcadd($truncated, $unit, $this->scale);
        }

        /** @var numeric-string */
        return $truncated;
    }

    /**
     * Formats a value to a numeric string with the given scale.
     *
     * String inputs are handled via BCMath to preserve arbitrary precision.
     * Int/float inputs use number_format (float precision is inherent to the type).
     *
     * @param int|float|numeric-string $amount
     * @return numeric-string
     */
    private function format(int|float|string $amount, ?int $scale = null): string
    {
        $scale ??= $this->scale;

        if (is_string($amount)) {
            $amount = trim($amount);
            if (!is_numeric($amount)) {
                throw new InvalidArgumentException('Amount must be numeric.');
            }

            // Handle scientific notation in string form (e.g., "1.5E+10")
            if (stripos($amount, 'e') !== false) {
                $amount = number_format((float)$amount, self::SCALE, '.', '');
            }

            return $this->bcRound($amount, $scale);
        }

        $formatted = number_format((float)$amount, $scale, '.', '');

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
