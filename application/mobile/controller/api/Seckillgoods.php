<?php

namespace app\mobile\controller\api;

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
	    return json($this->model->byId($data['id'])->format());
	}

	public function buy()
	{
		//取商品队列

			//1. 队列为空，商品卖完，错误返回

			// 2. 队列不为空，取客户ID入客户队列

					//1. 客户为新客户，入队列，已购商品数增加
					//2. 客户在队列中，检查商品数量
							//1. 大于秒杀商品数量限制，错误返回
							//2. 不超过限制 ,已购商品数增加
							//    下单生成订单，进入支付页面
		    // 3. 支付页面

		// return json();
	}

}