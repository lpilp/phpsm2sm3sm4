<?php
namespace Rtgm\sm;

use Rtgm\smecc\SM3\SM3Digest;

class RtSm3 {
    public function digest($msg,$format=1){
        $md = array();
        $sm3 = new SM3Digest();
        $msgArray = unpack("C*",$msg);
        $sm3->BlockUpdate($msgArray, 1, sizeof($msgArray));
        $sm3->DoFinal($md, 0);
        if($format){
            return $this->_dec2hex($md);
        } else {
            return $md;
        }
    }

    protected function _dec2hex($md){
        $res = array();
        for($i=0; $i<count($md);$i++){
            $res[$i] = sprintf("%02x",$md[$i]);
        }
        return implode("",$res);
    }
}
