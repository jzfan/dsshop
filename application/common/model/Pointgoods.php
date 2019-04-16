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


    public function getPointGoodsList($condition, $field = '*', $page = 10, $order = 'goods_commonid desc') {
        if ($page) {
            $result = db('pointgoods')->field($field)->where($condition)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            return db('pointgoods')->field($field)->where($condition)->order($order)->select();
        }
    }

}