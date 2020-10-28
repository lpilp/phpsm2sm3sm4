<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Serializer\PublicKey;

use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use Mdanter\Ecc\Math\GmpMathInterface;
use Mdanter\Ecc\Math\MathAdapterFactory;
use Mdanter\Ecc\Serializer\Point\PointSerializerInterface;
use Mdanter\Ecc\Serializer\Point\UncompressedPointSerializer;
use Mdanter\Ecc\Serializer\PublicKey\Der\Formatter;
use Mdanter\Ecc\Serializer\PublicKey\Der\Parser;

/**
 *
 * @link https://tools.ietf.org/html/rfc5480#page-3
 * @todo: review for full spec, should we support all prefixes here?
 */
class DerPublicKeySerializer implements PublicKeySerializerInterface
{

    const X509_ECDSA_OID = '1.2.840.10045.2.1';

    /**
     *
     * @var GmpMathInterface
     */
    private $adapter;

    /**
     *
     * @var Formatter
     */
    private $formatter;

    /**
     *
     * @var Parser
     */
    private $parser;

    /**
     * @param GmpMathInterface|null $adapter
     * @param PointSerializerInterface|null $pointSerializer
     */
    public function __construct(GmpMathInterface $adapter = null, PointSerializerInterface $pointSerializer = null)
    {
        $this->adapter = $adapter ?: MathAdapterFactory::getAdapter();

        $this->formatter = new Formatter();
        $this->parser = new Parser($this->adapter, $pointSerializer ?: new UncompressedPointSerializer());
    }

    /**
     *
     * @param  PublicKeyInterface $key
     * @return string
     */
    public function serialize(PublicKeyInterface $key): string
    {
        return $this->formatter->format($key);
    }

    /**
     * @param PublicKeyInterface $key
     * @return string
     */
    public function getUncompressedKey(PublicKeyInterface $key): string
    {
        return $this->formatter->encodePoint($key->getPoint());
    }

    /**
     * {@inheritDoc}
     * @see \Mdanter\Ecc\Serializer\PublicKey\PublicKeySerializerInterface::parse()
     */
    public function parse(string $string): PublicKeyInterface
    {
        return $this->parser->parse($string);
    }
}
