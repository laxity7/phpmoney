<?php

declare(strict_types=1);

namespace Laxity7\Money;

final class FiatCurrency extends Currency
{
    public function isAcceptable(): bool
    {
        return MoneyConfig::getFiatCurrencies()->contains($this);
    }
}
