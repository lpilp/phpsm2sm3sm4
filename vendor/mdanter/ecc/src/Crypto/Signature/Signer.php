<?php

declare(strict_types=1);

namespace Mdanter\Ecc\Crypto\Signature;

use Mdanter\Ecc\Math\GmpMathInterface;
use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use Mdanter\Ecc\Util\BinaryString;

class Signer
{

    /**
     *
     * @var GmpMathInterface
     */
    private $adapter;

    /**
     *
     * @param GmpMathInterface $adapter
     */
    public function __construct(GmpMathInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param PrivateKeyInterface $key
     * @param \GMP $truncatedHash - hash truncated for use in ECDSA
     * @param \GMP $randomK
     * @return SignatureInterface
     */
    public function sign(PrivateKeyInterface $key, \GMP $truncatedHash, \GMP $randomK): SignatureInterface
    {
        $math = $this->adapter;
        $generator = $key->getPoint();
        $modMath = $math->getModularArithmetic($generator->getOrder());

        $k = $math->mod($randomK, $generator->getOrder());
        $p1 = $generator->mul($k);
        $r = $p1->getX();
        $zero = gmp_init(0, 10);
        if ($math->equals($r, $zero)) {
            throw new \RuntimeException("Error: random number R = 0");
        }

        $s = $modMath->div($modMath->add($truncatedHash, $math->mul($key->getSecret(), $r)), $k);
        if ($math->equals($s, $zero)) {
            throw new \RuntimeException("Error: random number S = 0");
        }

        return new Signature($r, $s);
    }

    /**
     * @param PublicKeyInterface $key
     * @param SignatureInterface $signature
     * @param \GMP $hash
     * @return bool
     */
    public function verify(PublicKeyInterface $key, SignatureInterface $signature, \GMP $hash): bool
    {
        $generator = $key->getGenerator();
        $n = $generator->getOrder();
        $r = $signature->getR();
        $s = $signature->getS();

        $math = $this->adapter;
        $one = gmp_init(1, 10);
        if ($math->cmp($r, $one) < 0 || $math->cmp($r, $math->sub($n, $one)) > 0) {
            return false;
        }

        if ($math->cmp($s, $one) < 0 || $math->cmp($s, $math->sub($n, $one)) > 0) {
            return false;
        }

        $modMath = $math->getModularArithmetic($n);
        $c = $math->inverseMod($s, $n);
        $u1 = $modMath->mul($hash, $c);
        $u2 = $modMath->mul($r, $c);
        $xy = $generator->mul($u1)->add($key->getPoint()->mul($u2));
        $v = $math->mod($xy->getX(), $n);

        return BinaryString::constantTimeCompare($math->toString($v), $math->toString($r));
    }
}
