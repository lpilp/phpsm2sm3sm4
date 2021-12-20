<?php
require_once '../vendor/autoload.php';
use Rtgm\sm\RtSm4;

$key = "0123456789abcdef";
$iv = '1234567887654321';

$sm4 = new RtSm4($key);

$data = '我1爱你ILOVEYOU!!!';
$data = str_repeat('abc',7);
//sm4 的ecb 与cbc加密有补齐（16*n）l
// sm4->encrypt($data, $type = 'sm4', $iv = '', $format = 'hex')
//  openssl_encrypt ,和服务器openssl版本，PHP版本有关，有些服务器可能不支持sm4-* 相关的对称加密算法，


echo "==== test sm4 sm4-cbc============";
echo "\nphp sm4:     ".$hex = $sm4->encrypt($data,'sm4',$iv); //default is cbc
echo "\nphp decode:  ".$sm4->decrypt($hex,'sm4',$iv,'hex');

echo "\n==== test sm4-ecb============";
echo "\nphp sm4-ecb: ".$hex = $sm4->encrypt($data,'sm4-ecb');
echo "\nphp decode:  ".$sm4->decrypt($hex,'sm4-ecb','','hex');

echo "\n==== test sm4-ofb============";
echo "\nphp sm4-ofb: ".$hex = $sm4->encrypt($data,'sm4-ofb',$iv);
echo "\nphp decode:  ".$sm4->decrypt($hex,'sm4-ofb',$iv,'hex');

echo "\n==== test sm4-cfb============";
echo "\nphp sm4-cfb: ".$hex = $sm4->encrypt($data,'sm4-cfb',$iv);
echo "\nphp decode:  ".$sm4->decrypt($hex,'sm4-cfb',$iv,'hex');

echo "\n==== test sm4-ctr============";
echo "\nphp sm4-ctr: ".$hex = $sm4->encrypt($data,'sm4-ctr',$iv);
echo "\nphp decode:  ".$sm4->decrypt($hex,'sm4-ctr',$iv,'hex');