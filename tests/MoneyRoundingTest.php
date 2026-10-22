<?php

declare(strict_types=1);

namespace Laxity7\Money\Test;

use Laxity7\Money\Money;
use Laxity7\Money\Test\Stubs\MoneyConfigStub;
use PHPUnit\Framework\TestCase;

/**
 * Rounding contract of Money::multiply() / Money::divide():
 *  - result is rounded to the currency scale based on the exact (infinite precision) value;
 *  - ROUND_HALF_UP rounds an exact half away from zero (symmetric for negatives: -0.005 => -0.01);
 *  - ROUND_HALF_DOWN rounds an exact half toward zero (-0.005 => 0);
 *  - anything strictly below/above a half goes to the nearest unit in both modes.
 *
 * @covers Money
 */
final class MoneyRoundingTest extends TestCase
{
    private const SEED = 20240611;
    private const PAIRS = 5000;
    private const REF_SCALE = 40;

    protected function setUp(): void
    {
        parent::setUp();
        MoneyConfigStub::configure();
    }

    /**
     * @return iterable<string, array{non-empty-string, numeric-string, numeric-string, numeric-string, numeric-string}>
     */
    public static function multiplyProvider(): iterable
    {
        // [currency, amount, multiplier, expected half-up, expected half-down]
        // Regression cases from the CRM (EUR, scale 2)
        yield 'crm 19232.78*1.019007' => ['EUR', '19232.78', '1.019007', '19598.34', '19598.34'];
        yield 'crm 6109.5*1.141569' => ['EUR', '6109.5', '1.141569', '6974.42', '6974.42'];
        yield 'crm 212.16*1.141668' => ['EUR', '212.16', '1.141668', '242.22', '242.22'];
        yield 'crm 2455.71*1.028214' => ['EUR', '2455.71', '1.028214', '2525', '2525'];
        yield 'crm 1*0.0151' => ['EUR', '1', '0.0151', '0.02', '0.02'];
        yield 'crm 1*0.0051' => ['EUR', '1', '0.0051', '0.01', '0.01'];

        // Exact .5 boundary, short results (fewer fractional digits than scale+2)
        yield 'exact half 1*0.005' => ['EUR', '1', '0.005', '0.01', '0'];
        yield 'exact half 1*0.015' => ['EUR', '1', '0.015', '0.02', '0.01'];
        yield 'exact half 1*0.025' => ['EUR', '1', '0.025', '0.03', '0.02'];
        yield 'exact half 0.5*0.01' => ['EUR', '0.5', '0.01', '0.01', '0'];
        yield 'exact half 5*0.5 scale0' => ['JPY', '5', '0.5', '3', '2'];
        yield 'exact half 1*0.5 scale0' => ['JPY', '1', '0.5', '1', '0'];
        yield 'exact half 3*0.5 scale0' => ['JPY', '3', '0.5', '2', '1'];

        // Just below / just above a half
        yield 'below half 1*0.004999' => ['EUR', '1', '0.004999', '0', '0'];
        yield 'above half 1*0.005001' => ['EUR', '1', '0.005001', '0.01', '0.01'];
        yield 'below half 1*0.00499999999999' => ['EUR', '1', '0.00499999999999', '0', '0'];
        yield 'above half 1*0.00500000000001' => ['EUR', '1', '0.00500000000001', '0.01', '0.01'];
        yield 'below half 1*0.4999 scale0' => ['JPY', '1', '0.4999', '0', '0'];
        yield 'above half 1*0.5001 scale0' => ['JPY', '1', '0.5001', '1', '1'];

        // Exact results, no rounding
        yield 'exact 10.25*2' => ['EUR', '10.25', '2', '20.5', '20.5'];
        yield 'exact 100*0.01' => ['EUR', '100', '0.01', '1', '1'];

        // Negative values: half away from zero for HALF_UP, toward zero for HALF_DOWN
        yield 'neg exact half' => ['EUR', '-1', '0.005', '-0.01', '0'];
        yield 'neg exact half 0.015' => ['EUR', '1', '-0.015', '-0.02', '-0.01'];
        yield 'neg crm' => ['EUR', '-19232.78', '1.019007', '-19598.34', '-19598.34'];
        yield 'neg below half' => ['EUR', '-1', '0.004999', '0', '0'];
        yield 'neg above half' => ['EUR', '-1', '0.005001', '-0.01', '-0.01'];
        yield 'neg both' => ['EUR', '-1', '-0.005', '0.01', '0'];
        yield 'neg scale0' => ['JPY', '-5', '0.5', '-3', '-2'];

        // Product has more fractional digits than the internal working scale
        yield 'btc tail beyond 15 digits' => ['BTC', '0.00000001', '0.50000001', '0.00000001', '0.00000001'];
        yield 'eth tail beyond 15 digits' => ['ETH', '0.000000001', '0.500000000001', '0.000000001', '0.000000001'];
        yield 'eur tail beyond 15 digits' => ['EUR', '0.01', '0.50000000000001', '0.01', '0.01'];

        // Zero
        yield 'zero amount' => ['EUR', '0', '1.234567', '0', '0'];
        yield 'zero multiplier' => ['EUR', '12.34', '0', '0', '0'];
        yield 'tiny rounds to zero' => ['EUR', '0.01', '0.1', '0', '0'];

        // Scale 8 (BTC), scale 9 (ETH)
        yield 'btc exact half' => ['BTC', '0.00000001', '0.5', '0.00000001', '0'];
        yield 'btc above half' => ['BTC', '0.00000001', '0.50000001', '0.00000001', '0.00000001'];
        yield 'btc below half' => ['BTC', '0.00000001', '0.49999999', '0', '0'];
        yield 'btc 1.23456789*1.5' => ['BTC', '1.23456789', '1.5', '1.85185184', '1.85185183'];
        yield 'btc neg' => ['BTC', '-0.00000003', '0.5', '-0.00000002', '-0.00000001'];
        yield 'eth exact half' => ['ETH', '0.000000001', '0.5', '0.000000001', '0'];
        yield 'eth 1.123456789*3.3' => ['ETH', '1.123456789', '3.3', '3.707407404', '3.707407404'];
    }

