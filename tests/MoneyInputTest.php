<?php

declare(strict_types=1);

namespace Laxity7\Money\Test;

use Laxity7\Money\Exceptions\InvalidArgumentException;
use Laxity7\Money\Money;
use Laxity7\Money\Test\Stubs\MoneyConfigStub;
use PHPUnit\Framework\TestCase;

/**
 * @covers Money
 */
final class MoneyInputTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        MoneyConfigStub::configure();
    }

    public function testIntegerAmountKeepsFullPrecision(): void
    {
        self::assertSame('9223372036854775807', (new Money(PHP_INT_MAX, 'EUR'))->getAmount());
        self::assertSame('-9223372036854775808', (new Money(PHP_INT_MIN, 'EUR'))->getAmount());
        self::assertSame('9223372036854775807', (new Money(PHP_INT_MAX, 'JPY'))->getAmount());
        self::assertSame('18446744073709551614', (new Money(PHP_INT_MAX, 'EUR'))->multiply(2)->getAmount());
        self::assertSame('9223372036854775806', (new Money(PHP_INT_MAX - 1, 'EUR'))->multiply(1)->getAmount());
    }

    /**
     * @return iterable<string, array{float}>
     */
    public static function nonFiniteProvider(): iterable
    {
        yield 'INF' => [INF];
        yield '-INF' => [-INF];
        yield 'NAN' => [NAN];
    }

    /**
     * @dataProvider nonFiniteProvider
     */
    public function testNonFiniteAmountThrows(float $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Money($value, 'EUR');
    }

    /**
     * @dataProvider nonFiniteProvider
     */
    public function testNonFiniteMultiplierThrows(float $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new Money(1, 'EUR'))->multiply($value);
    }

    /**
     * @dataProvider nonFiniteProvider
     */
    public function testNonFiniteDivisorThrows(float $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new Money(1, 'EUR'))->divide($value);
    }

    public function testAllocateWithHugeRatios(): void
    {
        $parts = (new Money('10', 'EUR'))->allocate(PHP_INT_MAX, PHP_INT_MAX);

        self::assertSame('5', $parts[0]->getAmount());
        self::assertSame('5', $parts[1]->getAmount());
    }

    public function testSmallNegativeValuesAreZeroWithoutSign(): void
    {
        self::assertSame('0.00', (new Money('-0.001', 'EUR'))->getAmount(false));
        self::assertSame('0.00', (new Money(-0.001, 'EUR'))->getAmount(false));
        self::assertSame('0.00', (new Money(-1, 'EUR'))->multiply('0.001')->getAmount(false));
        self::assertSame('0.00', (new Money(-1, 'EUR'))->divide(1000)->getAmount(false));
        self::assertTrue((new Money('-0.001', 'EUR'))->isZero());
    }
}
