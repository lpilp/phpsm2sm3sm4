<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Crypto\Key;

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

use Mdanter\Ecc\Exception\PublicKeyException;
use Mdanter\Ecc\Math\GmpMathInterface;
use Mdanter\Ecc\Primitives\CurveFpInterface;
use Mdanter\Ecc\Primitives\GeneratorPoint;
use Mdanter\Ecc\Primitives\PointInterface;

/**
 * This class serves as public- private key exchange for signature verification
 */
class PublicKey implements PublicKeyInterface
{
    /**
     *
     * @var CurveFpInterface
     */
    private $curve;

    /**
     *
     * @var GeneratorPoint
     */
    private $generator;

    /**
     *
     * @var PointInterface
     */
    private $point;

    /**
     *
     * @var GmpMathInterface
     */
    private $adapter;

    /**
     * Initialize a new PublicKey instance.
     *
     * @param  GmpMathInterface  $adapter
     * @param  GeneratorPoint    $generator
     * @param  PointInterface    $point
     */
    public function __construct(GmpMathInterface $adapter, GeneratorPoint $generator, PointInterface $point)
    {
        $this->curve = $generator->getCurve();
        $this->generator = $generator;
        $this->point = $point;
        $this->adapter = $adapter;

        // step 1: not point at infinity.
        if ($point->isInfinity()) {
            throw new PublicKeyException($generator, $point, "Cannot use point at infinity for public key");
        }

        // step 2 full & partial public key validation routine
        if ($adapter->cmp($point->getX(), gmp_init(0, 10)) < 0 || $adapter->cmp($this->curve->getPrime(), $point->getX()) < 0
            || $adapter->cmp($point->getY(), gmp_init(0, 10)) < 0 || $adapter->cmp($this->curve->getPrime(), $point->getY()) < 0
        ) {
            throw new PublicKeyException($generator, $point, "Point has x and y out of range.");
        }

        // Sanity check. Point (x,y) values are qualified against it's
        // generator and curve. Here we ensure the Point and Generator
        // are the same.
        if (!$generator->getCurve()->equals($point->getCurve())) {
            throw new PublicKeyException($generator, $point, "Curve for given point not in common with GeneratorPoint");
        }
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Crypto\Key\PublicKeyInterface::getCurve()
     */
    public function getCurve(): CurveFpInterface
    {
        return $this->curve;
    }

    /**
     * {$inheritDoc}
     * @see \Mdanter\Ecc\Crypto\Key\PublicKeyInterface::getGenerator()
     */
    public function getGenerator(): GeneratorPoint
    {
        return $this->generator;
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Crypto\Key\PublicKeyInterface::getPoint()
     */
    public function getPoint(): PointInterface
    {
        return $this->point;
    }
}
