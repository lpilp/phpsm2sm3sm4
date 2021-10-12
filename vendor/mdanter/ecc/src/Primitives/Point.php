<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Primitives;

use Mdanter\Ecc\Exception\PointException;
use Mdanter\Ecc\Exception\PointNotOnCurveException;
use Mdanter\Ecc\Math\GmpMathInterface;
use Mdanter\Ecc\Math\ModularArithmetic;

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
 * This class is where the elliptic curve arithmetic takes place.
 * The important methods are:
 * - add: adds two points according to ec arithmetic
 * - double: doubles a point on the ec field mod p
 * - mul: uses double and add to achieve multiplication The rest of the methods are there for supporting the ones above.
 */
class Point implements PointInterface
{
    /**
     * @var CurveFpInterface
     */
    private $curve;

    /**
     * @var GmpMathInterface
     */
    private $adapter;

    /**
     * @var ModularArithmetic
     */
    private $modAdapter;

    /**
     * @var \GMP
     */
    private $x;

    /**
     * @var \GMP
     */
    private $y;

    /**
     * @var \GMP
     */
    private $order;

    /**
     * @var bool
     */
    private $infinity = false;

    /**
     * Initialize a new instance
     *
     * @param GmpMathInterface     $adapter
     * @param CurveFpInterface     $curve
     * @param \GMP                 $x
     * @param \GMP                 $y
     * @param \GMP                 $order
     * @param bool                 $infinity
     *
     * @throws \RuntimeException    when either the curve does not contain the given coordinates or
     *                                      when order is not null and P(x, y) * order is not equal to infinity.
     */
    public function __construct(GmpMathInterface $adapter, CurveFpInterface $curve, \GMP $x, \GMP $y, \GMP $order = null, bool $infinity = false)
    {
        $this->adapter    = $adapter;
        $this->modAdapter = $curve->getModAdapter();
        $this->curve      = $curve;
        $this->x          = $x;
        $this->y          = $y;
        $this->order      = $order !== null ? $order : gmp_init(0, 10);
        $this->infinity   = (bool) $infinity;
        if (! $infinity && ! $curve->contains($x, $y)) {
            throw new PointNotOnCurveException($x, $y, $curve);
        }

        if (!is_null($order)) {
            $mul = $this->mul($order);
            if (!$mul->isInfinity()) {
                throw new PointException("SELF * ORDER MUST EQUAL INFINITY. (" . (string)$mul . " found instead)");
            }
        }
    }

