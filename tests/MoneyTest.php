<?php

namespace Laxity7\Money\Tests;

use InvalidArgumentException;
use Laxity7\Money\Money;
use Laxity7\Money\Test\Stubs\MoneyConfigStub;
use PHPUnit\Framework\TestCase;

/**
 * @link Money
 */
final class MoneyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        MoneyConfigStub::configure();
    }

    public function test__construct(): void
    {
        $money = new Money(1.0, 'USD');
        $this->assertInstanceOf(Money::class, $money);
        $this->assertSame('1', $money->getAmount());

        $money = new Money(1, 'USDT');
        $this->assertSame('1', $money->getAmount());

        $money = new Money(100.0999999, 'ton');
        $this->assertSame('100.0999999', $money->getAmount());
        $this->assertEquals('TON', $money->getCurrency());

        $money = new Money(0.00000001, 'BTC');
        $this->assertSame('0.00000001', $money->getAmount());

        $money = new Money(100.099000, 'ton');
        $this->assertSame('100.099', $money->getAmount());

        $money = new Money(1000, 'ton');
        $this->assertSame('1000', $money->getAmount());
    }

    public function testIsSameCurrency(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        $this->assertTrue($money->isSameCurrency($other));

        $other = new Money(1.0, 'USDT');
        $this->assertFalse($money->isSameCurrency($other));
    }

    public function testIsEquals(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        $this->assertTrue($money->isEquals($other));

        $other = new Money(1.0, 'USDT');
        $this->assertFalse($money->isEquals($other));

        $other = new Money(2.0, 'USD');
        $this->assertFalse($money->isEquals($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999999, 'BTC');
        $this->assertTrue($money->isEquals($other));
    }

    public function testIsNotEquals(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        $this->assertFalse($money->isNotEquals($other));

        $other = new Money(1.0, 'USDT');
        $this->assertTrue($money->isNotEquals($other));

        $other = new Money(2.0, 'USD');
        $this->assertTrue($money->isNotEquals($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999998, 'BTC');
        $this->assertTrue($money->isNotEquals($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999999, 'BTC');
        $this->assertFalse($money->isNotEquals($other));
    }

    public function testIsGreaterThan(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        $this->assertFalse($money->isGreaterThan($other));

        $other = new Money(1.0, 'USDT');
        $this->assertFalse($money->isGreaterThan($other));

        $other = new Money(2.0, 'USD');
        $this->assertFalse($money->isGreaterThan($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999998, 'BTC');
        $this->assertTrue($money->isGreaterThan($other));
    }

    public function testIsLessThan(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        $this->assertFalse($money->isLessThan($other));

        $other = new Money(1.0, 'USDT');
        $this->assertFalse($money->isLessThan($other));

        $other = new Money(2.0, 'USD');
        $this->assertTrue($money->isLessThan($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999998, 'BTC');
        $this->assertFalse($money->isLessThan($other));
    }

    public function testIsGreaterThanOrEquals(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        $this->assertTrue($money->isGreaterThanOrEquals($other));

        $other = new Money(1.0, 'USDT');
        $this->assertFalse($money->isGreaterThanOrEquals($other));

        $other = new Money(2.0, 'USD');
        $this->assertFalse($money->isGreaterThanOrEquals($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999998, 'BTC');
        $this->assertTrue($money->isGreaterThanOrEquals($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999999, 'BTC');
        $this->assertTrue($money->isGreaterThanOrEquals($other));
    }

    public function testIsLessThanOrEquals(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        $this->assertTrue($money->isLessThanOrEquals($other));

        $other = new Money(1.0, 'USDT');
        $this->assertFalse($money->isLessThanOrEquals($other));

        $other = new Money(2.0, 'USD');
        $this->assertTrue($money->isLessThanOrEquals($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999998, 'BTC');
        $this->assertFalse($money->isLessThanOrEquals($other));

        $money = new Money(999.99999999, 'BTC');
        $other = new Money(999.99999999, 'BTC');
        $this->assertTrue($money->isLessThanOrEquals($other));
    }

    public function testAdd(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        $result = $money->add($other);
        $this->assertSame('2', $result->getAmount());

        $money = new Money('0.00000001', 'BTC');
        $other = new Money('999.99999998', 'BTC');
        $result = $money->add($other);
        $this->assertSame('999.99999999', $result->getAmount());

        $other = new Money(1.0, 'USDT');
        $this->expectException(InvalidArgumentException::class);
        $money->add($other);
    }

    /**
     * @depends testAdd
     */
    public function testPlus(): void
    {
        $this->assertTrue(true);
    }

    public function testSubtract(): void
    {
        $money = new Money(1.0, 'USD');
        $other = new Money(1.0, 'USD');
        $result = $money->subtract($other);
        $this->assertSame('0', $result->getAmount());

        $money = new Money('999.99999999', 'BTC');
        $other = new Money('999.99999998', 'BTC');
        $result = $money->subtract($other);
        $this->assertSame('0.00000001', $result->getAmount());

        $money = new Money(0.00000001, 'BTC');
        $other = new Money(0.00000001, 'BTC');
        $result = $money->subtract($other);
        $this->assertSame('0', $result->getAmount());

        $other = new Money(1.0, 'USDT');
        $this->expectException(InvalidArgumentException::class);
        $money->subtract($other);
    }

    /**
     * @depends testSubtract
     */
    public function testMinus(): void
    {
        $this->assertTrue(true);
    }

    public function testMultiply(): void
    {
        $money = new Money(1.0, 'USD');
        $result = $money->multiply(9);
        $this->assertSame('9', $result->getAmount());

        $money = new Money(1.44, 'USD');
        $result = $money->multiply(2);
        $this->assertSame('2.88', $result->getAmount());

        $money = new Money(1.45, 'USD');
        $result = $money->multiply(2);
        $this->assertSame('2.9', $result->getAmount());

        $money = new Money(1.455, 'USD');
        $result = $money->multiply(2);
        $this->assertSame('2.92', $result->getAmount());

        $money = new Money(99.99, 'USD');
        $result = $money->multiply(1 / 99.99);
        $this->assertSame('1', $result->getAmount());

        $money = new Money(99.99, 'USD');
        $result = $money->multiply(1 / 99.99, Money::ROUND_HALF_DOWN);
        $this->assertSame('1', $result->getAmount());

        $money = new Money(99.98, 'USD');
        $result = $money->multiply(1 / 99.99, Money::ROUND_HALF_DOWN);
        $this->assertSame('0.99', $result->getAmount());

        $money = new Money(-99.98, 'USD');
        $result = $money->multiply(1 / 99.99, Money::ROUND_HALF_DOWN);
        $this->assertSame('-0.99', $result->getAmount());

        $money = new Money(99.99, 'USD');
        $result = $money->multiply(1 / 99.99);
        $this->assertSame('1', $result->getAmount());

        $money = new Money(-99.99, 'USD');
        $result = $money->multiply(1 / 99.99);
        $this->assertSame('-1', $result->getAmount());

        $money = new Money('999.99999999', 'BTC');
        $result = $money->multiply(2);
        $this->assertSame('1999.99999998', $result->getAmount());

        $money = new Money('999.99999999', 'BTC');
        $result = $money->multiply(2, PHP_ROUND_HALF_UP);
        $this->assertSame('1999.99999998', $result->getAmount());

        $money = new Money('999.99999999', 'BTC');
        $result = $money->multiply(2, PHP_ROUND_HALF_DOWN);
        $this->assertSame('1999.99999998', $result->getAmount());

        $money = new Money('0.00000001', 'BTC');
        $result = $money->multiply(4);
        $this->assertSame('0.00000004', $result->getAmount());

        $money = new Money('0.001', 'BTC');
        $result = $money->multiply(4.0004);
        $this->assertSame('0.0040004', $result->getAmount());

        $money = new Money('4', 'BTC');
        $result = $money->multiply(0.00000001, Money::ROUND_HALF_DOWN);
        $this->assertSame('0.00000004', $result->getAmount());

        $money = new Money('4.4', 'BTC');
        $result = $money->multiply(0.00000001, Money::ROUND_HALF_DOWN);
        $this->assertSame('0.00000004', $result->getAmount());

        $money = new Money('4.5', 'BTC');
        $result = $money->multiply(0.0000001, Money::ROUND_HALF_DOWN);
        $this->assertSame('0.00000045', $result->getAmount());

        $money = new Money('4.5', 'BTC');
        $result = $money->multiply(0.00000001, Money::ROUND_HALF_DOWN);
        $this->assertSame('0.00000004', $result->getAmount());

        $money = new Money('4.5', 'BTC');
        $result = $money->multiply(0.00000001, Money::ROUND_HALF_DOWN);
        $this->assertSame('0.00000004', $result->getAmount());

        $money = new Money('4.6', 'BTC');
        $result = $money->multiply(0.00000001, Money::ROUND_HALF_DOWN);
        $this->assertSame('0.00000005', $result->getAmount());

        $money = new Money('4.9', 'BTC');
        $result = $money->multiply(0.00000001, Money::ROUND_HALF_DOWN);
        $this->assertSame('0.00000005', $result->getAmount());

        $money = new Money('4.9', 'BTC');
        $result = $money->multiply(0.00000001);
        $this->assertSame('0.00000005', $result->getAmount());
    }

    public function testDivide(): void
    {
        $money = new Money(9.0, 'USD');
        $result = $money->divide(3);
        $this->assertSame('3', $result->getAmount());

        $money = new Money('999.99999999', 'BTC');
        $result = $money->divide(2);
        $this->assertSame('500', $result->getAmount());

        $money = new Money('999.99999999', 'BTC');
        $result = $money->divide(2, Money::ROUND_HALF_DOWN);
        $this->assertSame('499.99999999', $result->getAmount());

        $money = new Money('999.99999998', 'BTC');
        $result = $money->divide(2);
        $this->assertSame('499.99999999', $result->getAmount());

        $money = new Money('0.00000004', 'BTC');
        $result = $money->divide(4);
        $this->assertSame('0.00000001', $result->getAmount());

        $money = new Money('0.00000004', 'BTC');
        $result = $money->divide(4, Money::ROUND_HALF_DOWN);
        $this->assertSame('0.00000001', $result->getAmount());

        $money = new Money('0.00000005', 'BTC');
        $result = $money->divide(2, Money::ROUND_HALF_DOWN);
        $this->assertSame('0.00000002', $result->getAmount());

        $money = new Money('0.00000005', 'BTC');
        $result = $money->divide(2, Money::ROUND_HALF_UP);
        $this->assertSame('0.00000003', $result->getAmount());

        $money = new Money('0.0000005', 'BTC');
        $result = $money->divide(2, Money::ROUND_HALF_UP);
        $this->assertSame('0.00000025', $result->getAmount());
    }

    public function testIsZero(): void
    {
        $money = new Money(1.0, 'USD');
        $this->assertFalse($money->isZero());

        $money = new Money(0.0, 'USD');
        $this->assertTrue($money->isZero());

        $money = new Money(-1.0, 'USD');
        $this->assertFalse($money->isZero());
    }

    public function testIsPositive(): void
    {
        $money = new Money(1.0, 'USD');
        $this->assertTrue($money->isPositive());

        $money = new Money(0.0, 'USD');
        $this->assertFalse($money->isPositive());

        $money = new Money(-1.0, 'USD');
        $this->assertFalse($money->isPositive());
    }

    public function testIsNegative(): void
    {
        $money = new Money(1.0, 'USD');
        $this->assertFalse($money->isNegative());

        $money = new Money(0.0, 'USD');
        $this->assertFalse($money->isNegative());

        $money = new Money(-1.0, 'USD');
        $this->assertTrue($money->isNegative());
    }

    public function testToString(): void
    {
        $money = new Money(0.00000001, 'USD');
        $this->assertSame('0 USD', $money->toString());

        $money = new Money(10.04000001, 'USD');
        $this->assertSame('10.04 USD', $money->toString());

        $money = new Money(10.04000009, 'USD');
        $this->assertSame('10.04 USD', $money->toString());

        $money = new Money(0.0, 'USDT');
        $this->assertSame('0 USDT', $money->toString());

        $money = new Money(999.99999999, 'btc');
        $this->assertSame('999.99999999 BTC', $money->toString());

        $money = new Money(1.012, 'TON');
        $this->assertSame('1.012 TON', (string)$money);
    }

    public function testJsonSerialize(): void
    {
        $money = new Money(1.0, 'usd');
        $this->assertSame(['amount' => '1', 'currency' => 'USD'], $money->jsonSerialize());
    }
}
