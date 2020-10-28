<?php

namespace Mdanter\Ecc\Crypto\Signature;

use Mdanter\Ecc\Primitives\GeneratorPoint;

interface HasherInterface
{
    /**
     * @return string
     */
    public function getAlgorithm(): string;

    /**
     * @return int
     */
    public function getLengthInBytes(): int;

    /**
     * @param string $data
     * @return string
     */
    public function makeRawHash(string $data): string;

    /**
     * @param string $data
     * @param GeneratorPoint $G
     * @return \GMP
     */
    public function makeHash(string $data, GeneratorPoint $G): \GMP;
}
