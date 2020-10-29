# php sm2 sm3 sm4 国密算法整理
php版本的国密sm2的签名算法，sm3的hash,  sm4的ecb加解密，要求PHP７，打开gmp支持
### SM2
* 该算法主体基于PHPECC算法架构，添加了sm2的椭圆参数算法， 
* 参考了 https://github.com/ToAnyWhere/phpsm2 童鞋的sm2验签算法，密钥生成算法
* 添加了签名算法， 支持sm2的16进制，base64公私钥的签名，验签算法
* 支持从文件中读取pem文件的签名，验签算法
* 未支持sm2的文本的加密解密算法，git上也没有相关的好的源，限于水平有限，待添加

### SM3
* 该算法直接使用 https://github.com/ToAnyWhere/phpsm2 童鞋的sm3, 未做修改

### SM4
* 该算法直接使用 https://github.com/yinfany/sm 童鞋的sm4算法
* 只实现了ecb算法，没有实现cbc算法

### 总结
* 这里封装的测试函数已与相关的js, python, java都可以互签互认
* js: https://github.com/JuneAndGreen/sm-crypto
* python: https://github.com/duanhongyi/gmssl
* java: https://github.com/ZZMarquis/gmhelper
