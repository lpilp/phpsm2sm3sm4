<?php
require_once '../vendor/autoload.php';
use Rtgm\sm\RtSm2;
$sm2 = new RtSm2();
$publicKey = '043d9d4cc71a285af936b36880fd4d6155c22957cd2c84ea313469065207fb951b9ef1db79d69af8886e91e833da1ebc6bfdde86e70f52923d6e042eaa147624c7'; // 公钥
$privateKey = 'a7763cd4fe7db2a2146fc09bf2d5e5a30e10c51b7e4bed00b3a26ec79ba78ff3'; // 私钥
$publicKey = '04eb4b8bbe15e3ad94b85196adc2c6f694436b3c1336170fd1daac8b10d2b8824ada9687c138fb81590e0f66ab9678161732ac0d7866b169e76b74483285f2bc04';
$privateKey = '0bc1c1d2771b64ba1922d72f8a451cd09a82176f74d975d484ec62c862176b75';
$document = str_repeat('abcdef',10);
// sm2的非对称加解密，不建议加密太长的字符串
echo "原始: $document";
$m2EncryptData = $sm2 ->doEncrypt($document, $publicKey);
echo ("\n加密后: ".$m2EncryptData);
$m2DecryptData = $sm2->doDecrypt($m2EncryptData,$privateKey);
echo ("\n解密后:".$m2DecryptData);
echo "\n------------------------------------------------------------------\n";
$document = "我爱你ILOVEYOU!";
echo "\n原始: $document";
$m2EncryptData = $sm2 ->doEncrypt($document, $publicKey);
echo ("\n加密后: ".$m2EncryptData);
$m2DecryptData = $sm2->doDecrypt($m2EncryptData,$privateKey);

echo ("\n解密后: ".$m2DecryptData);
echo "\n------------------以上是标准的 c1c3c2 串----------------------------\n";
// define("C1C3C2",1);
// define("C1C2C3",0);
// doEncrypt($document, $publicKey, $model = C1C3C2), 
// trim是如果加密后前面带着04就去掉
// doDecrypt($encryptData,$privateKey,$trim = true,$model = C1C3C2)
echo "\n------------------以下是使用 c1c2c3 串可对比上面生成------------------\n";


$document = "我爱你ILOVEYOU!";
echo "\n原始: $document";
$m2EncryptData = $sm2 ->doEncrypt($document, $publicKey,C1C2C3);
echo ("\n加密后: ".$m2EncryptData);
$m2DecryptData = $sm2->doDecrypt($m2EncryptData,$privateKey,1,C1C2C3);
echo ("\n解密后: ".$m2DecryptData);


