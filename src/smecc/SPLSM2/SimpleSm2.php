<?php

namespace Rtgm\smecc\SPLSM2;

use Exception;

class SimpleSm2
{
    protected $p;
    protected $a;
    protected $b;
    protected $n;
    protected $gx;
    protected $gy;
    protected $size;
    protected $userId = '1234567812345678';
    //请自行重新生成一对，示例中的foreignKey可能被大量项目使用，而被对方加黑
    protected $foreignKey = array(
        '21fbd478026e2d668e3570e514de0d312e443d1e294c1ca785dfbfb5f74de225',
        '04e27c3780e7069bda7082a23a489d77587ce309583ed99253f66e1d9833ed1a1d0b5ce86dc6714e9974cf258589139d7b1855e8c9fa2f2c1175ee123a95a23e9b'
    );
    
    protected $privateKey;
    protected $publicKey;
    protected $sm3;
    protected $randSign = true; // true则同一字符串，同样密钥，每次签名不一样，false是每次签名都一样
    protected $randEnc = true;  // true则同一字符串，同样密钥，每次签名不一样，false是每次签名都一样
    protected $fixForeignKey = false; //中间了椭圆固定，在加密时减少生成密码对，性能有所提升，安全性有所下降，
    function __construct()
    {
        $this->sm3 = new Sm3();
        $eccParams = Sm2Ecc::get_params();
        $this->p = $eccParams['p'];
        $this->a = $eccParams['a'];
        $this->b = $eccParams['b'];
        $this->n = $eccParams['n'];
        // 计算基点G
        $this->gx = $eccParams['gx'];
        $this->gy = $eccParams['gy'];
    }

    public function  general_pair()
    {
        $pointG = new Sm2Point($this->gx, $this->gy);
        $prikeyGmp = $this->rand_prikey();
        $publicKey = $this->get_pkey_from_prikey($prikeyGmp, $pointG);
        $prikey = $this->decHex($prikeyGmp, 64);
        return array(
            $prikey, $publicKey
        );
    }

