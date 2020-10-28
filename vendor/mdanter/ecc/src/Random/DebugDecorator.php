<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Random;

class DebugDecorator implements RandomNumberGeneratorInterface
{
    /**
     * @var RandomNumberGeneratorInterface
     */
    private $generator;

    /**
     * @var string
     */
    private $generatorName;

    /**
     * @param RandomNumberGeneratorInterface $generator
     * @param string $name
     */
    public function __construct(RandomNumberGeneratorInterface $generator, string $name)
    {
        $this->generator = $generator;
        $this->generatorName = $name;
    }

    /**
     * @param \GMP $max
     * @return \GMP
     */
    public function generate(\GMP $max): \GMP
    {
        echo $this->generatorName.'::rand() = ';

        $result = $this->generator->generate($max);

        echo gmp_strval($result, 10).PHP_EOL;

        return $result;
    }
}
