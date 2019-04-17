<?php

namespace app\api\controller;

class Seckilljobs extends BaseController
{
	public function getModel()
	{
		return model('SeckillJobs');
	}

	public function active()
	{
		$job = $this->model->get(75);
		$job_goods = $job->goods()->with('info')->select();
		$goods = array_map(function ($good) {
			// dd($good->toArray());
			return [
				'id' => $good->id,
				'name' => $good->info->goods_name,
				'image' => $good->info->goods_image,
				'qty' => $good->qty,
				'sold' => $good->sold
			];
		}, $job_goods);
		return json(compact('job', 'goods'));
	}

}