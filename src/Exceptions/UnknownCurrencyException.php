<?php

declare(strict_types=1);

namespace Laxity7\Money\Exceptions;

use DomainException;

/**
 * Thrown when trying to get currency that does not exist.
 */
final class UnknownCurrencyException extends DomainException implements MoneyException
{
}
