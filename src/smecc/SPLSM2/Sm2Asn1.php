<?php
namespace Rtgm\smecc\SPLSM2;
/**
 * 
 * 针对签名，加解密只有一层 asn1的解析，不做其他类型的解析，
 */
define('MAXLEVEL', 2);  // 简单解析，只解析两层就够了
class Sm2Asn1
{
    const CLASS_UNIVERSAL        = 0;
    const CLASS_APPLICATION      = 1;
    const CLASS_CONTEXT_SPECIFIC = 2;
    const CLASS_PRIVATE          = 3;
    const TYPE_BOOLEAN           = 1;
    const TYPE_INTEGER           = 2;
    const TYPE_BIT_STRING        = 3;
    const TYPE_OCTET_STRING      = 4;
    const TYPE_NULL              = 5;
    const TYPE_OBJECT_IDENTIFIER = 6;
    const TYPE_OBJECT_DESCRIPTOR = 7;
    const TYPE_INSTANCE_OF       = 8; // EXTERNAL
    const TYPE_REAL              = 9;
    const TYPE_ENUMERATED        = 10;
    const TYPE_EMBEDDED          = 11;
    const TYPE_UTF8_STRING       = 12;
    const TYPE_RELATIVE_OID      = 13;
    const TYPE_SEQUENCE          = 16; // SEQUENCE OF
    const TYPE_SET               = 17; // SET OF
    const TYPE_NUMERIC_STRING    = 18;
    const TYPE_PRINTABLE_STRING  = 19;
    const TYPE_TELETEX_STRING    = 20; // T61String
    const TYPE_VIDEOTEX_STRING   = 21;
    const TYPE_IA5_STRING        = 22;
    const TYPE_UTC_TIME          = 23;
    const TYPE_GENERALIZED_TIME  = 24;
    const TYPE_GRAPHIC_STRING    = 25;
    const TYPE_VISIBLE_STRING    = 26; // ISO646String
    const TYPE_GENERAL_STRING    = 27;
    const TYPE_UNIVERSAL_STRING  = 28;
    const TYPE_CHARACTER_STRING  = 29;
    const TYPE_BMP_STRING        = 30;
    const TYPE_BIG_SEQUENCE      = 48;
    const TYPE_BIG_SET           = 49;
    const TYPE_CHOICE            = -1;
    const TYPE_ANY               = -2;
    const TYPE_ANY_RAW           = -3;
    const TYPE_ANY_SKIP          = -4;
    const TYPE_ANY_DER           = -5;

