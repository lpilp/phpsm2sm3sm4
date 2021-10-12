<?php

declare( strict_types = 1 );

// namespace Mdanter\Ecc\Crypto\Signature;
namespace Rtgm\ecc;

use Mdanter\Ecc\Math\GmpMathInterface;
use Mdanter\Ecc\Crypto\Key\PrivateKeyInterface;
use Mdanter\Ecc\Crypto\Key\PublicKeyInterface;
use Mdanter\Ecc\Util\BinaryString;
use Mdanter\Ecc\Crypto\Signature\SignatureInterface;
use Mdanter\Ecc\Crypto\Signature\Signature;
/**
 * sm2签名算法
 */
class Sm2Signer {

    /**
    *
    * @var GmpMathInterface
    */
    private $adapter;

    /**
    *
    * @param GmpMathInterface $adapter
    */

    public function __construct( GmpMathInterface $adapter ) {
        $this->adapter = $adapter;
    }

    /**
    * @param PrivateKeyInterface $key
    * @param \GMP $truncatedHash - hash truncated for use in ECDSA hash算法然后truncated by 相关的椭圆字节
    * @param \GMP $randomK
    * @return SignatureInterface
    */

    public function sign( PrivateKeyInterface $key, \GMP $truncatedHash, \GMP $randomK ): SignatureInterface {
        $math = $this->adapter;
        $generator = $key->getPoint();
        // var_dump($generator);die();
        $n = $generator->getOrder();
        $modMath = $math->getModularArithmetic( $n );
        $prikey = $key->getSecret();
        //第一二步是userid, msg 生成 trucatedhash ,
        $count = 0;
        while (true) {
            $count++;
            // echo "count: $count\n";
            if($count >5){
                throw new \RuntimeException( 'Error: sign R or S = 0' );
            }
            // 第三步生成随机数
            $k = $math->mod( $randomK, $n );
            // 第四步 计算pt1(x1,y1) = [K]G这个点
            // 生成一个新的点P = kG
            $p1 = $generator->mul( $k );
            // var_dump($p1);die();
            // 第五步 计算 r = (truncatedHash + x1) mod n
            $r = $modMath->add($truncatedHash,$p1->getX());
            // var_dump(gmp_strval($r,16));die();
            $zero = gmp_init( 0, 10 );
            if ( $math->equals( $r, $zero ) ) {
                // @todo 如报错，重来 
                // continue; //报错重来一次  
                // @todo
                throw new \RuntimeException( 'Error: random number R = 0' );
            }
            // 第六步 计算 s = ((1 + d)^-1 * (k - rd)) mod n
            
            $one = gmp_init(1,10);
            $s1 = $math->inverseMod($math->add($one, $prikey),$n );
            // print_r(gmp_strval($s1,16));die();
            $s2 = $math->sub($k,$math->mul($r,$prikey));
            // print_r(gmp_strval($s2,16));die();
            $s = $modMath->mul($s1,$s2);
            // var_dump($generator->mul($s));die();
            if ( $math->equals( $s, $zero ) ) {
                // continue;
                throw new \RuntimeException( 'Error: random number S = 0' );
            }
            return new Signature( $r, $s );
        }
    }

    /**
    * @param PublicKeyInterface $key
    * @param SignatureInterface $signature
    * @param \GMP $hash
    * @return bool
    */

    public function verify( PublicKeyInterface $key, SignatureInterface $signature, \GMP $hash ): bool {

        $generator = $key->getGenerator();
        // var_dump($generator);die();
        $n = $generator->getOrder();
        $r = $signature->getR();
        $s = $signature->getS();
        $math = $this->adapter;
        $one = gmp_init( 1, 10 );
        if ( $math->cmp( $r, $one ) < 0 || $math->cmp( $r, $math->sub( $n, $one ) ) > 0 ) {
            return false;
        }

        if ( $math->cmp( $s, $one ) < 0 || $math->cmp( $s, $math->sub( $n, $one ) ) > 0 ) {
            return false;
        }
        
        // 1.2.3.4 sm3 取msg,userid的 hash值,这里直接就传过来了，
        $modMath = $math->getModularArithmetic( $n );
        // 第五步 计算t=(r'+s')mod n
        $t = $modMath->add($r,$s);    
        // // 第六步 计算(x1,y1) = [s]G + [t]PA
        $p1 = $generator->mul($s); // p1 = sG 是OK的与签名生成的sG一样
        $p2 = $key->getPoint()->mul($t);
        $xy = $p1->add($p2);
        // // 第七步 R=(e' + x1') 验证R==r'?       
        $v = $modMath->add($hash, $xy->getX());

        return BinaryString::constantTimeCompare( $math->toString( $v ), $math->toString( $r ) );
    }
}
