<?php
/**
 * 营销设置
 */

namespace app\admin\controller;


use think\Lang;
class Seckill extends AdminControl
{

    public function index()
    {
        $this->setAdminCurItem('index');
        $list = db('seckill_jobs')->select();
        $this->assign('seckill_list', $list);
        return $this->fetch();
    }

    public function create()
    {
        $this->setAdminCurItem('create');
        
        return $this->fetch();
    }

    public function goods()
    {
        $this->setAdminCurItem('goods');
        $list = db('seckill_goods')->select();
        $this->assign('seckill_list', $list);
        return $this->fetch();
    }
}