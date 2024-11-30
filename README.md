# PHP Money

PHP library to make working with money with fiat and cryptocurrencies safer and easier!

[![License](https://img.shields.io/github/license/laxity7/phpmoney.svg)](https://github.com/laxity7/phpmoney/blob/master/LICENSE)
[![Latest Stable Version](https://img.shields.io/packagist/v/laxity7/phpmoney.svg)](https://packagist.org/packages/laxity7/phpmoney)
[![Total Downloads](https://img.shields.io/packagist/dt/laxity7/phpmoney.svg)](https://packagist.org/packages/laxity7/phpmoney)

## Install

Install via composer

```shell
composer require laxity7/phpmoney
```

## Requirements

This library requires the [BCMath PHP extension](https://www.php.net/manual/en/book.bc.php).

Version 1.0.0 requires PHP 8.1+. For older version of PHP, use version 0.0.1 of this library, which requires PHP 7.4.

## Features

- Fiat and cryptocurrency support
- JSON Serialization
- String representation
- Transparent calculation logic upon availability
- Money formatting
- Currency repositories (ISO currencies and TOP100 cryptocurrencies included)

## Usage

Basic usage example:

```php
use Laxity7\Money\Money;
use Laxity7\Money\Currency;

$tenEur = new Money(10.50, 'EUR');
//$tenEur = new Money(10.50, new Currency('EUR')); // the same as above
$twentyOneEur = $tenEur->add($tenEur);
echo $twentyOneEur->getAmount(); // 21
echo $twentyOneEur->getCurrency(); // EUR
echo $twentyOneEur; // 21 EUR
echo json_encode($twentyOneEur); // {"amount":"21","currency":"EUR"}

$btc = new Money(0.00000001, 'BTC');
$eth = new Money(1.01, 'USDT');
$sum = $btc->add($eth); // throws \Laxity7\Money\Exceptions\InvalidArgumentException
```

Configuration example:

```php
use Laxity7\Money\Money;
use Laxity7\Money\MoneyConfig;

// You can configure your own currencies only once. You can't change it later.
// Scale is the number of decimal places in the currency (e.g. 2 for USD, 8 for BTC). Max is 14.
MoneyConfig::configure(
    new Currencies([
        ['name' => 'US Dollar', 'symbol' => 'USD', 'scale' => 2],
        ['name' => 'Euro', 'symbol' => 'EUR', 'scale' => 4],
        ['name' => 'MyCoin', 'symbol' => 'MC', 'scale' => 8],
   ]
);


//...
$tenEur = new Money(10.50, 'USD'); // ok
$tenMyCoin = new Money(10.50, 'MyCoin'); // ok
$tenBtc = new Money(10.50, 'BTC'); // throws \Laxity7\Money\Exceptions\UnacceptableCurrencyException
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
