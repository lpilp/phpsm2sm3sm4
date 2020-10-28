<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Serializer\PublicKey\Der;

use FG\ASN1\Universal\Sequence;
use FG\ASN1\Universal\ObjectIdentifier;
use FG\ASN1\Universal\BitString;
use Mdanter\Ecc\Primitives\PointInterface;
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use Mdanter\Ecc\Curves\NamedCurveFp;
use Mdanter\Ecc\Serializer\Util\CurveOidMapper;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use Mdanter\Ecc\Serializer\Point\PointSerializerInterface;
use Mdanter\Ecc\Serializer\Point\UncompressedPointSerializer;

class Formatter
{
    /**
     * @var UncompressedPointSerializer
     */
    private $pointSerializer;

    /**
     * Formatter constructor.
     * @param PointSerializerInterface|null $pointSerializer
     */
    public function __construct(PointSerializerInterface $pointSerializer = null)
    {
        $this->pointSerializer = $pointSerializer ?: new UncompressedPointSerializer();
    }

    /**
     * @param PublicKeyInterface $key
     * @return string
     */
    public function format(PublicKeyInterface $key): string
    {
        if (! ($key->getCurve() instanceof NamedCurveFp)) {
            throw new \RuntimeException('Not implemented for unnamed curves');
        }

        $sequence = new Sequence(
            new Sequence(
                new ObjectIdentifier(DerPublicKeySerializer::X509_ECDSA_OID),
                CurveOidMapper::getCurveOid($key->getCurve())
            ),
            new BitString($this->encodePoint($key->getPoint()))
        );

        return $sequence->getBinary();
    }

    /**
     * @param PointInterface $point
     * @return string
     */
    public function encodePoint(PointInterface $point): string
    {
        return $this->pointSerializer->serialize($point);
    }
}
