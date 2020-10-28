<?php
include '../src/MySm3.php';
$sm3 = new MySm3();
$data = '我爱你ILOVEYOU!';
print_r($sm3->digest($data,1));