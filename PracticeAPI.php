<?php
/**
 * 实践说明：
 * 每个方法都有说明，不懂的可以联系我哦~ 
 * wechat: 13201529090
 */
//引入WeiXinPay_V3.php
include './WeiXinPay_V3.php';
/**
 * 电商收付通 合单下单-JS支付
 * @desc 微信小程序支付 V3 版本
 */
public function FeePay_V3(){

    //查询订单信息
    $order_info = GetXXXXX();

    //商户配置文件
    $combine_appid          = 'your appid'; //合单发起方的appid
    $combine_mchid          = 'your mchid'; //合单发起方商户号
    $combine_out_trade_no   = 'order_no'; //合单支付总订单号 
    //子单信息 最多支持子单条数：50
    $sub_orders['mchid']    = 'mchid';
    $sub_orders['attach']           = '深圳分店'; //自定义数据
    $sub_orders['total_amount']     = intval($price * 100); //单位为分
    $sub_orders['currency']         = 'CNY';
    $sub_orders['out_trade_no']     = "20152".time();
    $sub_orders['sub_mchid']        = '二级商户商户号'; //二级商户商户号
    $sub_orders['detail']           = '包间预订'; //商品详情
    $sub_orders['profit_sharing']   = true;
    $sub_orders['description']      = '包间预订定金';
    $sub_orders['profit_sharing_settle'] = true;
    $sub_orders['subsidy_amount']   = intval($price * 100);//单位为分
    $openid                 = $openid; //使用合单appid获取的对应用户openid
    $time_start             = '2015-05-20T13:29:35+08:00';//订单生成时间
    $notify_url             = 'https://your domain/xxxx.php';    //回调通知地址
    $limit_pay              = 'no_debit';    //指定支付方式
    $wxPay  = new WeiXinPay_V3();
    $return =   $wxPay->closingorder($combine_appid,$combine_mchid,$combine_out_trade_no,$sub_orders,$openid,$time_start,$notify_url,$limit_pay);

    return json_decode($return);
}

/**
 * 通用接口：图片上传
 * @desc 微信小程序支付 V3 版本
 */
public function Upload(){
    $wxPay    = new WeiXinPay_V3();
    $filepath = getcwd().'/share.jpg';
    $return   = $wxPay->upload($filepath);

    return json_decode($return);
}

/**
 * 公共API：下载平台证书
 * @desc 电商收付通进阶接口
 */
public function Certificates(){
    $wxPay    = new WeiXinPay_V3();
    $return   = $wxPay->Certificates();

    return json_decode($return);
}
?>