    /**
     * @dataProvider multiplyProvider
     * @param non-empty-string $currency
     * @param numeric-string $amount
     * @param numeric-string $multiplier
     */
    public function testMultiply(string $currency, string $amount, string $multiplier, string $halfUp, string $halfDown): void
    {
        $money = new Money($amount, $currency);

        self::assertSame($halfUp, $money->multiply($multiplier)->getAmount(), 'default mode');
        self::assertSame($halfUp, $money->multiply($multiplier, Money::ROUND_HALF_UP)->getAmount(), 'half up');
        self::assertSame($halfDown, $money->multiply($multiplier, Money::ROUND_HALF_DOWN)->getAmount(), 'half down');
    }

    public function testMultiplyWithFloatMultiplier(): void
    {
        self::assertSame('19598.34', (new Money(19232.78, 'EUR'))->multiply(1.019007)->getAmount());
        self::assertSame('6974.42', (new Money(6109.5, 'EUR'))->multiply(1.141569)->getAmount());
        self::assertSame('242.22', (new Money(212.16, 'EUR'))->multiply(1.141668)->getAmount());
        self::assertSame('2525', (new Money(2455.71, 'EUR'))->multiply(1.028214)->getAmount());
        self::assertSame('0.02', (new Money(1, 'EUR'))->multiply(0.0151)->getAmount());
        self::assertSame('0.01', (new Money(1, 'EUR'))->multiply(0.0051)->getAmount());
    }

