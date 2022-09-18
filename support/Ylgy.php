<?php

namespace support;

class Ylgy
{
    protected $header = [
        "Accept:*/*",
        "Accept-Encoding:gzip,compress,br,deflate",
        "Connection:keep-alive",
        "content-type:application/json",
        "Referer:https://servicewechat.com/wx141bfb9b73c970a9/16/page-frame.html",
        "User-Agent:Mozilla/5.0 (Linux; Android 12; M2012K11C Build/SKQ1.211006.001; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/86.0.4240.99 XWEB/4313 MMWEBSDK/20220805 Mobile Safari/537.36 MMWEBID/4629 MicroMessenger/8.0.27.2220(0x28001B37) WeChat/arm64 Weixin NetType/WIFI Language/zh_CN ABI/arm64 MiniProgramEnv/android",
        "t:eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE2OTQ1MDI0NDUsIm5iZiI6MTY2MzQwMDI0NSwiaWF0IjoxNjYzMzk4NDQ1LCJqdGkiOiJDTTpjYXRfbWF0Y2g6bHQxMjM0NTYiLCJvcGVuX2lkIjoiIiwidWlkIjo0NTk0MjYwMiwiZGVidWciOiIiLCJsYW5nIjoiIn0.1lXIcb1WL_SdsXG5N_i1drjjACRhRZUS2uadHlT6zIY"
    ];
    
    public function getTokenByUid($uid)
    {
        //先从缓存里找找
        $token = Cache::get('token_' . $uid);
        try {
            if (empty($token)) {
                $url = "https://cat-match.easygame2021.com/sheep/v1/game/user_info?uid=$uid";
                $resp = $this->curl($url, '', false, $this->header);
                $resp = json_decode($resp, true);
                if (is_array($resp)) {
                    if ($resp['err_code'] === 0) {
                        $openid = $resp['data']['wx_open_id'];
                        $data = [
                            'uuid' => $openid,
                        ];
                        $data = json_encode($data);
                        $url = 'https://cat-match.easygame2021.com/sheep/v1/user/login_tourist';
                        $resp = $this->curl($url, $data, true, $this->header);
                        $resp = json_decode($resp, true);
                        if ($resp['err_code'] === 0) {
                            $token = $resp['data']['token'];
                            Cache::set('token_' . $uid, $token);
                        }
                    }
                }
            }
        } catch (\Exception $exception) {
            $token = '';
        }
        return $token;
    }
    
    public function startMieMie($uid, $times = 1): bool
    {
        try {
            for ($i = 1; $i <= $times; $i++) {
                $token = $this->getTokenByUid($uid);
                $this->header[6] = "t:$token";
                $use_time = random_int(3, 5);
                $url = "https://cat-match.easygame2021.com/sheep/v1/game/game_over?t=$token&rank_score=1&rank_state=1&rank_time=$use_time&rank_role=1&skin=17";
                $rs = $this->curl($url, '', false, $this->header);
                $rs = json_decode($rs, true);
                if ($rs['err_code'] == 0) {
                    echo "UID:$uid" . '的第' . $i . '次通关完成@' . date('Y-m-d H:i:s') . "通关耗时：$use_time" . "\n";
                }
                if ($i % 10 === 0) {
                    sleep($use_time);
                }
            }
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
    
    function curl($url, $data = '', bool $isPostRequest = false, array $header = [], array $certParam = [])
    {
        // 模拟提交数据函数
        $curlObj = curl_init(); // 启动一个CURL会话
        //如果是POST请求
        if ($isPostRequest) {
            curl_setopt($curlObj, CURLOPT_POST, 1); // 发送一个常规的Post请求
            curl_setopt($curlObj, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        } else {  //get请求检查是否拼接了参数，如果没有，检查$data是否有参数，有参数就进行拼接操作
            $getParamStr = '';
            if (!empty($data) && is_array($data)) {
                $tmpArr = [];
                foreach ($data as $k => $v) {
                    $tmpArr[] = $k . '=' . $v;
                }
                $getParamStr = implode('&', $tmpArr);
            }
            //检查链接中是否有参数
            $url .= strpos($url, '?') !== false ? '&' . $getParamStr : '?' . $getParamStr;
        }
        curl_setopt($curlObj, CURLOPT_URL, $url); // 要访问的地址
        //检查链接是否https请求
        if (strpos($url, 'https') !== false) {
            //设置证书
            curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
            if (!empty($certParam) && isset($certParam['cert_path']) && isset($certParam['key_path'])) {
                // 对认证证书来源的检查
                curl_setopt($curlObj, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
                //设置证书
                //使用证书：cert 与 key 分别属于两个.pem文件
                curl_setopt($curlObj, CURLOPT_SSLCERTTYPE, 'PEM');
                curl_setopt($curlObj, CURLOPT_SSLCERT, $certParam['cert_path']);
                curl_setopt($curlObj, CURLOPT_SSLKEYTYPE, 'PEM');
                curl_setopt($curlObj, CURLOPT_SSLKEY, $certParam['key_path']);
            } else {
                // 对认证证书来源的检查
                curl_setopt($curlObj, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
            }
        }
        // 模拟用户使用的浏览器
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            curl_setopt($curlObj, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        }
        curl_setopt($curlObj, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curlObj, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curlObj, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curlObj, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, $header);   //设置头部
        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $responseText = curl_exec($curlObj); // 返回结果
        curl_close($curlObj); // 关闭CURL会话
        return $responseText;
    }
}
