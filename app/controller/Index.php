<?php

namespace app\controller;

use support\Request;
use support\Ylgy;
use Webman\RedisQueue\Redis;

class Index
{
    public function index(Request $request): \support\Response
    {
        return view('index/view', []);
    }
    
    public function do(Request $request): string
    {
        $uid = $request->post('uid', '');
        $times = $request->post('times', 1);    //通关次数
        $is_queue = $request->post('is_queue', 1);  //使用队列
        if ($is_queue) {
            $queue = 'fireMieMie';
            $data = [
                'uid' => $uid,
                'times' => $times,
            ];
            Redis::send($queue, $data);
            return json_encode(['code' => 1, 'msg' => '任务提交成功']);
        }
//        return (new Ylgy())->startMieMie($uid, $times); 不使用队列的话 打开
    }
}
