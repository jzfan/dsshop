<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-20
 * Time: 13:57
 */

namespace app\common\model;


use think\Model;

class Memberforsaleorder extends Model
{

    public function createMemberForsaleOrder($order_id, $order_sn, $buyer_id,$memberforsalegoods)
    {
        $insert_data = array();
        foreach ($memberforsalegoods as $value) {
            $insert_data[] = array (
                "order_id"  =>  $order_id,
                "order_sn"  =>  $order_sn,
                "buyer_id"  =>  $buyer_id,
                "goods_number" => $value['goods_number'],
                "goods_price" => $value['goods_price'],
                "member_id" => $value['member_id'],
                "order_state" => 0,
            );
        }
        return self::saveAll($insert_data);
    }
}