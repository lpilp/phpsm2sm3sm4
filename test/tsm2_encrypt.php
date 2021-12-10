<?php
require_once '../vendor/autoload.php';
use Rtgm\sm\RtSm2;
$sm2 = new RtSm2();
$publicKey = '043d9d4cc71a285af936b36880fd4d6155c22957cd2c84ea313469065207fb951b9ef1db79d69af8886e91e833da1ebc6bfdde86e70f52923d6e042eaa147624c7'; // 公钥
$privateKey = 'a7763cd4fe7db2a2146fc09bf2d5e5a30e10c51b7e4bed00b3a26ec79ba78ff3'; // 私钥

$document = str_repeat('abcdef',70);
// sm2的非对称加解密，不建议加密太长的字符串
$m2EncryptData = $sm2 ->doEncrypt($document, $publicKey);
echo ("加密后: ".$m2EncryptData);
$m2DecryptData = $sm2->doDecrypt($m2EncryptData,$privateKey);
echo "\n===========\n";
echo ("解密后：".$m2DecryptData);
$document = "我爱你ILOVEYOU!";
echo "\n===========\n";
$m2EncryptData = $sm2 ->doEncrypt($document, $publicKey);
echo ("加密后: ".$m2EncryptData);
$m2DecryptData = $sm2->doDecrypt($m2EncryptData,$privateKey);
echo "\n===========\n";
echo ("解密后：".$m2DecryptData);