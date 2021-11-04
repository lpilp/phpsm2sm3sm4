# php sm2 sm3 sm4 国密算法整理
* php版本的国密sm2的签名算法，sm3的hash,  sm4的ecb加解密，要求PHP７，打开gmp支持
* 目前如果服务器配套的使用的是openssl 1.1.1x, 目前到1.1.1.l(L) ,sm3,sm4都可以直接用openssl_xxx系列函数直接实现，不必大量的代码,但不支持sm2的签名，sm2的加解密
* 有一个sm3, sm4的比较好的代码： https://github.com/lizhichao/sm  可以使用composer安装

### 使用(how to use)
* composer require lpilp/guomi 
* please make sure you upgrade to Composer 2+
* 测试是在php 7.4下做的
### SM2
* 该算法主体基于PHPECC算法架构，添加了sm2的椭圆参数算法， 
* 参考了 https://github.com/ToAnyWhere/phpsm2 童鞋的sm2验签算法，密钥生成算法
* 添加了签名算法， 支持sm2的16进制，base64公私钥的签名，验签算法
* 支持从文件中读取pem文件的签名，验签算法
* sm2的加密解密算法在openssl 1.1.1的版本下自带的函数中暂无sm2的公钥私钥的加密函数，得自己实现，建议使用C，C++的算法，打包成PHP扩展的方式
* 由于 openssl没有实现sm2withsm3算法，用系统函数无法实现签名及证书的自签名分发

### SM3
* 该算法直接使用 https://github.com/ToAnyWhere/phpsm2 童鞋的sm3, 未做修改
* 也可使用 openssl的函数, 详见openssl_tsm3.php

### SM4
* 该算法直接使用 https://github.com/yinfany/sm 童鞋的sm4算法
* 只实现了ecb算法，没有实现cbc算法
* 在openssl 1.1.1下可使用系统的函数，已支持sm4-cbc,sm4-cfb,sm4-ctr,sm4-ecb,sm4-ofb，  详见openssl_tsm4.php

### 总结
* 这里封装的测试函数已与相关的js, python, java都可以互签互认
* js: https://github.com/JuneAndGreen/sm-crypto
* python: https://github.com/duanhongyi/gmssl
* java: https://github.com/ZZMarquis/gmhelper
* openssl: 升到1.1.1以后，支持sm3,sm4的加解密，还不支持sm2的公私钥加解密，也不支持sm2的签名，得使用原生代码实现，签名中需要实现sm2withsm3, openssl1.1.1只实现了sm2whithsha256
