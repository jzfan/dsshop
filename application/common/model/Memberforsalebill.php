<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-20
 * Time: 17:14
 */

namespace app\common\model;


use think\Model;

class Memberforsalebill extends Model
{

    public function createMemberForsaleBill($order_id, $order_sn, $order_amount, $buyer_id, $memberforsalegoods)
    {
        $insert_data = array();
        foreach ($memberforsalegoods as $value) {
            $insert_data[] = array (
                "order_id"  =>  $order_id,
                "order_sn"  =>  $order_sn,
                "member_id" => $value['member_id'],
                "order_amount" => $order_amount,
                "bill_percent" => 0,
                "bill_amount" => 0,
                "bill_state" => 0,
            );
        }

        // 增加上级
        $member_info = model("member")->getMemberInfo(array("member_id"=>$buyer_id));
        if ($member_info['inviter_id']) {
            $insert_data[] = array (
                "order_id"  =>  $order_id,
                "order_sn"  =>  $order_sn,
                "member_id" => $member_info['inviter_id'],
                "order_amount" => $order_amount,
                "bill_percent" => 2,
                "bill_amount" => 0,
                "bill_state" => 0,
            );
        }

        return self::saveAll($insert_data);
    }

}