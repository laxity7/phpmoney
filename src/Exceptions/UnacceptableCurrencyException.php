<?php

declare(strict_types=1);

namespace Laxity7\Money\Exceptions;

use DomainException;

class UnacceptableCurrencyException extends DomainException implements MoneyException
{

}
