<?php

namespace app\mobile\controller\api;

use app\common\model\Goods;

class Seckillgoods extends BaseController
{
	public function getModel()
	{
		return model('SeckillGoods');
	}

	public function index()
	{
		$good = $this->model->where('goods_id', input('goods_id'))->order('id desc')->find();
		if (!$good) {
			$good = Goods::where('goods_id', input('goods_id'))->find();
			// $good = $this->formatByGoods($good);
		}
		return json($good);
	}

	public function get()
	{
		$data = checkInput([
			'goods_id' => 'require|number',
			'job_id' => 'require|number'
		]);
	    return json($this->model->byJobGoodsId($data['job_id'], $data['goods_id'])->format());
	}

	// protected function formatByGoods($good)
	// {
	// 	return [
	// 		'price' => $good->goods_price,
	// 		'name' => $good->goods_name
	// 	];
	// }

}