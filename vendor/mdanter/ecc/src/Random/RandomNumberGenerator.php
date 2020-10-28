<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Random;

use Mdanter\Ecc\Math\GmpMathInterface;
use Mdanter\Ecc\Util\NumberSize;

class RandomNumberGenerator implements RandomNumberGeneratorInterface
{
    /**
     * @var GmpMathInterface
     */
    private $adapter;

    /**
     * RandomNumberGenerator constructor.
     * @param GmpMathInterface $adapter
     */
    public function __construct(GmpMathInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param \GMP $max
     * @return \GMP
     */
    public function generate(\GMP $max): \GMP
    {
        $numBits = NumberSize::bnNumBits($this->adapter, $max);
        $numBytes = (int) ceil($numBits / 8);
        // Generate an integer of size >= $numBits
        $bytes = random_bytes($numBytes);
        $value = $this->adapter->stringToInt($bytes);

        $mask = gmp_sub(gmp_pow(2, $numBits), 1);
        $integer = gmp_and($value, $mask);
        // return gmp_init('0xa7763cd4fe7db2a2146fc09bf2d5e5a30e10c51b7e4bed00b3a26ec79ba78ff3',16);
        return $integer;
    }
}
