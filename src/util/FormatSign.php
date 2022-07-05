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

    private function _trim_int_pad($binStr)
    {
        if (ord($binStr[0]) > 0) {
            return $binStr;
        } else {
            if (ord($binStr[1]) <= 127) {
                return substr($binStr, 1);
            }
            return $binStr;
        }
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
