<?php
/**
 * openssl 1.1.1 已支持以下加密算法
 * aes-128-cbc,aes-128-cbc-hmac-sha1,aes-128-cbc-hmac-sha256,aes-128-ccm,aes-128-cfb,aes-128-cfb1,aes-128-cfb8,aes-128-ctr,aes-128-ecb,aes-128-gcm,aes-128-ocb,aes-128-ofb,aes-128-xts,aes-192-cbc,aes-192-ccm,aes-192-cfb,aes-192-cfb1,aes-192-cfb8,aes-192-ctr,aes-192-ecb,aes-192-gcm,aes-192-ocb,aes-192-ofb,aes-256-cbc,aes-256-cbc-hmac-sha1,aes-256-cbc-hmac-sha256,aes-256-ccm,aes-256-cfb,aes-256-cfb1,aes-256-cfb8,aes-256-ctr,aes-256-ecb,aes-256-gcm,aes-256-ocb,aes-256-ofb,aes-256-xts,aes128,aes128-wrap,aes192,aes192-wrap,aes256,aes256-wrap,aria-128-cbc,aria-128-ccm,aria-128-cfb,aria-128-cfb1,aria-128-cfb8,aria-128-ctr,aria-128-ecb,aria-128-gcm,aria-128-ofb,aria-192-cbc,aria-192-ccm,aria-192-cfb,aria-192-cfb1,aria-192-cfb8,aria-192-ctr,aria-192-ecb,aria-192-gcm,aria-192-ofb,aria-256-cbc,aria-256-ccm,aria-256-cfb,aria-256-cfb1,aria-256-cfb8,aria-256-ctr,aria-256-ecb,aria-256-gcm,aria-256-ofb,aria128,aria192,aria256,bf,bf-cbc,bf-cfb,bf-ecb,bf-ofb,blowfish,camellia-128-cbc,camellia-128-cfb,camellia-128-cfb1,camellia-128-cfb8,camellia-128-ctr,camellia-128-ecb,camellia-128-ofb,camellia-192-cbc,camellia-192-cfb,camellia-192-cfb1,camellia-192-cfb8,camellia-192-ctr,camellia-192-ecb,camellia-192-ofb,camellia-256-cbc,camellia-256-cfb,camellia-256-cfb1,camellia-256-cfb8,camellia-256-ctr,camellia-256-ecb,camellia-256-ofb,camellia128,camellia192,camellia256,cast,cast-cbc,cast5-cbc,cast5-cfb,cast5-ecb,cast5-ofb,chacha20,chacha20-poly1305,des,des-cbc,des-cfb,des-cfb1,des-cfb8,des-ecb,des-ede,des-ede-cbc,des-ede-cfb,des-ede-ecb,des-ede-ofb,des-ede3,des-ede3-cbc,des-ede3-cfb,des-ede3-cfb1,des-ede3-cfb8,des-ede3-ecb,des-ede3-ofb,des-ofb,des3,des3-wrap,desx,desx-cbc,id-aes128-CCM,id-aes128-GCM,id-aes128-wrap,id-aes128-wrap-pad,id-aes192-CCM,id-aes192-GCM,id-aes192-wrap,id-aes192-wrap-pad,id-aes256-CCM,id-aes256-GCM,id-aes256-wrap,id-aes256-wrap-pad,id-smime-alg-CMS3DESwrap,idea,idea-cbc,idea-cfb,idea-ecb,idea-ofb,rc2,rc2-128,rc2-40,rc2-40-cbc,rc2-64,rc2-64-cbc,rc2-cbc,rc2-cfb,rc2-ecb,rc2-ofb,rc4,rc4-40,rc4-hmac-md5,seed,seed-cbc,seed-cfb,seed-ecb,seed-ofb,
 * sm4,sm4-cbc,sm4-cfb,sm4-ctr,sm4-ecb,sm4-ofb
 * 其中支持sm4相关的各种算法模式,缺省使用的是cbc算法
 * 加密串短可以使用ecb，计算速度快，串长的话ecb的扩散性差，安全性不足，就使用带向量的其他算法如cbc
 */
// echo implode(',',openssl_get_cipher_methods(true));

// $ivlen = openssl_cipher_iv_length("sm4");
// $iv = openssl_random_pseudo_bytes($ivlen);
// sm4的iv长度是16字符
$key = "0123456789abcdef";
$iv = "1234567887654321"; 

$data = '我爱你ILOVEYOU!';

//  openssl_encrypt($plaintext, "sms4", $key, $options=0, $iv);
// options = 0 base64, 1( OPENSSL_RAW_DATA ):原始二进制 , 这里为了与tsm4对比，就使用hex方式
// sm4-ecb算法
$ciphertext =openssl_encrypt($data, "sm4-ecb", $key, $options=OPENSSL_RAW_DATA);
$enc = bin2hex($ciphertext);
echo "encrypt: $enc\n";
$original_plaintext = openssl_decrypt($ciphertext, "sm4-ecb", $key, $options=OPENSSL_RAW_DATA);
echo "encrypt: $original_plaintext\n";

// 缺省的sm4算法，使用的是sm4-cbc模式
$ciphertext =openssl_encrypt($data, "sm4", $key, $options=OPENSSL_RAW_DATA,$iv);
// $ciphertext =openssl_encrypt($data, "sm4-cbc", $key, $options=OPENSSL_RAW_DATA,$iv);  //这两个是一样的
$enc = bin2hex($ciphertext);
echo "encrypt: $enc\n";
$original_plaintext = openssl_decrypt($ciphertext, "sm4", $key, $options=OPENSSL_RAW_DATA,$iv);
echo "encrypt: $original_plaintext\n";

// sm4-cfb,sm4-ctr,sm4-ofb 的使用方法一样
$ciphertext =openssl_encrypt($data, "sm4-cfb", $key, $options=OPENSSL_RAW_DATA,$iv);
$enc = bin2hex($ciphertext);
echo "encrypt: $enc\n";
$original_plaintext = openssl_decrypt($ciphertext, "sm4-cfb", $key, $options=OPENSSL_RAW_DATA,$iv);
echo "encrypt: $original_plaintext\n";
