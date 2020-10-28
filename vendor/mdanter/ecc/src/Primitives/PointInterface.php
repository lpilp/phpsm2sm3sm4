<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Primitives;

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
 * This is the contract for implementing Point, which encapsulates entities
 * and operations over the points on the Elliptic Curve. Implementations must be immutable.
 *
 * Implementors must be wary of the special "Infinity" implementation, which breaks LSP, and should always
 * be checked against (and properly handled) when receiving a PointInterface as an argument or when a method indicates it can
 * return infinity.
 *
 * @todo Fix LSP break (possibly derive an extra interface, FinitePointInterface from current one, and move
 * coordinate-related ops to sub-interface).
 */
interface PointInterface
{

    /**
     * Returns true if instance is an non-finite point.
     */
    public function isInfinity(): bool;

    /**
     * Adds another point to the current one and returns the resulting point.
     *
     * @param  PointInterface $addend
     * @return PointInterface
     */
    public function add(PointInterface $addend): PointInterface;

    /**
     * Compares the current instance to another point.
     *
     * @param  PointInterface $other
     * @return int            A number different than 0 when current instance is less than the given point, 0 when they are equal.
     */
    public function cmp(PointInterface $other): int;

    /**
     * Checks whether the current instance is equal to the given point.
     *
     * @param  PointInterface $other
     * @return bool                    true when points are equal, false otherwise.
     */
    public function equals(PointInterface $other): bool;

    /**
     * Multiplies the point by a scalar value and returns the resulting point.
     *
     * @param  \GMP $multiplier
     * @return PointInterface
     */
    public function mul(\GMP $multiplier): PointInterface;

    /**
     * Returns the curve to which the point belongs.
     *
     * @return CurveFpInterface
     */
    public function getCurve(): CurveFpInterface;

    /**
     * Doubles the current point and returns the resulting point.
     *
     * @return PointInterface
     */
    public function getDouble(): PointInterface;

    /**
     * Returns the order of the point.
     *
     * @return \GMP
     */
    public function getOrder(): \GMP;

    /**
     * Returns the X coordinate of the point.
     *
     * @return \GMP
     */
    public function getX(): \GMP;

    /**
     * Returns the Y coordinate of the point.
     *
     * @return \GMP
     */
    public function getY(): \GMP;

    /**
     * Returns the string representation of the point.
     *
     * @return string
     */
    public function __toString(): string;
}