    /**
     * 通过私钥算公钥  Pub = pG
     *
     * @param GMP $prikey 
     * @return string
     */
    public function get_pkey_from_prikey($prikeyGmp, $pointG = null)
    {
        if (empty($pointG)) {
            $pointG = new Sm2Point($this->gx, $this->gy);
        }
        $kG = $pointG->mul($prikeyGmp, false);
        $x1 = $this->decHex($kG->getX(), 64);
        $y1 = $this->decHex($kG->getY(), 64);
        $publicKey = '04' . $x1 . $y1;
        return $publicKey;
    }
    // 生成标准的 base64 的 asn1(r,s)签名
    public function sign($document, $prikey, $publicKey = null, $userId = null){
        list($r,$s) = $this->sign_raw($document, $prikey, $publicKey, $userId);
        return Sm2Asn1::rs_2_asn1($r,$s);
    }
    /**
     * 
     *
     * @param string $document
     * @param string $prikey
     * @param string $publicKey  //这个值虽然可以从prikey中计算出来，但直接给出来，不用每次计算，性能会好一点点
     * @param string $userId
     * @return array
     */
    public function sign_raw($document, $prikey, $publicKey = null, $userId = null)
    {
        $gmpPrikey = gmp_init($prikey, 16);
        // 如果知道公钥直接填上是好的，减少运算，虽说从私钥可以算出公钥，这不浪费资源不是
        if (empty($publicKey)) {
            $publicKey = $this->get_pkey_from_prikey($gmpPrikey);
        }

        $hash = $this->get_sm2withsm3_hash($document, $publicKey, $userId);
        $gmpHash = gmp_init($hash, 16);

        $count = 0;
        while (true) {
            $count++;
            if ($count > 5) {
                //5次都有问题，肯定有问题了
                throw new \RuntimeException('Error: sign R or S = 0');
            }
            //中间椭圆的私钥
            // $k = gmp_init('21fbd478026e2d668e3570e514de0d312e443d1e294c1ca785dfbfb5f74de225',16);
            $k = $this->_get_forign_prikey($document);
            // var_dump(gmp_strval($k,16),'21fbd478026e2d668e3570e514de0d312e443d1e294c1ca785dfbfb5f74de225');
            $gmpP1x = $this->_get_forign_pubkey_x($k);
            $r = gmp_mod(gmp_add($gmpHash, $gmpP1x), $this->n);
            $zero = gmp_init(0, 10);
            if (gmp_cmp($r, $zero) === 0) {
                continue; //报错重来一次  
            }

            $one = gmp_init(1, 10);
            $s1 = gmp_invert(gmp_add($one, $gmpPrikey), $this->n);
            $s2 = gmp_sub($k, gmp_mul($r, $gmpPrikey));
            $s = gmp_mod(gmp_mul($s1, $s2), $this->n);

            if (gmp_cmp($s, $zero) === 0) {
                continue;
                // throw new \RuntimeException('Error: random number S = 0');
            }
            return array(gmp_strval($r, 16), gmp_strval($s, 16));
        }
    }
    // 标准的asn1 base64签名验签
    public function verify($document, $publicKey, $sign, $userId = null){
        list($hexR,$hexS) = Sm2Asn1::asn1_2_rs($sign);
        return $this->verifty_sign_raw($document, $publicKey, $hexR, $hexS, $userId);

    }
    /**
     * Undocumented function
     *
     * @param string $document  bin
     * @param string $publicKey hex
     * @param string $r hex
     * @param string $s hex
     * @param string $userId
     * @return bool
     */
    public function verifty_sign_raw($document, $publicKey, $hexR, $hexS, $userId = null)
    {
        $plen = strlen($publicKey);
        if ($plen == 130 && substr($publicKey, 0, 2) == '04') {
            $pubX = substr($publicKey, 2, 64);
            $pubY = substr($publicKey, -64);
        } else if ($plen == 128) {
            $pubX = substr($publicKey, 0, 64);
            $pubY = substr($publicKey, -64);
        } else {
            throw new Exception("bad publickey $publicKey");
        }
        // 1.2.3.4 sm3 取msg,userid的 hash值,
        $hash = gmp_init($this->get_sm2withsm3_hash($document, $publicKey, $userId), 16);

        $r = gmp_init($hexR, 16);
        $s = gmp_init($hexS, 16);
        $n = $this->n;

        $one = gmp_init(1, 10);
        if (gmp_cmp($r, $one) < 0 || gmp_cmp($r, gmp_sub($n, $one)) > 0) {
            return false;
        }

        if (gmp_cmp($s, $one) < 0 || gmp_cmp($s, gmp_sub($n, $one)) > 0) {
            return false;
        }

        // 第五步 计算t=(r'+s')mod n
        $t = gmp_mod(gmp_add($r, $s), $n);
        // // 第六步 计算(x1,y1) = [s]G + [t]P
        $pointG = new Sm2Point($this->gx, $this->gy); //生成基准点
        $p1 = $pointG->mul($s, false); // p1 = sG
        $pointPub = new Sm2Point(gmp_init($pubX, 16), gmp_init($pubY, 16)); //生成公钥的基准点
        $p2 = $pointPub->mul($t, false); // p2 = tP
        $xy = $p1->add($p2);
        // // 第七步 vR=(hash' + x1')    
        $v = gmp_mod(gmp_add($hash, $xy->getX()), $n);

        // 最后结果 比较 $v和$r是否一致
        // var_dump(gmp_strval($v,16),$hexR);    
        return gmp_strval($v, 16) == $hexR;
    }


    public function encrypt_raw($publicKey, $data)
    {
        list($pubX, $pubY) = $this->_get_pub_xy($publicKey);
        $point = new Sm2Point($pubX,$pubY);
        $t = '';
        $count = 0;
        while (!$t) {
            $count++;
            if($count>5){
                throw new Exception('bad kdf '); // 这处一般是生成的$k问题，5次都有问题，这运气差的可以买双色球了
            }

            if($this->fixForeignKey) { //使用固定的第中间椭圆
                list($x1,$y1) = $this->_get_pub_xy($this->foreignKey[1],false);
                $k = gmp_init($this->foreignKey[0],16);

                $x1 = $this->format_hex($x1,64);// 不足前面补0
                $y1 = $this->format_hex($y1,64);// 不足前面补0
            } else {
                $k = $this->_get_forign_prikey($data.'_'.$count);
                //dump($k);
                //$k = gmp_init('104953050056413721046883757640585885959005820148174417356964987920496726278110',10);
                $kG = $point->mul($k);
                $x1 = $this->decHex($kG->getX(), 64);
                $y1 = $this->decHex($kG->getY(), 64);
            }
            $c1 = $x1 . $y1;
            $kPb = $point->mul($k, false);
            $x2 = gmp_strval($kPb->getX(), 16);
            $y2 = gmp_strval($kPb->getY(), 16);
            $x2 = pack('H*', str_pad($x2, 64, 0, STR_PAD_LEFT));
            $y2 = pack('H*', str_pad($y2, 64, 0, STR_PAD_LEFT));
            $t = $this->kdf($x2 . $y2, strlen($data));
        }
        $c2 = gmp_xor(gmp_init($t, 16), $this->str2gmp($data));
        $c2 = $this->decHex($c2, strlen($data) * 2);
        $c3 = $this->hash_sm3($x2 . $data . $y2);
        return array($c1, $c3, $c2);
    }


