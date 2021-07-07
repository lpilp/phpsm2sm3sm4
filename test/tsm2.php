<?php
include "../src/MySm2.php";
$publicKey = '043d9d4cc71a285af936b36880fd4d6155c22957cd2c84ea313469065207fb951b9ef1db79d69af8886e91e833da1ebc6bfdde86e70f52923d6e042eaa147624c7'; // 公钥
$privateKey = 'a7763cd4fe7db2a2146fc09bf2d5e5a30e10c51b7e4bed00b3a26ec79ba78ff3'; // 私钥
$publicfile = "./data/sm2pub.pem";
$privatefile = "./data/sm2.pem";
$userId = '1234567812345678';
$document = "我爱你ILOVEYOU!";

define('GK',1);
define('SIGN',0);
define('SIGNPEM',1);
//返回的签名16进制还是base64, 目前可选hex,与base64两种
$sm2 = new MySm2('base64');

if(GK){
    print_r($sm2->generatekey());
    print_r($sm2->generatePemkey());
}
if(SIGN){
    $sign = $sm2->doSign( $document, $privateKey, $userId);
    print_r($sign."\n");
    var_dump($sm2->verifySign( $document, $sign, $publicKey, $userId ));
}

if(SIGNPEM){
    $sign = $sm2->doSignOutKey( $document, $privatefile, $userId);
    print_r($sign."\n");
    var_dump($sm2->verifySignOutKey( $document, $sign, $publicfile, $userId ));
}
