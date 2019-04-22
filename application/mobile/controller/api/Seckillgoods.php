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
	    return json($this->model->byGoodsId($data['id'])->format());
	}

	public function buy()
	{
		$data = checkInput([
			'goods_id' => 'require|number',
			'seckill_id' => 'require|number',
			'number' => 'require|number',
			'key' => 'require|number'
		]);
		$good = $this->model->where('goods_id', $data['goods_id'])->find();

		$redis = new \Redis();
		$redis->connect('127.0.0.1', 6379);

		//取商品队列
		$redisGood = $redis->lpop('seckill:' . input('seckill_id'));
			//1. 队列为空，商品卖完，错误返回
		if (!$redisGood) {
			throw new JsonException("此商品已售完", 422);
		}
			// 2. 队列不为空，取客户ID 检查限额
		// $member_id = memberIdByKey($data('key'));
		// \Db::transaction(function () use ($good, $data) {
		// 	$buyLog = SeckillUsers::where('member_id', $member_id)
		// 			->where('seckill_id', $data['seckill_id'])
		// 			->lock(true)
		// 			->find();

		// 	$good->checkLimit($buyLog, $data['number']);
		// 	// 3. 生成订单

		//     // 4. 支付页面
		// 			// 1. 支付失败， 错误返回
		// 			// 2. 支付成功，更新订单状态，客户队列已购商品数量增加
		// // return json();

		// 	$buyLog->purchased += $data['number'];
		// 	$buyLog->save();
		// }
	}

}