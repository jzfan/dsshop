<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-18
 * Time: 15:41
 */

namespace app\common\model;


use think\Model;

class Forsalegoods extends Model
{
    public $page_info;

    public function getGoodsOnlineList($condition, $field = '*', $page = 0, $order = 'goods_id desc', $limit = 0, $group = '', $lock = false, $count = 0)
    {
        $condition = $this->getBaseCondition($condition);
        if ($page) {
            $paginate = db('goods')->field($field)->where($condition)->group($group)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info = $paginate;
            $goodsList =  $paginate->items();
        } else {
            $goodsList = db('goods')->field($field)->where($condition)->limit($limit)->group($group)->order($order)->select();
        }

        return $this->parseGoodsList($goodsList);
    }

    public function getBaseCondition($condition)
    {
        $condition['goods_id'] = array("in", $this->getPointGoodsId());
        $condition['goods_state'] = 1;

        return $condition;
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


    public function getGoodsInfoAndPromotionById($goods_id)
    {
        $goods = db('goods')->where("goods_id",$goods_id)->find();
        $point_goods = db('forsalegoods')->where("goods_id",$goods_id)->find();

        if ($point_goods) {
            $goods['goods_price'] = $point_goods['goods_price'];
            $goods['goods_storage'] = $point_goods['goods_storage'];
            $goods['goods_type'] = $point_goods['goods_type'];
            $goods['goods_promotion_price'] = $point_goods['goods_price'];

            return $goods;
        }

        return array();
    }


    /**
     * 查询出售中的商品详细信息及其促销信息
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品ID
     * @return array
     */
    public function getGoodsOnlineInfoAndPromotionById($goods_id) {
        $goods_info = $this->getGoodsInfoAndPromotionById($goods_id);
        if (empty($goods_info) || $goods_info['goods_state'] != 1) {
            return array();
        }
        return $goods_info;
    }
}