<?php

namespace app\mobile\controller\api;

class Seckilljobs extends BaseController
{
	public function getModel()
	{
		return model('SeckillJobs');
	}

	public function active()
	{
		$job = $this->model->active();

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

	public function find()
	{
		checkInput([
			'id' => 'require|number'
		]);

		$job = $this->model->find(input('id'));
		
		if ($job) {
			$goods = $job->formatGoods();
			return json(compact('job', 'goods'));
		}
	}

}