    /**
     * @return GmpMathInterface
     */
    public function getAdapter(): GmpMathInterface
    {
        return $this->adapter;
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Primitives\PointInterface::isInfinity()
     */
    public function isInfinity(): bool
    {
        return (bool) $this->infinity;
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Primitives\PointInterface::getCurve()
     */
    public function getCurve(): CurveFpInterface
    {
        return $this->curve;
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Primitives\PointInterface::getOrder()
     */
    public function getOrder(): \GMP
    {
        return $this->order;
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Primitives\PointInterface::getX()
     */
    public function getX(): \GMP
    {
        return $this->x;
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Primitives\PointInterface::getY()
     */
    public function getY(): \GMP
    {
        return $this->y;
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Primitives\PointInterface::add()
     * @return self
     */
    public function add(PointInterface $addend): PointInterface
    {
        if (! $this->curve->equals($addend->getCurve())) {
            throw new \RuntimeException("The Elliptic Curves do not match.");
        }

        if ($addend->isInfinity()) {
            return clone $this;
        }

        if ($this->isInfinity()) {
            return clone $addend;
        }

        $math = $this->adapter;
        $modMath = $this->modAdapter;

        if ($math->equals($addend->getX(), $this->x)) {
            if ($math->equals($addend->getY(), $this->y)) {
                return $this->getDouble();
            } else {
                return $this->curve->getInfinity();
            }
        }

        $slope = $modMath->div(
            $math->sub($addend->getY(), $this->y),
            $math->sub($addend->getX(), $this->x)
        );

        $xR = $modMath->sub(
            $math->sub($math->pow($slope, 2), $this->x),
            $addend->getX()
        );

        $yR = $modMath->sub(
            $math->mul($slope, $math->sub($this->x, $xR)),
            $this->y
        );

        return $this->curve->getPoint($xR, $yR, $this->order);
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Primitives\PointInterface::cmp()
     */
    public function cmp(PointInterface $other): int
    {
        if ($other->isInfinity() && $this->isInfinity()) {
            return 0;
        }

        if ($other->isInfinity() || $this->isInfinity()) {
            return 1;
        }

        $math = $this->adapter;
        $equal = ($math->equals($this->x, $other->getX()));
        $equal &= ($math->equals($this->y, $other->getY()));
        $equal &= $this->isInfinity() == $other->isInfinity();
        $equal &= $this->curve->equals($other->getCurve());

        if ($equal) {
            return 0;
        }

        return 1;
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Primitives\PointInterface::equals()
     */
    public function equals(PointInterface $other): bool
    {
        return $this->cmp($other) == 0;
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Primitives\PointInterface::mul()
     */
    public function mul(\GMP $n): PointInterface
    {
        if ($this->isInfinity()) {
            return $this->curve->getInfinity();
        }

        $zero = gmp_init(0, 10);
        if ($this->adapter->cmp($this->order, $zero) > 0) {
            $n = $this->adapter->mod($n, $this->order);
        }

        if ($this->adapter->equals($n, $zero)) {
            return $this->curve->getInfinity();
        }

        /** @var Point[] $r */
        $r = [
            $this->curve->getInfinity(),
            clone $this
        ];

        $k = $this->curve->getSize();
        $n = str_pad($this->adapter->baseConvert($this->adapter->toString($n), 10, 2), $k, '0', STR_PAD_LEFT);

        for ($i = 0; $i < $k; $i++) {
            $j = $n[$i];

            $this->cswap($r[0], $r[1], $j ^ 1, $k);

            $r[0] = $r[0]->add($r[1]);
            $r[1] = $r[1]->getDouble();

            $this->cswap($r[0], $r[1], $j ^ 1, $k);
        }

        $r[0]->validate();

        return $r[0];
    }

    /**
     * @param Point $a
     * @param Point $b
     * @param int $cond
     * @param int $curveSize
     */
    private function cswap(self $a, self $b, int $cond, int $curveSize)
    {
        $this->cswapValue($a->x, $b->x, $cond, $curveSize);
        $this->cswapValue($a->y, $b->y, $cond, $curveSize);
        $this->cswapValue($a->order, $b->order, $cond, $curveSize);
        $this->cswapValue($a->infinity, $b->infinity, $cond, 8);
    }

    /**
     * @param bool|\GMP $a
     * @param bool|\GMP $b
     * @param int $cond
     * @param int $maskBitSize
     */
    public function cswapValue(& $a, & $b, int $cond, int $maskBitSize)
    {
        $isGMP = is_object($a) && $a instanceof \GMP;

        $sa = $isGMP ? $a : gmp_init(intval($a), 10);
        $sb = $isGMP ? $b : gmp_init(intval($b), 10);

        $mask = str_pad('', $maskBitSize, (string) (1 - intval($cond)), STR_PAD_LEFT);
        $mask = gmp_init($mask, 2);

        $taA = $this->adapter->bitwiseAnd($sa, $mask);
        $taB = $this->adapter->bitwiseAnd($sb, $mask);

        $sa = $this->adapter->bitwiseXor($this->adapter->bitwiseXor($sa, $sb), $taB);
        $sb = $this->adapter->bitwiseXor($this->adapter->bitwiseXor($sa, $sb), $taA);
        $sa = $this->adapter->bitwiseXor($this->adapter->bitwiseXor($sa, $sb), $taB);

        $a = $isGMP ? $sa : (bool) gmp_strval($sa, 10);
        $b = $isGMP ? $sb : (bool) gmp_strval($sb, 10);
    }

    /**
     *
     */
    private function validate()
    {
        if (! $this->infinity && ! $this->curve->contains($this->x, $this->y)) {
            throw new \RuntimeException('Invalid point');
        }
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Primitives\PointInterface::getDouble()
     * @return self
     */
    public function getDouble(): PointInterface
    {
        if ($this->isInfinity()) {
            return $this->curve->getInfinity();
        }

        $math = $this->adapter;
        $modMath = $this->modAdapter;

        $a = $this->curve->getA();
        $threeX2 = $math->mul(gmp_init(3, 10), $math->pow($this->x, 2));

        $tangent = $modMath->div(
            $math->add($threeX2, $a),
            $math->mul(gmp_init(2, 10), $this->y)
        );

        $x3 = $modMath->sub(
            $math->pow($tangent, 2),
            $math->mul(gmp_init(2, 10), $this->x)
        );

        $y3 = $modMath->sub(
            $math->mul($tangent, $math->sub($this->x, $x3)),
            $this->y
        );

        return $this->curve->getPoint($x3, $y3, $this->order);
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Primitives\PointInterface::__toString()
     */
    public function __toString(): string
    {
        if ($this->infinity) {
            return '[ (infinity) on ' . (string) $this->curve . ' ]';
        }

        return "[ (" . $this->adapter->toString($this->x) . "," . $this->adapter->toString($this->y) . ') on ' . (string) $this->curve . ' ]';
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        $info = [
            'x' => $this->adapter->toString($this->x),
            'y' => $this->adapter->toString($this->y),
            'z' => $this->adapter->toString($this->order),
            'curve' => $this->curve
        ];

        if ($this->infinity) {
            $info['x'] = 'inf (' . $info['x'] . ')';
            $info['y'] = 'inf (' . $info['y'] . ')';
            $info['z'] = 'inf (' . $info['z'] . ')';
        }

        return $info;
    }
}
