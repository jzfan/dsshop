<?php
/**
 * 秒米商品控制器
 */

namespace app\admin\controller;

use app\admin\controller\AdminControl;

class Seckillgoods extends AdminControl
{

    public function index()
    {
        $this->setAdminCurItem('index');
        $goods = db('seckill_goods')->select();
        $this->assign('goods', $goods);
        return $this->fetch('seckill/goods');
    }

    public function add()
    {
        $this->setAdminCurItem('index');
        $categories = db('goodsclass')->where('gc_parent_id', 0)->select();
        // $goods = db('seckill_goods')->select();
        // $this->assign('goods', $goods);
        return $this->fetch('seckill/add_good', compact('categories'));
    }

    // public function create() {

    //     $this->setAdminCurItem('create');

    //     // $this->model = model('seckill_goods');
    //     $categories = model('goodsclass')->getGoodsclassListByParentId(0);
    //     // dd($categories);
    //     $this->assign(compact('categories'));

    //     return $this->fetch();
    // }

    // public function goods()
    // {
    //     $this->setAdminCurItem('goods');
    //     $goods = db('seckill_goods')->select();
    //     $this->assign('goods', $goods);
    //     return $this->fetch();
    // }
}