<?php
namespace Rtgm\smecc\SPLSM2;

class SimpleSm2
{

    protected $userId = '1234567812345678';
    //请自行重新生成一对，示例中的foreignKey可能被大量项目使用，而被对方加黑
    protected $foreignKey= [
        '21fbd478026e2d668e3570e514de0d312e443d1e294c1ca785dfbfb5f74de225',
        '04e27c3780e7069bda7082a23a489d77587ce309583ed99253f66e1d9833ed1a1d0b5ce86dc6714e9974cf258589139d7b1855e8c9fa2f2c1175ee123a95a23e9b'  
    ];
    protected $p;
    protected $a;
    protected $b;
    protected $n;
    protected $gx;
    protected $gy;
    protected $privateKey;
    protected $publicKey;
    protected $sm3;

    function __construct($privateKey=null,$publicKey=null,$userId=null)
    {
         // 获取椭圆曲线参数
        $this->p = gmp_init("FFFFFFFEFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF00000000FFFFFFFFFFFFFFFF", 16);
        $this->a = gmp_init("FFFFFFFEFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF00000000FFFFFFFFFFFFFFFC", 16);
        $this->b = gmp_init("28E9FA9E9D9F5E344D5A9E4BCF6509A7F39789F515AB8F92DDBCBD414D940E93", 16);
        $this->n = gmp_init("FFFFFFFEFFFFFFFFFFFFFFFFFFFFFFFF7203DF6B21C6052B53BBF40939D54123", 16);
        
        // 计算基点G
        $this->gx = gmp_init("32C4AE2C1F1981195F9904466A39C9948FE30BBFF2660BE1715A4589334C74C7", 16);
        $this->gy = gmp_init("BC3736A2F4F6779C59BDCEE36B692153D0A9877CC62A474002DF32E52139F0A0", 16);
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
        $this->sm3 = new Sm3();
        if(!empty($this->userId)){
            $this->userId = $userId;
        }
    }

    public function set_private_key($privateKey){
        $this->privateKey = $privateKey;
    }
    public function set_public_key($publicKey){
        $this->publicKey = $publicKey;
    }
    /**
     * Undocumented function
     *
     * @param string $document
     * @param string $publicKey
     * @param string $userId
     * @return string
     */
    public function get_sm3_hash($document, $publicKey, $userId){
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
     * 
     *
     * @param string $document
     * @param string $prikey
     * @param string $publicKey  //这个值虽然可以从prikey中计算出来，但直接给出来，不用每次计算，性能会好一点点
     * @param string $userId
     * @return string
     */
    public function sign_raw($document, $prikey, $publicKey,$userId){
        $hash = $this->get_sm3_hash($document,$publicKey, $userId);
        $count = 0;
        while(true){
            $count++;
            if($count >5){
                //5次都有问题，肯定有问题了
                throw new \RuntimeException( 'Error: sign R or S = 0' );
            }
            $gmpHash = gmp_init($hash,16);
            //中间椭圆的私钥
            // $k = gmp_init('21fbd478026e2d668e3570e514de0d312e443d1e294c1ca785dfbfb5f74de225',16);
            $k = $this->_get_forign_prikey($document);           
            $gmpP1x = $this->_get_forign_pubkey_x($k);
            $r = gmp_mod(gmp_add($gmpHash,$gmpP1x),$this->n);
            $zero = gmp_init( 0, 10 );
            if ( gmp_cmp( $r, $zero ) ===0 ) {
                continue; //报错重来一次  
            }
            $gmpPrikey = gmp_init($prikey,16);
            $one = gmp_init(1, 10);
            $s1 = gmp_invert(gmp_add($one, $gmpPrikey), $this->n);
            $s2 = gmp_sub($k,gmp_mul($r,$gmpPrikey));
            $s = gmp_mod(gmp_mul($s1, $s2),$this->n);
 
            if (gmp_cmp($s, $zero) === 0) {
                continue;
                // throw new \RuntimeException('Error: random number S = 0');
            }
            return array($r,$s);
        }
    }

    /**
     * Undocumented function
     *
     * @param string $document  bin
     * @param string $publicKey hex
     * @param string $r hex
     * @param string $s hex
     * @param string $userId
     * @return void
     */
    public function verifty_sing_raw($document,$publicKey,$r,$s,$userId=null){
        $hash = $this->get_sm3_hash($document,$publicKey, $userId);
        $r = gmp_init($r,16);
        $s = gmp_init($s,16);
    }
    protected function _get_forign_prikey($document=''){
        return gmp_init($this->foreignKey[0],16);
    }
    protected function _get_forign_pubkey_x(){
        // @todo  这里先写写死的，也可以随机生成一个密码对
        $publicKey = $this->foreignKey[1];
        if(strlen($publicKey)==130){ //去掉04
            $px = gmp_init(substr($publicKey, 2, 64), 16);
        } else{
            $px = gmp_init(substr($publicKey, 0, 64), 16);
        }
        
        return $px;
    }
    protected function _get_entla($userId){
        $len = strlen($userId) * 8;
        $l1 = $len>>8 & 0x00ff;
        $l2 = $len & 0x00ff;
        return chr($l1).chr($l2);
    }
    protected function  _gmp_to_bin($gmp){
        return hex2bin(gmp_strval($gmp, 16));
    }
    /**
     * Undocumented function
     *
     * @param string $message
     * @param boolean $raw
     * @return string
     */
    public function hash_sm3($message,$raw = false){
        return $this->sm3->digest($message,$raw);
        // 有些版本的PHP直接支持sm3
        // return openssl_digest($message,'sm3',$raw);
    }


}
