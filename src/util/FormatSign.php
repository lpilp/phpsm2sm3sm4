<?php
namespace Rtgm\util;

class FormatSign
{
    public function run($sign)
    {
        list($binR, $binS) = $this->_decode_rs(base64_decode($sign));
        $binR = $this->_trim_int_pad($binR);
        $binS = $this->_trim_int_pad($binS);
        $lenR = strlen($binR);
        $lenS = strlen($binS);
        $result = chr(48) . chr(2 + $lenR + 2 + $lenS) . chr(2) . chr($lenR) . $binR . chr(2) . chr($lenS) . $binS;
        return base64_encode($result);
    }
    /**
     * 
     *
     * @return string
     */
    /**
     * 招行的解签，没有用标准的asn1解析函数，当出现r,s的位数不足的时候就报错了，只支持rs, 31,32字节，当字节数少时强制补0吧
     *
     * @param string $sign
     * @return string
     */
    public function format_cmbc($sign){
        list($binR, $binS) = $this->_decode_rs(base64_decode($sign));
        while(strlen($binR)<32){
            $binR = chr(0).$binR;
        }
        while(strlen($binS)<32){
            $binS = chr(0).$binS;
        }
        $lenR = strlen($binR);
        $lenS = strlen($binS);
        $result = chr(48) . chr(2 + $lenR + 2 + $lenS) . chr(2) . chr($lenR) . $binR . chr(2) . chr($lenS) . $binS;
        return base64_encode($result);
    }
    private function _trim_int_pad($binStr)
    {
        // echo bin2hex($binStr)."\n";
        //trim 0
        while(ord($binStr[0])==0){
            $binStr = substr($binStr,1);
        }
        // add 0 if necessary
        if(ord($binStr[0])>127){
            $binStr =  chr(0).$binStr;
        }
        // echo bin2hex($binStr)."\n";
        return $binStr;

    }
    private function _decode_rs($binSign)
    {
        $rLen = ord($binSign[3]);
        $binR = substr($binSign, 4, $rLen);
        $binS = substr($binSign, (4 + $rLen + 2));
        // echo bin2hex($binR) . "\n----------\n" . bin2hex($binS) . "\n";
        return [$binR, $binS];
    }
}
