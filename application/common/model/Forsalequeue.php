<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-20
 * Time: 10:02
 */

namespace app\common\model;


use think\Model;

class Forsalequeue extends Model
{

    public function getMemberForsaleGoods($goods_id)
    {
        return self::alias('a')->field('b.*,c.goods_price,c.service_fee')
            ->join('ds_memberforsalegoods b',' a.forsale_id=b.id')
            ->join('ds_forsalegoods c','b.goods_id=c.goods_id')
            ->where("b.goods_id",$goods_id)
            ->where('b.goods_state',1)
            ->where('(b.left_number-b.freeze_number)','gt',0)
            ->order('a.sortable asc,a.id asc')
            ->find();
    }


    public function addMemberForsaleGoods($forsale_goods)
    {
        foreach ($forsale_goods as $value) {
            $queue = self::get(['forsale_id'=>$value['forsale_id']]);
            if ($queue) {
                continue;
            }
            $insert_data = [
                'forsale_id' => $value['forsale_id'],
                'goods_commonid' => $value['goods_commonid'],
                'sortable' => 1,
                "created_at"  => date('Y-m-d H:i:s',time()),
                "updated_at"  => date('Y-m-d H:i:s',time()),
            ];
            self::create($insert_data);
        }
    }


    public function updateMemberForsaleGoods()
    {

    }



}