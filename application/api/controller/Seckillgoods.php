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

}