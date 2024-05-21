<?php

declare(strict_types=1);

namespace Laxity7\Money;

class CryptoCurrency extends Currency
{
    public function isAcceptable(): bool
    {
        return MoneyConfig::getCryptoCurrencies()->contains($this);
    }
}
