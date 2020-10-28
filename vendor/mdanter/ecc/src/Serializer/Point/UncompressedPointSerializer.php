<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Serializer\Point;

use Mdanter\Ecc\Primitives\PointInterface;
use Mdanter\Ecc\Primitives\CurveFpInterface;
use Mdanter\Ecc\Serializer\Util\CurveOidMapper;
use Mdanter\Ecc\Util\BinaryString;

class UncompressedPointSerializer implements PointSerializerInterface
{
    /**
     * @param PointInterface $point
     * @return string
     */
    public function serialize(PointInterface $point): string
    {
        $length = CurveOidMapper::getByteSize($point->getCurve()) * 2;

        $hexString = '04';
        $hexString .= str_pad(gmp_strval($point->getX(), 16), $length, '0', STR_PAD_LEFT);
        $hexString .= str_pad(gmp_strval($point->getY(), 16), $length, '0', STR_PAD_LEFT);

        return $hexString;
    }

    /**
     * @param CurveFpInterface $curve
     * @param string           $data
     * @return PointInterface
     */
    public function unserialize(CurveFpInterface $curve, string $data): PointInterface
    {
        if (BinaryString::substring($data, 0, 2) != '04') {
            throw new \InvalidArgumentException('Invalid data: only uncompressed keys are supported.');
        }

        $data = BinaryString::substring($data, 2);
        $dataLength = BinaryString::length($data);

        $x = gmp_init(BinaryString::substring($data, 0, $dataLength / 2), 16);
        $y = gmp_init(BinaryString::substring($data, $dataLength / 2), 16);

        return $curve->getPoint($x, $y);
    }
}
