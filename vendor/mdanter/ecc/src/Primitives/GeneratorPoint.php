<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Primitives;

use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use Mdanter\Ecc\Math\GmpMathInterface;
use Mdanter\Ecc\Crypto\Key\PrivateKey;
use Mdanter\Ecc\Crypto\Key\PublicKey;
use Mdanter\Ecc\Random\RandomGeneratorFactory;
use Mdanter\Ecc\Random\RandomNumberGeneratorInterface;

/**
 * Curve point from which public and private keys can be derived.
 */
class GeneratorPoint extends Point
{
    /**
     * @var RandomNumberGeneratorInterface
     */
    private $generator;

    /**
     * @param GmpMathInterface               $adapter
     * @param CurveFpInterface               $curve
     * @param \GMP                           $x
     * @param \GMP                           $y
     * @param \GMP                           $order
     * @param RandomNumberGeneratorInterface $generator
     */
    public function __construct(
        GmpMathInterface $adapter,
        CurveFpInterface $curve,
        \GMP $x,
        \GMP $y,
        \GMP $order,
        RandomNumberGeneratorInterface $generator = null
    ) {
        $this->generator = $generator ?: RandomGeneratorFactory::getRandomGenerator();
        parent::__construct($adapter, $curve, $x, $y, $order);
    }

    /**
     * Verifies validity of given coordinates against the current point and its point.
     *
     * @todo   Check if really necessary here (only used for testing in lib)
     * @param  \GMP $x
     * @param  \GMP $y
     * @return bool
     */
    public function isValid(\GMP $x, \GMP $y): bool
    {
       
        $math = $this->getAdapter();

        $n = $this->getOrder();
        $zero = gmp_init(0, 10);
        $curve = $this->getCurve();

        if ($math->cmp($x, $zero) < 0 || $math->cmp($n, $x) <= 0 || $math->cmp($y, $zero) < 0 || $math->cmp($n, $y) <= 0) {
            return false;
        }

        if (! $curve->contains($x, $y)) {
            return false;
        }

        $point = $curve->getPoint($x, $y)->mul($n);

        if (! $point->isInfinity()) {
            return false;
        }

        return true;
    }

    /**
     * @return PrivateKeyInterface
     */
    public function createPrivateKey(): PrivateKeyInterface
    {
        $secret = $this->generator->generate($this->getOrder());

        return new PrivateKey($this->getAdapter(), $this, $secret);
    }

    /**
     * @param \GMP $x
     * @param \GMP $y
     * @return PublicKeyInterface
     */
    public function getPublicKeyFrom(\GMP $x, \GMP $y): PublicKeyInterface
    {
        $pubPoint = $this->getCurve()->getPoint($x, $y, $this->getOrder());
        return new PublicKey($this->getAdapter(), $this, $pubPoint);
    }

    /**
     * @param \GMP $secretMultiplier
     * @return PrivateKeyInterface
     */
    public function getPrivateKeyFrom(\GMP $secretMultiplier): PrivateKeyInterface
    {
        return new PrivateKey($this->getAdapter(), $this, $secretMultiplier);
    }
}
