<?php

declare(strict_types=1);

namespace Laxity7\Money\Currencies;

use Laxity7\Money\Currency;
use Laxity7\Money\Exceptions\InvalidArgumentException;
use Laxity7\Money\Exceptions\UnknownCurrencyException;
use Traversable;

/**
 * Provides a list of currencies.
 * @template-implements \Laxity7\Money\Currencies<int, Currency>
 */
final class Currencies implements \Laxity7\Money\Currencies
{
    /** @var array<non-empty-string, array{name: non-empty-string, symbol: non-empty-string, scale: int<0, 14>}> */
    private readonly array $currencies;

    /** @param array{name: non-empty-string, symbol: non-empty-string, scale: int<0, 14>}[] $currencies */
    public function __construct(array $currencies)
    {
        if (count($currencies) === 0) {
            throw new InvalidArgumentException('No currencies provided');
        }

        $list = [];
        foreach ($currencies as $currency) {
            if (!is_string($currency['name'] ?? null) || !is_string($currency['symbol'] ?? null) || !is_int($currency['scale'] ?? null)) {
                throw new InvalidArgumentException('Invalid currency data');
            }
            if ($currency['scale'] > PHP_FLOAT_DIG) {
                throw new InvalidArgumentException('Currency scale is too large. Maximum is ' . (PHP_FLOAT_DIG - 1));
            }

            $list[$currency['symbol']] = $currency;
        }

        $this->currencies = $list;
    }

    public function contains(Currency $currency): bool
    {
        return isset($this->currencies[$currency->getCode()]);
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator(
            array_map(
                static fn(string $code) => new Currency($code),
                array_keys($this->currencies)
            )
        );
    }

    public function getDecimalCount(Currency $currency): int
    {
        if (!isset($this->currencies[$currency->getCode()])) {
            throw new UnknownCurrencyException('Cannot find currency ' . $currency->getCode());
        }

        return $this->currencies[$currency->getCode()]['scale'];
    }

    public function getName(Currency $currency): string
    {
        if (!isset($this->currencies[$currency->getCode()])) {
            throw new UnknownCurrencyException('Cannot find currency ' . $currency->getCode());
        }

        return $this->currencies[$currency->getCode()]['name'];
    }
}
