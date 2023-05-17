<?php
//declare(strict_types=1);
namespace Rtgm\smecc\SM3;

use Rtgm\smecc\SM3\GeneralDigest;

class SM3Digest extends  GeneralDigest
{
    public static $AlgorithmName="SM3";

    private const DIGEST_LENGTH = 32;

    public function  GetDigestSize():int
    {
        return $this::DIGEST_LENGTH;
    }
    

    private static $v0 = array(0x7380166f, 0x4914b2b9, 0x172442d7, -628488704,  -1452330820, 0x163138aa,-477237683,  -1325724082);
    
    private $v = array(0, 0, 0, 0, 0, 0, 0, 0);
    private $v_ = array(0, 0, 0, 0, 0, 0, 0, 0);
    
    private static $X0 = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
    
    private $X = array();
    private $xOff;
    
    private $T_00_15 = 0x79cc4519;
    private $T_16_63 = 0x7a879d8a;
    
    public function __construct()
    {
        parent::__construct(); 
       // parent::$gSM3Digest=$this;
        $this->Reset();
    }

    
    public function setSM3Digest($t)
    {
       parent::setGeneralDigest($t);
       $this->arraycopy($t->X, 0, $this->X, 0, sizeof($t->X));
         $this->xOff = $t->xOff;
        $this->arraycopy($t->v, 0, $this->v, 0, sizeof($t->v));
    }
    
    public function  Reset()
    {
        parent::Reset();
        
        $this->arraycopy(SM3Digest::$v0, 0, $this->v, 0, sizeof(SM3Digest::$v0));
        
        $this->xOff = 0;
        $this->arraycopy(SM3Digest::$X0, 0, $this->X, 0, sizeof(SM3Digest::$X0));
    }

    public static function arraycopy($InBuf,$InBufPos,&$OutBuf,$OutBufPos,$Len)
    {
        for( $n = 0 ;$n< $Len;$n++)
		{
		    $OutBuf[$n + $OutBufPos] =$InBuf[$n+$InBufPos];
		}
		
    }
	
