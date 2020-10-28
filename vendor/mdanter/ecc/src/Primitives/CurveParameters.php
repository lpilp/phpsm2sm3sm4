<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Primitives;

class CurveParameters
{
    /**
     * Elliptic curve over the field of integers modulo a prime.
     *
     * @var \GMP
     */
    protected $a;

    /**
     *
     * @var \GMP
     */
    protected $b;

    /**
     *
     * @var \GMP
     */
    protected $prime;

    /**
     * Binary length of keys associated with these curve parameters
     *
     * @var int
     */
    protected $size;

    /**
     * @param int $size
     * @param \GMP $prime
     * @param \GMP $a
     * @param \GMP $b
     */
    public function __construct(int $size, \GMP $prime, \GMP $a, \GMP $b)
    {
        $this->size = $size;
        $this->prime = $prime;
        $this->a = $a;
        $this->b = $b;
    }

    /**
     * @return \GMP
     */
    public function getA(): \GMP
    {
        return $this->a;
    }

    /**
     * @return \GMP
     */
    public function getB(): \GMP
    {
        return $this->b;
    }

    /**
     * @return \GMP
     */
    public function getPrime(): \GMP
    {
        return $this->prime;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }
}
