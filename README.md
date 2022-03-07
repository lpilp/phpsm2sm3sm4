# php sm2 sm3 sm4 国密算法整理
* 本项目支持php版本的国密sm2的签名算法，非对称加解密算法（非对称加密刚上线，目前测试无问题，不能保证兼容其他语言，有问题可以提issues），sm3的hash,  sm4的对称加解密，要求PHP７，打开gmp支持
* 目前如果服务器配套的使用的是openssl 1.1.1x, 目前到1.1.1.l(L) ,sm3,sm4都可以直接用openssl_xxx系列函数直接实现，不必大量的代码,但不支持sm2的签名，sm2的加解密
* 有一个sm3, sm4的比较好的代码： https://github.com/lizhichao/sm  可以使用composer安装，只是这个的ecb, cbc没有做补齐

### 使用(how to use)
* composer require lpilp/guomi
* please make sure you upgrade to Composer 2+
* PHP >=7.2
* 如需要使用php5.6 请使用wzhih童鞋fork修改的 https://github.com/wzhih/guomi ; composer require wzhih/guomi
### SM2
* 签名验签算法主体基于PHPECC算法架构，添加了sm2的椭圆参数， 
* 参考了 https://github.com/ToAnyWhere/phpsm2 童鞋的sm2验签算法，密钥生成算法
* 添加了签名算法， 支持sm2的16进制，base64公私钥的签名，验签算法
* 支持从文件中读取pem文件的签名，验签算法
* 添加了sm2的非对称加密的算法，但速度一般，有待优化，不能保证兼容所有语言进行加解密，目前测试了js, python的相互加解密
* sm2的加密解密算法在openssl 1.1.1的版本下自带的函数中暂无sm2的公钥私钥的加密函数，得自己实现，建议使用C，C++的算法，打包成PHP扩展的方式
* 由于 openssl没有实现sm2withsm3算法，用系统函数无法实现签名及证书的自签名分发

### SM3
* 该算法直接使用 https://github.com/ToAnyWhere/phpsm2 中sm2签名用到的匹配sm3, 未做修改
* 也可使用 openssl的函数, 详见openssl_tsm3.php

### SM4
* 该算法直接封装使用 https://github.com/lizhichao/sm  的sm4算法， 同时该项目支持 sm3,sm4 ,可以composer安装
* 由于sm4-ecb, sm4-cbc加密需要补齐，项目lizhichao/sm项目未做补齐操作，这里封装的时候，针对这两个算法做了补齐操作， 其他如sm4-ctr,sm4-cfb,sm4-ofb等，可以直接用
* 在openssl 1.1.1下可使用系统的函数，已支持sm4-cbc,sm4-cfb,sm4-ctr,sm4-ecb,sm4-ofb，  详见openssl_tsm4.php

### SM2总结
* 这里封装的测试函数已与相关的js, python, java都可以互签互认
* js: https://github.com/JuneAndGreen/sm-crypto 一个注意点就是： js的中文字符转成byte[]时，缺省的是unicode编码两字节，需要转成utf8的三字节编码，一个简单的方案 unescape(encodeURIComponent(str)) 然后再一个字节一个字节读就行了
* python: https://github.com/duanhongyi/gmssl  使用 pip install gmssl 安装就可， 注意的就是在python2下 ， string与byte[] 是可以隐式转换的，在python3下需要显式地将string转成byte[]
* java: https://github.com/ZZMarquis/gmhelper 注意下java中文的转码问题，getBytes("UTF-8"), 要加上编码类型， 因为 getBytes()函数的缺省编码是随操作系统的，如果是在中文版的windows中使用，缺省是GBK编码，就会出现中文的编码的问题，而造成签名无法通过
* openssl: 升到1.1.1以后，支持sm3,sm4的加解密，还不支持sm2的公私钥加解密，也不支持sm2的签名，得使用原生代码实现，签名中需要实现sm2withsm3, openssl1.1.1只实现了sm2whithsha256;还有一点很诡异，用yum/dnf安装的openssl只支持sm3, 如果是自己编译安装的就支持sm3,sm4
+ go: https://github.com/tjfoc/gmsm 一家做区块链的公司开源的项目，在go方面可以说是最早开源的了，sm2主要有两个问题：
  1. 暂无使用外部密码明文生成公私钥构造函数，比较简单可自行添加
  2. sm2的非对称加解密例子中使用EncryptAsn1这个函数，其实在sm2中有直接Encrypt函数， sm2.Encrypt(pub, data, rand, C1C3C2)， 与其它语言生成的加密串在c1部分多加了一个\x04, 在与其它语言进行互通时，去掉\x04,如是其他语言加密的，用该GO解密时前面加个\x04 就可以解出来了
  3. 签名与验签的方法可与本项目的PHP语言是互签互认的。 
  4. sm4的对称加解密cbc等需要IV向量的模式，将iv做为全局变量了，对于不同的串要使用不同的iv时，就会出现麻烦，可看它的issue里有相关的解决方案