    public function ProcessBlock()
    {
        $ww = $this->X;
        $ww_ = array();
        
        for ($i = 16; $i < 68; $i++)
        {
            $ww[$i] = $this->P1( $ww[ $i - 16] ^ $ww[$i - 9] ^ ($this->ROTATE($ww[$i - 3], 15))) ^ ($this->ROTATE($ww[$i - 13], 7)) ^ $ww[$i - 6];
        }
        
        for ($i = 0; $i < 64; $i++)
        {
            $ww_[$i] = $ww[$i] ^ $ww[$i + 4];
        }
        
        $vv = $this->v;
        $vv_ = $this->v_;
        
        $this->arraycopy($vv, 0, $vv_, 0, sizeof($vv));
        
       // int SS1, SS2, TT1, TT2, aaa;
        for ($i = 0; $i < 16; $i++)
        {
            $aaa = $this->ROTATE($vv_[0], 12);
            $SS1 = $this->MyAdd($aaa , $vv_[4]);
            $SS1 = $this->MyAdd($SS1, $this->ROTATE($this->T_00_15, $i));
            $SS1 = $this->ROTATE($SS1, 7);
            $SS2 = $SS1 ^ $aaa;
            
            $TT1=$this->FF_00_15($vv_[0], $vv_[1], $vv_[2]);
            $TT1=$this->MyAdd($TT1, $vv_[3]);
            $TT1=$this->MyAdd($TT1, $SS2);
            $TT1=$this->MyAdd($TT1, $ww_[$i]);
            
            $TT2=$this->GG_00_15($vv_[4], $vv_[5], $vv_[6]);
            $TT2=$this->MyAdd($TT2, $vv_[7]);
            $TT2=$this->MyAdd($TT2, $SS1);
            $TT2=$this->MyAdd($TT2, $ww[$i]);

            $vv_[3] = $vv_[2];
            $vv_[2] = $this->ROTATE($vv_[1], 9);
            $vv_[1] = $vv_[0];
            $vv_[0] = $TT1;
            $vv_[7] = $vv_[6];
            $vv_[6] = $this->ROTATE($vv_[5], 19);
            $vv_[5] = $vv_[4];
            $vv_[4] = $this->P0($TT2);
        }
        for ($i = 16; $i < 64; $i++)
        {
        
            $aaa = $this->ROTATE($vv_[0], 12);
            $SS1 = $this->MyAdd($aaa , $vv_[4]);
            $z= $this->ROTATE($this->T_16_63, $i);
            $SS1 = $this->MyAdd( $SS1, $this->ROTATE($this->T_16_63, $i));
            $SS1 = $this->ROTATE($SS1, 7);
            $SS2 = $SS1 ^ $aaa;
            
            $TT1 = $this->MyAdd($this->FF_16_63($vv_[0], $vv_[1], $vv_[2]) , $vv_[3]);
            $TT1 = $this->MyAdd( $TT1, $SS2);
            $TT1 = $this->MyAdd( $TT1, $ww_[$i]);

            $TT2 = $this->MyAdd($this->GG_16_63($vv_[4], $vv_[5], $vv_[6]) , $vv_[7]);
            $TT2 = $this->MyAdd( $TT2, $SS1);
            $TT2 = $this->MyAdd($TT2 , $ww[$i]);
            $vv_[3] = $vv_[2];
            $vv_[2] = $this->ROTATE($vv_[1], 9);
            $vv_[1] = $vv_[0];
            $vv_[0] = $TT1;
            $vv_[7] = $vv_[6];
            $vv_[6] = $this->ROTATE($vv_[5], 19);
            $vv_[5] = $vv_[4];
            $vv_[4] = $this->P0($TT2);
        }
        for ($i = 0; $i < 8; $i++)
        {
            $vv[$i] ^= $vv_[$i];
        }
        $this->v=$vv;
        $this->v_=$vv_;
        
        // Reset
        $this->xOff = 0;
        $this->arraycopy(SM3Digest::$X0, 0, $this->X, 0, sizeof(SM3Digest::$X0));
    }

    public function  ProcessWord($in_Renamed, $inOff)
    {
     
       $n = $this->LeftRotateLong($in_Renamed[$inOff] , 24);
       $n |= $this->LeftRotateLong(($in_Renamed[++$inOff] & 0xff) , 16);
       $n |= $this->LeftRotateLong(($in_Renamed[++$inOff] & 0xff) , 8);
       $n |= ($in_Renamed[++$inOff] & 0xff);

        $this->X[$this->xOff] = $n;
        
        if (++$this->xOff == 16)
        {
            $this->ProcessBlock();
        }
    }


    public function ProcessLength($bitLength)
    {
        if ($this->xOff > 14)
        {
            $this->ProcessBlock();
        }
        
        $this->X[14] =  ($this->RightRotateLong($bitLength, 32));
        $this->X[15] = ($bitLength &  0xffffffff);
    }
    
    public function  IntToBigEndian($n, &$bs, $off)
    {
        $bs[$off] = ($this->RightRotateLong($n, 24)) & 0xff;
        $bs[++$off] = ($this->RightRotateLong($n, 16))  & 0xff;
        $bs[++$off] =  ($this->RightRotateLong($n, 8))  & 0xff;
        $bs[++$off] = ($n)  & 0xff ;
    }

    public function DoFinal(&$out_Renamed, $outOff):int
    {
        $this->Finish();
        
        for ($i = 0; $i < 8; $i++)
        {
            $this->IntToBigEndian($this->v[$i], $out_Renamed, $outOff + $i * 4);
        }
        
        $this->Reset();
        
        return $this::DIGEST_LENGTH;
    }

    private function HandleSign($lValue)
    {
        $lValue = $lValue & 0xFFFFFFFF;
        if($lValue>=0x80000000)
        {
            $lValue=$lValue-(0xffffffff+1);
        }
        return  $lValue;
    }

