<?php

namespace Rtgm\sm;

define("C1C3C2", 1);
define("C1C2C3", 0);

use Rtgm\ecc\RtEccFactory;
use Rtgm\ecc\Sm2Signer;
use Mdanter\Ecc\Crypto\Key\PrivateKey;
use Mdanter\Ecc\Crypto\Key\PublicKey;
use Mdanter\Ecc\Primitives\Point;
use Mdanter\Ecc\Serializer\PrivateKey\PemPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PrivateKey\DerPrivateKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\PemPublicKeySerializer;
use Mdanter\Ecc\Serializer\PublicKey\DerPublicKeySerializer;
use Mdanter\Ecc\Serializer\Signature\DerSignatureSerializer;

use Rtgm\smecc\SM2\Sm2WithSm3;
use Rtgm\smecc\SM2\Hex2ByteBuf;

class RtSm2
{
    protected $adapter = null;
    protected $generator = null;
    protected $userId = '1234567812345678';
    // 是否固定签名不随机，好处是同一段参数的签名固定，增大别人的猜测的难度,
    // 同样的key + document每次签名是一样的，如果为false则每次不一样
    protected $useDerandomizedSignatures = true;
    // 是否固定加密不随机，算法中的是否每次都用不同的中间椭圆，如果固定的话，
    // 同样的文本加密后的数据是一样的，但速度会更快一些，随机的话，每次加密出来的数据不一样
    protected $useDerandomizedEncrypt = true;
    // 输入输出的签名方式 16进制的还是base64
    protected $formatSign = 'hex';
    // 可扩展自定义多种返回签名方式
    protected $arrFormat = ['hex', 'base64'];
    // 加密时的中间椭圆,取任意的sm2中间椭圆都可以，useDerandomizedEncrypt = true时使用，为false时每次加密，重新生成一个
    protected $foreignKey = [
        '21fbd478026e2d668e3570e514de0d312e443d1e294c1ca785dfbfb5f74de225',
        '04e27c3780e7069bda7082a23a489d77587ce309583ed99253f66e1d9833ed1a1d0b5ce86dc6714e9974cf258589139d7b1855e8c9fa2f2c1175ee123a95a23e9b'
    ];
    protected $cipher = null;
    /**
     * Undocumented function
     *
     * @param string $formatSign 
     * @param boolean $randFixed 是否使用中间椭圆，使用中间椭圆的话，速度会快一些，但同样的数据的签名或加密的值就固定了
     */
    function __construct($formatSign = 'hex', $randFixed = true)
    {
        $this->adapter = RtEccFactory::getAdapter();
        $this->generator = RtEccFactory::getSmCurves()->generatorSm2();
        if (in_array($formatSign, $this->arrFormat)) {
            $this->formatSign = $formatSign;
        } else {
            $this->formatSign = 'hex';
        }
        if (!$randFixed) {
            $this->useDerandomizedSignatures = false;
            $this->useDerandomizedEncrypt = false;
        }
    }


    /**
     * 随机生成一对16进制明文公私钥
     */
    public function generatekey()
    {
        // $adapter = $this->adapter;
        $generator = $this->generator;
        //随机生成一个私钥类
        $private  = $generator->createPrivateKey();
        //取出私钥16进制表示出来
        $privateKey = $this->decHex($private->getSecret());
        //取出公钥的椭圆点

        $pubPoint = $private->getPublicKey()->getPoint();
        //公钥上的点x, y
        $pubX = $this->decHex($pubPoint->getX());
        $pubY = $this->decHex($pubPoint->getY());
        $publicKey = '04' . $pubX . $pubY;
        return [$privateKey, $publicKey];
    }

