# php sm2 sm3 sm4 国密算法整理
* 本项目支持php版本的国密sm2的签名算法，非对称加解密算法，sm3的hash，  sm4的对称加解密，要求PHP７，打开gmp支持
* 目前如果服务器配套的使用的是openssl 1.1.1x， 目前到1.1.1.l(w) ，sm3，sm4都可以直接用openssl_xxx系列函数直接实现，不必大量的代码，不支持sm2的签名，sm2的加解密

### 使用
* composer require lpilp/guomi
* please make sure you upgrade to Composer 2+
* PHP >=7.2,打开gmp组件支持
* 如需要使用php5.6 请使用wzhih童鞋fork修改的 https://github.com/wzhih/guomi ; composer require wzhih/guomi 或是使用该项目的简化版本 https://github.com/lpilp/simplesm2
### SM2
* 签名验签算法主体基于PHPECC算法架构，添加了sm2的椭圆参数，
* 参考了 https://github.com/ToAnyWhere/phpsm2 童鞋的sm2验签算法，密钥生成算法
* 添加了签名算法， 支持sm2的16进制，base64公私钥的签名，验签算法
* 支持从文件中读取pem文件的签名，验签算法
* 由于 openssl没有实现sm2withsm3算法，用系统函数无法实现签名及证书的自签名分发

### SM2非对称加密
* 添加了sm2的非对称加密的算法，但速度一般，有待优化，不能保证兼容所有语言进行加解密，目前测试了js， python的相互加解密
* sm2的加密解密算法在openssl 1.1.1的版本下自带的函数中暂无sm2的公钥私钥的加密函数，得自己实现，建议使用C，C++的算法，打包成PHP扩展的方式
* SM2的非对称加密缺省的是c1c3c2， 请使用的时候注意下，对方返回的是c1c3c2还是c1c2c3，进行相应的修改更新,还有一点就是本项目中c1前面没有04， 视对接方的需求，看是否添加\x04, v1.0.6版已对c1c3c2还是c1c2c3做了兼容，缺省是c1c3c2,添加相应的modetype后可以兼容两种模式，使用方法见  test/tsm2_encrypt.php
* 如对方sm2非对称加密生成的不是c1c3c2 而是 asn1(c1x,c1y,c3,c2), 目前本项目不支持这种样式的，请先asn1解开后，拼接成 C1C3C2的形式后再调用解密函数，否则会报椭圆不匹配错误, 请自行处理
### 关于数据格式
* sm2的缺省返回是asn1(r,s)的base64字符串
* sm2的非对称加密返回的是 c1c3c2的hex字符串
* sm3缺省hex的字符串
* sm4缺省也是hex的字符串
* 在于其他语言互通的时候请自行统一格式，以免因为格式的问题而造成运算不成功
### SM3
* 该算法直接使用 https://github.com/ToAnyWhere/phpsm2 中sm2签名用到的匹配sm3， 未做修改
* 也可使用 openssl的函数， 详见openssl_tsm3.php
* hmac-sm3,这个算法与hmac-sha256在hmac的算法是一样的，只是hash的算法不一样，一个是sm3,一个sha256, 没有什么特殊的注意的地方 
### SM4
* 该算法直接封装使用 https://github.com/lizhichao/sm  的sm4算法， 同时该项目支持 sm3,sm4 ，可以composer安装
* 由于sm4-ecb， sm4-cbc加密需要补齐，项目lizhichao/sm项目未做补齐操作，这里封装的时候，针对这两个算法做了补齐操作， 其他如sm4-ctr,sm4-cfb，sm4-ofb等，可以直接用
* 在openssl 1.1.1下可使用系统的函数，已支持sm4-cbc，sm4-cfb，sm4-ctr,sm4-ecb，sm4-ofb，  详见openssl_tsm4.php ，有一点很诡异，用yum/dnf安装的openssl只支持sm3， 如果是自己编译安装的就支持sm3，sm4

### SM2各语言总结
* 这里封装的测试函数已与相关的js,python,java,go等都可以互签互认
* js: https://github.com/JuneAndGreen/sm-crypto 一个注意点就是： js的中文字符转成byte[]时，缺省的是unicode编码两字节，需要转成utf8的三字节编码，一个简单的方案 unescape(encodeURIComponent(str)) 然后再一个字节一个字节读就行了
* python: https://github.com/duanhongyi/gmssl  使用 pip install gmssl 安装就可
* java: https://github.com/ZZMarquis/gmhelper 注意下java中文的转码问题，getBytes("UTF-8")， 要加上编码类型， 因为 getBytes()函数的缺省编码是随操作系统的，如果是在中文版的windows中使用，缺省是GBK编码，就会出现中文的编码的问题，而造成签名无法通过
* openssl: 升到1.1.1以后，支持sm3，sm4的加解密，还不支持sm2的公私钥加解密，也不支持sm2的签名
+ go: https://github.com/tjfoc/gmsm 一家做区块链的公司开源的项目，在go方面可以说是最早开源的了，https://github.com/deatil/go-cryptobin 这个是个go的宝藏项目，有各种的加解密，签名
+ C#: 项目也比较少，基本是基于https://www.bouncycastle.org/ 的BC加密库(java也是基于该库)，该库1.8.4后版本支持sm2，sm3，sm4，考察搜索到的几个项目，https://github.com/hz281529512/SecretTest 完整性算比较好
+ C: https://github.com/guanzhi/GmSSL 北大计算机的开源项目，fork多,star也多。
+ php-openssl:  php7 好像支持了sm3, 在openssl1.1.1以上，可用编译的方式加入sm3,sm4的支持。 xampp套件下的php7以上的版本支持sm3, sm4的openssl_系列函数， openssl_get_md_methods() 查看是否支持sm3, openssl_get_cipher_methods() 查看是否支持sm4
### SM2签名常见问题
  * 提供的私钥是base64的短串，一般直接 bin2hex(base64_decode(str)) 就是明文的密钥了
  * 文件格式的密钥一般有pkcs1与pkcs8两个格式，本项目只支持pkcs1格式的密钥，使用前请先进行相关的转换，一般 pkcs8是四行，pkcs1是三行，区别见 https://www.jianshu.com/p/a428e183e72e
  * 关于签名的字符串的问题，有些项目会将原始字符串哈稀后，再对哈稀值进行签名，有些对这哈稀值又进行了hex2bin操作后再签名，请双方按约定的标准确定最后签名的数据值，双方保持一致即可
  * 签名的结果是asn1(r,s)，个别的项目签名出来的只是 r+s的字符串组合，验证签名的时候注意下。 base64的签名如果以MEU开头的，这个是asn1的，解开后是64字节是r + s 的  在src/util/SmSignFormatRS.php 有相关的转换函数，请按需使用
### 特别注意
  * sm2的构造函数中缺省是固定了中间椭圆，目前发现个别的接入方（目前发现是招行金融平台）将这个中间椭圆私钥随机算法给加黑了， 请使用的时候 $randFixed 设为false 以及重新生成一个中间椭圆的密钥对替换原有程序的数据
```
function __construct($formatSign='hex', $randFixed = true) {
    // 注意： 这个randFixed尽量取false, 如需要固定，请重新生成$foreignkey密码对
    $this->adapter = RtEccFactory::getAdapter();
    $this->generator = RtEccFactory::getSmCurves()->generatorSm2();
    if(in_array($formatSign,$this->arrFormat)){
        $this->formatSign = $formatSign;
    } else {
        $this->formatSign = 'hex';
    }
    if(!$randFixed){
        $this->useDerandomizedSignatures = false;
        $this->useDerandomizedEncrypt = false;
    }
}
```


