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
		// just for dev
		$job = $this->model->get(75);
		// $job = $this->model->active();

		if ($job) {
			$goods = $job->formatGoods();
			return json(compact('job', 'goods'));
		}
	}

	public function next()
	{
		$job = $this->model->where('status', 0)->order('start', 'asc')->find();
		if ($job) {
			$goods = $job->formatGoods();
			return json(compact('job', 'goods'));
		}
	}

}