    /**
     * 解析简单的asn1
     *
     * @param string bin $data
     * @param integer $level
     * @return array <string>
     */
    public static function decode($data, $level = 0)
    {
        $result = array();
        $pos = 0;
        while (abs($pos) < strlen($data)) {
            $octets = 0;
            $length = 0;
            $tag = ord($data[$pos]);
            $pos++;
            if ($tag == 0) {
                $result[] = '00';
                continue;
            }
            $temp = ord($data[$pos]);
            // echo "temp = $temp\n";
            if ($temp == 128) {
                $length = 0;
            } else if ($temp > 128) {
                $octets = $temp & 127;
                $length = 0;
                // echo $octets."====\n";
                for ($i = 0; $i < $octets; $i++) {
                    $pos++;
                    $length <<= 8;
                    $length |= ord($data[$pos]);
                }
            } else {
                $length = $temp;
            }
            $content = substr($data, ++$pos, $length);
            $pos += $length;
            $res = self::_do_decode($content, $tag, $level);
            $result[] = $res;
        }
        return $result;
    }
    protected static function _do_decode($content, $tag, $level)
    {
        $level++;
        if ($level > MAXLEVEL) {
            return bin2hex($content);
        }
        switch ($tag) {
            case self::TYPE_BOOLEAN:
                return (bool)ord($content[0]);
            case self::TYPE_INTEGER:
                return bin2hex($content);
            case self::TYPE_OCTET_STRING:
                return bin2hex($content);
            case self::TYPE_BIT_STRING:
                $padByte = bin2hex($content[0]);
                $contentText = bin2hex(substr($content, 1));
                return array($padByte, $contentText);
            case self::TYPE_SEQUENCE:
            case self::TYPE_BIG_SEQUENCE:
            case self::TYPE_BIG_SET:
                return self::decode($content, $level);
            case self::TYPE_UTF8_STRING:
                return $content;
            case self::TYPE_BMP_STRING:
                return extension_loaded("iconv") ? iconv('UCS-2BE', 'UTF-8', $content) : $content;
            case self::TYPE_UNIVERSAL_STRING:
                return extension_loaded("iconv") ? iconv('UCS-4BE', 'UTF-8', $content) : $content;
            case self::TYPE_NULL:
                return null;
            default: // 其他复杂的不处理了，如有复杂需要，请使用其他的 asn1库处理
                return bin2hex($content);
        }
    }
    /**
     * 
     * @param string hex bigint $r
     * @param string hex bigint $s
     * @return string base64  一般约定签名用bas64, 加解密用hex
     */
    public static function rs_2_asn1($r, $s, $outFormat = 'base64')
    {
        $binR = self::_format_int_pad(hex2bin($r));
        $binS = self::_format_int_pad(hex2bin($s));
        $lenR = strlen($binR);
        $lenS = strlen($binS);
        $result = chr(48) . chr(2 + $lenR + 2 + $lenS) . chr(2) . chr($lenR) . $binR . chr(2) . chr($lenS) . $binS;
        if ($outFormat == 'base64') {
            return base64_encode($result);
        } else {
            return bin2hex($result);
        }
    }
    public static function asn1_2_rs($asn1Str,$inFormat='base64'){
        
        if($inFormat=='base64'){
            $bin = base64_decode($asn1Str);
        } else {
            $bin = hex2bin($asn1Str);
        }
        $data = self::decode($bin);
        $r = gmp_strval(gmp_init($data[0][0],16),16);
        $s = gmp_strval(gmp_init($data[0][1],16),16);
        return array($r, $s);
    }
    /**
     * 
     *
     * @param string hex bigint $c1x
     * @param string hex bigint $c1y
     * @param string hex bin $c3
     * @param string hex bin $c2
     * @return string hex 一般约定签名用bas64, 加解密用hex
     */
    public static function asn1_cccc($c1x, $c1y, $c3, $c2, $outFormat = 'hex')
    {
        $binc1x = self::_format_int_pad($c1x); // c1为椭圆点，得是bigint
        $binc1y = self::_format_int_pad($c1y);
        $binc3 = hex2bin($c3);
        $binc2 = hex2bin($c2);
        $c1xEncoded = chr(2) . strlen($binc1x) . $binc1x; // c1x  64 hex
        $c1yEncoded = chr(2) . strlen($binc1y) . $binc1y; // c1y 64 hex
        $c3Encoded = chr(4) . self::_encode_length(strlen($binc3)) . $binc3;  // c3的长度是固定的,可不用长宽度的方式求宽度，但有些就要c2 放前面呢，兼容下
        $c2Encoded = chr(4) . self::_encode_length(strlen($binc2)) . $binc2;
        $cccc = $c1xEncoded . $c1yEncoded . $c3Encoded . $c2Encoded;
        $lenAll = strlen($cccc);
        $result = chr(48) . self::_encode_length($lenAll) . $cccc;
        if ($outFormat == 'hex') {
            return bin2hex($result);
        } else {
            return base64_encode($result);
        }
    }

    /**
     * rs要固定长度，经测试会有小概率出现长度短的，要补0
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
     * 去掉多余的0, int 的话补上必发
     *
     * @param string $binStr
     * @return string
     */
    protected static function  _format_int_pad($binStr)
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

    protected static function _encode_length($length)
    {

        if ($length <= 0x7F) {
            return chr($length);
        }
        $temp = ltrim(pack('N', $length), chr(0));
        return pack('Ca*', 0x80 | strlen($temp), $temp);
    }
}
