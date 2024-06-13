<?php

namespace Laxity7\Money;

use JsonSerializable;
use Laxity7\Money\Exceptions\InvalidArgumentException;
use Stringable;

/**
 * Класс для корректной работы с деньгами в разных валютах.
 *
 * Позволяет выполнять арифметические операции с деньгами.
 * Поддерживает операции сравнения.
 * Поддерживает до 8 знаков после запятой, числа получаемые в результате операций с большим количеством округляются до 8 знаков.
 * Поддерживает округление в большую и меньшую сторону (для чисел свыше 8 знаков после запятой).
 *
 * Класс не позволяет выполнять операции с разными валютами.
 *
 * Пример использования:
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
 * При вывозе метода getAmount() возвращается строка, в которой удалены незначащие нули после запятой.
 * Метод toString() возвращает строку в формате "сумма валюта", например "1 USD", "0.00000001 BTC".
 */
class Money implements JsonSerializable, Stringable
{
    public const ROUND_HALF_UP = PHP_ROUND_HALF_UP;
    public const ROUND_HALF_DOWN = PHP_ROUND_HALF_DOWN;
    private const SCALE = PHP_FLOAT_DIG - 1;

    private string $amount;
    private Currency $currency;
    private int $scale;

    /**
     * @param int|float|string $amount Сумма денег
     * @param string|Currency $currency Валюта
     */
    public function __construct(float $amount, $currency)
    {
        $this->currency = $currency instanceof Currency ? $currency : new Currency($currency);
        $this->scale = $this->currency->getDecimalCount();
        $this->amount = $this->floatToString($amount);
    }

    /**
     * Проверить, одинаковая ли валюта у двух объектов.
     */
    final public function isSameCurrency(self $other): bool
    {
        return $this->currency->equals($other->currency);
    }

    /**
     * Проверить, равны ли два объекта.
     */
    final public function isEquals(self $other): bool
    {
        return $this->isSameCurrency($other) && $this->compare($this->amount, $other->amount) === 0;
    }

    /**
     * Проверить, что два объекта не равны.
     */
    final public function isNotEquals(self $other): bool
    {
        return !$this->isSameCurrency($other) || $this->compare($this->amount, $other->amount) !== 0;
    }

    /**
     * Проверить, больше ли текущий объект, чем переданный.
     */
    final public function isGreaterThan(self $other): bool
    {
        return $this->isSameCurrency($other) && $this->compare($this->amount, $other->amount) === 1;
    }

    /**
     * Проверить, меньше ли текущий объект, чем переданный.
     */
    final public function isLessThan(self $other): bool
    {
        return $this->isSameCurrency($other) && $this->compare($this->amount, $other->amount) === -1;
    }

    /**
     * Проверить, больше или равен текущий объект, чем переданный.
     */
    final public function isGreaterThanOrEquals(self $other): bool
    {
        return $this->isSameCurrency($other) && $this->compare($this->amount, $other->amount) >= 0;
    }

    /**
     * Проверить, меньше или равен текущий объект, чем переданный.
     */
    final public function isLessThanOrEquals(self $other): bool
    {
        return $this->isSameCurrency($other) && $this->compare($this->amount, $other->amount) <= 0;
    }

    /**
     * Сложить два объекта.
     */
    final public function add(self $other): self
    {
        if (!$this->isSameCurrency($other)) {
            throw new InvalidArgumentException('Валюты должны совпадать.');
        }

        return $this->newInstance(bcadd($this->amount, $other->amount, $this->scale));
    }

    /**
     * Сложить два объекта.
     * Алиас для метода add.
     */
    final public function plus(self $other): self
    {
        return $this->add($other);
    }

    /**
     * Вычесть из текущего объекта переданный.
     */
    final public function subtract(self $other): self
    {
        if (!$this->isSameCurrency($other)) {
            throw new InvalidArgumentException('Валюты должны совпадать.');
        }

        return $this->newInstance(bcsub($this->amount, $other->amount, $this->scale));
    }

