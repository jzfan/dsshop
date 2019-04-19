<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-18
 * Time: 20:36
 */

namespace app\common\logic;


use think\Model;

class Forsalegoodsqueue extends Model
{


    public function getGoodsInfoForSale($goods_id, $goods_number)
    {
        $goods_info = array();
        while(true) {
            $forsalegoods = $this->getGoodsInfoByGoodsId($goods_id);
            if ($goods_number > $forsalegoods->left) {
                $goods_info[] = array(
                    "member_id" => $forsalegoods->member_id,
                    "goods_number" => $forsalegoods->left,
                );
                $goods_number -= $forsalegoods->left;
            } else {
                $goods_info[] = array(
                    "member_id" => $forsalegoods->member_id,
                    "goods_number" => $goods_number,
                );
                $goods_number = 0;
            }
            if ($goods_number == 0) {
                break;
            }
        }
        return $goods_info;
    }


    public function getGoodsInfoByGoodsId($goods_id)
    {
        return self::where("goods_id",$goods_id)->where("status",0)
            ->order("id asc,sort desc")->find();
    }


    public function updateForsaleGoodsInfo($goods_id, $goods_info)
    {
        foreach ($goods_info as $goods) {
            
        }
    }
}