# php sm2 sm3 sm4 国密算法整理
* 本项目支持php版本的国密sm2的签名算法，非对称加解密算法（非对称加密刚上线，目前测试无问题，不能保证兼容其他语言，有问题可以提issues），sm3的hash，  sm4的对称加解密，要求PHP７，打开gmp支持
* 目前如果服务器配套的使用的是openssl 1.1.1x， 目前到1.1.1.l(L) ，sm3，sm4都可以直接用openssl_xxx系列函数直接实现，不必大量的代码，但不支持sm2的签名，sm2的加解密
* 有一个sm3， sm4的比较好的代码： https://github.com/lizhichao/sm  可以使用composer安装，只是这个的ecb， cbc没有做补齐

### 使用(how to use)
* composer require lpilp/guomi
* please make sure you upgrade to Composer 2+
* PHP >=7.2,打开gmp组件支持
* 如需要使用php5.6 请使用wzhih童鞋fork修改的 https://github.com/wzhih/guomi ; composer require wzhih/guomi
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

### SM3
* 该算法直接使用 https://github.com/ToAnyWhere/phpsm2 中sm2签名用到的匹配sm3， 未做修改
* 也可使用 openssl的函数， 详见openssl_tsm3.php

### SM4
* 该算法直接封装使用 https://github.com/lizhichao/sm  的sm4算法， 同时该项目支持 sm3,sm4 ，可以composer安装
* 由于sm4-ecb， sm4-cbc加密需要补齐，项目lizhichao/sm项目未做补齐操作，这里封装的时候，针对这两个算法做了补齐操作， 其他如sm4-ctr,sm4-cfb，sm4-ofb等，可以直接用
* 在openssl 1.1.1下可使用系统的函数，已支持sm4-cbc，sm4-cfb，sm4-ctr,sm4-ecb，sm4-ofb，  详见openssl_tsm4.php

### SM2各语言总结
* 这里封装的测试函数已与相关的js,python,java,go等都可以互签互认
* js: https://github.com/JuneAndGreen/sm-crypto 一个注意点就是： js的中文字符转成byte[]时，缺省的是unicode编码两字节，需要转成utf8的三字节编码，一个简单的方案 unescape(encodeURIComponent(str)) 然后再一个字节一个字节读就行了
* python: https://github.com/duanhongyi/gmssl  使用 pip install gmssl 安装就可， 注意的就是在python2下 ， string与byte[] 是可以隐式转换的，在python3下需要显式地将string转成byte[]
* java: https://github.com/ZZMarquis/gmhelper 注意下java中文的转码问题，getBytes("UTF-8")， 要加上编码类型， 因为 getBytes()函数的缺省编码是随操作系统的，如果是在中文版的windows中使用，缺省是GBK编码，就会出现中文的编码的问题，而造成签名无法通过
* openssl: 升到1.1.1以后，支持sm3，sm4的加解密，还不支持sm2的公私钥加解密，也不支持sm2的签名，得使用原生代码实现，签名中需要实现sm2withsm3， openssl1.1.1只实现了sm2whithsha256;还有一点很诡异，用yum/dnf安装的openssl只支持sm3， 如果是自己编译安装的就支持sm3，sm4
+ go: https://github.com/tjfoc/gmsm 一家做区块链的公司开源的项目，在go方面可以说是最早开源的了，sm2主要有两个问题：
  1. 暂无使用外部密码明文生成公私钥构造函数，比较简单可自行添加
  2. sm2的非对称加解密例子中使用EncryptAsn1这个函数，asn1是将 c1拆开为px，py两部分，asn1(px，py，c3，c2)的编码；在sm2类中也有直接Encrypt函数，生成的是'\x04'+c1+c3+c2 与其它语言生成的加密串在c1部分多加了一个\x04， 看互通代码的需求自行处理
  3. 签名与验签的方法可与本项目的PHP语言是互签互认的
  4. sm4的对称加解密cbc等需要IV向量的模式，将iv做为全局变量了，对于不同的串要使用不同的iv时，就会出现麻烦，请根据需要修改代码，将iv加到参数里
+ C#: 项目也比较少，基本是基于https://www.bouncycastle.org/ 的BC加密库(java也是基于该库)，该库1.8.4后版本支持sm2，sm3，sm4，考察搜索到的几个项目，https://github.com/hz281529512/SecretTest 完整性算比较好
  1. 项目提供sm2的签名，加解密都加入的项目，其他的项目里没有签名算法，sm2的加解密的返回值与上面的go项目一样返回的是asn1编码过的，如需要原始的请自行修改更新
  2. sm4的cbc的封装后把 key与iv弄成一个值了，如需要请自行修改更新
### SM2签名常见问题
  * 提供的私钥是base64的短串，一般直接 bin2hex(base64_decode(str)) 就是明文的密钥了
  * 文件格式的密钥一般有pkcs1与pkcs8两个格式，本项目只支持pkcs1格式的密钥，使用前请先进行相关的转换，一般 pkcs8是四行，pkcs1是三行，区别见 https://www.jianshu.com/p/a428e183e72e
  * 关于签名的字符串的问题，有些项目会将原始字符串哈稀后，再对哈稀值进行签名，有些对这哈稀值又进行了hex2bin操作后再签名，请双方按约定的标准确定最后签名的数据值，双方保持一致即可
  * 个别项目会碰到asn1的时候解析不了，如果是密钥解析不了，一般就是pkcs8的问题，如果只是数据解析不了的话看报错，调试下，目前碰到的是招行的一个问题，对int数据的asn1编码有前面补0与否不按标准来，全都补0
  * 签名的结果是asn1(r,s)，有极个别的项目签名出来的只是 r+s的字符串组合，验证签名的时候注意下。
  * ***招行*** 的签名回调系统在做asn1编码的int类型的时候，标准是最高位大于7D是要补0，反之则不要补0，而招行返回的统一32字节长度，对于一些不用补0的也强制补0，使用的第三方phpasn1会报错，可以屏蔽该错误，或是将返回的签名过滤下，项目提供了一个过滤的类 src/util/FormatSign->run()，使用测试见 test/tfsign.php
  * 同样的，在用本项目给 ***招行*** 签名的时候，就会出现上面同样的问题，编码int的时候，招行认为签名的r,s都是32字节，这时对于偶发的小于32字节的r,s，就得补0操作，src/util/FormatSign->format_cmbc()函数，目前只针对招行， 所以只是以补丁的方式，没有加入主程序
  * 以上是现行的招行的程序打的补丁，不能保证招行修复了这个bug, 假设招行修复了这个bug,使用了format_cmbc()，那个小概率的反而会错了，请根据实际情况使用，招行有提供相关的sdk,看是否更新就知道了


  

