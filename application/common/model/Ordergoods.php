<?php

namespace app\common\model;

use think\Model;

class Ordergoods extends Model
{
    const TYPE = [
        40 => '\app\common\model\SeckillGoods',
        41 => '\app\common\model\SeckillGoods',
    ];

    public function good()
    {
        return $this->belongsTo('Goods::class', 'goods_id', 'goods_id');
    }

    public function returnWareHouse($order_type)
    {
        $class = self::TYPE[$order_type];
        $num = $this->goods_num;
        (new $class)->unSold($num)
        			->push($num);
    }
}
