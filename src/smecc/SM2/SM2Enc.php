<?php
namespace Rtgm\smecc\SM2;

use Rtgm\smecc\SM3\SM3Digest;

class SM2Enc
{

    private const  SM2_ADDBYTE = 97;//加密后的数据会增加的长度
    private const  MAX_ENCLEN = 128; //最大的加密长度分组
    private const  MAX_DECLEN = (self::MAX_ENCLEN + self::SM2_ADDBYTE); //最大的解密长度分组

/*     public function SM2_EncStringBySoft($InString, $PubKeyX, $PubKeyY, $generator)
    {

        $Kx = gmp_init($PubKeyX, 16);
        $Ky = gmp_init($PubKeyY, 16);
        $userKey = $generator->getPublicKeyFrom($Kx,$Ky,null);

        $n = 0;
        $incount = 0;
        $outcount = 0;
        $temp_InBuf = array();
        $temp_OutBuf = array();
        $inlen = strlen($InString) + 1;
        $outlen = ($inlen / $this::MAX_ENCLEN + 1) * $this::SM2_ADDBYTE + $inlen;
        $OutBuf = array();
        $InBuf = array();
        $InBuf=unpack("C*",$InString);
        $InBuf[$inlen]=0;//这样是为了保挂与其它开发语言一致
        $ret = 0;
        $temp_inlen = 0;
        while ( $inlen > 0)
        {
            if ( $inlen > $this::MAX_ENCLEN)
                $temp_inlen = $this::MAX_ENCLEN;
            else
                $temp_inlen =  $inlen;
            for ( $n = 0;  $n <  $temp_inlen;  $n++)
            {
                $temp_InBuf[$n] =  $InBuf[$incount +  $n + 1];//注意，这里要加1，因为UNPACK后是从1开始
            }
            $temp_OutBuf=$this->sub_EncBufBySoft($temp_InBuf,  $temp_inlen, $userKey);
            for ( $n = 0; $n < ($temp_inlen + $this::SM2_ADDBYTE); $n++)
            {
                $OutBuf[ $outcount +  $n] =  $temp_OutBuf[$n];
            }
            $inlen =  $inlen - $this::MAX_ENCLEN;
            $incount =  $incount + $this::MAX_ENCLEN;
            $outcount =  $outcount + $this::MAX_DECLEN;
        }
        return Hex2ByteBuf::ByteArrayToHexString( $OutBuf,  sizeof($OutBuf));
    }


    private function sub_EncBufBySoft($InBuf, $InBuflen, $userKey)
    {

        $n = 0 ;
        $data = array();

        $data = $InBuf;


        $cipher = new Cipher();
        $c1 =  $cipher->Init_enc( $userKey);

        $bc1[0]=4;
        $gmp_x = $c1->getPoint()->GetX();
        $x=Hex2ByteBuf::ConvertGmp2ByteArray($gmp_x);
        SM3Digest::arraycopy($x,0,$bc1,1,sizeof($x));

        $gmp_y = $c1->getPoint()->GetY();
        $y=Hex2ByteBuf::ConvertGmp2ByteArray($gmp_y);
        SM3Digest::arraycopy($y,0,$bc1,1+32,sizeof($y));

        $c1_len = sizeof($bc1);

        $data=$cipher->Encrypt( $data ,$InBuflen);

        $c3 = array();
        $c3=$cipher->Dofinal( );

        $OutBuf=array();
        for ( $n = 0;  $n <  $c1_len;  $n++)
        {
            
            $OutBuf[$n] =  $bc1[ $n];
        }
        for ( $n = 0;  $n <  $InBuflen;  $n++)
        {
            $OutBuf[ $n +  $c1_len] =  $data[ $n];
        }
        for ( $n = 0;  $n < 32;  $n++)
        {
            $OutBuf[ $n +  $c1_len +  $InBuflen] =  $c3[ $n];
        }
        return $OutBuf;
    } */
}
