<?php

namespace app\common\logic;

use think\Db;
use think\Model;
use app\common\model\Order;
use app\common\model\Member;

class Seckill
{
    public function b4Pay($order_id)
    {
        if (!$this->isBuyerMiEnough($order_id)) {
                exception('买家秒米不够');
        }
    }

    public function payed()
    {
        
    }

    protected function isBuyerMiEnough($order_id)
    {
        return Db::transaction(function () use ($order_id) {
            $order = Order::where('order_id', $order_id)->lock(true)->find();
            $buyer = Member::where('buyer_id', $order->member_id)->lock(true)->find();
            return $order->miaomi_amount > $buyer->meter_second);
        });
    }

}