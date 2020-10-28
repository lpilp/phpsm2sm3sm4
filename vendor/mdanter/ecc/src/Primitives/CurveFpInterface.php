<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Primitives;

use Mdanter\Ecc\Math\ModularArithmetic;
use Mdanter\Ecc\Random\RandomNumberGeneratorInterface;

/**
 * *********************************************************************
 * Copyright (C) 2012 Matyas Danter
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES
 * OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 * ***********************************************************************
 */

/**
 * This is the contract for implementing CurveFp (EC prime finite-field).
 */
interface CurveFpInterface
{

    /**
     * Returns a modular arithmetic adapter.
     *
     * @return ModularArithmetic
     */
    public function getModAdapter(): ModularArithmetic;

    /**
     * Returns the point identified by given coordinates.
     *
     * @param  \GMP $x
     * @param  \GMP $y
     * @param  \GMP $order
     * @return PointInterface
     */
    public function getPoint(\GMP $x, \GMP $y, \GMP $order = null): PointInterface;

    /**
     * @param bool $wasOdd
     * @param \GMP $x
     * @return \GMP
     */
    public function recoverYfromX(bool $wasOdd, \GMP $x): \GMP;

    /**
     * Returns a point representing infinity on the curve.
     *
     * @return PointInterface
     */
    public function getInfinity(): PointInterface;

    /**
     *
     * @param  \GMP $x
     * @param  \GMP $y
     * @param  \GMP $order
     * @param  RandomNumberGeneratorInterface $randomGenerator
     * @return GeneratorPoint
     */
    public function getGenerator(\GMP $x, \GMP $y, \GMP $order, RandomNumberGeneratorInterface $randomGenerator = null): GeneratorPoint;

    /**
     * Checks whether the curve contains the given coordinates.
     *
     * @param  \GMP $x
     * @param  \GMP $y
     * @return bool
     */
    public function contains(\GMP $x, \GMP $y): bool;

    /**
     * Returns the a parameter of the curve.
     *
     * @return \GMP
     */
    public function getA(): \GMP;

    /**
     * Returns the b parameter of the curve.
     *
     * @return \GMP
     */
    public function getB(): \GMP;

    /**
     * Returns the prime associated with the curve.
     *
     * @return \GMP
     */
    public function getPrime(): \GMP;

    /**
     * @return int
     */
    public function getSize(): int;

    /**
     * Compares the curve to another.
     *
     * @param  CurveFpInterface $other
     * @return int              < 0 if $this < $other, 0 if $other == $this, > 0 if $this > $other
     */
    public function cmp(CurveFpInterface $other): int;

    /**
     * Checks whether the curve is equal to another.
     *
     * @param  CurveFpInterface $other
     * @return bool
     */
    public function equals(CurveFpInterface $other): bool;

    /**
     * Return string representation of curve for debugging
     *
     * @return string
     */
    public function __toString(): string;
}