    /**
     * Вычесть из текущего объекта переданный.
     * Алиас для метода subtract.
     */
    final public function minus(self $other): self
    {
        return $this->subtract($other);
    }

    /**
     * Умножить текущий объект на множитель.
     *
     * @param int|float $multiplier Множитель
     * @param int{self::ROUND_*} $roundingMode Режим округления
     */
    final public function multiply(float $multiplier, int $roundingMode = self::ROUND_HALF_UP): self
    {
        $value = bcmul($this->amount, $this->floatToString($multiplier, self::SCALE), self::SCALE);
        $value = $this->round($value, $roundingMode);

        return $this->newInstance($value);
    }

    /**
     * Разделить текущий объект на делитель.
     *
     * @param int|float $divisor Делитель
     * @param int{self::ROUND_*} $roundingMode Режим округления
     */
    final public function divide(float $divisor, int $roundingMode = self::ROUND_HALF_UP): self
    {
        $value = bcdiv($this->amount, $this->floatToString($divisor, self::SCALE), self::SCALE);
        $value = $this->round($value, $roundingMode);

        return $this->newInstance($value);
    }

    /**
     * Проверить, равен ли объект нулю.
     */
    final public function isZero(): bool
    {
        return $this->compare($this->amount, '0') === 0;
    }

    /**
     * Проверить, положительное ли число.
     */
    final public function isPositive(): bool
    {
        return $this->compare($this->amount, '0') === 1;
    }

    /**
     * Проверить, отрицательное ли число.
     */
    final public function isNegative(): bool
    {
        return $this->compare($this->amount, '0') === -1;
    }

    /**
     * Получить сумму.
     */
    final public function getAmount(): string
    {
        if ($this->isZero()) {
            return '0';
        }

        return rtrim(rtrim($this->amount, '0'), '.');
    }

    /**
     * Получить валюту.
     */
    final public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * Получить объект в виде строки в формате "сумма валюта".
     */
    final public function toString(bool $trimZero = true): string
    {
        if ($trimZero) {
            return $this->getAmount() . ' ' . $this->getCurrency()->getCode();
        }

        return $this->amount . ' ' . $this->getCurrency()->getCode();
    }

    final public function __toString(): string
    {
        return $this->toString();
    }

    final public function jsonSerialize(): array
    {
        return [
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency()->getCode(),
        ];
    }

    private function getFractionalCount(string $value): int
    {
        return strlen($this->getFractionalPart($value));
    }

    private function getFractionalPart(string $value): string
    {
        return explode('.', rtrim($value, 0))[1];
    }

    private function getIntegerPart(string $value): string
    {
        return explode('.', $value)[0];
    }

    private function getMinimalFractionalUnit(string $value): string
    {
        if ($this->getFractionalCount($value) === 0) {
            return '0';
        }

        $unit = '0.' . str_repeat('0', $this->getFractionalCount($value) - 1) . '1';
        if ($value[0] === '-') {
            $unit = '-' . $unit;
        }

        return $unit;
    }

    private function roundUp(string $number): string
    {
        $scale = $this->getFractionalCount($number);

        $adjustment = $this->getMinimalFractionalUnit($number);
        $number = bcadd($number, $adjustment, self::SCALE);

        return $this->floatToString($number, $scale - 1);
    }

    private function roundDown(string $number): string
    {
        return bcadd($number, '0', $this->scale);
    }

    private function round(string $value, int $roundingMode): string
    {
        if ($this->scale >= $this->getFractionalCount($value)) {
            return $value;
        }

        $lastChar = substr($this->getFractionalPart($value), -1);
        if ($lastChar === '5') {
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

    private function floatToString(float $amount, ?int $scale = null): string
    {
        $scale ??= $this->scale;

        return sprintf("%.{$scale}F", $amount);
    }

    private function newInstance(string $amount): self
    {
        return new self($amount, $this->currency);
    }

    private function compare(string $amount, string $other): int
    {
        return bccomp($amount, $other, $this->scale);
    }
}