    /**
     * 随机生成一对PEM编码公私钥
     */
    public function generatePemkey()
    {
        $adapter = $this->adapter;
        $generator = $this->generator;
        $private  = $generator->createPrivateKey();
        $derSerializer = new DerPrivateKeySerializer($adapter);
        // der包 ans1编码 1 版本号 2私钥 3 oid  4 公钥  四组数据
        // $der = $derSerializer->serialize( $private );
        $pemSerializer = new PemPrivateKeySerializer($derSerializer);
        $privateKeyPem = $pemSerializer->serialize($private);

        $derPubSerializer = new DerPublicKeySerializer($adapter);
        $pemPubSerializer = new PemPublicKeySerializer($derPubSerializer);
        $publicKeyPem = $pemPubSerializer->serialize($private->getPublicKey());


        return [$privateKeyPem, $publicKeyPem];
    }
    /**
     * SM2 公钥加密算法
     *
     * @param string $document
     * @param string $publicKey 如提供的base64的，可使用 bin2hex(base64_decode($publicKey))
     * @return string
     */
    public function doEncrypt($document, $publicKey, $model = C1C3C2)
    {
        $adapter = $this->adapter;
        $generator = $this->generator;
        $this->cipher = new \Rtgm\smecc\SM2\Cipher();
        $arrMsg = Hex2ByteBuf::HexStringToByteArray2(bin2hex($document));

        list($pubKeyX, $pubKeyY) = $this->_getKeyXY($publicKey);
        // $key = $this->_getPubKeyObject( $pubKeyX, $pubKeyY );
        $point = new Point($adapter, $generator->getCurve(), gmp_init($pubKeyX, 16), gmp_init($pubKeyY, 16));
        // 是否使用固定的中间椭圆加密，
        if ($this->useDerandomizedEncrypt) {
            $c1 = $this->cipher->initEncipher($point, $this->foreignKey);
        } else {
            $c1 = $this->cipher->initEncipher($point, null);
        }

        // print_r($c1);

        $arrMsg = $this->cipher->encryptBlock($arrMsg);
        $c2 = strtolower(Hex2ByteBuf::ByteArrayToHexString($arrMsg));
        // print_R($c2);echo "\n";
        $c3 = strtolower(Hex2ByteBuf::ByteArrayToHexString($this->cipher->Dofinal()));
        // print_r($c1.$c3.$c2);
        if ($model == C1C3C2) {
            return $c1 . $c3 . $c2;
        } else {
            return $c1 . $c2 . $c3;
        }
    }
    /**
     * SM2 私钥解密算法, 
     *
     * @param string $document
     * @param string $privateKey  如提供的base64的，可使用 bin2hex(base64_decode($privateKey))
     * @param bool $trim 是否做04开头的去除，看业务返回
     * @return string
     */
    public function doDecrypt($encryptData, $privateKey, $trim = true, $model = C1C3C2)
    {
        // $encryptData = $c1.$c3.$c2
        if (substr($encryptData, 0, 2) == '04' && $trim) {
            $encryptData = substr($encryptData, 2);
        }
        if (strlen($privateKey) == 66 && substr($privateKey, 0, 2) == '00') {
            $privateKey = substr($privateKey, 2); // 个别的key 前面带着00
        }
        $adapter = $this->adapter;
        $generator = $this->generator;
        $this->cipher = new \Rtgm\smecc\SM2\Cipher();
        $c1X = substr($encryptData, 0, 64);
        $c1Y = substr($encryptData, strlen($c1X), 64);
        $c1Length = strlen($c1X) + strlen($c1Y);
        if ($model == C1C3C2) {
            $c3 = substr($encryptData, $c1Length, 64);
            $c2 = substr($encryptData, $c1Length + strlen($c3));
        } else {
            $c3 = substr($encryptData, -64);
            $c2 = substr($encryptData, $c1Length, strlen($encryptData) - $c1Length - 64);
        }

        $p1 = new Point($adapter, $generator->getCurve(), gmp_init($c1X, 16), gmp_init($c1Y, 16));
        $this->cipher->initDecipher($p1, $privateKey);

        $arrMsg = Hex2ByteBuf::HexStringToByteArray2($c2);
        $arrMsg = $this->cipher->decryptBlock($arrMsg);
        $document = hex2bin(Hex2ByteBuf::ByteArrayToHexString($arrMsg));

        $c3_ = strtolower(Hex2ByteBuf::ByteArrayToHexString($this->cipher->Dofinal()));
        $c3 = strtolower($c3);
        if ($c3 == $c3_) { //hash签名相同，
            return $document;
        } else {
            return '';
        }
    }
    /**
     * SM2 签名明文16进制密码, 如提供的base64的，可使用 bin2hex(base64_decode($privateKey))
     *
     */
    public function doSign($document, $privateKey, $userId = null)
    {
        if (empty($userId)) {
            $userId = $this->userId;
        }
        $adapter = $this->adapter;
        $generator = $this->generator;
        $algorithm = 'sha256';
        $secret = gmp_init($privateKey, 16);
        $key = new PrivateKey($adapter, $generator, $secret);
        return $this->_dosign($document, $key, $adapter, $generator, $userId, $algorithm);
    }
    /**
     * SM2 签名pem密码 
     *
     */
    public function doSignOutKey($document, $privateKeyFile, $userId = null)
    {
        if (empty($userId)) {
            $userId = $this->userId;
        }
        if (!file_exists($privateKeyFile)) {
            throw new \Exception('privatekey file not exists');
        }
        $adapter = $this->adapter;
        $generator = $this->generator;
        //这个sha256 只是生成随机数时用到，和主体算法无关
        $algorithm = 'sha256';

        $pemSerializer = new PemPrivateKeySerializer(new DerPrivateKeySerializer($adapter));
        $keyData = file_get_contents($privateKeyFile);

        $key = $pemSerializer->parse($keyData);

        return $this->_dosign($document, $key, $adapter, $generator, $userId, $algorithm);
    }

