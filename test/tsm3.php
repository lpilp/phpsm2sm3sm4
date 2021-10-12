<?php
require_once '../vendor/autoload.php';
use Rtgm\sm\RtSm3;
$sm3 = new RtSm3();
$data = '我爱你ILOVEYOU!';
print_r($sm3->digest($data,1));