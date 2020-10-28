<?php
declare(strict_types=1);

namespace Mdanter\Ecc\Random;

use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;
use Mdanter\Ecc\Math\GmpMathInterface;
use Mdanter\Ecc\Util\BinaryString;
use Mdanter\Ecc\Util\NumberSize;

class HmacRandomNumberGenerator implements RandomNumberGeneratorInterface
{
    /**
     * @var GmpMathInterface
     */
    private $math;

    /**
     * @var string
     */
    private $algorithm;

    /**
     * @var PrivateKeyInterface
     */
    private $privateKey;

    /**
     * @var \GMP
     */
    private $messageHash;

    /**
     * @var array
     */
    private $algSize = array(
        'sha1' => 160,
        'sha224' => 224,
        'sha256' => 256,
        'sha384' => 384,
        'sha512' => 512
    );

    /**
     * Hmac constructor.
     * @param GmpMathInterface $math
     * @param PrivateKeyInterface $privateKey
     * @param \GMP $messageHash - decimal hash of the message (*may* be truncated)
     * @param string $algorithm - hashing algorithm
     */
    public function __construct(GmpMathInterface $math, PrivateKeyInterface $privateKey, \GMP $messageHash, string $algorithm)
    {
        if (!isset($this->algSize[$algorithm])) {
            throw new \InvalidArgumentException('Unsupported hashing algorithm');
        }

        $this->math = $math;
        $this->algorithm = $algorithm;
        $this->privateKey = $privateKey;
        $this->messageHash = $messageHash;
    }

    /**
     * @param string $bits - binary string of bits
     * @param \GMP $qlen - length of q in bits
     * @return \GMP
     */
    public function bits2int(string $bits, \GMP $qlen): \GMP
    {
        $vlen = gmp_init(BinaryString::length($bits) * 8, 10);
        $hex = bin2hex($bits);
        $v = gmp_init($hex, 16);

        if ($this->math->cmp($vlen, $qlen) > 0) {
            $v = $this->math->rightShift($v, (int) $this->math->toString($this->math->sub($vlen, $qlen)));
        }

        return $v;
    }

    /**
     * @param \GMP $int
     * @param \GMP $rlen - rounded octet length
     * @return string
     */
    public function int2octets(\GMP $int, \GMP $rlen): string
    {
        $out = pack("H*", $this->math->decHex(gmp_strval($int, 10)));
        $length = gmp_init(BinaryString::length($out), 10);
        if ($this->math->cmp($length, $rlen) < 0) {
            return str_pad('', (int) $this->math->toString($this->math->sub($rlen, $length)), "\x00") . $out;
        }

        if ($this->math->cmp($length, $rlen) > 0) {
            return BinaryString::substring($out, 0, (int) $this->math->toString($rlen));
        }

        return $out;
    }

    /**
     * @param string $algorithm
     * @return int
     */
    private function getHashLength(string $algorithm): int
    {
        return $this->algSize[$algorithm];
    }

    /**
     * @param \GMP $q
     * @return \GMP
     */
    public function generate(\GMP $q): \GMP
    {
        $qlen = gmp_init(NumberSize::bnNumBits($this->math, $q), 10);
        $rlen = $this->math->rightShift($this->math->add($qlen, gmp_init(7, 10)), 3);
        $hlen = $this->getHashLength($this->algorithm);
        $bx = $this->int2octets($this->privateKey->getSecret(), $rlen) . $this->int2octets($this->messageHash, $rlen);

        $v = str_pad('', $hlen >> 3, "\x01", STR_PAD_LEFT);
        $k = str_pad('', $hlen >> 3, "\x00", STR_PAD_LEFT);

        $k = hash_hmac($this->algorithm, $v . "\x00" . $bx, $k, true);
        $v = hash_hmac($this->algorithm, $v, $k, true);

        $k = hash_hmac($this->algorithm, $v . "\x01" . $bx, $k, true);
        $v = hash_hmac($this->algorithm, $v, $k, true);

        $t = '';
        for (;;) {
            $toff = gmp_init(0, 10);
            while ($this->math->cmp($toff, $rlen) < 0) {
                $v = hash_hmac($this->algorithm, $v, $k, true);

                $cc = min(BinaryString::length($v), (int) gmp_strval(gmp_sub($rlen, $toff), 10));
                $t .= BinaryString::substring($v, 0, $cc);
                $toff = gmp_add($toff, $cc);
            }

            $k = $this->bits2int($t, $qlen);
            if ($this->math->cmp($k, gmp_init(0, 10)) > 0 && $this->math->cmp($k, $q) < 0) {
                return $k;
            }

            $k = hash_hmac($this->algorithm, $v . "\x00", $k, true);
            $v = hash_hmac($this->algorithm, $v, $k, true);
        }
    }
}
