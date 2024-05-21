<?php

declare(strict_types=1);

namespace Laxity7\Money;

use Laxity7\Money\Exceptions\UnacceptableCurrencyException;

final class CryptoMoney extends Money
{
    /**
     * @param int|float|string $amount Сумма денег
     * @param string|CryptoCurrency $currency Валюта
     */
    public function __construct(float $amount, $currency)
    {
        if (is_string($currency)) {
            $currency = new CryptoCurrency($currency);
        } elseif (!$currency instanceof CryptoCurrency) {
            throw new UnacceptableCurrencyException(sprintf('"%s" is not crypto currency', $currency));
        }

        if (!MoneyConfig::isCheckAcceptable() && !$currency->isAcceptable()) {
            throw new UnacceptableCurrencyException(sprintf('"%s" is an unacceptable currency', $currency->getCode()));
        }

        parent::__construct($amount, $currency);
    }
}
