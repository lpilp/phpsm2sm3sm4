<?php
// openssl支持密码对的生成
// 但未支持解签名,证书颁发，openssl 1.1.1 版本目前 不支持 sm3withsm2, 因为sm3withsm2的算法，与普通的sha256 with ecdsa的椭圆算法不一样
// git上有相关的gmssl或是tassl的基于openssl开发会支持，需要安装然后替换掉当前的openssl, 并且将替换的openssl用源码方式编译到PHP中，较麻烦
// 操作请参考: http://gmssl.org/docs/php-api.html

// 生成密码对
$config = array(
    "private_key_type" => OPENSSL_KEYTYPE_EC,
    "curve_name" => "SM2"
);
$sslconf = "/usr/local/php/extras/openssl/openssl.cnf";
$config['config'] = $sslconf;
$prikey = openssl_pkey_new($config);
openssl_pkey_export($prikey, $prikeypem,null,$config);
echo $prikeypem."\n";
$pubkeypem = openssl_pkey_get_details($prikey)["key"];
echo $pubkeypem."\n";