    /**
     * @return iterable<string, array{non-empty-string, numeric-string, numeric-string, numeric-string, numeric-string}>
     */
    public static function divideProvider(): iterable
    {
        // [currency, amount, divisor, expected half-up, expected half-down]
        yield 'exact half 1/200' => ['EUR', '1', '200', '0.01', '0'];
        yield 'exact half 3/200' => ['EUR', '3', '200', '0.02', '0.01'];
        yield 'exact half 1/2 scale0' => ['JPY', '1', '2', '1', '0'];
        yield 'exact half 5/2 scale0' => ['JPY', '5', '2', '3', '2'];
        yield 'below half 1/201' => ['EUR', '1', '201', '0', '0'];
        yield 'above half 1/199' => ['EUR', '1', '199', '0.01', '0.01'];
        yield 'repeating 10/3' => ['EUR', '10', '3', '3.33', '3.33'];
        yield 'repeating 20/3' => ['EUR', '20', '3', '6.67', '6.67'];
        yield 'repeating 2/3 scale0' => ['JPY', '2', '3', '1', '1'];
        yield 'short result 1/8' => ['EUR', '1', '8', '0.13', '0.12'];
        yield 'short result 1/16' => ['EUR', '1', '16', '0.06', '0.06'];
        yield 'exact 100/8' => ['EUR', '100', '8', '12.5', '12.5'];
        yield 'divide by fraction' => ['EUR', '1', '0.0151', '66.23', '66.23'];
        yield 'divide by fraction 2' => ['EUR', '85.86', '1.019007', '84.26', '84.26'];
        yield 'neg exact half' => ['EUR', '-1', '200', '-0.01', '0'];
        yield 'neg exact half negative divisor' => ['EUR', '1', '-200', '-0.01', '0'];
        yield 'neg both' => ['EUR', '-3', '-200', '0.02', '0.01'];
        yield 'neg repeating' => ['EUR', '-10', '3', '-3.33', '-3.33'];
        yield 'neg scale0' => ['JPY', '-5', '2', '-3', '-2'];
        yield 'inexact quotient just above half' => ['EUR', '1', '199.99999999999999', '0.01', '0.01'];
        yield 'inexact quotient tail beyond scale+1' => ['BTC', '0.00000001', '1.99999999999999', '0.00000001', '0.00000001'];
        yield 'zero amount' => ['EUR', '0', '7', '0', '0'];
        yield 'btc exact half' => ['BTC', '0.00000001', '2', '0.00000001', '0'];
        yield 'btc repeating' => ['BTC', '1', '3', '0.33333333', '0.33333333'];
        yield 'btc 2/3' => ['BTC', '2', '3', '0.66666667', '0.66666667'];
        yield 'eth exact half' => ['ETH', '0.000000003', '2', '0.000000002', '0.000000001'];
    }

    /**
     * @dataProvider divideProvider
     * @param non-empty-string $currency
     * @param numeric-string $amount
     * @param numeric-string $divisor
     */
    public function testDivide(string $currency, string $amount, string $divisor, string $halfUp, string $halfDown): void
    {
        $money = new Money($amount, $currency);

        self::assertSame($halfUp, $money->divide($divisor)->getAmount(), 'default mode');
        self::assertSame($halfUp, $money->divide($divisor, Money::ROUND_HALF_UP)->getAmount(), 'half up');
        self::assertSame($halfDown, $money->divide($divisor, Money::ROUND_HALF_DOWN)->getAmount(), 'half down');
    }

    /**
     * @return iterable<string, array{non-empty-string, int}>
     */
    public static function randomScaleProvider(): iterable
    {
        yield 'EUR scale 2' => ['EUR', 2];
        yield 'JPY scale 0' => ['JPY', 0];
        yield 'BTC scale 8' => ['BTC', 8];
        yield 'ETH scale 9' => ['ETH', 9];
    }

    /**
     * @dataProvider randomScaleProvider
     * @param non-empty-string $currency
     */
    public function testMultiplyMatchesBcmathReference(string $currency, int $scale): void
    {
        mt_srand(self::SEED + $scale);

        for ($i = 0; $i < self::PAIRS; $i++) {
            $amount = self::randomDecimal($scale, 10);
            $factor = self::randomDecimal(mt_rand(0, 8), 3);
            $product = bcmul($amount, $factor, self::REF_SCALE);

            foreach ([Money::ROUND_HALF_UP, Money::ROUND_HALF_DOWN] as $mode) {
                self::assertSame(
                    self::normalize(self::referenceRound($product, $scale, $mode)),
                    (new Money($amount, $currency))->multiply($factor, $mode)->getAmount(),
                    "$amount * $factor ($currency, mode $mode)"
                );
            }
        }
    }

    /**
     * @dataProvider randomScaleProvider
     * @param non-empty-string $currency
     */
    public function testDivideMatchesBcmathReference(string $currency, int $scale): void
    {
        mt_srand(self::SEED * 2 + $scale);

        for ($i = 0; $i < self::PAIRS; $i++) {
            $amount = self::randomDecimal($scale, 10);
            $divisor = self::randomDecimal(mt_rand(0, 6), 3);
            if (bccomp($divisor, '0', 10) === 0) {
                continue;
            }
            $quotient = bcdiv($amount, $divisor, self::REF_SCALE);

            foreach ([Money::ROUND_HALF_UP, Money::ROUND_HALF_DOWN] as $mode) {
                self::assertSame(
                    self::normalize(self::referenceRound($quotient, $scale, $mode)),
                    (new Money($amount, $currency))->divide($divisor, $mode)->getAmount(),
                    "$amount / $divisor ($currency, mode $mode)"
                );
            }
        }
    }

