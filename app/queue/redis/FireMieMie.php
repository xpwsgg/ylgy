<?php

namespace App\queue\redis;

use support\Ylgy;
use Webman\RedisQueue\Consumer;

class FireMieMie implements Consumer
{
    public $queue = 'fireMieMie';
    public $connection = 'default';
    
    public function consume($data)
    {
        (new Ylgy())->startMieMie($data['uid'], $data['times']);
    }
}
