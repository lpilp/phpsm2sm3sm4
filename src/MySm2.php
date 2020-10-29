<?php
include_once __DIR__ . '/../vendor/autoload.php';

// use Mdanter\Ecc\Crypto\Signature\SignHasher;
use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Crypto\Signature\Signer;
use Mdanter\Ecc\Crypto\Signature\Sm2Signer;
use Mdanter\Ecc\Crypto\Key\PrivateKey;
use Mdanter\Ecc\Crypto\Key\PublicKey;
use Mdanter\Ecc\Primitives\Point;
use Mdanter\Ecc\Serializer\PrivateKey\PemPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PrivateKey\DerPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\PemPublicKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use Mdanter\Ecc\Serializer\Signature\DerSignatureSerializer;

use Mdanter\Ecc\SM2\Sm2WithSm3;
use Mdanter\Ecc\SM2\Hex2ByteBuf;

class MySm2 {
    protected $adapter = null;
    protected $generator = null;
    protected $userId = '1234567812345678';
    // 是否固定签名不随机，好处是同一段参数的签名固定，增大别人的猜测的难度
    protected $useDerandomizedSignatures = true;
    //输入输出的签名方式 16进制的还是base64
    protected $formatSign = 'hex'; 
    //可扩展自定义多种返回签名方式
    protected $arrFormat = ['hex','base64'];

    function __construct($formatSign='hex') {
        $this->adapter = EccFactory::getAdapter();
        $this->generator = EccFactory::getNistCurves()->generatorSm2();
        if(in_array($formatSign,$this->arrFormat)){
            $this->formatSign = $formatSign;
        } else {
            $this->formatSign = 'hex';
        }
    }

    /**
    * 随机生成一对16进制明文公私钥
    */
    public function generatekey() {
        $adapter = $this->adapter;
        $generator = $this->generator;
        //随机生成一个私钥类
        $private  = $generator->createPrivateKey();
        //取出私钥16进制表示出来
        $privateKey = $adapter->decHex( $private->getSecret() );
        //取出公钥的椭圆点
        $pubPoint = $private->getPublicKey()->getPoint();
        //公钥上的点x, y
        $pubX = $adapter->decHex( $pubPoint->getX() );
        $pubY = $adapter->decHex( $pubPoint->getY() );
        $publicKey = '04'.$pubX.$pubY;
        return [$privateKey, $publicKey];

    }

    /**
    * 随机生成一对PEM编码公私钥
    */
    public function generatePemkey() {
        $adapter = $this->adapter;
        $generator = $this->generator;
        $private  = $generator->createPrivateKey();
        $derSerializer = new DerPrivateKeySerializer( $adapter );
        // der包 ans1编码 1 版本号 2私钥 3 oid  4 公钥  四组数据
        $der = $derSerializer->serialize( $private );
        // der与pem的区别在于 der二进制表示， pem将der base64 每64个字符切开一行，再在头尾加上注释----- xxx PEM SIGN---- 之类
        // echo sprintf( 'DER encoding:\n%s\n\n', base64_encode( $der ) );
        $pemSerializer = new PemPrivateKeySerializer( $derSerializer );
        $privateKeyPem = $pemSerializer->serialize( $private );

        $derPubSerializer = new DerPublicKeySerializer( $adapter );
        $pemPubSerializer = new PemPublicKeySerializer( $derPubSerializer );
        $publicKeyPem = $pemPubSerializer->serialize( $private->getPublicKey() );
        return [$privateKeyPem, $publicKeyPem];
    }

    public function doSign( $document, $privateKey, $userId = null ) {
        if ( empty( $userId ) ) {
            $userId = $this->userId;
        }
        $adapter = $this->adapter;
        $generator = $this->generator;
        $algorithm = 'sha256';
        $secret = gmp_init( $privateKey, 16 );
        $key = new PrivateKey( $adapter, $generator, $secret );
        return $this->_dosign( $document, $key, $adapter, $generator, $userId, $algorithm );
    }

    public function doSignOutKey( $document, $privateKeyFile, $userId = null ) {
        if ( empty( $userId ) ) {
            $userId = $this->userId;
        }
        if ( !file_exists( $privateKeyFile ) ) {
            throw new Exception( 'privatekey file not exists' );
        }
        $adapter = $this->adapter;
        $generator = $this->generator;
        //这个sha256 只是生成随机数时用到，和主体算法无关
        $algorithm = 'sha256';

        $pemSerializer = new PemPrivateKeySerializer( new DerPrivateKeySerializer( $adapter ) );
        $keyData = file_get_contents( $privateKeyFile );

        $key = $pemSerializer->parse( $keyData );

        return $this->_dosign( $document, $key, $adapter, $generator, $userId, $algorithm );
    }

