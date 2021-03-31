<?php
include '../src/MySm4.php';
$key = "0123456789abcdef";

$sm4 = new MySm4();

$data = '我爱你ILOVEYOU!';

$enc = $sm4->encrypt($key, $data); //ecb
echo "encrypt: $enc\n";

$decdata = $sm4->decrypt($key, $enc);
echo "decrypt: $decdata\n";