    /**
     * sm2非对称解密
     *
     * @param string $prikey 私钥明文 hex
     * @param string  $c1 hex
     * @param string  $c3 hex
     * @param string  $c2 hex
     * @return string  decode($c2) 解密结果
     */
    public function decrypt_raw($prikey, $c1,$c3,$c2)
    {
        list($x1, $y1) = $this->_get_pub_xy($c1);
        $point = new Sm2Point($x1,$y1);
        $dbC1 = $point->mul(gmp_init($prikey,16), false);
        $x2 = gmp_strval($dbC1->getX(), 16);
        $y2 = gmp_strval($dbC1->getY(), 16);
        $x2 = pack('H*', str_pad($x2, 64, 0, STR_PAD_LEFT));
        $y2 = pack('H*', str_pad($y2, 64, 0, STR_PAD_LEFT));
        $len = strlen($c2);
        $t = $this->kdf($x2 . $y2, $len / 2);  // 转成16进制后 字符长度要除以2        
        $m1 = gmp_strval(gmp_xor(gmp_init($t, 16), gmp_init($c2, 16)), 16);
        $m1 = pack("H*", $m1);
        $u = $this->hash_sm3($x2 . $m1 . $y2);

        if (strtoupper($u) != strtoupper($c3)) {
            throw new \Exception("error decrypt data");
        }

        return $m1;
    }
    protected function kdf($z, $klen)
    {
        $res = '';
        $ct = 1;
        $j = ceil($klen / 32);
        for ($i = 0; $i < $j; $i++) {
            $ctStr = str_pad(chr($ct), 4, chr(0), STR_PAD_LEFT);
            $hex = $this->hash_sm3($z . $ctStr);
            if ($i + 1 == $j && $klen % 32 != 0) {  // 最后一个 且 $klen/$v 不是整数
                $res .= substr($hex, 0, ($klen % 32) * 2); // 16进制比byte长度少一半 要乘2
            } else {
                $res .= $hex;
            }
            $ct++;
        }

        return $res;
    }
    /**
     * Undocumented function
     *
     * @param string $message
     * @param boolean $raw
     * @return string
     */
    public function hash_sm3($message, $raw = false)
    {
        // return $this->sm3->digest($message, $raw);
        // 有些版本的PHP直接支持sm3
        return openssl_digest($message,'sm3',$raw);
    }
    /**
     * hex 用0补齐一定的位置
     *
     * @param string $hex
     * @param integer $count
     * @return string
     */
    public function format_hex($hex, $count = 64)
    {
        return str_pad($hex, $count, "0", STR_PAD_LEFT);
    }
    /**
     * 采用gmp自带的函数随机生成私钥，gmp_random_bits需要5.6.3才有
     * 也可其他随机函数生成
     * 
     * @param integer $numBits
     * @return \GMP
     */
    public function rand_prikey($numBits = 256)
    {
        if (!function_exists('gmp_random_bits')) {
            return $this->_get_forign_prikey('loveyou');
        }
        $value = gmp_random_bits($numBits);
        $mask = gmp_sub(gmp_pow(2, $numBits), 1);
        $integer = gmp_and($value, $mask);

        return $integer;
    }
    /**
     * gmp 转 hex,并用0补齐位数
     *
     * @param GMP|int $dec
     * @param integer $len
     * @return string
     */
    public function decHex($dec, $len = 0)
    {
        if (!$dec instanceof \GMP) {
            $dec = gmp_init($dec, 10);
        }
        if (gmp_cmp($dec, 0) < 0) {
            throw new \Exception('Unable to convert negative integer to string');
        }

        $hex = gmp_strval($dec, 16);

        if (strlen($hex) % 2 != 0) {
            $hex = '0' . $hex;
        }
        if ($len && strlen($hex) < $len) {  // point x y 要补齐 64 位
            $hex = str_pad($hex, $len, "0", STR_PAD_LEFT);
        }

        return $hex;
    }



