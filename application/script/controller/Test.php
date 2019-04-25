<?php

namespace app\script\controller;

use think\Controller;
use app\common\DsRedis;
use app\common\Formula;

class Test extends Controller
{

	public function index()
	{
		$data = [
			[
				'name' => '金骏眉茶叶',
				'marketPrice' => 3600,
				'seckillPrice' => 360,
				'returnRate' => 0.089
			],
			[
				'name' => '茅台不老酒V30（箱）',
				'marketPrice' => 4860,
				'seckillPrice' => 486,
				'returnRate' => 0.087
			],
		];

		$this->cal($data);
		exit;
	}

	public function cal($data)
	{
		foreach ($data as $item) {
			$fo = new Formula($item['marketPrice'], $item['seckillPrice'], $item['returnRate'] );
			echo 'name: ' . $item['name'] . "\n";
			echo 'payClient: ' . $fo->payClient . "\n";
			echo 'fee: ' . $fo->fee . "\n";
			echo 'mi: ' . $fo->mi . "\n";
		}
	}

	public function redis()
	{
		$redis = DsRedis::getInstance();
		$redis->setLock('test lock');
		$redis->releaseLock('test lock');
		echo 'done';
		exit;
	}

}