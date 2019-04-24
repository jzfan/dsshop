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
    public $page_info;

    public function createMemberForsaleOrder($order_id, $order_sn, $buyer_id,$memberforsalegoods)
    {
        $insert_data = array();
        foreach ($memberforsalegoods as $value) {
            $insert_data[] = array (
                "order_id"  =>  $order_id,
                "order_sn"  =>  $order_sn,
                "buyer_id"  =>  $buyer_id,
                "goods_id"  =>  $value['goods_id'],
                "goods_number" => $value['goods_number'],
                "goods_price" => $value['goods_price'],
                "member_id" => $value['member_id'],
                "goods_amount" => $value['goods_price'] * $value['goods_number'],
                "service_fee" => $value['service_fee'] * $value['goods_number'],
                "order_state" => 0,
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s'),
            );
        }
        return self::saveAll($insert_data);
    }


    public function getmemberforsaleorder($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '')
    {
        if ($pagesize) {
            $pdlog_list_paginate = db('memberforsaleorder')->where($condition)->field($fields)->order($order)->paginate($pagesize, false, ['query' => request()->param()]);
            $this->page_info = $pdlog_list_paginate;
            return $pdlog_list_paginate->items();
        } else {
            $pdlog_list_paginate = db('memberforsaleorder')->where($condition)->field($fields)->order($order)->limit($limit)->select();
            return $pdlog_list_paginate;
        }
    }


    public function updateForsaleOrder($condition,$data)
    {
        return self::update($data,$condition);
    }

}