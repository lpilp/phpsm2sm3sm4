<?php
namespace Rtgm\ecc;

use Mdanter\Ecc\Curves\NamedCurveFp;
use Mdanter\Ecc\Math\GmpMathInterface;
use Mdanter\Ecc\Primitives\CurveParameters;
use Mdanter\Ecc\Primitives\GeneratorPoint;
use Mdanter\Ecc\Random\RandomNumberGeneratorInterface;

/**
 * 国密sm2椭圆
 */
class Sm2Curve
{
    const NAME_PSM2 = 'SM2';
    /**
     * @var GmpMathInterface
     */
    private $adapter;

    /**
     * @param GmpMathInterface $adapter
     */
    public function __construct(GmpMathInterface $adapter)
    {
        $this->adapter = $adapter;
        // echo "I am sm ecc\n";
    }
    /**
     * Returns an sm2国密 curve.
     *
     * @return NamedCurveFp
     */
    public function curveSm2(): NamedCurveFp
    {
        $p = gmp_init('0xFFFFFFFEFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF00000000FFFFFFFFFFFFFFFF', 16);
        $b = gmp_init('0x28E9FA9E9D9F5E344D5A9E4BCF6509A7F39789F515AB8F92DDBCBD414D940E93', 16);
        $a = gmp_init('0xFFFFFFFEFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF00000000FFFFFFFFFFFFFFFC', 16);
        $parameters = new CurveParameters(256, $p, $a, $b);

        return new NamedCurveFp(self::NAME_PSM2, $parameters, $this->adapter);
    }

    /**
     * Returns an sm2 generator.
     *
     * @param  RandomNumberGeneratorInterface $randomGenerator
     * @return GeneratorPoint
     */
    public function generatorSm2(RandomNumberGeneratorInterface $randomGenerator = null): GeneratorPoint
    {
        $curve = $this->curveSm2();
        $order = gmp_init('0xFFFFFFFEFFFFFFFFFFFFFFFFFFFFFFFF7203DF6B21C6052B53BBF40939D54123', 16);

        $x = gmp_init('0x32C4AE2C1F1981195F9904466A39C9948FE30BBFF2660BE1715A4589334C74C7', 16);
        $y = gmp_init('0xBC3736A2F4F6779C59BDCEE36B692153D0A9877CC62A474002DF32E52139F0A0', 16);

        return $curve->getGenerator($x, $y, $order, $randomGenerator);
    }
}
