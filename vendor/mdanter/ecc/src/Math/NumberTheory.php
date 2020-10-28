<?php

namespace Mdanter\Ecc\Math;

/***********************************************************************
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
     ************************************************************************/

/**
 * Implementation of some number theoretic algorithms
 *
 * @author Matyas Danter
 */

use Mdanter\Ecc\Exception\NumberTheoryException;
use Mdanter\Ecc\Exception\SquareRootException;

/**
 * Rewritten to take a MathAdaptor to handle different environments. Has
 * some desireable functions for public key compression/recovery.
 */
class NumberTheory
{
    /**
     * @var GmpMathInterface
     */
    private $adapter;

    /**
     * @param GmpMathInterface $adapter
     */
    public function __construct(GmpMathInterface $adapter)
    {
        $this->adapter = $adapter;
        $this->zero = gmp_init(0, 10);
        $this->one = gmp_init(1, 10);
        $this->two = gmp_init(2, 10);
    }

    /**
     * @param \GMP[] $poly
     * @param \GMP[] $polymod
     * @param \GMP $p
     * @return \GMP[]
     */
    public function polynomialReduceMod(array $poly, array $polymod, \GMP $p): array
    {
        $adapter = $this->adapter;

        // Only enter if last value is set, implying count > 0
        if ((($last = end($polymod)) instanceof \GMP) && $adapter->equals($last, $this->one)) {
            $count_polymod = count($polymod);
            while (count($poly) >= $count_polymod) {
                if (!$adapter->equals(end($poly), $this->zero)) {
                    for ($i = 2; $i < $count_polymod + 1; $i++) {
                        $poly[count($poly) - $i] =
                            $adapter->mod(
                                $adapter->sub(
                                    $poly[count($poly) - $i],
                                    $adapter->mul(
                                        end($poly),
                                        $polymod[$count_polymod - $i]
                                    )
                                ),
                                $p
                            );
                    }
                }

                $poly = array_slice($poly, 0, count($poly) - 1);
            }

            return $poly;
        }

        throw new NumberTheoryException('Unable to calculate polynomialReduceMod');
    }

    /**
     * @param \GMP[] $m1
     * @param \GMP[] $m2
     * @param \GMP[] $polymod
     * @param \GMP $p
     * @return \GMP[]
     */
    public function polynomialMultiplyMod(array $m1, array $m2, array $polymod, \GMP $p): array
    {
        $prod = array();
        $cm1 = count($m1);
        $cm2 = count($m2);

        for ($i = 0; $i < $cm1; $i++) {
            for ($j = 0; $j < $cm2; $j++) {
                $index = $i + $j;
                if (!isset($prod[$index])) {
                    $prod[$index] = $this->zero;
                }
                $prod[$index] =
                    $this->adapter->mod(
                        $this->adapter->add(
                            $prod[$index],
                            $this->adapter->mul(
                                $m1[$i],
                                $m2[$j]
                            )
                        ),
                        $p
                    );
            }
        }

        return $this->polynomialReduceMod($prod, $polymod, $p);
    }

    /**
     * @param \GMP[] $base
     * @param \GMP $exponent
     * @param \GMP[] $polymod
     * @param \GMP $p
     * @return \GMP[]
     */
    public function polynomialPowMod(array $base, \GMP $exponent, array $polymod, \GMP $p): array
    {
        $adapter = $this->adapter;

        if ($adapter->cmp($exponent, $p) < 0) {
            if ($adapter->equals($exponent, $this->zero)) {
                return $this->one;
            }

            $G = $base;
            $k = $exponent;

            if ($adapter->equals($adapter->mod($k, $this->two), $this->one)) {
                $s = $G;
            } else {
                $s = array($this->one);
            }

            while ($adapter->cmp($k, $this->one) > 0) {
                $k = $adapter->div($k, $this->two);

                $G = $this->polynomialMultiplyMod($G, $G, $polymod, $p);
                if ($adapter->equals($adapter->mod($k, $this->two), $this->one)) {
                    $s = $this->polynomialMultiplyMod($G, $s, $polymod, $p);
                }
            }

            return $s;
        }

        throw new NumberTheoryException('Unable to calculate polynomialPowMod');
    }

    /**
     * @param \GMP $a
     * @param \GMP $p
     * @return \GMP
     */
    public function squareRootModP(\GMP $a, \GMP $p): \GMP
    {
        $math = $this->adapter;
        $four = gmp_init(4, 10);
        $eight = gmp_init(8, 10);

        $modMath = $math->getModularArithmetic($p);
        if ($math->cmp($this->one, $p) < 0) {
            if ($math->equals($a, $this->zero)) {
                return $this->zero;
            }

            if ($math->equals($p, $this->two)) {
                return $a;
            }

            $jac = $math->jacobi($a, $p);
            if ($jac === -1) {
                throw new SquareRootException("{$math->toString($a)} has no square root modulo {$math->toString($p)}");
            }

            if ($math->equals($math->mod($p, $four), gmp_init(3, 10))) {
                return $modMath->pow($a, $math->div($math->add($p, $this->one), $four));
            }

            if ($math->equals($math->mod($p, $eight), gmp_init(5, 10))) {
                $d = $modMath->pow($a, $math->div($math->sub($p, $this->one), $four));
                if ($math->equals($d, $this->one)) {
                    return $modMath->pow($a, $math->div($math->add($p, gmp_init(3, 10)), $eight));
                }

                if ($math->equals($d, $math->sub($p, $this->one))) {
                    return $modMath->mul(
                        $math->mul(
                            $this->two,
                            $a
                        ),
                        $modMath->pow(
                            $math->mul(
                                $four,
                                $a
                            ),
                            $math->div(
                                $math->sub(
                                    $p,
                                    gmp_init(5, 10)
                                ),
                                $eight
                            )
                        )
                    );
                }
                //shouldn't get here
            }

            for ($b = $this->two; $math->cmp($b, $p) < 0; $b = gmp_add($b, $this->one)) {
                if ($math->jacobi(
                    $math->sub(
                        $math->mul($b, $b),
                        $math->mul($four, $a)
                    ),
                    $p
                ) == -1
                ) {
                    $f = array($a, $math->sub($this->zero, $b), $this->one);

                    $ff = $this->polynomialPowMod(
                        array($this->zero, $this->one),
                        $math->div(
                            $math->add(
                                $p,
                                $this->one
                            ),
                            $this->two
                        ),
                        $f,
                        $p
                    );

                    if ($math->equals($ff[1], $this->zero)) {
                        return $ff[0];
                    }
                    // if we got here no b was found
                }
            }
        }

        throw new SquareRootException('Unable to calculate square root mod p!');
    }
}
