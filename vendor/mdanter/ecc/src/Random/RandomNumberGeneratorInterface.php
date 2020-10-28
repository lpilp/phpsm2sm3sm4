<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Random;

interface RandomNumberGeneratorInterface
{
    /**
     * Generate a random number between 0 and the specified upper boundary.
     * @param \GMP $max - Upper boundary, inclusive
     * @return \GMP
     */
    public function generate(\GMP $max): \GMP;
}
