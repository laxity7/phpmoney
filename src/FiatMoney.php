<?php

namespace Laxity7\Money;

use Laxity7\Money\Exceptions\UnacceptableCurrencyException;

final class FiatMoney extends Money
{
    /**
     * @param int|float|string $amount Amount of money
     * @param string|FiatCurrency $currency Currency
     */
    public function __construct(float $amount, $currency)
    {
        if (is_string($currency)) {
            $currency = new FiatCurrency($currency);
        } elseif (!$currency instanceof FiatCurrency) {
            throw new UnacceptableCurrencyException(sprintf('"%s" is not fiat currency', $currency));
        }

        if (!MoneyConfig::isCheckAcceptable() && !$currency->isAcceptable()) {
            throw new UnacceptableCurrencyException(sprintf('"%s" is an unacceptable currency', $currency->getCode()));
        }

        parent::__construct($amount, $currency);
    }
}
