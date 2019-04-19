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
		$data = checkInput([
			'id' => 'require|number',
			'seckill_id' => 'require|number',
			'number' => 'require|number',
			'key' => 'require|number'
		]);
		$good = $this->model->find($data['id']);

		$redis = new \Redis();
		$redis->connect('127.0.0.1', 6379);

		//取商品队列
		$redisGood = $redis->lpop('seckill:' . input('seckill_id'));
			//1. 队列为空，商品卖完，错误返回
		if (!$redisGood) {
			throw new JsonException("此商品已售完", 422);
		}
			// 2. 队列不为空，取客户ID 检查限额
		$member_id = memberIdByKey($data('key'));
		\Db::transaction(function () use ($good, $data) {
			$buyLog = SeckillUsers::where('member_id', $member_id)
					->where('seckill_id', $data['seckill_id'])
					->lock(true)
					->find();

			$good->checkLimit($buyLog, $data['number']);
			// 3. 生成订单

		    // 4. 支付页面
					// 1. 支付失败， 错误返回
					// 2. 支付成功，更新订单状态，客户队列已购商品数量增加
		// return json();

			$buyLog->purchased += $data['number'];
			$buyLog->save();
		}
	}


	public function order() {
		$param = [
	    	'cart_id' = explode(',', input('post.cart_id')),
		    'ifcart' = input('post.ifcart'),
		    'address_id' = input('post.address_id'),
		    'vat_hash' = input('post.vat_hash'),
		    'offpay_hash' = input('post.offpay_hash'),
		    'offpay_hash_batch' = input('post.offpay_hash_batch'),
		    'pay_name' = input('post.pay_name'),
		    'invoice_id' = input('post.invoice_id'),

		    'pintuan_id' = input('post.pintuan_id'),
		    'pintuangroup_id' = input('post.pintuangroup_id'),

		];

	    //处理代金券
	    $voucher = array();
	    $post_voucher = explode(',', input('post.voucher'));
	    if (!empty($post_voucher)) {
	        foreach ($post_voucher as $value) {
	            list($vouchertemplate_id, $store_id, $voucher_price) = explode('|', $value);
	            $voucher = $value;
	        }
	    }
	    $param['voucher'] = $voucher;

	    $pay_message = trim(input('post.pay_message'), ',');
	    $pay_message = explode(',', $pay_message);
	    $param['pay_message'] = array();
	    if (is_array($pay_message) && $pay_message) {
	        foreach ($pay_message as $v) {
	            if (strpos($v, '|') !== false) {
	                $v = explode('|', $v);
	                $param['pay_message'][$v[0]] = $v[1];
	            }
	        }
	    }
	    $param['pd_pay'] = input('post.pd_pay');
	    $param['rcb_pay'] = input('post.rcb_pay');
	    $param['password'] = input('post.password');
	    $param['fcode'] = input('post.fcode');
	    $param['order_from'] = 2;
	    $logic_buy = model('buy', 'logic');

	    //得到会员等级
	    /* $member_model = model('member');
	      $member_info = $member_model->getMemberInfoByID($this->member_info['member_id']);
	      if ($member_info) {
	      $member_gradeinfo = $member_model->getOneMemberGrade(intval($member_info['member_exppoints']));
	      $member_discount = $member_gradeinfo['orderdiscount'];
	      $member_level = $member_gradeinfo['level'];
	      }
	      else {
	      $member_discount = $member_level = 0;
	      } */

	    $result = $logic_buy->buyStep2($param, $this->member_info['member_id'], $this->member_info['member_name'], $this->member_info['member_email']);
	    if (!$result['code']) {
	        ds_json_encode(10001,$result['msg']);
	    }
	    $order_info = current($result['data']['order_list']);
	    $res = array('pay_sn' => $result['data']['pay_sn'], 'payment_code' => $order_info['payment_code']);
	    ds_json_encode(10000, '', $res);
	}

}