<?php
require_once '../vendor/autoload.php';
use Rtgm\sm\RtSm4;

$key = "0123456789abcdef";

$sm4 = new RtSm4();

$data = '我爱你ILOVEYOU!';

$enc = $sm4->encrypt($key, $data); //ecb
echo "encrypt: $enc\n";

$decdata = $sm4->decrypt($key, $enc);
echo "decrypt: $decdata\n";
