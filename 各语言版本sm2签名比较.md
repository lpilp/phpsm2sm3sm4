# 国密sm2在GitHub各语言高星项目简介
本文针对github上的国密sm2各语言的高星项目进行了使用测试，使用各语言进行互签互验，发现不少细节。
## 背景
* 2010年底，国家密码管理局公布了我国自主研制的“椭圆曲线公钥密码算法”（SM2算法）。要求“自2011年3月1日起，在建和拟建公钥密码基础设施电子认证系统和密钥管理系统应使用国密算法。自2011年7月1日起，投入运行并使用公钥密码的信息系统，应使用SM2算法。
* 一个好消息，目前支持sm2 patch已经merge到了Linux主线的5.10-rc1：<https://git.kernel.org/pub/scm/linux/kernel/git/torvalds/linux.git/log/?qt=author&q=Tianjia+Zhang>， 如不出意外会在5.10内核正式版本release中使用

## java版本
目前版本基本都是基于加密算法BC库( 官网: <http://www.bouncycastle.org> )进行封装开发的，BC库最新版本是1.66，网上有很多关于JAVA版本与其他语言无法互通的问题，在使用1.66版本时倒是没有碰到相关的问题，且BC库的作者近几个月内还有做更新
* 排名第一的是339星的 ZZMarquis/gmhelper 基于BC库封装开发。 作者在近期还有更新，处维护状态
* 排名第二的是180星的 PopezLotado/SM2Java，主体还是基于老版本BC库，主程序4年前更新，自已实现的SM2算法，新版的BC库已自带着SM2的实现方法
* 注意： java的`getBytes()`函数会根据操作系统的编码来进行操作，在中文操作时请使用`getBytes("UTF-8")`, 如不然在中文操作系统中使用就不与其他语言互通了
## python版本
* 有着156星的duanhongyi/gmssl，可以用pip直接安装，
* 使用时的公钥要去掉前面的04，签名后的数据只有r+s, 没有做asn1编码，需要做下简单兼容
## js版本
js版本的列表中前几名都年代比较久远，其中有一个49星的 yazhouZhang/js-sm2-sm3-sm4-sm9-zuc 收录了其他三个js的git在使用与维护状态上，重点测试了 JuneAndGreen/sm-crypto 的js算法，近期作者还有更新，修复了位数不够未补齐的bug，并与读者有互动
* JuneAndGreen/sm-crypto项目已上了npm可以直接安装使用，提供了多种参数自选的各种签名方式，总有一种合乎你的要求，与其他语言互通的是一般参数是 {der:true,hash: true}, 其他语言基本都是用der(asn1)编码过，且msg做了sm3 hash
* 注意： 关于汉字的字节化问题，js缺省的是unicode，在字节化的时候一个汉字变成是两个字符，需要进行处理， 该项目的sm3目录里的sm3算法没有进行处理，但sm2中使用的sm3算法是处理过的
```javascript
function parseUtf8StringToHex(input) {
  input = unescape(encodeURIComponent(input))
  const length = input.length
  // 转换到字数组
  const words = []
  for (let i = 0; i < length; i++) {
    words[i >>> 2] |= (input.charCodeAt(i) & 0xff) << (24 - (i % 4) * 8)
  }
  // 转换到16进制
  const hexChars = []
  for (let i = 0; i < length; i++) {
    const bite = (words[i >>> 2] >>> (24 - (i % 4) * 8)) & 0xff
    hexChars.push((bite >>> 4).toString(16))
    hexChars.push((bite & 0x0f).toString(16))
  }
  return hexChars.join('')
}
```

## PHP版本
PHP版本的sm2量很少，且星都不高，中间还夹杂着不知道是什么的项目
* 最多只有12星的项目 ToAnyWhere/phpsm2 是一个不完整的项目，在phpecc椭圆算法的项目上进行修改，为了工作需要添加的验证签名的算法，比较简陋，2年没更新了
* 后面只有3星 lpilp/phpsm2sm3sm4 是一个参考第一个项目的封装项目，在PHPECC上添加了sm2椭圆进行了整合，并实现了相关的签名算法，比较整体化的实现了sm2密钥生成，加签，验签等算法，同时也提供sm3, sm4的算法，该项目参考了多个项目进行了封装，在使用上载入相关的类，直接就可以使用，使用方便。近期刚上线的，处维护状态
* 目前来说比较好的整合方案还是应该在 <https://github.com/phpecc/phpecc> 上进行整合，但phpecc也好些年不更新了，  X509证书中没有为SM2公钥算法定义独立的OID标识， 整合的时候 lpilp/phpsm2sm3sm4 自定义了一个oid， 自定义了sm2的椭圆名，等标准出来了，需要进行相应的更新
