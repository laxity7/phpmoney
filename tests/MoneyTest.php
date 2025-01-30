<?php

namespace Laxity7\Money\Test;

use InvalidArgumentException;
use Laxity7\Money\Money;
use Laxity7\Money\Test\Stubs\MoneyConfigStub;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertSame;

/**
 * @covers Money
 */
final class MoneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        MoneyConfigStub::configure();
    }

    /**
     * @covers Money::__construct()
     */
    public function test__construct(): void
    {
        $money = new Money(1.0, 'USD');
        self::assertInstanceOf(Money::class, $money);
        self::assertSame('1', $money->getAmount());

        $money = new Money(1, 'USDT');
        self::assertSame('1', $money->getAmount());

        $money = new Money(100.0999999, 'ton');
        self::assertSame('100.0999999', $money->getAmount());
        self::assertEquals('TON', $money->getCurrency());

        $money = new Money(0.00000001, 'BTC');
        self::assertSame('0.00000001', $money->getAmount());

        $money = new Money(100.099000, 'ton');
        self::assertSame('100.099', $money->getAmount());

        $money = new Money(1000, 'ton');
        self::assertSame('1000', $money->getAmount());
    }

    /**
     * @covers Money::isSameCurrency()
     */
    public function testIsSameCurrency(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        self::assertTrue($money->isSameCurrency($other));

        $other = new Money(1.0, 'USDT');
        self::assertFalse($money->isSameCurrency($other));
    }

    /**
     * @covers Money::isEquals()
     */
    public function testIsEquals(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        self::assertTrue($money->isEquals($other));

        $other = new Money(1.0, 'USDT');
        self::assertFalse($money->isEquals($other));

        $other = new Money(2.0, 'USD');
        self::assertFalse($money->isEquals($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999999, 'BTC');
        self::assertTrue($money->isEquals($other));
    }

    /**
     * @covers Money::isNotEquals()
     */
    public function testIsNotEquals(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        self::assertFalse($money->isNotEquals($other));

        $other = new Money(1.0, 'USDT');
        self::assertTrue($money->isNotEquals($other));

        $other = new Money(2.0, 'USD');
        self::assertTrue($money->isNotEquals($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999998, 'BTC');
        self::assertTrue($money->isNotEquals($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999999, 'BTC');
        self::assertFalse($money->isNotEquals($other));
    }

    /**
     * @covers Money::isGreaterThan()
     */
    public function testIsGreaterThan(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        self::assertFalse($money->isGreaterThan($other));

        $other = new Money(1.0, 'USDT');
        self::assertFalse($money->isGreaterThan($other));

        $other = new Money(2.0, 'USD');
        self::assertFalse($money->isGreaterThan($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999998, 'BTC');
        self::assertTrue($money->isGreaterThan($other));
    }

    /**
     * @covers Money::isLessThan()
     */
    public function testIsLessThan(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        self::assertFalse($money->isLessThan($other));

        $other = new Money(1.0, 'USDT');
        self::assertFalse($money->isLessThan($other));

        $other = new Money(2.0, 'USD');
        self::assertTrue($money->isLessThan($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999998, 'BTC');
        self::assertFalse($money->isLessThan($other));
    }

    /**
     * @covers Money::isGreaterThanOrEquals()
     */
    public function testIsGreaterThanOrEquals(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        self::assertTrue($money->isGreaterThanOrEquals($other));

        $other = new Money(1.0, 'USDT');
        self::assertFalse($money->isGreaterThanOrEquals($other));

        $other = new Money(2.0, 'USD');
        self::assertFalse($money->isGreaterThanOrEquals($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999998, 'BTC');
        self::assertTrue($money->isGreaterThanOrEquals($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999999, 'BTC');
        self::assertTrue($money->isGreaterThanOrEquals($other));
    }

    /**
     * @covers Money::isLessThanOrEquals()
     */
    public function testIsLessThanOrEquals(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        self::assertTrue($money->isLessThanOrEquals($other));

        $other = new Money(1.0, 'USDT');
        self::assertFalse($money->isLessThanOrEquals($other));

        $other = new Money(2.0, 'USD');
        self::assertTrue($money->isLessThanOrEquals($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999998, 'BTC');
        self::assertFalse($money->isLessThanOrEquals($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999999, 'BTC');
        self::assertTrue($money->isLessThanOrEquals($other));
    }

    /**
     * @covers Money::add()
     */
    public function testAdd(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        $result = $money->add($other);
        self::assertSame('2', $result->getAmount());

        $money = new Money('0.00000001', 'BTC');
        $other = new Money('999.99999998', 'BTC');
        $result = $money->add($other);
        self::assertSame('999.99999999', $result->getAmount());

        $other = new Money(1.0, 'USDT');
        $this->expectException(InvalidArgumentException::class);
        $money->add($other);
    }

    /**
     * @covers Money::subtract()
     */
    public function testSubtract(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        $result = $money->subtract($other);
        self::assertSame('0', $result->getAmount());

        $money = new Money('999.99999999', 'BTC');
        $other = new Money('999.99999998', 'BTC');
        $result = $money->subtract($other);
        self::assertSame('0.00000001', $result->getAmount());

        $money = new Money(0.00000001, 'BTC');
        $other = new Money(0.00000001, 'BTC');
        $result = $money->subtract($other);
        self::assertSame('0', $result->getAmount());

        $other = new Money(1.0, 'USDT');
        $this->expectException(InvalidArgumentException::class);
        $money->subtract($other);
    }

    /**
     * @covers Money::multiply()
     */
    public function testMultiply(): void
    {
        $money = new Money(1.0, 'USD');
        $result = $money->multiply(9);
        self::assertSame('9', $result->getAmount());

        $money = new Money(1.44, 'USD');
        $result = $money->multiply(2);
        self::assertSame('2.88', $result->getAmount());

        $money = new Money(1.45, 'USD');
        $result = $money->multiply(2);
        self::assertSame('2.9', $result->getAmount());

        $money = new Money(1.455, 'USD');
        $result = $money->multiply(2);
        self::assertSame('2.92', $result->getAmount());

        $money = new Money(99.99, 'USD');
        $result = $money->multiply(1 / 99.99);
        self::assertSame('1', $result->getAmount());

        $money = new Money(99.99, 'BTC');
        $result = $money->multiply(1 / 99.99, Money::ROUND_HALF_DOWN);
        self::assertSame('1', $result->getAmount());

        $money = new Money(99.99, 'USD');
        $result = $money->multiply(1 / 99.99, Money::ROUND_HALF_DOWN);
        self::assertSame('1', $result->getAmount());

        $money = new Money(99.98, 'USD');
        $result = $money->multiply(1 / 99.99, Money::ROUND_HALF_DOWN);
        self::assertSame('1', $result->getAmount());

        $money = new Money(99.98, 'BTC');
        $result = $money->multiply(1 / 99.99, Money::ROUND_HALF_DOWN);
        self::assertSame('0.99989999', $result->getAmount());

        $money = new Money(-99.98, 'USD');
        $result = $money->multiply(1 / 99.99, Money::ROUND_HALF_DOWN);
        self::assertSame('-1', $result->getAmount());

        $money = new Money(99.99, 'USD');
        $result = $money->multiply(1 / 99.99);
        self::assertSame('1', $result->getAmount());

        $money = new Money(-99.99, 'USD');
        $result = $money->multiply(1 / 99.99);
        self::assertSame('-1', $result->getAmount());

        $money = new Money('999.99999999', 'BTC');
        $result = $money->multiply(2);
        self::assertSame('1999.99999998', $result->getAmount());

        $money = new Money('999.99999999', 'BTC');
        $result = $money->multiply(2, PHP_ROUND_HALF_UP);
        self::assertSame('1999.99999998', $result->getAmount());

        $money = new Money('999.99999999', 'BTC');
        $result = $money->multiply(2, PHP_ROUND_HALF_DOWN);
        self::assertSame('1999.99999998', $result->getAmount());

        $money = new Money('0.00000001', 'BTC');
        $result = $money->multiply(4);
        self::assertSame('0.00000004', $result->getAmount());

        $money = new Money('0.001', 'BTC');
        $result = $money->multiply(4.0004);
        self::assertSame('0.0040004', $result->getAmount());

        $money = new Money('4', 'BTC');
        $result = $money->multiply(0.00000001, Money::ROUND_HALF_DOWN);
        self::assertSame('0.00000004', $result->getAmount());

        $money = new Money('4.4', 'BTC');
        $result = $money->multiply(0.00000001, Money::ROUND_HALF_DOWN);
        self::assertSame('0.00000004', $result->getAmount());

        $money = new Money('4.5', 'BTC');
        $result = $money->multiply(0.0000001, Money::ROUND_HALF_DOWN);
        self::assertSame('0.00000045', $result->getAmount());

        $money = new Money('4.5', 'BTC');
        $result = $money->multiply(0.00000001, Money::ROUND_HALF_DOWN);
        self::assertSame('0.00000004', $result->getAmount());

        $money = new Money('4.5', 'BTC');
        $result = $money->multiply(0.00000001, Money::ROUND_HALF_DOWN);
        self::assertSame('0.00000004', $result->getAmount());

        $money = new Money('4.6', 'BTC');
        $result = $money->multiply(0.00000001, Money::ROUND_HALF_DOWN);
        self::assertSame('0.00000005', $result->getAmount());

        $money = new Money('4.6', 'ETH');
        $result = $money->multiply(0.00000001, Money::ROUND_HALF_DOWN);
        self::assertSame('0.000000046', $result->getAmount());

        $money = new Money('4.9', 'BTC');
        $result = $money->multiply(0.00000001, Money::ROUND_HALF_DOWN);
        self::assertSame('0.00000005', $result->getAmount());

        $money = new Money('4.9', 'BTC');
        $result = $money->multiply(0.00000001);
        self::assertSame('0.00000005', $result->getAmount());

        $money = new Money('965', 'BTC');
        $result = $money->multiply(1 / 3);
        self::assertSame('321.66666666', $result->getAmount());

        $money = new Money('965', 'BTC');
        $result = $money->multiply(1 / 3);
        self::assertSame('321.66666666', $result->getAmount());

        $money = new Money('965', 'ETH');
        $result = $money->multiply(1 / 3);
        self::assertSame('321.666666666666628771', $result->getAmount());

        $money = new Money('965', 'JPY');
        $result = $money->multiply(1 / 3, Money::ROUND_HALF_DOWN);
        self::assertSame('322', $result->getAmount());

        $money = new Money('965', 'JPY');
        $result = $money->multiply(1 / 3, Money::ROUND_HALF_UP);
        self::assertSame('322', $result->getAmount());

        $money = new Money('100', 'JPY');
        $result = $money->multiply(1 / 2.2, Money::ROUND_HALF_UP);
        self::assertSame('45', $result->getAmount());

        $money = new Money('100', 'JPY');
        $result = $money->multiply(1 / 2.31, Money::ROUND_HALF_UP);
        self::assertSame('43', $result->getAmount());

        $money = new Money('100', 'USD');
        $result = $money->multiply(1 / 2.31, Money::ROUND_HALF_UP);
        self::assertSame('43.29', $result->getAmount());

        $money = new Money('965', 'USDT');
        $result = $money->multiply(1 / 3, Money::ROUND_HALF_UP);
        self::assertSame('321.6666', $result->getAmount());

        $money = new Money('965', 'USDT');
        $result = $money->multiply(1 / 3, Money::ROUND_HALF_DOWN);
        self::assertSame('321.6666', $result->getAmount());
    }

    /**
     * @covers Money::divide()
     */
    public function testDivide(): void
    {
        $money = new Money(9.0, 'USD');
        $result = $money->divide(3);
        self::assertSame('3', $result->getAmount());

        $money = new Money('999.99999999', 'BTC');
        $result = $money->divide(2, Money::ROUND_HALF_UP);
        self::assertSame('500', $result->getAmount());

        $money = new Money('999.99999999', 'BTC');
        $result = $money->divide(2, Money::ROUND_HALF_DOWN);
        self::assertSame('499.99999999', $result->getAmount());

        $money = new Money('999.99999998', 'BTC');
        $result = $money->divide(2);
        self::assertSame('499.99999999', $result->getAmount());

        $money = new Money('0.00000004', 'BTC');
        $result = $money->divide(4);
        self::assertSame('0.00000001', $result->getAmount());

        $money = new Money('0.00000004', 'BTC');
        $result = $money->divide(4, Money::ROUND_HALF_DOWN);
        self::assertSame('0.00000001', $result->getAmount());

        $money = new Money('0.00000005', 'BTC');
        $result = $money->divide(2, Money::ROUND_HALF_DOWN);
        self::assertSame('0.00000002', $result->getAmount());

        $money = new Money('0.00000005', 'BTC');
        $result = $money->divide(2, Money::ROUND_HALF_UP);
        self::assertSame('0.00000003', $result->getAmount());

        $money = new Money('0.0000005', 'BTC');
        $result = $money->divide(2, Money::ROUND_HALF_UP);
        self::assertSame('0.00000025', $result->getAmount());

        $money = new Money('965', 'BTC');
        $result = $money->divide(1 / 3);
        self::assertSame('2895', $result->getAmount());

        $money = new Money('965', 'ETH');
        $result = $money->divide(3);
        self::assertSame('321.666666666666685614', $result->getAmount());

        $money = new Money('965', 'JPY');
        $result = $money->divide(3, Money::ROUND_HALF_DOWN);
        self::assertSame('322', $result->getAmount());

        $money = new Money('965', 'JPY');
        $result = $money->divide(3, Money::ROUND_HALF_UP);
        self::assertSame('322', $result->getAmount());
    }

    /**
     * @covers Money::isZero()
     */
    public function testIsZero(): void
    {
        $money = new Money(1.0, 'USD');
        self::assertFalse($money->isZero());

        $money = new Money(0.0, 'USD');
        self::assertTrue($money->isZero());

        $money = new Money(-1.0, 'USD');
        self::assertFalse($money->isZero());
    }

    /**
     * @covers Money::isPositive()
     */
    public function testIsPositive(): void
    {
        $money = new Money(1.0, 'USD');
        self::assertTrue($money->isPositive());

        $money = new Money(0.0, 'USD');
        self::assertFalse($money->isPositive());

        $money = new Money(-1.0, 'USD');
        self::assertFalse($money->isPositive());
    }

    /**
     * @covers Money::isNegative()
     */
    public function testIsNegative(): void
    {
        $money = new Money(1.0, 'USD');
        self::assertFalse($money->isNegative());

        $money = new Money(0.0, 'USD');
        self::assertFalse($money->isNegative());

        $money = new Money(-1.0, 'USD');
        self::assertTrue($money->isNegative());
    }

    /**
     * @covers Money::getAmount()
     */
    public function testGetAmount(): void
    {
        $money = new Money(0.0, 'USD');
        self::assertSame('0', $money->getAmount());

        $money = new Money(0.0, 'USD');
        self::assertSame('0.00', $money->getAmount(false));

        $money = new Money(1.0, 'USD');
        self::assertSame('1', $money->getAmount());

        $money = new Money(100.0, 'USD');
        self::assertSame('100', $money->getAmount());

        $money = new Money(100.0, 'USD');
        self::assertSame('100.00', $money->getAmount(false));

        $money = new Money(0.0, 'USD');
        self::assertSame('0', $money->getAmount());

        $money = new Money(-10.0, 'USD');
        self::assertSame('-10', $money->getAmount());

        $money = new Money(10.00000001, 'USD');
        self::assertSame('10', $money->getAmount());

        $money = new Money(10.00000009, 'USD');
        self::assertSame('10', $money->getAmount());

        $money = new Money(10.0000001, 'BTC');
        self::assertSame('10.0000001', $money->getAmount());

        $money = new Money(10.00100, 'BTC');
        self::assertSame('10.00100000', $money->getAmount(false));

        $money = new Money(00.00000, 'BTC');
        self::assertSame('0.00000000', $money->getAmount(false));

        $money = new Money(10.0090000, 'BTC');
        self::assertSame('10.009', $money->getAmount());

        $money = new Money(10000, 'JPY');
        self::assertSame('10000', $money->getAmount());

        $money = new Money(10000.40, 'JPY');
        self::assertSame('10000', $money->getAmount());

        $money = new Money(10000.50, 'JPY');
        self::assertSame('10001', $money->getAmount());
    }

    /**
     * @covers Money::toString()
     */
    public function testToString(): void
    {
        $money = new Money(0.00000001, 'USD');
        self::assertSame('0 USD', $money->toString());

        $money = new Money(10.04000001, 'USD');
        self::assertSame('10.04 USD', $money->toString());

        $money = new Money(10.04000009, 'USD');
        self::assertSame('10.04 USD', $money->toString());

        $money = new Money(0.0, 'USDT');
        self::assertSame('0 USDT', $money->toString());

        $money = new Money(999.99999999, 'btc');
        self::assertSame('999.99999999 BTC', $money->toString());

        $money = new Money(1.012, 'TON');
        self::assertSame('1.012 TON', (string)$money);
    }

    /**
     * @covers Money::jsonSerialize()
     */
    public function testJsonSerialize(): void
    {
        $money = new Money(1.0, 'usd');
        self::assertSame(['amount' => '1', 'currency' => 'USD'], $money->jsonSerialize());
        assertSame('{"amount":"1","currency":"USD"}', json_encode($money));
    }
}
