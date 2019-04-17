<?php

namespace app\admin\controller;

class Seckilljobs extends AdminControl
{
    public function index()
    {
        $jobs = model('SeckillJobs')->order('id desc')->select();
        $this->assign('jobs', $jobs);
        return $this->fetch('seckill/jobs');
    }

    public function add()
    {
        $this->assign('goods', model('SeckillGoods')->with('info')->order('id desc')->select());
        return $this->fetch('seckill/add_job');
    }

    public function store()
    {
        $data = checkInput([
            'start' => 'require|date|after:' . date('Y-m-d'),
            'end' => 'require|date|after:' . date('Y-m-d H:i:s'),
            'name' => 'require'
        ]);

        $job = model('SeckillJobs')->create($data);
        $this->assign('job_id', $job->id);

        return $this->fetch('seckill/add_job_goods');
    }

    public function searchGoods()
    {
        if ($goods = $this->getGoodsByInput()) {
            $this->assign('goods', $goods);
        }
        $this->assign('job_id', input('job_id'));

        return $this->fetch('seckill/add_job_goods');
        
    }

    protected function getGoodsByInput()
    {
        if ($id = input('goods_commonid')) {
            return db('goods')->where('goods_commonid', $id)->select();
        }
        if ($name = input('goods_name')) {
            return db('goods')->where('goods_name', 'like', "%{$name}%")->select();
        }
    }
}
