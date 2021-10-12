<?php
// 说明： PHP自带的函数好像不支持椭圆相关的非对称加密,只支持rsa

$prifile = 'data/sm2.pem';
$pubfile = 'data/sm2pub.pem';

$prifile = 'data/rsa.pem';
$pubfile = 'data/rsapub.pem';

$data = "I love you!";

$priKey = openssl_pkey_get_private(file_get_contents($prifile));
$pubKey = openssl_pkey_get_public(file_get_contents($pubfile));
// var_dump($priKey);die();
// print_r(openssl_get_md_methods());
openssl_sign($data,$sign,$priKey,'sha256');

openssl_sign($data,$sign2,$priKey,'sha256WithRSAEncryption');

echo bin2hex($sign) ."\n";
echo bin2hex($sign2) ."\n";



exit();

echo "----------以下rsa的加解密----------------\n";
$res = openssl_private_encrypt($data, $encrypted, $priKey);
echo bin2hex($encrypted)."\n--------------------------\n";

$res = openssl_public_decrypt($encrypted,$decryptd,$pubKey);

echo $decryptd."\n--------------------------\n";
if($decryptd == $data){
    echo "good !!!";
} else {
    echo "bad !!!";
}


