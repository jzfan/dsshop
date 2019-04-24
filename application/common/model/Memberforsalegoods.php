<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-20
 * Time: 10:05
 */

namespace app\common\model;


use think\Model;
use app\common\ModelTrait;

class Memberforsalegoods extends Model
{
    use ModelTrait;
    
    public $page_info;

    public function freezeMemberForsaleGoods($goods_id, $goods_number)
    {
        $queue = model("forsalequeue");
        $memberforsalegoods_model = model("memberforsalegoods");
        $goods_info = array();
        while (true) {
            $forsalegoods = $queue->getForsaleGoods($goods_id);
            #当前商品真实剩余库存
            $goods_storage = $forsalegoods->left_number - $forsalegoods->freeze_number;

            if ($goods_storage < $goods_number) {
                $update_data['freeze_number'] = $forsalegoods->freeze_number + $goods_storage;
                $goods_number -= $goods_storage;
                $goods_info[] = array(
                    "member_id" => $forsalegoods->member_id,
                    "goods_number" => $goods_storage,
                    "goods_price" => $forsalegoods->goods_price,
                );
            } else {
                $update_data['freeze_number'] = $forsalegoods->freeze_number + $goods_number;
                $goods_info[] = array(
                    "member_id" => $forsalegoods->member_id,
                    "goods_number" => $goods_number,
                    "goods_price" => $forsalegoods->goods_price,
                );
                $goods_number = 0;
            }
            $memberforsalegoods_model->update($update_data,['id'=>$forsalegoods->id]);

            if ($goods_number == 0) {
                break;
            }
        }

        return $goods_info;
    }


    public function unfreezeMemberForsaleGoods($goods_id, $goods_number, $member_id)
    {
        $forsalegoods = self::where('goods_id',$goods_id)->where('member_id',$member_id)->find();
        $forsalegoods->sale_number += $goods_number;
        $forsalegoods->left_number -= $goods_number;
        $forsalegoods->freeze_number -= $goods_number;
        $forsalegoods->save();
    }


    public function getlist($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '')
    {
        if ($pagesize) {
            $pdlog_list_paginate = db('memberforsalegoods')->where($condition)->field($fields)->order($order)->paginate($pagesize, false, ['query' => request()->param()]);
            $this->page_info = $pdlog_list_paginate;
            return $pdlog_list_paginate->items();
        } else {
            $pdlog_list_paginate = db('memberforsalegoods')->where($condition)->field($fields)->order($order)->limit($limit)->select();
            return $pdlog_list_paginate;
        }
    }


    public function getgoodsinfo($goods_common_id)
    {
        $model = db('goodscommon')->where('goods_commonid', $goods_common_id)->find();
        if ($model['spec_name'] == 'N;' && $model['spec_value'] == 'N;') {
            return '';
        } else {
            $model['spec_value'] = unserialize($model['spec_value']);
            foreach ($model['spec_value'] as $k => $v) {
                foreach ($v as $vo) {
                    $arr[] = $vo;
                }
            }
            $result = implode(',', $arr);
            return $result;
        }

    }


    public function getname($id)
    {
        $model = db('goods')->where('goods_id', $id)->find();
        if (!empty($model)) {
            return $model['goods_name'];
        } else {
            return '';
        }
    }


    public function getpic($id)
    {
        $model = db('goods')->where('goods_id',$id)->find();
        if (!empty($model)) {
            return UPLOAD_SITE_URL.'/'.ATTACH_GOODS.'/'.$model['goods_image'];
        } else {
            return '';
        }
    }


    public function getStatus($status)
    {
        if ($status == 0) {
            return '等待挂售';
        } elseif ($status==1) {
            return '挂售中';
        } else {
            return '挂售完成';
        }
    }


    public function getNum($order_sn)
    {
        $model = db('memberforsaleorder')->where('order_sn', $order_sn)->find();
        if (!empty($model)) {
            return $model['goods_number'];
        } else {
            return '';
        }
    }


    public function addOrUpdateForsaleGoods($data)
    {
        $common_id  = $data['common_id'];
        $count = count($data['goods_id']);
        $insert_data = array();
        for($i=0; $i < $count; ++$i) {
            $insert_data[] = array(
                "goods_commonid" =>  $common_id,
                "goods_id" => $data['goods_id'][$i],
                "goods_number" => $data['goods_storage'][$i],
                "left_number" => $data['goods_storage'][$i],
                "sale_number" => 0,
                "goods_state" => 1,
                "freeze_number" => 0,
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


    public static function add($data)
    {
        return self::updateOrCreate([
            'goods_id' => $data['goods_id']
        ], $data);
    }

}