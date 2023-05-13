<?php
namespace Rtgm\util;

class KeyCompress {
    /**
     * sm2压缩公钥计算全公钥
     *
     * @param string $compressedKey
     * @return string
     */
    public static function decompressPublicKey($compressedKey)
    {
        // 获取压缩标志和X坐标
        $flag = substr($compressedKey, 0, 2);
        $x = substr($compressedKey, 2);
    
        // 将16进制字符串转换为大整数
        $p = gmp_init('FFFFFFFEFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF00000000FFFFFFFFFFFFFFFF', 16);
        $a = gmp_init('FFFFFFFEFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF00000000FFFFFFFFFFFFFFFC', 16);
        $b = gmp_init('28E9FA9E9D9F5E344D5A9E4BCF6509A7F39789F515AB8F92DDBCBD414D940E93', 16);
    
        // 计算Y坐标
        // y2 = x3 + ax + b
        $x = gmp_init($x, 16);
        $alpha = gmp_powm($x, 3, $p);
        $beta = gmp_add(gmp_mod(gmp_mul($a, $x), $p), $b);
        $y2 = gmp_mod(gmp_add($alpha, $beta), $p);
        $y = gmp_powm($y2, gmp_div_q(gmp_add($p, 1), 4), $p);
    
    
        // y2 的值开方有两个值，根据奇偶判定取哪一个
        if ($flag == "02") {
            // 如果压缩标志为“02”，则Y坐标为偶数
            if (gmp_strval(gmp_mod($y, 2)) == "0") {
                return "04" . gmp_strval($x, 16) . str_pad(gmp_strval($y, 16), 64, "0", STR_PAD_LEFT);
            } else {
                $y = gmp_sub($p, $y);
                return "04" . gmp_strval($x, 16) . str_pad(gmp_strval($y, 16), 64, "0", STR_PAD_LEFT);
            }
        } elseif ($flag == "03") {
            // 如果压缩标志为“03”，则Y坐标为奇数
            if (gmp_strval(gmp_mod($y, 2)) == "1") {
                return "04" . gmp_strval($x, 16) . str_pad(gmp_strval($y, 16), 64, "0", STR_PAD_LEFT);
            } else {
                $y = gmp_sub($p, $y);
                return "04" . gmp_strval($x, 16) . str_pad(gmp_strval($y, 16), 64, "0", STR_PAD_LEFT);
            }
        } else {
            return null;
        }
    }
    /**
     * 压缩公钥
     *
     * @param string $publicKey
     * @return string
     */
    public static function compressPublicKey($publicKey)
    {
        if (strlen($publicKey) == 130) {
            $publicKey = substr($publicKey, 2);
        }
        // 将16进制字符串转换为GMP数值
        $x = gmp_init(substr($publicKey, 0, 64), 16);
        $y = gmp_init(substr($publicKey, 64, 64), 16);
    
        // 判断Y坐标奇偶性
        if (gmp_strval(gmp_mod($y, 2)) == "0") {
            $flag = "02";
        } else {
            $flag = "03";
        }
    
        // 拼接压缩后的公钥
        $compressedPublicKey = $flag . str_pad(gmp_strval($x, 16), 64, "0", STR_PAD_LEFT);
    
        return $compressedPublicKey;
    } 
}