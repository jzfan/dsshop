<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-16
 * Time: 13:09
 */

namespace app\common\model;


use think\Model;

class Pointgoods extends Model
{
    public $page_info;


    public function getPointGoodsList($condition, $field = '*', $page = 10, $order = 'goods_id desc') {
        $condition = $this->getBaseCondition($condition);
        if ($page) {
            $paginate = db('goods')->field($field)->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info = $paginate;
            $goodsList =  $paginate->items();
        } else {
            $goodsList = db('goods')->field($field)->where($condition)->order($order)->select();
        }

        return $this->parseGoodsList($goodsList);
    }


    public function getBaseCondition($condition)
    {
        $condition['goods_id'] = array("in", $this->getPointGoodsId());
        $condition['goods_state'] = 1;

        return model("Goods")->__getRecursiveClass($condition);
    }


    public function getPointGoodsId()
    {
        return self::where('goods_state',1)->where('goods_storage','gt',0)
            ->column('goods_id');
    }


    public function parseGoodsList($goodsList)
    {
        foreach ($goodsList as &$goods) {
            $pointGoods = self::get(['goods_id'=>$goods['goods_id']]);

            $goods['goods_price'] = $pointGoods->goods_price;
            $goods['goods_storage'] = $pointGoods->goods_storage;
            $goods['goods_type'] = $pointGoods->goods_type;
            $goods['goods_promotion_price'] = $pointGoods->goods_price;
        }
        return $goodsList;
    }


    public function add_pointgoods($data)
    {
        return self::create($data);
    }


    public function updateGoodsSpecValue($common_id)
    {
        
    }
}