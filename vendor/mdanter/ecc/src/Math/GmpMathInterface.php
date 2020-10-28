<?php

namespace Mdanter\Ecc\Math;

interface GmpMathInterface
{
    /**
     * Compares two numbers
     *
     * @param  \GMP $first
     * @param  \GMP $other
     * @return int        less than 0 if first is less than second, 0 if equal, greater than 0 if greater than.
     */
    public function cmp(\GMP $first, \GMP $other): int;

    /**
     * @param \GMP $first
     * @param \GMP $other
     * @return bool
     */
    public function equals(\GMP $first, \GMP $other): bool;
    
    /**
     * Returns the remainder of a division
     *
     * @param  \GMP $number
     * @param  \GMP $modulus
     * @return \GMP
     */
    public function mod(\GMP $number, \GMP $modulus): \GMP;

    /**
     * Adds two numbers
     *
     * @param  \GMP $augend
     * @param  \GMP $addend
     * @return \GMP
     */
    public function add(\GMP $augend, \GMP $addend): \GMP;

    /**
     * Substract one number from another
     *
     * @param  \GMP $minuend
     * @param  \GMP $subtrahend
     * @return \GMP
     */
    public function sub(\GMP $minuend, \GMP $subtrahend): \GMP;

    /**
     * Multiplies a number by another.
     *
     * @param  \GMP $multiplier
     * @param  \GMP $multiplicand
     * @return \GMP
     */
    public function mul(\GMP $multiplier, \GMP $multiplicand): \GMP;

    /**
     * Divides a number by another.
     *
     * @param  \GMP $dividend
     * @param  \GMP $divisor
     * @return \GMP
     */
    public function div(\GMP $dividend, \GMP $divisor): \GMP;

    /**
     * Raises a number to a power.
     *
     * @param  \GMP $base     The number to raise.
     * @param  int $exponent The power to raise the number to.
     * @return \GMP
     */
    public function pow(\GMP $base, int $exponent): \GMP;

    /**
     * Performs a logical AND between two values.
     *
     * @param  \GMP $first
     * @param  \GMP $other
     * @return \GMP
     */
    public function bitwiseAnd(\GMP $first, \GMP $other): \GMP;

    /**
     * Performs a logical XOR between two values.
     *
     * @param  \GMP $first
     * @param  \GMP $other
     * @return \GMP
     */
    public function bitwiseXor(\GMP $first, \GMP $other): \GMP;

    /**
     * Shifts bits to the right
     * @param \GMP        $number    Number to shift
     * @param int  $positions Number of positions to shift
     * @return \GMP
     */
    public function rightShift(\GMP $number, int $positions): \GMP;

    /**
     * Shifts bits to the left
     * @param \GMP       $number    Number to shift
     * @param int $positions Number of positions to shift
     * @return \GMP
     */
    public function leftShift(\GMP $number, int $positions): \GMP;

    /**
     * Returns the string representation of a returned value.
     *
     * @param \GMP $value
     * @return string
     */
    public function toString(\GMP $value): string;

    /**
     * Converts an hexadecimal string to decimal.
     *
     * @param  string $hexString
     * @return int|string
     */
    public function hexDec(string $hexString): string;

    /**
     * Converts a decimal string to hexadecimal.
     *
     * @param  int|string $decString
     * @return string
     */
    public function decHex(string $decString): string;

    /**
     * Calculates the modular exponent of a number.
     *
     * @param \GMP $base
     * @param \GMP $exponent
     * @param \GMP $modulus
     * @return \GMP
     */
    public function powmod(\GMP $base, \GMP $exponent, \GMP $modulus): \GMP;

    /**
     * Checks whether a number is a prime.
     *
     * @param  \GMP $n
     * @return boolean
     */
    public function isPrime(\GMP $n): bool;

    /**
     * Gets the next known prime that is greater than a given prime.
     *
     * @param  \GMP $currentPrime
     * @return \GMP
     */
    public function nextPrime(\GMP $currentPrime): \GMP;

    /**
     * @param \GMP $a
     * @param \GMP $m
     * @return \GMP
     */
    public function inverseMod(\GMP $a, \GMP $m): \GMP;

    /**
     * @param \GMP $a
     * @param \GMP $p
     * @return int
     */
    public function jacobi(\GMP $a, \GMP $p): int;

    /**
     * @param  \GMP $x
     * @return string
     */
    public function intToString(\GMP $x): string;

    /**
     * @param \GMP $x
     * @param int $byteSize
     * @return string
     */
    public function intToFixedSizeString(\GMP $x, int $byteSize): string;

    /**
     *
     * @param  int|string $s
     * @return \GMP
     */
    public function stringToInt(string $s): \GMP;

    /**
     *
     * @param  \GMP $m
     * @return \GMP
     */
    public function digestInteger(\GMP $m): \GMP;

    /**
     * @param  \GMP $a
     * @param  \GMP $m
     * @return \GMP
     */
    public function gcd2(\GMP $a, \GMP $m): \GMP;

    /**
     * @param string $value
     * @param int $fromBase
     * @param int $toBase
     * @return string
     */
    public function baseConvert(string $value, int $fromBase, int $toBase): string;

    /**
     * @return NumberTheory
     */
    public function getNumberTheory(): NumberTheory;

    /**
     * @param \GMP $modulus
     * @return ModularArithmetic
     */
    public function getModularArithmetic(\GMP $modulus): ModularArithmetic;
}
