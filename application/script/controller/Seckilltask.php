<?php

namespace app\script\controller;

use think\Db;
use think\Controller;
use app\common\model\SeckillJobs;

class Seckilltask extends Controller
{
	protected $redis;

	protected function _initialize()
	{
		$this->redis = new \Redis;
		$this->redis->connect('127.0.0.1', 6379);
		$this->seed();
		sleep(1);
	}

	public function handle()
	{
		Db::transaction(function () {
			$jobs = SeckillJobs::where('status', 0)->order('start', 'asc')->lock(true)->select();
			foreach ($jobs as $job) {
				if ($job->isOver() || $job->isWaiting()) {
					continue;
				}
				if ($job->isActive()) {
					echo "handle job {$job->id}:{$job->name}" . PHP_EOL;
					$this->start($job);
				}

			}
		});
		echo 'done' . PHP_EOL;
		exit;
	}

	protected function start($job)
	{
		foreach ($job->goods as $good) {
			for ($i = $good->qty; $i--;) {
				$this->redis->lpush("seckill:{$job->id}", "good:{$good->id}");
			}
		}
	}

	protected function seed()
	{
		$job = SeckillJobs::create([
			'name' => 'test',
			'start' => date('Y-m-d H:i:s', time()),
			'end' => date('Y-m-d H:i:s', time() + 3600),
			'status' => 0
		]);
		db('seckill_goods')->insert([
			'goods_id' => rand(1, 100),
			'job_id' => $job->id,
			'mi' => rand(1, 10) * 100,
			'price' =>  number_format(rand(1, 100), 2),
			'qty' => rand(10, 500)
		]);
	}

}