<?php
namespace Rtgm\util;

class SmSignFormatRS
{
    public static function asn1_to_rs($str, $format = 'base64')
    {
        if ($format == 'base64') {
            $str =  base64_decode($str);
        } else if ($format == 'hex') {
            $str =  hex2bin($str);
        }
        $arr = \FG\ASN1\ASNObject::fromBinary($str);
        // var_dump($arr[0]->getContent());die();
        $r = self::_padding_zero(self::_format_bigint($arr[0]->getContent()));
        $s = self::_padding_zero(self::_format_bigint($arr[1]->getContent()));
        return base64_encode($r . $s);
    }
    protected static function _format_bigint($data)
    {
        $hex = gmp_strval(gmp_init($data, 10), 16);
        return $hex;
    }
    /**
     * rs要固定长度，经测试会有1%的概率出现长度短的，要补0
     *
     * @param string $hex
     * @return string
     */
    protected static function _padding_zero($hex)
    {
        $len = 64; // r,s都是32字节
        $left = $len - strlen($hex);
        if ($left > 0) {
            $hex = str_repeat('0', $left) . $hex;
        }
        return hex2bin($hex);
    }

    /**
     * r+s ==> asn1(r+s)
     *
     * @param string $str
     * @param string $format
     * @return string
     */
    public static function rs_to_asn1($str, $format = 'base64')
    {
        if ($format == 'base64') {
            $str =  base64_decode($str);
        } else if ($format == 'hex') {
            $str =  hex2bin($str);
        }

        $binR = self::_trim_int_pad(substr($str, 0, 32));
        $binS = self::_trim_int_pad(substr($str, 32));
        $lenR = strlen($binR);
        $lenS = strlen($binS);
        $result = chr(48) . chr(2 + $lenR + 2 + $lenS) . chr(2) . chr($lenR) . $binR . chr(2) . chr($lenS) . $binS;
        return base64_encode($result);
    }
    /**
     * 去掉多余的0
     *
     * @param string $binStr
     * @return string
     */
    protected static function  _trim_int_pad($binStr)
    {
        //trim 0
        while (ord($binStr[0]) == 0) {
            $binStr = substr($binStr, 1);
        }
        // add 0 if necessary
        if (ord($binStr[0]) > 127) {
            $binStr =  chr(0) . $binStr;
        }
        // echo bin2hex($binStr)."\n";
        return $binStr;
    }
}
