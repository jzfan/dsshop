<?php
/**
 * 秒米商品控制器
 */

namespace app\admin\controller;

class Seckillgoods extends AdminControl
{

    public function index()
    {
        $this->setAdminCurItem('index');
        $goods = db('seckill_goods')->select();
        $this->assign('goods', $goods);
        return $this->fetch();
    }

    public function create() {

        $this->setAdminCurItem('create');

        // $goods_model = model('seckill_goods');
        $categories = model('goodsclass')->getGoodsclassListByParentId(0);
        // dd($categories);
        $this->assign(compact('categories'));

        return $this->fetch();
    }

    public function goods()
    {
        $this->setAdminCurItem('goods');
        $goods = db('seckill_goods')->select();
        $this->assign('goods', $goods);
        return $this->fetch();
    }
}