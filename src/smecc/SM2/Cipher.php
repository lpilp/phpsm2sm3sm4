<?php
//
namespace Rtgm\smecc\SM2;

use Rtgm\smecc\SM3\SM3Digest;
use Rtgm\sm\RtSm2;

class Cipher
{
    private $ct = 1;

    private  $p2;
    /**
     * @var SM3Digest
     */
    private  $sm3keybase;
    /**
     * @var SM3Digest
     */
    private  $sm3c3;

    private  $key = array();
    private  $keyOff = 0;


    private function  Reset() //注意，加密使用无符号的数组转换，以便与硬件相一致
    {
        $this->sm3keybase = new SM3Digest();
        $this->sm3c3 = new SM3Digest();

        $p = array();

        $gmp_x = $this->p2->GetX();
        $x = Hex2ByteBuf::ConvertGmp2ByteArray($gmp_x);
        $this->sm3keybase->BlockUpdate($x, 0, sizeof($x));
        $this->sm3c3->BlockUpdate($x, 0, sizeof($x));

        $gmp_y = $this->p2->GetY();
        $y = Hex2ByteBuf::ConvertGmp2ByteArray($gmp_y);
        $this->sm3keybase->BlockUpdate($y, 0, sizeof($y));

        $this->ct = 1;
        $this->NextKey();
    }
    public function initEncipher($userPoint, $foreignKey = null)
    {
        if (empty($foreignKey)) {
            $sm2 = new RtSm2();
            $foreignKey = $sm2->generatekey();
        }
        $foreignPriKey = $foreignKey[0];
        $foreignPubKey = $foreignKey[1];
        $this->p2 = $userPoint->mul(gmp_init($foreignPriKey, 16));
        $this->reset();
        return substr($foreignPubKey, -128);
    }
    public function initDecipher($userPoint, $privateKey)
    {
        $this->p2 = $userPoint->mul(gmp_init($privateKey, 16));
        $this->reset();
    }

    private function  NextKey()
    {
        $sm3keycur = new SM3Digest();
        $sm3keycur->setSM3Digest($this->sm3keybase);
        $sm3keycur->Update(($this->ct >> 24 & 0x00ff));
        $sm3keycur->Update(($this->ct >> 16 & 0x00ff));
        $sm3keycur->Update(($this->ct >> 8 & 0x00ff));
        $sm3keycur->Update(($this->ct & 0x00ff));
        $sm3keycur->DoFinal($this->key, 0);
        $this->keyOff = 0;
        $this->ct++;
    }

    public function  encryptBlock($data)
    {
        $len  = count($data);
        $this->sm3c3->BlockUpdate($data, 0, $len);
        // print_r($data);die();
        for ($i = 0; $i < $len; $i++) {
            if ($this->keyOff == sizeof($this->key)) {
                $this->NextKey();
            }

            $data[$i] ^= $this->key[$this->keyOff++];
        }
        return $data;
    }



    public function decryptBlock($data)
    {
        $len  = count($data);
        for ($i = 0; $i < $len; $i++) {
            if ($this->keyOff == sizeof($this->key))
                $this->NextKey();

            $data[$i] ^= $this->key[$this->keyOff++];
        }
        $this->sm3c3->BlockUpdate($data, 0, $len);
        return $data;
    }

    public function  Dofinal()
    {
        $c3 = array();
        $gmp_p = $this->p2->GetY();
        $p = Hex2ByteBuf::ConvertGmp2ByteArray($gmp_p);
        $this->sm3c3->BlockUpdate($p, 0, sizeof($p));
        $this->sm3c3->DoFinal($c3, 0);
        $this->Reset(); 
        return $c3;
    }
}
