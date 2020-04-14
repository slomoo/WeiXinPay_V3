<?php
/**
 * 微信电商收付通API V3
 * 现存在问题:
 * 不同的商户，对应的微信支付平台证书是不一样的，平台证书会周期性更换。建议商户定时通过API下载新的证书，不要依赖人工更换证书。
 * 微信支付的平台证书序列号位于HTTP头Wechatpay-Serial。验证签名前，请商户先检查序列号是否跟商户当前所持有的微信支付平台证书的序列号一致。
 * 如果不一致，请重新获取证书。否则，签名的私钥和证书不匹配，将无法成功验证签名。
 * 该问题会在后续集成哦~
 */
class WeiXinPay_V3 {
    function __construct() {
        $this->merchant_id      =  your merchant_id;
        $this->serial_no        = 'your serial_no';
    }

    /**
     * [closingorder 合单下单-JS支付]
     * @param  [type] $combine_appid        [合单发起方的appid]
     * @param  [type] $openid               [使用合单appid获取的对应用户openid]
     * @param  [type] $combine_mchid        [合单发起方商户号]
     * @param  [type] $combine_out_trade_no [合单支付总订单号]
     * @param  [type] $mchid                [子单发起方商户号]
     * @param  [type] $attach               [附加数据]
     * @param  [type] $time_start           [订单生成时间]
     * @param  [type] $notify_url           [回调通知地址]
     * @param  [type] $limit_pay            [指定支付方式 目前为：no_debit]
     * @return [type]                       [返回参数预支付交易会话标识：prepay_id。示例值：wx201410272009395522657a690389285100]
     */
    private function closingorder($combine_appid,$openid,$combine_mchid,$combine_out_trade_no,$mchid,$attach,$time_start,$notify_url,$limit_pay) {
        $url = 'https://api.mch.weixin.qq.com/v3/combine-transactions/jsapi';
        $parameters = array(
            //合单商户appid
            'combine_appid' => $combine_appid,  //合单发起方的appid  示例值：wxd678efh567hg6787

            //合单发起方商户号
            'combine_mchid' => $combine_mchid, //合单发起方商户号。示例值：1900000109

            //合单商户订单号
            'combine_out_trade_no' => $combine_out_trade_no, //合单支付总订单号，要求32个字符内，只能是数字、大小写字母_-|*@ ，且在同一个商户号下唯一。示例值：P20150806125346

            //子单信息 最多支持子单条数：50  仅支持json格式
            'sub_orders' => array(
                //子单商户号
                'mchid'=>$mchid, //子单发起方商户号，必须与发起方appid有绑定关系。 示例值：1900000109
                //附加信息
                'attach'=>$attach //附加数据，在查询API和支付通知中原样返回，可作为自定义参数使用。  示例值：深圳分店
            ),

            //支付者 支付者信息
            'combine_payer_info' => array(
                //子单商户号
                'openid'=>  $openid //使用合单appid获取的对应用户openid。是用户在商户appid下的唯一标识。 示例值：oUpF8uMuAJO_M2pxb1Q9zNjWeS6o
            ),

            //交易起始时间
            'time_start'    => $time_start,//订单生成时间，遵循rfc3339标准格式，格式为YYYY-MM-DDTHH:mm:ss+TIMEZONE，YYYY-MM-DD表示年月日，T出现在字符串中，表示time元素的开头，HH:mm:ss表示时分秒，TIMEZONE表示时区（+08:00表示东八区时间，领先UTC 8小时，即北京时间）。例如：2015-05-20T13:29:35+08:00表示，北京时间2015年5月20日 13点29分35秒。示例值：2019-12-31T15:59:60+08:00

            //交易结束时间
            //'time_expire'    => $this->time_expire,//订单失效时间，遵循rfc3339标准格式，格式为YYYY-MM-DDTHH:mm:ss+TIMEZONE，YYYY-MM-DD表示年月日，T出现在字符串中，表示time元素的开头，HH:mm:ss表示时分秒，TIMEZONE表示时区（+08:00表示东八区时间，领先UTC 8小时，即北京时间）。例如：2015-05-20T13:29:35+08:00表示，北京时间2015年5月20日 13点29分35秒。示例值：2019-12-31T15:59:60+08:00

            //通知地址
            'notify_url'    => $notify_url, //接收微信支付异步通知回调地址，通知url必须为直接可访问的URL，不能携带参数。格式: URL 示例值：https://yourapp.com/notify

            //指定支付方式
            'limit_pay'     => $limit_pay,//指定支付方式 示例值：no_debit
        );
        //发起请求的商户（包括直连商户、服务商或渠道商）的商户号mchid
        $merchant_id    =   $this->merchant_id;
        //证书序列号
        $serial_no      =   $this->serial_no;
        //获取私钥
        $mch_private_key=$this->getPrivateKey(getcwd().'/cert/apiclient_key.pem');       //商户私钥
        $date = time();
        $nonce = $this->createNoncestr();
        //post 请求  
        $sign = $this->sign($url,'POST',$date,$nonce,json_encode($parameters),$mch_private_key,$merchant_id,$serial_no);//$http_method要大写
        $header[] = 'User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.108 Safari/537.36';
        $header[] = 'Accept:application/json';
        $header[] = 'Content-Type:application/json';
        $header[] = 'Authorization:WECHATPAY2-SHA256-RSA2048 '.$sign;
        $r = $this->_requestPost($url,$parameters,$header);
        return $r;
    }