    private function MyAdd($A,$B)
    {
        $lValue=$this->HandleSign($A) + $this->HandleSign($B);
        
    
        return  $this->HandleSign($lValue);
    }
    
	public function LeftRotateLong($lValue, $lBits )
	{
        $lBits = $lBits % 32;
        $lValue=$lValue<< $lBits;
        $lValue=$lValue & 0xffffffff;
        if($lValue>=0x80000000)
        {
            $lValue=$lValue-(0xffffffff+1);
        }
        return  $lValue;
	   /* $lngSign=0; $intI=0;
	    $mValue=0;
	    
	    $lBits = $lBits % 32;
	    $mValue = $lValue;
	    if($lBits == 0) return  $mValue;
	    
	    For ($intI = 1 ;$intI<= $lBits;$intI++)
	    {
	        $lngSign = $mValue & 0x40000000;
	        $mValue = ($mValue & 0x3FFFFFFF) * 2;
	     
	        if($lngSign & 0x40000000)
	           $mValue = $mValue | 0x80000000;
	    }
	    
	    return  $mValue;*/
	}
	
	
	private function RightRotateLong($lValue , $lBits) 
	{
	   $lngSign=0;$intI=0;
	   $mValue =0;
	   
	   $mValue = $lValue;
      // $lBits = $lBits % 32;

       if( $lBits == 0 ) 
       {
           return $mValue ;
       }

       if ($lValue >= 0)
       {
            if($lBits<0) $lBits= 32+ $lBits;
            $r = $lValue >> $lBits;
       }
       else
        {
            $t=~$lBits;
            if($t<0) $t= 32+ $t;
           // $t = $t % 32;
            $r= ($lValue >>  $lBits) + (2 << $t);
        }
        return $r;
      
	  /* For ($intI = 1 ;$intI<= $lBits;$intI++)
	   {
	      $lngSign = $mValue & 0x80000000;
	      $mValue = ($mValue & 0x7FFFFFFF) / 2;
	      if ($lngSign)
	         $mValue = $mValue | 0x40000000;
	   }
       return  $mValue;*/

       
        
      
	}
    
    private  function FFj($X, $Y, $Z, $j) :int
    {
		if($j>=0 && $j<=15) {
			return $this->FF_00_15($X, $Y, $Z);
		} else {
			return $this->FF_16_63($X, $Y, $Z);
		}
	}
    private function GGj($X, $Y, $Z, $j) :int
    {
		if($j>=0 && $j<=15) {
			return $this->GG_00_15($X, $Y, $Z);
		} else {
			return $this->GG_16_63($X, $Y, $Z);
		}
	}

    private function ROTATE($X, $n):int
    {
       // $r=($this->RightRotateLong($X, (32 - $n)));
        //$r1=$this->LeftRotateLong($X , $n);
       // $r2=($X << $n);
        return $this->LeftRotateLong($X , $n) | ($this->RightRotateLong($X, (32 - $n)));
    }
    
    private function P0($X):int
    {
        $a=$this->ROTATE(($X), 9);
        $b= $this->ROTATE(($X), 17);
        return (($X) ^ $this->ROTATE(($X), 9) ^ $this->ROTATE(($X), 17));
    }
    
    private function P1($X):int
    {
        return (($X) ^ $this->ROTATE(($X), 15) ^ $this->ROTATE(($X), 23));
    }
    
    private static function FF_00_15($X, $Y, $Z):int
    {
        return ($X ^ $Y ^ $Z);
    }
    
    private static function FF_16_63($X, $Y, $Z):int
    {
        return (($X & $Y) | ($X & $Z) | ($Y & $Z));
    }
    
    private static function GG_00_15($X, $Y, $Z):int
    {
        return ($X ^ $Y ^ $Z);
    }
    
    private static function GG_16_63($X, $Y, $Z):int
    {
        return (($X & $Y) | (~ $X & $Z));
    }
}