    protected function _dosign($document, $key, $adapter, $generator, $userId, $algorithm = 'sha256')
    {
        // $publickey = $key->getPublicKey();

        $obPoint = $key->getPublicKey()->getPoint();

        $pubKeyX = $adapter->decHex($obPoint->getX());
        $pubKeyY = $adapter->decHex($obPoint->getY());

        $hash = $this->_doS3Hash($document, $pubKeyX, $pubKeyY, $generator, $userId);

        # Derandomized signatures are not necessary, but is avoids
        # the risk of a low entropy RNG, causing accidental reuse
        # of a k value for a different message, which leaks the
        # private key.
        if ($this->useDerandomizedSignatures) {
            $random = \Mdanter\Ecc\Random\RandomGeneratorFactory::getHmacRandomGenerator($key, $hash, $algorithm);
        } else {
            $random = \Mdanter\Ecc\Random\RandomGeneratorFactory::getRandomGenerator();
        }

        $randomK = $random->generate($generator->getOrder());

        $signer = new Sm2Signer($adapter);
        $signature = $signer->sign($key, $hash, $randomK);

        $serializer = new DerSignatureSerializer();
        $serializedSig = $serializer->serialize($signature);

        if ($this->formatSign == 'hex') {
            return bin2hex($serializedSig);
        } else if ($this->formatSign == 'base64') {
            return base64_encode($serializedSig) . PHP_EOL;
        }
        //缺省 hex
        return bin2hex($serializedSig);
    }

    
    public function verifySign($document, $sign, $publicKey, $userId = null)
    {
        $adapter = $this->adapter;
        $generator = $this->generator;
        if (empty($userId)) {
            $userId = $this->userId;
        }

        if ($this->formatSign == 'hex') {
            $sigData = hex2bin($sign);
        } else if ($this->formatSign == 'base64') {
            $sigData = base64_decode($sign);
        } else {
            $sigData = hex2bin($sign);
        }
        // Parse signature
        $sigSerializer = new DerSignatureSerializer();
        $sig = $sigSerializer->parse($sigData);

        // get hash
        list($pubKeyX, $pubKeyY) = $this->_getKeyXY($publicKey);

        $hash = $this->_doS3Hash($document, $pubKeyX, $pubKeyY, $generator, $userId);

        // get pubkey parse
        $key = $this->_getPubKeyObject($pubKeyX, $pubKeyY);

        $signer = new Sm2Signer($adapter);
        return  $signer->verify($key, $sig, $hash);
    }

