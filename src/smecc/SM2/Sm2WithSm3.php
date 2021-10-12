<?php
//
namespace Rtgm\smecc\SM2;


use Rtgm\smecc\SM3\SM3Digest;

class Sm2WithSm3
{


    private function GetZ($userId, $HexPubKeyX,$HexPubKeyY,$generator)
    {
        //$PubKeyX_gmp = gmp_init($HexPubKeyX, 16);
       // $PubKeyY_gmp = gmp_init($HexPubKeyY, 16);

        $md = array();
        $sm3 = new SM3Digest();

        // userId length

        $id=unpack("C*",$userId);
        $len = sizeof($id)*8;
        $sm3->Update( ($len >> 8 & 0x00ff));
        $sm3->Update( ($len & 0x00ff));

        // userId
        $sm3->BlockUpdate($id, 1, sizeof($id));

        // a,b
        $gmp_a = $generator->getCurve()->GetA();
        $a=Hex2ByteBuf::ConvertGmp2ByteArray($gmp_a);
        $sm3->BlockUpdate($a, 0, sizeof($a));

        $gmp_b = $generator->getCurve()->GetB();
        $b=Hex2ByteBuf::ConvertGmp2ByteArray($gmp_b);
        $sm3->BlockUpdate($b, 0, sizeof($b));
        // gx,gy
        $gmp_gx = $generator->GetX();
        $gx=Hex2ByteBuf::ConvertGmp2ByteArray($gmp_gx);
        $sm3->BlockUpdate($gx, 0, sizeof($gx));

        $gmp_gy = $generator->GetY();
        $gy=Hex2ByteBuf::ConvertGmp2ByteArray($gmp_gy);
        $sm3->BlockUpdate($gy, 0, sizeof($gy));
        // x,y
        $bPubKeyX=array();
        $bPubKeyX=Hex2ByteBuf::HexStringToByteArray($HexPubKeyX);
        $sm3->BlockUpdate($bPubKeyX, 0, sizeof($bPubKeyX));
        $bPubKeyY=array();
        $bPubKeyY=Hex2ByteBuf::HexStringToByteArray($HexPubKeyY);
        $sm3->BlockUpdate($bPubKeyY, 0, sizeof($bPubKeyY));

        $sm3->DoFinal($md, 0);
        return $md;

    }

    private function GetE($z, $HashMsgValue)
    {
        $md = array();
        $sm3 = new SM3Digest();

        $sm3->BlockUpdate($z, 0, sizeof($z));
        // byte[] p = Encoding.Default.GetBytes(msg);
        // sm3.BlockUpdate(p, 0, p.Length);

        $sm3->BlockUpdate($HashMsgValue, 0, 32);

        $sm3->DoFinal($md, 0);
        return $md;

    }

    public function GetMsgHash( $msg)
    {
        $md = array();
        $sm3 = new SM3Digest();
        $msgArray=unpack("C*",$msg);
        $sm3->BlockUpdate($msgArray, 1, sizeof($msgArray));
        $sm3->DoFinal($md, 0);
        return $md;
    }


    private function Sm2Verify($md, $PubKeyX, $PubKeyY, $VerfiySign,$generator)
    {
        //SM2Result sm2Ret = new SM2Result();
        $InSignBuf = array();

        $InSignBuf=Hex2ByteBuf::HexStringToByteArray($VerfiySign);


        $Kx = gmp_init($PubKeyX, 16);
        $Ky = gmp_init($PubKeyY, 16);

        $PubKey = $generator->getPublicKeyFrom($Kx,$Ky,null);
        $r = gmp_init(substr($VerfiySign,0, 64), 16);
        $s = gmp_init(substr($VerfiySign,64, 64), 16);

        $ecc_point_g=$generator->getCurve()->getPoint($generator->GetX(), $generator->GetY());

        $Sm2Ret=$this->sub_Sm2Verify($md, $PubKey , $r, $s,$ecc_point_g,$generator);

        if (gmp_cmp($r,$Sm2Ret)==0)
        {
            return true;
        }
        else
            return false;

    }

    private  function  sub_Sm2Verify($md, $userKey,$r, $s,$ecc_point_g,$generator)
    {

        $generator = $userKey->getGenerator();
        $ecc_n = $generator->getOrder();

        // e_
        $md_gmp=Hex2ByteBuf::ByteArrayToHexString($md,sizeof($md));
        $e =gmp_init($md_gmp,16);
        // t
        $t=gmp_add($r,$s);
        $t=gmp_mod($t,$ecc_n);

        $zero = gmp_init(0, 10);

        if (gmp_cmp($t,$zero)==0)
            return null;


        // x1y1
        $x1y1 = $ecc_point_g->mul($s);
        $x1y1 = $x1y1->add($userKey->getPoint()->mul($t));

        // R
        return gmp_mod(gmp_add($e,$x1y1->GetX()),$ecc_n);
    }

    public function YtVerfiyBySoft($id, $msg, $PubKeyX, $PubKeyY, $VerfiySign,$generator)
    {


        $Z = array();
        $E = array();
        $MsgHashValue = array();

        $Z = $this->GetZ($id, $PubKeyX, $PubKeyY,$generator);
        $MsgHashValue = $this->GetMsgHash($msg);
        $E = $this->GetE($Z, $MsgHashValue);



        $IsVailSign = $this->Sm2Verify($E, $PubKeyX, $PubKeyY, $VerfiySign,$generator);
        return $IsVailSign;

    }
    public function getSm3Hash($msg,$PubKeyX, $PubKeyY, $generator, $userId="1234567812345678") {
        // sm3(z+msg)
        $z = $this->GetZ($userId, $PubKeyX, $PubKeyY,$generator);

        $md = array();
        $sm3 = new SM3Digest();
        $sm3->BlockUpdate($z, 0, sizeof($z));
        $msgArray=unpack("C*",$msg);
        $sm3->BlockUpdate($msgArray, 1, sizeof($msgArray));
        $sm3->DoFinal($md, 0);
        return $md;
    }

}
