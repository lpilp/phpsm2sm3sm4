<?php
//
namespace Rtgm\smecc\SM3;

class GeneralDigest 
{
    private const  BYTE_LENGTH = 64;

    private $xBuf=array();
    private $xBufOff;

    private $byteCount;


    public function setGeneralDigest($t)
    {
        $this->arraycopy($t->xBuf, 0, $this->xBuf, 0, sizeof($t->xBuf));

        $this->xBufOff = $t->xBufOff;
        $this->byteCount = $t->byteCount;
    }
  
    public function __construct( )
    {
        $this->xBuf[0]=0; 
        $this->xBuf[1]=0;
        $this->xBuf[2]=0; $this->xBuf[3]=0;
    }

    
    public function Update($input)
    {
        $this->xBuf[$this->xBufOff++] = $input;

        if ($this->xBufOff == sizeof($this->xBuf))
        {
            $this->ProcessWord($this->xBuf, 0);
            $this->xBufOff = 0;
        }

       $this->byteCount++;
    }

    public function BlockUpdate(
        $input,
        $inOff,
        $length)
    {
        //
        // fill the current word
        //
        while (($this->xBufOff != 0) && ($length > 0))
        {
            $this->Update($input[$inOff]);
            $inOff++;
            $length--;
        }

        //
        // process whole words.
        //
        while ($length > sizeof($this->xBuf))
        {
            $this->ProcessWord($input, $inOff);

            $inOff += sizeof($this->xBuf);
            $length -= sizeof($this->xBuf);
           $this->byteCount += sizeof($this->xBuf);
        }

        //
        // load in the remainder.
        //
        while ($length > 0)
        {
            $this->Update($input[$inOff]);

            $inOff++;
            $length--;
        }
    }

    public function Finish()
    {
       // $bitLength = ($this->byteCount << 3);
        $bitLength = $this->LeftRotateLong($this->byteCount , 3);
        //
        // add the pad bytes.
        //
        $this->Update(128);

        while ($this->xBufOff != 0) $this->Update(0);
        $this->ProcessLength($bitLength);
        $this->ProcessBlock();
    }

    public function  Reset()
    {
        $this->byteCount = 0;
        $this->xBufOff = 0;
        $this->xBuf[0]=0; 
        $this->xBuf[1]=0;
        $this->xBuf[2]=0; $this->xBuf[3]=0;
    }

    public function GetByteLength():int
    {
        return $this::BYTE_LENGTH;
    }

  
}
