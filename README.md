# WeiXinPay_V3
一个PHP文件搞定微信电商收付通。

微信电商收付通也出来一段时间了,但是网上相关资源甚少,官网说明文档看似简洁明了,实则存在很多细节上的不足,前段时间经过各种踩坑,才摸清楚门道,So,封装了一个PHP类,希望额能帮助各位PHPer。眼下就打算在这个文件内封装微信支付V3版本的相关接口,但是每个接口都会做相关说明。
如果感觉不合理,可以把每个接口单独提出来哦~

# 官方地址
移步：https://pay.weixin.qq.com/wiki/doc/apiv3/wxpay/pages/index.shtml

# 环境依赖

PHP5.0以上，且需要开启CURL服务、SSL服务。

#准备工作
移步：https://pay.weixin.qq.com/wiki/doc/apiv3/wxpay/pages/ico-guide/chapter1_1.shtml

# 注意事项

1.需要掉哪个微信的哪个接口,只需要把该文件引入到项目下面,并实例化,掉用对应的方法即可。

2.出现签名错误,请一定要检查、检查、检查（微信论坛基本上都是因为参数不对引起签名过不了）各种参数是否正确。或者利用微信官方的验签工具尝试,移步：https://pan.baidu.com/share/init?surl=ixOAnYyZVW13dFr0jWVpvw 提取码：wujv

3.文件最好放在公共类库下面(便于授权)。

# 关于本文件
该文件会在后续不断假如微信支付V3相关接口,大家使用时尽量多注意差异化。


# 若对您有帮助，可以赞助并支持下作者哦，谢谢！

<p align="center">
    <img src="https://slomoo.oss-cn-beijing.aliyuncs.com/blog/wechatpay.jpg" width="500px">
    <p align="center">联系邮箱：slomoo@aliyun.com</p>
</p>
