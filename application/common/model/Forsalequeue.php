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

    public function getForsaleGoods($goods_id)
    {
        return self::alias('q')->field("g.*")->join('ds_memberforsalegoods g',' q.forsale_id=g.id')
            ->where("g.goods_id",$goods_id)->where('g.goods_state',1)
            ->where('(g.left_number-g.freeze_number)','gt',0)
            ->order('q.sortable asc,q.id asc')
            ->find();
    }



}