    public function verifySignOutKey($document, $sign, $publickeyFile, $userId = null)
    {

        if (empty($userId)) {
            $userId = $this->userId;
        }
        if (!file_exists($publickeyFile)) {
            throw new \Exception('publickey file not exists');
        }
        $adapter = $this->adapter;
        $generator = $this->generator;

        if ($this->formatSign == 'hex') {
            $sigData = hex2bin($sign);
        } else if ($this->formatSign == 'base64') {
            $sigData = base64_decode($sign);
        } else {
            $sigData = hex2bin($sign);
        }

        // Parse signature
        $sigSerializer = new DerSignatureSerializer();
        $sig = $sigSerializer->parse($sigData);

        // Parse public key
        $keyData = file_get_contents($publickeyFile);
        $derSerializer = new DerPublicKeySerializer($adapter);
        $pemSerializer = new PemPublicKeySerializer($derSerializer);
        $key = $pemSerializer->parse($keyData);

        $pubKeyX = $this->decHex($key->getPoint()->getX());
        $pubKeyY = $this->decHex($key->getPoint()->getY());
        $hash = $this->_doS3Hash($document, $pubKeyX, $pubKeyY, $generator, $userId);
        $signer = new Sm2Signer($adapter);

        return $signer->verify($key, $sig, $hash);
    }
    /**
     *
     */
    protected function _doS3Hash($document, $pubKeyX, $pubKeyY, $generator, $userId)
    {
        $hasher = new Sm2WithSm3();
        $hash = $hasher->getSm3Hash($document, $pubKeyX, $pubKeyY, $generator, $userId);
        return  gmp_init(Hex2ByteBuf::ByteArrayToHexString($hash), 16);
    }

    protected function _getKeyXY($publicKey)
    {
        if (strlen($publicKey) == 128) {
            $pubKeyX = substr($publicKey, 0, 64);
            $pubKeyY = substr($publicKey, -64);
        } else if (strlen($publicKey) == 130 && substr($publicKey, 0, 2) == '04') {
            $pubKeyX = substr($publicKey, 2, 64);
            $pubKeyY = substr($publicKey, -64);
        } else {
            throw new \Exception('publickey format error');
        }
        return [$pubKeyX, $pubKeyY];
    }

    protected function _getPubKeyObject($pubKeyX, $pubKeyY)
    {
        $generator = $this->generator;
        // __construct( GmpMathInterface $adapter, CurveFpInterface $curve, \GMP $x, \GMP $y, \GMP $order = null, bool $infinity = false )
        $x = gmp_init($pubKeyX, 16);
        $y = gmp_init($pubKeyY, 16);
        $point = new Point($this->adapter, $generator->getCurve(), $x, $y);

        // __construct( GmpMathInterface $adapter, GeneratorPoint $generator, PointInterface $point )
        return new PublicKey($this->adapter, $this->generator, $point);
    }

    protected function _str2hex($str)
    {
        $res = array();
        for ($i = 0; $i < strlen($str); $i++) {
            $res[$i] = sprintf("%02x", ord($str[$i]));
        }
        return implode("", $res);
    }
    private function decHex($dec, $len = 64): string
    {
        if (gettype($dec) == 'string') {
            $dec = gmp_init($dec, 10);
        }
        if (gmp_cmp($dec, 0) < 0) {
            throw new \InvalidArgumentException('Unable to convert negative integer to string');
        }

        $hex = gmp_strval($dec, 16);

        /* if (strlen($hex) % 2 != 0) {
            $hex = '0'.$hex;
        } */
        $left = $len - strlen($hex);
        if ($left > 0) {
            $hex = str_repeat('0', $left) . $hex;
        }

        return $hex;
    }
}
