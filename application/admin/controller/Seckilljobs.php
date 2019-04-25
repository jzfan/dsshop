<?php

namespace app\admin\controller;

class Seckilljobs extends AdminControl
{
    public function index()
    {
        $jobs = model('SeckillJobs')->with('goods')->order('id desc')->select();
        // dd($jobs[0]->toArray());
        $this->assign('jobs', $jobs);
        $this->setAdminCurItem('index');
        return $this->fetch('seckill/jobs');
    }

    public function form()
    {
        $this->assign('goods', model('SeckillGoods')->with('info')->order('id desc')->select());
        return $this->fetch('seckill/job_form');
    }

    public function store()
    {
        if (request()->isPost()) {
            $data = checkInput([
                'start' => 'require|date|after:' . date('Y-m-d'),
                'end' => 'require|date|after:' . date('Y-m-d H:i:s'),
                'name' => 'require',
            ]);
            $job = model('SeckillJobs')->create($data);
            session('job_id', $job->id);
            session('job_name', $job->name);
        }
        $this->assignGoods();
        // $this->redirect('/admin/seckilljobs/goods');

        return $this->fetch('seckill/add_job_goods');
    }

    public function goods()
    {
        $this->assignGoods();
        return $this->fetch('seckill/add_job_goods');

    }

    protected function assignGoods()
    {
        $this->assign('goods', $this->getGoodsByInput());
    }

    protected function getGoodsByInput()
    {
        if ($id = input('goods_commonid')) {
            return db('goods')->where('goods_commonid', $id)->paginate();
        }
        if ($name = input('goods_name')) {
            return db('goods')->where('goods_name', 'like', "%{$name}%")->paginate();
        }
        return db('goods')->paginate();
    }

    protected function getAdminItemList()
    {
        return [
            [
                'name' => 'index',
                'text' => lang('秒杀'),
                'url' => url('Seckilljobs/index'),
            ],
        ];
    }
}
