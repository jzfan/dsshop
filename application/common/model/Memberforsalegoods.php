<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-20
 * Time: 10:05
 */

namespace app\common\model;


use think\Db;
use think\Model;
use app\common\ModelTrait;

class Memberforsalegoods extends Model
{
    use ModelTrait;
    
    public $page_info;

    public function freezeMemberForsaleGoods($goods_id, $goods_number)
    {
        $goods_info = array();
        while (true) {
            $forsalegoods = $this->getMemberForsaleGoodsInfo($goods_id);
            #当前商品真实剩余库存
            $goods_storage = $forsalegoods->left_number - $forsalegoods->freeze_number;

            if ($goods_storage < $goods_number) {
                $update_data['freeze_number'] = $forsalegoods->freeze_number + $goods_storage;
                $goods_number -= $goods_storage;
                $goods_info[] = array(
                    "goods_id" => $goods_id,
                    "member_id" => $forsalegoods->member_id,
                    "goods_number" => $goods_storage,
                    "goods_price" => $forsalegoods->goods_price,
                    "service_fee" => $forsalegoods->service_fee,

                );
            } else {
                $update_data['freeze_number'] = $forsalegoods->freeze_number + $goods_number;
                $goods_info[] = array(
                    "goods_id" => $goods_id,
                    "member_id" => $forsalegoods->member_id,
                    "goods_number" => $goods_number,
                    "goods_price" => $forsalegoods->goods_price,
                    "service_fee" => $forsalegoods->service_fee,
                );
                $goods_number = 0;
            }
            $this->update($update_data,['id'=>$forsalegoods->id]);

            if ($goods_number == 0) {
                break;
            }
        }

        return $goods_info;
    }


    public function unfreezeMemberForsaleGoods($order_id)
    {
        $forsaleorder_model = model('memberforsaleorder');
        $condition = array('order_id' => $order_id);

        $forsaleorder_list = $forsaleorder_model->getmemberforsaleorder($condition);
        foreach ($forsaleorder_list as $forsaleorder) {
            $update_data = [
                'freeze_number' => Db::raw('freeze_number-' . $forsaleorder['goods_number']),
                'left_number'   => Db::raw('left_number-' . $forsaleorder['goods_number']),
                'sale_number'   => Db::raw('sale_number+' . $forsaleorder['goods_number']),
            ];
            $update_condition = [
                'goods_id'  =>  $forsaleorder['goods_id'],
                'member_id' =>  $forsaleorder['member_id'],
            ];
            self::update($update_data, $update_condition);

        }
    }


    public function getMemberForsaleGoodsInfo($goods_id)
    {
        return self::alias('a')->field('a.*,b.goods_price,b.service_fee')
            ->join('ds_forsalegoods b','a.goods_id=b.goods_id')
            ->where("a.goods_id",$goods_id)
            ->where('a.goods_state',1)
            ->where('(a.left_number-a.freeze_number)','gt',0)
            ->order('a.sortable asc,a.id asc')
            ->find();
    }


    public function getMemberForsaleGoodsInfoByGoodsName($search_goods_name)
    {
        return self::where('goods_name','like', '%' . $search_goods_name . '%')->find();
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


    public function getMemberForsaleGoodsList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '')
    {
        $goods_list = $this->getlist($condition,$pagesize,$fields,$order,$limit);

        foreach ($goods_list as &$goods_info) {
            $forsalegoods = model('forsalegoods')->get(['goods_id'=>$goods_info['goods_id']]);
            $goods_info['goods_price'] = $forsalegoods->goods_price;
        }

        return $goods_list;
    }


    public function updateMemberForsaleGoodsSortable($forsale_id, $step)
    {
        self::startTrans();
        try {
            $queue = self::get(['id' => $forsale_id]);
            if ($queue->sortable > $step) {
                // 上移
                self::where('goods_commonid', $queue->goods_commonid)->where('sortable', 'egt', $step)
                    ->where('sortable', 'lt', $queue->sortable)
                    ->setInc('sortable', 1);
                //交换位置
            } else {
                // 下移
                self::where('goods_commonid', $queue->goods_commonid)->where('sortable', 'elt', $step)
                    ->where('sortable', 'gt', $queue->sortable)
                    ->setDec('sortable', 1);
            }

            // 更新当前商品位置
            self::update(['sortable' => $step], ['id' => $forsale_id]);

            self::commit();
            return true;
        }catch (\Exception $e) {
            echo $e->getMessage();die;
            self::rollback();
        }
        return false;
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


    /**
     * 说明：管理后台增加91购商品
     * @param $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addMemberForsaleGoods($data)
    {
        $common_id  = $data['common_id'];
        $count = count($data['goods_id']);

        for($i=0; $i < $count; ++$i) {
            $insert_data = array(
                "goods_commonid"    =>  $common_id,
                "goods_id"          =>  $data['goods_id'][$i],
                "goods_name"        =>  $data['goods_name'][$i],
                "goods_number"      =>  $data['goods_storage'][$i],
                "left_number"       =>  $data['goods_storage'][$i],
                "sale_number"       =>  0,
                "goods_state"       =>  1,
                "freeze_number"     =>  0,
                "member_phone"      =>  "0000",
                "created_at"        =>  date('Y-m-d H:i:s',time()),
                "updated_at"        =>  date('Y-m-d H:i:s',time()),
            );

            #需要默认添加的同商品最末端
            $last = self::where("goods_commonid",$common_id)->order('sortable','desc')->find();
            $sortable = 1;
            if (!is_null($last)) {
                $sortable = $last->sortable + 1;
            }
            $insert_data['sortable'] = $sortable;

            self::create($insert_data);
        }
    }


    public static function add($data)
    {
        return self::updateOrCreate([
            'goods_id' => $data['goods_id']
        ], $data);
    }


}