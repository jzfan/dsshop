<?php

namespace app\script\controller;

use think\Db;
use think\Controller;
use app\common\model\SeckillJobs;

class Seckilltask extends Controller
{

	public function handle()
	{
		Db::transaction(function () {
			$jobs = SeckillJobs::unCompleted()
							->lock(true)->select();
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
		$job->pushGoods();
	}

	public function seed()
	{
		$job = SeckillJobs::create([
			'name' => 'test',
			'start' => date('Y-m-d H:i:s'),
			'end' => date('Y-m-d H:i:s', time() + 3600),
			'status' => 0
		]);
		// $sql = Db::getLastSql();
		// dd($sql);
		$goods = db('goods')->orderRaw('rand()')->limit(rand(1, 3))->select();
		foreach ($goods as $good) {
			db('seckill_goods')->insert([
				'goods_id' => $good['goods_id'],
				'job_id' => $job->id,
				'mi' => rand(1, 10) * 100,
				'price' =>  $good['goods_price'],
				'qty' => rand(1, $good['goods_storage'])
			]);
		}
	}

}