    /**
     * [upload 商户收付通图片上传]
     * @param  [type] $imgpath        [文件物理地址]
     * @return [type] [返回参数媒体文件标识 media_id 示例值：6uqyGjGrCf2GtyXP8bxrbuH9-aAoTjH-rKeSl3Lf4_So6kdkQu4w8BYVP3bzLtvR38lxt4PjtCDXsQpzqge_hQEovHzOhsLleGFQVRF-U_0]
     */
    public function upload($imgpath){
        $url = 'https://api.mch.weixin.qq.com/v3/merchant/media/upload';
        $filename = $imgpath;
        //发起请求的商户（包括直连商户、服务商或渠道商）的商户号mchid
        $merchant_id    =   $this->merchant_id;
        //证书序列号
        $serial_no      =   $this->serial_no;
        //获取私钥
        $mch_private_key=$this->getPrivateKey(getcwd().'/cert/apiclient_key.pem');       //商户私钥
        $fi = new \finfo(FILEINFO_MIME_TYPE);
        $mime_type = $fi->file($filename);
        $data['filename'] = '1.png';
        $meta['filename'] = '1.png';
        $meta['sha256'] = hash_file('sha256',$filename);
        $boundary = uniqid(); //分割符号
        $date = time();
        $nonce = $this->createNoncestr();
        $sign = $this->sign($url,'POST',$date,$nonce,json_encode($meta),$mch_private_key,$merchant_id,$serial_no);//$http_method要大写
        $header[] = 'User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.108 Safari/537.36';
        $header[] = 'Accept:application/json';
        $header[] = 'Authorization:WECHATPAY2-SHA256-RSA2048 '.$sign;
        $header[] = 'Content-Type:multipart/form-data;boundary='.$boundary;

        $boundaryStr = "--{$boundary}\r\n";
        $out = $boundaryStr;
        $out .= 'Content-Disposition: form-data; name="meta"'."\r\n";
        $out .= 'Content-Type: application/json'."\r\n";
        $out .= "\r\n";
        $out .= json_encode($meta)."\r\n";
        $out .=  $boundaryStr;
        $out .= 'Content-Disposition: form-data; name="file"; filename="'.$data['filename'].'"'."\r\n";
        $out .= 'Content-Type: '.$mime_type.';'."\r\n";
        $out .= "\r\n";
        $out .= file_get_contents($filename)."\r\n";
        $out .= "--{$boundary}--\r\n";
        $r = $this->_requestPost($url,$out,$header);
        return $r;
    }

    /**
     * [_requestPost CURL请求]
     * @param  [type]  $url     [请求目标]
     * @param  [type]  $data    [请求参数]
     * @param  array   $header  [头部参数]
     * @param  string  $referer [referer]
     * @param  integer $timeout [超时时间：单位秒]
     * @return [type]           [结果返回]
     */
    public function _requestPost($url, $data , $header = array(), $referer = '', $timeout = 30){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //避免https 的ssl验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        // 模拟来源
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        $response = curl_exec($ch);
        if ($error = curl_error($ch)) {
            die($error);
        }
        curl_close($ch);
        return $response;
    }

    /**
     * [sign 签名]
     * @param  [type] $url             [请求目标]
     * @param  [type] $http_method     [请求方式 GET POST PUT]
     * @param  [type] $timestamp       [时间戳]
     * @param  [type] $nonce           [随机串]
     * @param  [type] $body            [报文 GET请求时可以为空]
     * @param  [type] $mch_private_key [api 密钥]
     * @param  [type] $merchant_id     [发起请求的商户（包括直连商户、服务商或渠道商）的商户号mchid]
     * @param  [type] $serial_no       [证书序列号
     * @return [type]                  [返回为签名串]
     */
    private function sign($url,$http_method,$timestamp,$nonce,$body,$mch_private_key,$merchant_id,$serial_no){

        if (!in_array('sha256WithRSAEncryption', \openssl_get_md_methods(true))) {
            throw new BadRequestException("当前PHP环境不支持SHA256withRSA");
        }

        $url_parts = parse_url($url);

        $canonical_url = ($url_parts['path'] . (!empty($url_parts['query']) ? "?${url_parts['query']}" : ""));
        $message =
            $http_method."\n".
            $canonical_url."\n".
            $timestamp."\n".
            $nonce."\n".
            $body."\n";
        openssl_sign($message, $raw_sign, $mch_private_key, 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);
        $token = sprintf('mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $merchant_id, $nonce, $timestamp, $serial_no, $sign);
        return $token;
    }

    //获取私钥
    private static function getPrivateKey($filepath) {
        return openssl_get_privatekey(file_get_contents($filepath));
    }

    //作用：产生随机字符串，不长于32位
    private static function createNoncestr($length = 32) {
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) { 
                $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        } 
        return $str; 
    }
}