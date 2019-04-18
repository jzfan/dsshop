<?php

namespace app\api\controller;

class Seckillgoods extends BaseController
{
	public function getModel()
	{
		return model('SeckillGoods');
	}

	public function index()
	{
		if ($id = input('goods_id')) {
			return $this->model->where('goods_id', $id)->find();
		}
	}

	public function get()
	{
		$data = checkInput([
			'id' => 'require|number'
		]);
	    return json($this->model->byId($data['id']));
	}

}