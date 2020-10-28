<?php
declare(strict_types=1);
namespace Mdanter\Ecc\Random;

use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;

use Mdanter\Ecc\Math\MathAdapterFactory;

class RandomGeneratorFactory
{
    /**
     * @param bool $debug
     * @return DebugDecorator|RandomNumberGeneratorInterface
     */
    public static function getRandomGenerator(bool $debug = false): RandomNumberGeneratorInterface
    {
        return self::wrapAdapter(
            new RandomNumberGenerator(
                MathAdapterFactory::getAdapter($debug)
            ),
            'random_bytes',
            $debug
        );
    }

    /**
     * @param PrivateKeyInterface $privateKey
     * @param \GMP                $messageHash
     * @param string              $algorithm
     * @param bool                $debug
     * @return DebugDecorator|RandomNumberGeneratorInterface
     */
    public static function getHmacRandomGenerator(PrivateKeyInterface $privateKey, \GMP $messageHash, string $algorithm, bool $debug = false): RandomNumberGeneratorInterface
    {
        return self::wrapAdapter(
            new HmacRandomNumberGenerator(
                MathAdapterFactory::getAdapter($debug),
                $privateKey,
                $messageHash,
                $algorithm
            ),
            'rfc6979',
            $debug
        );
    }

    /**
     * @param RandomNumberGeneratorInterface $generator
     * @param string                         $name
     * @param bool                           $debug
     * @return DebugDecorator|RandomNumberGeneratorInterface
     */
    private static function wrapAdapter(RandomNumberGeneratorInterface $generator, string $name, bool $debug = false): RandomNumberGeneratorInterface
    {
        if ($debug === true) {
            return new DebugDecorator($generator, $name);
        }

        return $generator;
    }
}
