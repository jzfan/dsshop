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


    public function getGoodsInfoAndPromotionById($goods_id)
    {
        $goods = db('goods')->where("goods_id",$goods_id)->find();
        $point_goods = db('pointgoods')->where("goods_id",$goods_id)->find();

        if ($point_goods) {
            $goods['goods_price'] = $point_goods['goods_price'];
            $goods['goods_storage'] = $point_goods['goods_storage'];
            $goods['goods_type'] = $point_goods['goods_type'];
            $goods['goods_promotion_price'] = $point_goods['goods_price'];
            $goods['goods_point'] = $point_goods['goods_point'];
            $goods['goods_salenum'] = $point_goods['sale_number'];

            return $goods;
        }

        return array();
    }


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


    public function getCommonPointGoodsList($condition, $field = '*', $page = 10, $order = 'goods_commonid desc') {

        $condition['goods_commonid'] = array("in", $this->getPointCommonId());
        $condition = model("Goods")->__getRecursiveClass($condition);

        if ($page) {
            $result = db('goodscommon')->field($field)->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            return db('goodscommon')->field($field)->where($condition)->order($order)->select();
        }
    }

    public function calculateStorage($goods_list)
    {
        if (!empty($goods_list)) {
            foreach ($goods_list as $value) {
                $goodscommonid_array[] = $value['goods_commonid'];
            }
            $goods_storage = $this->getGoodsOnlineList(array('goods_commonid' => array('in', $goodscommonid_array)), 'goods_storage,goods_commonid,goods_id');
            $storage_array = array();
            foreach ($goods_storage as $val) {
                //初始化
                if (!isset($storage_array[$val['goods_commonid']]['sum'])) {
                    $storage_array[$val['goods_commonid']]['sum'] = 0;
                }
                $storage_array[$val['goods_commonid']]['sum'] += $val['goods_storage'];
                $storage_array[$val['goods_commonid']]['goods_id'] = $val['goods_id'];
            }
            return $storage_array;
        } else {
            return false;
        }
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


    public function getPointCommonId()
    {
        return self::where('goods_state',1)->where('goods_storage','gt',0)
            ->column('goods_commonid');
    }


    public function parseGoodsList($goodsList)
    {
        foreach ($goodsList as &$goods) {
            $pointGoods = self::get(['goods_id'=>$goods['goods_id']]);

            $goods['goods_price'] = $pointGoods->goods_price;
            $goods['goods_storage'] = $pointGoods->goods_storage;
            $goods['goods_type'] = $pointGoods->goods_type;
            $goods['goods_promotion_price'] = $pointGoods->goods_price;
            $goods['goods_point'] = $pointGoods->goods_point;
            $goods['goods_salenum'] = $pointGoods->sale_number;
        }
        return $goodsList;
    }


    public function addOrUpdatePointGoods($data)
    {
        $common_id  = $data['common_id'];
        $count = count($data['goods_id']);
        $insert_data = array();
        for($i=0; $i < $count; ++$i) {
            $insert_data[] = array(
                "goods_commonid" =>  $common_id,
                "goods_id" => $data['goods_id'][$i],
                "goods_price" => $data['goods_price'][$i],
                "goods_storage" => $data['goods_storage'][$i],
                "goods_point" => $data['goods_point'][$i],
                "goods_state" => 1,
                "sale_number" => 0,
                "created_at"  => date('Y-m-d H:i:s',time()),
                "updated_at"  => date('Y-m-d H:i:s',time()),
            );
        }
        //商品已经存在就更新
        foreach ($insert_data as $insert_datum) {
            $pointgoods = self::get(['goods_id'=>$insert_datum['goods_id']]);
            if($pointgoods) {
                unset($insert_datum['created_at']);
                $pointgoods->save($insert_datum);
            } else {
                self::create($insert_datum);
            }
        }
    }


    public function updateGoodsStorageAndSell($goods_id, $goods_number)
    {
        $pointgoods = self::get(['goods_id'=>$goods_id]);
        $pointgoods->goods_storage -= $goods_number;
        $pointgoods->sale_number += $goods_number;
        return $pointgoods->save();
    }

}