    /**
     * Strings with more than 15 significant digits must not be corrupted by a float round trip.
     */
    public function testLongStringAmountsKeepPrecision(): void
    {
        self::assertSame('12345678901234567890.12', (new Money('12345678901234567890.12', 'EUR'))->getAmount());
        self::assertSame('12345678901234567890.13', (new Money('12345678901234567890.125', 'EUR'))->getAmount());
        self::assertSame('-12345678901234567890.13', (new Money('-12345678901234567890.125', 'EUR'))->getAmount());
        self::assertSame('99999999999999999999.99', (new Money('99999999999999999999.994', 'EUR'))->getAmount());
        self::assertSame('100000000000000000000', (new Money('99999999999999999999.995', 'EUR'))->getAmount());
        self::assertSame(
            '12345678901234567890.12',
            (new Money('12345678901234567890.12', 'EUR'))->multiply('1')->getAmount()
        );
        self::assertSame(
            '24691357802469135780.24',
            (new Money('12345678901234567890.12', 'EUR'))->multiply('2')->getAmount()
        );
    }

    /**
     * Scientific notation strings are converted exactly, not through a 15-digit float.
     */
    public function testScientificNotationString(): void
    {
        self::assertSame('1500', (new Money('1.5E+3', 'EUR'))->getAmount());
        self::assertSame('0.01', (new Money('1E-2', 'EUR'))->getAmount());
        self::assertSame('0.02', (new Money('1.5E-2', 'EUR'))->getAmount());
        self::assertSame('1234567890123456789.12', (new Money('1.23456789012345678912E+18', 'EUR'))->getAmount());
        self::assertSame('-0.01', (new Money('-5E-3', 'EUR'))->getAmount());
    }

    /**
     * @return numeric-string
     */
    private static function randomDecimal(int $fractionDigits, int $maxIntDigits): string
    {
        $intDigits = mt_rand(0, $maxIntDigits);
        $int = '';
        for ($i = 0; $i < $intDigits; $i++) {
            $int .= (string)mt_rand(0, 9);
        }
        $int = ltrim($int, '0');
        if ($int === '') {
            $int = '0';
        }

        $frac = '';
        for ($i = 0; $i < $fractionDigits; $i++) {
            $frac .= (string)mt_rand(0, 9);
        }

        $sign = mt_rand(0, 3) === 0 ? '-' : '';

        /** @var numeric-string $result */
        $result = $sign . $int . ($frac !== '' ? '.' . $frac : ''); // @phpstan-ignore varTag.nativeType

        return $result;
    }

    /**
     * Pure bcmath reference: rounds a high-precision value to $scale.
     *
     * @param numeric-string $value
     */
    private static function referenceRound(string $value, int $scale, int $mode): string
    {
        $negative = str_starts_with($value, '-');
        $abs = ltrim($value, '-');

        /** @var numeric-string $abs */
        $truncated = bcadd($abs, '0', $scale);
        $remainder = bcsub($abs, $truncated, self::REF_SCALE);
        /** @var numeric-string $half */
        $half = '0.' . str_repeat('0', $scale) . '5'; // @phpstan-ignore varTag.nativeType
        $cmp = bccomp($remainder, $half, self::REF_SCALE);

        if ($cmp === 1 || ($cmp === 0 && $mode === Money::ROUND_HALF_UP)) {
            /** @var numeric-string $unit */
            $unit = $scale === 0 ? '1' : '0.' . str_repeat('0', $scale - 1) . '1'; // @phpstan-ignore varTag.nativeType
            $truncated = bcadd($truncated, $unit, $scale);
        }

        return ($negative && bccomp($truncated, '0', $scale) !== 0 ? '-' : '') . $truncated;
    }

    private static function normalize(string $number): string
    {
        if (!str_contains($number, '.')) {
            return $number;
        }

        $number = rtrim(rtrim($number, '0'), '.');

        return $number === '-0' || $number === '' ? '0' : $number;
    }
}