    /**
     * Undocumented function
     *
     * @param string $document
     * @param string $publicKey
     * @param string $userId
     * @return string
     */
    public function get_sm2withsm3_hash($document, $publicKey, $userId)
    {
        //  置M’=ZA∥M；ZA= Hv(ENTLA||IDA||a||b||Gx||Gy||Ax||Ay)； IDA==>userId
        // ENTLA为IDA的比特长度，2字节；IDA用户标识默认值见上节；a,b,Gx,Gy见曲线参数；Ax,Ay为公钥坐标
        $len = strlen($publicKey);
        if ($len == 130) {
            $publicKey = substr($publicKey, 2);
        } else if ($len == 128) {
            //OK
        } else {
            throw new \Exception('bad pulickey');
        }
        $px = gmp_init(substr($publicKey, 0, 64), 16);
        $py = gmp_init(substr($publicKey, 64, 64), 16);
        $zStr = $this->_get_entla($userId);
        $zStr .= $userId;
        $zStr .= hex2bin(gmp_strval($this->a, 16));
        $zStr .= hex2bin(gmp_strval($this->b, 16));
        $zStr .= hex2bin(gmp_strval($this->gx, 16));
        $zStr .= hex2bin(gmp_strval($this->gy, 16));
        $zStr .= hex2bin(gmp_strval($px, 16));
        $zStr .= hex2bin(gmp_strval($py, 16));
        $hashStr = $this->hash_sm3($zStr);
        $hash = $this->hash_sm3(hex2bin($hashStr) . $document);
        return $hash;
    }
    /**
     * 生成随机私钥
     *
     * @param string $document
     * @return \GMP
     */
    protected function _get_forign_prikey($document = '')
    {
        // 要支持php5的话，没有什么好函数了，如果是php7或以上或以使用 
        // $s = random_bytes(64) 或  this->rand_prikey(int bits=256)  代替
        if ($this->randSign || $this->randEnc) { // 从document ==>k 变化
            $s = substr(openssl_digest('S1' . $document . microtime(), 'sha1'), 1, 32) . md5($document . microtime() . 'S2');
        } else {
            $s = substr(openssl_digest('S1' . $document, 'sha1'), 1, 32) . md5($document . 'S2');
        }

        $s = strtolower($s);
        if (substr($s, 0, 1) == 'f') { //私钥不要太大了，超过 n值就不好了，
            $s =  'e' . substr($s, 1);
        }
        return gmp_init($s, 16);
    }
    protected function _get_forign_pubkey_x($k)
    {
        $pointG = new Sm2Point($this->gx, $this->gy);
        $kG = $pointG->mul($k, false);
        return $kG->getX();
    }
    protected function _get_entla($userId)
    {
        $len = strlen($userId) * 8;
        $l1 = $len >> 8 & 0x00ff;
        $l2 = $len & 0x00ff;
        return chr($l1) . chr($l2);
    }
    protected function  _gmp_to_bin($gmp)
    {
        return hex2bin(gmp_strval($gmp, 16));
    }

    public function set_private_key($privateKey)
    {
        $this->privateKey = $privateKey;
    }
    public function set_public_key($publicKey)
    {
        $this->publicKey = $publicKey;
    }

    public function set_userid($userId)
    {
        if (empty($userId) || strlen($userId) != 16) {
            throw new Exception(" userid 格式不对");
        }
        $this->userId = $userId;
    }
    public function set_rand_sign_flag($flag = false)
    {
        $this->randSign  = $flag;
    }
    public function set_rand_enc_flag($flag = false)
    {
        $this->randEnc  = $flag;
    }

    public function set_fix_foreignkey_flag($flag = true){
        $this->fixForeignKey = $flag;
    }
    public function str2gmp($string)
    {
        $hex = unpack('H*', $string);

        return gmp_init($hex[1], 16);
    }

    protected function _get_pub_xy($publicKey,$rtGmp = true){
        $plen = strlen($publicKey);
        if ($plen == 130 && substr($publicKey, 0, 2) == '04') {
            $pubX = substr($publicKey, 2, 64);
            $pubY = substr($publicKey, -64);
        } else if ($plen == 128) {
            $pubX = substr($publicKey, 0, 64);
            $pubY = substr($publicKey, -64);
        } else {
            throw new Exception("bad publickey $publicKey");
        }
        if($rtGmp){
            return array(gmp_init($pubX,16),gmp_init($pubY,16));
        }
        // var_dump($publicKey,$pubX, $pubY,'==============');
        return array($pubX, $pubY);
    }
}
