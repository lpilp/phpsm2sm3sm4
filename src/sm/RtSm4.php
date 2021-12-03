<?php

namespace Rtgm\sm;

/**
 * Sm4 from  https://github.com/lizhichao/sm
 */

use Exception;
use Rtgm\smecc\SM4\Sm4;

class RtSm4
{
    protected $sm4;
    protected $keyLen = 16;
    protected $ivLen = 16;

    function __construct($key)
    {
        $this->sm4 = new Sm4($key);
    }

    public function encrypt($data, $type = 'sm4', $iv = '', $formatOut = 'hex')
    {

        if ($type != 'sm4-ecb') {
            $this->check_iv($iv);
        }
        $ret = '';
        switch ($type) {
            case 'sm4':
            case 'sm4-cbc':
                $data = $this->mystr_pad($data, $this->keyLen); //需要补齐
                $ret = $this->sm4->enDataCbc($data, $iv);
                break;
            case 'sm4-ecb':
                $data = $this->mystr_pad($data, $this->keyLen); //需要补齐
                $ret = $this->sm4->enDataEcb($data);
                break;
            case 'sm4-ctr':
                $ret = $this->sm4->enDataCtr($data, $iv);
                break;
            case 'sm4-ofb':
                $ret = $this->sm4->enDataOfb($data, $iv);
                break;
            case 'sm4-cfb':
                $ret = $this->sm4->enDataCfb($data, $iv);
                break;
            default:
                throw new Exception('bad type');
        }
        if ($formatOut == 'hex') {
            return bin2hex($ret);
        } else if ($formatOut == 'base64') {
            return base64_encode($ret);
        }
        return $ret;
    }

    public function decrypt($data, $type = 'sm4', $iv = '', $formatInput = 'hex')
    {
        if ($type != 'sm4-ecb') {
            $this->check_iv($iv);
        }
        if ($formatInput == 'hex') {
            $data = hex2bin($data);
        } else if ($formatInput == 'base64') {
            $data = base64_decode($data);
        }
        //else  is raw
        switch ($type) {
            case 'sm4':
            case 'sm4-cbc':
                $ret = $this->sm4->deDataCbc($data, $iv);
                $ret =  $this->mystr_unpad($ret);
                break;
            case 'sm4-ecb':
                $ret = $this->sm4->deDataEcb($data);
                $ret =  $this->mystr_unpad($ret);
                break;
            case 'sm4-ctr':
                $ret = $this->sm4->deDataCtr($data, $iv);
                break;
            case 'sm4-ofb':
                $ret = $this->sm4->deDataOfb($data, $iv);
                break;
            case 'sm4-cfb':
                $ret = $this->sm4->deDataCfb($data, $iv);
                break;
            default:
                throw new Exception('bad type');
        }
        return $ret;
    }
    //加密前补齐
    protected function mystr_pad($data, $len = 16)
    {
        $n = $len - strlen($data) % $len;
        return $data . str_repeat(chr($n), $n);
    }
    // 解密后去掉补齐
    protected function mystr_unpad($data)
    {
        $n = ord(substr($data, -1));
        return substr($data, 0, -$n);
    }
    protected  function check_iv($iv)
    {
        if (strlen($iv) != $this->ivLen) {
            throw new Exception('bad iv');
        }
    }
}
