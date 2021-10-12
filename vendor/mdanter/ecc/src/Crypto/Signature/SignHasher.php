<?php

namespace Mdanter\Ecc\Crypto\Signature;

use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Math\GmpMathInterface;
use Mdanter\Ecc\Primitives\GeneratorPoint;
use Mdanter\Ecc\Util\BinaryString;
use Mdanter\Ecc\Util\NumberSize;

class SignHasher implements HasherInterface
{
    /**
     * @var int[]
     */
    protected static $sizeMap = [
        'sha1' => 20,
        'sha224' => 28,
        'sha256' => 32,
        'sha384' => 48,
        'sha512' => 64,
    ];

    /**
     * @var GmpMathInterface
     */
    private $adapter;

    /**
     * @var string
     */
    private $algorithm;

    /**
     * SignHasher constructor.
     * @param string $algorithm
     * @param GmpMathInterface|null $math
     */
    public function __construct(string $algorithm, GmpMathInterface $math = null)
    {
        if (!array_key_exists($algorithm, self::$sizeMap)) {
            throw new \InvalidArgumentException("Unsupported hashing algorithm");
        }

        $this->algorithm = $algorithm;
        $this->adapter = $math ?: EccFactory::getAdapter();
    }

    /**
     * @return string
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }

    /**
     * @return int
     */
    public function getLengthInBytes(): int
    {
        return self::$sizeMap[$this->algorithm];
    }

    /**
     * @param string $data
     * @return string
     */
    public function makeRawHash(string $data): string
    {
        return hash($this->algorithm, $data, false);
    }

    /**
     * @param \GMP $hash
     * @param GeneratorPoint $G
     * @return \GMP
     */
    public function truncateForECDSA(\GMP $hash, GeneratorPoint $G)
    {
        $hashBits = gmp_strval($hash, 2);
        if (BinaryString::length($hashBits) < self::$sizeMap[$this->algorithm] * 8) {
            $hashBits = str_pad($hashBits, self::$sizeMap[$this->algorithm] * 8, '0', STR_PAD_LEFT);
        }

        return gmp_init(BinaryString::substring($hashBits, 0, NumberSize::bnNumBits($this->adapter, $G->getOrder())), 2);
    }

    /**
     * @param string $data
     * @param GeneratorPoint $G
     * @return \GMP
     */
    public function makeHash(string $data, GeneratorPoint $G): \GMP
    {
        $hash = gmp_init($this->makeRawHash($data), 16);
        return $this->truncateForECDSA($hash, $G);
    }
}
