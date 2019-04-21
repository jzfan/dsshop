<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-20
 * Time: 10:05
 */

namespace app\common\model;


use think\Model;

class Memberforsalegoods extends Model
{


    public function freezeMemberForsaleGoods($goods_id, $goods_number)
    {
        $queue = model("forsalequeue");
        $goods_info = array();
        while(true) {
            $forsalegoods = $queue->getForsaleGoods($goods_id);
            #当前商品真实剩余库存
            $goods_storage = $forsalegoods->left_number - $forsalegoods->freeze_number;
            if ($goods_storage < $goods_number) {
                $forsalegoods->freeze_number += $goods_storage;
                $goods_number -= $goods_storage;
                $goods_info[] = array(
                    "member_id" => $forsalegoods->member_id,
                    "goods_number" => $goods_storage,
                    "goods_price" => $forsalegoods->goods_storage,
                );
            } else {
                $forsalegoods->freeze_number += $goods_number;
                $goods_info[] = array(
                    "member_id" => $forsalegoods->member_id,
                    "goods_number" => $goods_number,
                    "goods_price" => $forsalegoods->goods_storage,
                );
                $goods_number = 0;
            }
            $forsalegoods->save();

            if ($goods_number == 0) {
                break;
            }
        }

        return $goods_info;
    }
}