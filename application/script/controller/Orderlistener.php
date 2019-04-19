<?php

namespace app\script\controller;

use think\Db;
use think\Controller;
use app\common\model\SeckillJobs;

class Orderlistener extends Controller
{
	protected $redis;

	protected function _initialize()
	{
		$this->redis = new \Redis;
		$this->redis->connect('127.0.0.1', 6379);
	}

	public function listen()
	{
		$this->redis->psubscribe(['__keyevent@0__:expired'], [$this, 'callBack']);
	}


	public function callBack($redis, $pattern, $chan, $msg)
	{
	    echo "Pattern: $pattern\n";
	    echo "Channel: $chan\n";
	    echo "Payload: $msg\n\n";
	}


}