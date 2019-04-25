<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-18
 * Time: 15:41
 */

namespace app\common\model;


use think\Model;
use app\common\ModelTrait;

class Forsalegoods extends Model
{
    use ModelTrait;

    public $page_info;

    public function getGoodsInfoAndPromotionById($goods_id)
    {
        $goods = db('goods')->where("goods_id", $goods_id)->find();
        $point_goods = db('forsalegoods')->where("goods_id", $goods_id)->find();

        if ($point_goods) {
            $goods['goods_price'] = $point_goods['goods_price'];
            $goods['goods_storage'] = $point_goods['goods_storage'];
            $goods['goods_type'] = $point_goods['goods_type'];
            $goods['goods_promotion_price'] = $point_goods['goods_price'];
            $goods['goods_miaomi'] = $point_goods['goods_miaomi'];
            $goods['goods_salenum'] = $point_goods['sale_number'];

            return $goods;
        }

        return array();
    }


    public function getGoodsOnlineList($condition, $field = '*', $page = 0, $order = 'goods_id desc', $limit = 0, $group = '', $lock = false, $count = 0)
    {
        return $this->getGoodsList($condition, $field, $page, $order, $limit, $group, $lock, $count);
    }


    public function getGoodsList($condition, $field = '*', $page = 0, $order = 'goods_id desc', $limit = 0, $group = '', $lock = false, $count = 0)
    {
        $condition['goods_id'] = array("in", $this->getGoodsId());
        $goods_model = model("goods");

        $goods_list = $goods_model->getGoodsList($condition, $field, $group, $order, $limit, $page, $lock, $count);
        $this->page_info = $goods_model->page_info;

        return $this->parseGoodsList($goods_list);
    }


    public function getGoodsCommonList($condition, $field = '*', $page = 0, $order = 'goods_commonid desc')
    {
        $goods_model = model("goods");
        $condition['goods_commonid'] = array("in", $this->getGoodsCommonId());

        $goodscommon_list = $goods_model->getGoodsCommonList($condition, $field, $page, $order);
        $this->page_info = $goods_model->page_info;

        return $goodscommon_list;
    }


    public function getGoodsOnlineInfoAndPromotionById($goods_id)
    {
        $goods_info = $this->getGoodsInfoAndPromotionById($goods_id);
        if (empty($goods_info) || $goods_info['goods_state'] != 1) {
            return array();
        }
        return $goods_info;
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


    public function getGoodsId()
    {
        return self::group("goods_id")->column('goods_id');
    }


    public function getGoodsCommonId()
    {
        return self::group("goods_commonid")->column('goods_commonid');
    }


    public function parseGoodsList($goodsList)
    {
        foreach ($goodsList as &$goods) {
            $forsalegoods = self::get(['goods_id' => $goods['goods_id']]);

            $goods['goods_price'] = $forsalegoods->goods_price;
            $goods['goods_storage'] = $forsalegoods->goods_storage;
            $goods['goods_type'] = $forsalegoods->goods_type;
            $goods['goods_promotion_price'] = $forsalegoods->goods_price;
            $goods['goods_salenum'] = $forsalegoods->sale_number;
            $goods['goods_miaomi'] = $forsalegoods->goods_miaomi;
            $goods['goods_seckprice'] = $forsalegoods->goods_seckprice;
            $goods['profit_rate'] = $forsalegoods->profit_rate;
        }
        return $goodsList;
    }


    public function addOrUpdateForsaleGoods($data)
    {
        $common_id = $data['common_id'];
        $count = count($data['goods_id']);

        for ($i = 0; $i < $count; ++$i) {
            $insert_data = array(
                "goods_commonid" => $common_id,
                "goods_id" => $data['goods_id'][$i],
                "goods_name" => $data['goods_name'][$i],
                "goods_price" => $data['goods_price'][$i],
                "goods_storage" => $data['goods_storage'][$i],
                "goods_seckprice" => $data['goods_seckprice'][$i],
                "profit_rate" => $data['profit_rate'][$i],
                "service_fee" => $this->calculateServiceFee($data['goods_price'][$i], $data['goods_seckprice'][$i], $data['profit_rate'][$i]),
                "goods_miaomi" => $data['goods_miaomi'][$i],
                "goods_state" => 1,
                "sale_number" => 0,
                "created_at" => date('Y-m-d H:i:s', time()),
                "updated_at" => date('Y-m-d H:i:s', time()),
            );

            $forsalegoods = self::get(['goods_id' => $insert_data['goods_id']]);
            if ($forsalegoods) {
                # 如果商品存在就更新商品信息
                $forsalegoods->goods_price = $insert_data['goods_price'];
                $forsalegoods->goods_seckprice = $insert_data['goods_seckprice'];
                $forsalegoods->profit_rate = $insert_data['profit_rate'];
                $forsalegoods->service_fee = $insert_data['service_fee'];
                $forsalegoods->goods_miaomi = $insert_data['goods_miaomi'];
                $forsalegoods->goods_storage += $insert_data['goods_storage'];
                $forsalegoods->save();
            } else {
                # 添加商品
                self::create($insert_data);
            }
        }
    }


    public function calculateGoodsMiaomi($goods_price,$goods_seckprice,$profit_rate)
    {
        $service_fee = $this->calculateServiceFee($goods_price,$goods_seckprice,$profit_rate);
        $profit_rate = (float)$profit_rate;

        return $service_fee * (1 + $profit_rate/100);
    }


    public function calculateServiceFee($goods_price,$goods_seckprice,$profit_rate)
    {
        $config_model = model("Config");
        $forsale_bill_platform_rate = $config_model->getOneConfigByCode('forsale_bill_platform_rate');
        $forsale_bill_member_rate = $config_model->getOneConfigByCode('forsale_bill_member_rate');

        $platform_rate = (float)$forsale_bill_platform_rate['value'];
        $member_rate = (float)$forsale_bill_member_rate['value'];
        $goods_price = (float)$goods_price;
        $goods_seckprice = (float)$goods_seckprice;
        $profit_rate = (float)$profit_rate;

        return ($goods_price * $member_rate/100 - $goods_seckprice - $goods_seckprice * $profit_rate/100) * $platform_rate/100;
    }


    public function updateGoodsStorageAndSell($goods_id, $goods_number)
    {
        $pointgoods = self::get(['goods_id'=>$goods_id]);
        $pointgoods->goods_storage -= $goods_number;
        $pointgoods->sale_number += $goods_number;
        return $pointgoods->save();
    }


    public static function add($data)
    {
        return self::updateOrCreate(
            [
                'goods_id' => $data['goods_id']
            ], $data);
    }


    public function getGoodsCommendList($limit = 5) {
        $goods_commend_list = $this->getGoodsOnlineList(array('goods_commend' => 1), 'goods_id,goods_name,goods_advword,goods_image,goods_promotion_price,goods_price', 0, '', $limit, 'goods_commonid');
        if (!empty($goods_id_list)) {
            $tmp = array();
            foreach ($goods_id_list as $v) {
                $tmp[] = $v['goods_id'];
            }
            $goods_commend_list = $this->getGoodsOnlineList(array('goods_id' => array('in', $tmp)), 'goods_id,goods_name,goods_advword,goods_image,goods_promotion_price', 0, '', $limit);
        }
        return $goods_commend_list;
    }


}