    protected function _dosign( $document, $key, $adapter,$generator, $userId, $algorithm = 'sha256' ) {
        $publickey = $key->getPublicKey();

        $obPoint = $key->getPublicKey()->getPoint();

        $pubKeyX = $adapter->decHex( $obPoint->getX() );
        $pubKeyY = $adapter->decHex( $obPoint->getY() );

        $hash = $this->_doS3Hash( $document, $pubKeyX, $pubKeyY, $generator, $userId );

        # Derandomized signatures are not necessary, but is avoids
        # the risk of a low entropy RNG, causing accidental reuse
        # of a k value for a different message, which leaks the
        # private key.
        if ( $this->useDerandomizedSignatures ) {
            $random = \Mdanter\Ecc\Random\RandomGeneratorFactory::getHmacRandomGenerator( $key, $hash, $algorithm );
        } else {
            $random = \Mdanter\Ecc\Random\RandomGeneratorFactory::getRandomGenerator();
        }

        $randomK = $random->generate( $generator->getOrder() );

        $signer = new Sm2Signer( $adapter );
        $signature = $signer->sign( $key, $hash, $randomK );

        $serializer = new DerSignatureSerializer();
        $serializedSig = $serializer->serialize( $signature );

        if($this->formatSign == 'hex') {
            return bin2hex($serializedSig);
        } else if($this->formatSign == 'base64' ) {
            return base64_encode( $serializedSig ) . PHP_EOL;
        }
        //缺省 hex
        return bin2hex($serializedSig);
    }

    public function verifySign( $document, $sign, $publicKey, $userId = null ) {
        $adapter = $this->adapter;
        $generator = $this->generator;
        if ( empty( $userId ) ) {
            $userId = $this->userId;
        }

        if($this->formatSign == 'hex') {
            $sigData = hex2bin($sign);
        } else if($this->formatSign == 'base64' ) {
            $sigData = base64_decode( $sign );
        } else {
            $sigData = hex2bin($sign);
        }
        // Parse signature
        $sigSerializer = new DerSignatureSerializer();
        $sig = $sigSerializer->parse( $sigData );

        // get hash
        list( $pubKeyX, $pubKeyY ) = $this->_getKeyXY( $publicKey );

        $hash = $this->_doS3Hash( $document, $pubKeyX, $pubKeyY, $generator, $userId );

        // get pubkey parse
        $key = $this->_getPubKeyObject( $pubKeyX, $pubKeyY );

        $signer = new Sm2Signer( $adapter );
        return  $signer->verify( $key, $sig, $hash );
    }

    public function verifySignOutKey( $document, $sign, $publickeyFile, $userId = null ) {

        if ( empty( $userId ) ) {
            $userId = $this->userId;
        }
        if ( !file_exists( $publickeyFile ) ) {
            throw new Exception( 'publickey file not exists' );
        }
        $adapter = $this->adapter;
        $generator = $this->generator;

        if($this->formatSign == 'hex') {
            $sigData = hex2bin($sign);
        } else if($this->formatSign == 'base64' ) {
            $sigData = base64_decode( $sign );
        } else {
            $sigData = hex2bin($sign);
        }

        // Parse signature
        $sigSerializer = new DerSignatureSerializer();
        $sig = $sigSerializer->parse( $sigData );

        // Parse public key
        $keyData = file_get_contents( $publickeyFile );
        $derSerializer = new DerPublicKeySerializer( $adapter );
        $pemSerializer = new PemPublicKeySerializer( $derSerializer );
        $key = $pemSerializer->parse( $keyData );

        $pubKeyX = $adapter->decHex( $key->getPoint()->getX() );
        $pubKeyY = $adapter->decHex( $key->getPoint()->getY() );
        $hash = $this->_doS3Hash( $document, $pubKeyX, $pubKeyY, $generator, $userId );
        $signer = new Sm2Signer( $adapter );

        return $signer->verify( $key, $sig, $hash );
    }
    /**
    *
    */
    protected function _doS3Hash( $document, $pubKeyX, $pubKeyY, $generator, $userId ) {
        $hasher = new Sm2WithSm3();
        $hash = $hasher->getSm3Hash( $document, $pubKeyX, $pubKeyY, $generator, $userId );
        return  gmp_init( Hex2ByteBuf::ByteArrayToHexString( $hash ), 16 );
    }

    protected function _getKeyXY( $publicKey ) {
        if ( strlen( $publicKey ) == 128 ) {
            $pubKeyX = substr( $publicKey, 0, 64 );
            $pubKeyY = substr( $publicKey, -64 );
        } else if ( strlen( $publicKey ) == 130 && substr( $publicKey, 0, 2 ) == '04' ) {
            $pubKeyX = substr( $publicKey, 2, 64 );
            $pubKeyY = substr( $publicKey, -64 );
        } else {
            throw new Exception( 'publickey format error' );
        }
        return [$pubKeyX, $pubKeyY];
    }

    protected function _getPubKeyObject( $pubKeyX, $pubKeyY ) {
        $generator = $this->generator;
        // __construct( GmpMathInterface $adapter, CurveFpInterface $curve, \GMP $x, \GMP $y, \GMP $order = null, bool $infinity = false )
        $x = gmp_init( $pubKeyX, 16 );
        $y = gmp_init( $pubKeyY, 16 );
        $point = new Point( $this->adapter, $generator->getCurve(), $x, $y );

        // __construct( GmpMathInterface $adapter, GeneratorPoint $generator, PointInterface $point )
        return new PublicKey( $this->adapter, $this->generator, $point );
    }

    protected function _str2hex($str){
        $res = array();
        for($i=0; $i<strlen($str);$i++){
            $res[$i] = sprintf("%02x",ord($str[$i]));
        }
        return implode("",$res);
    }
}
