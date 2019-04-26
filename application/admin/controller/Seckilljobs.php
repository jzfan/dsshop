<?php

namespace app\admin\controller;

class Seckilljobs extends AdminControl
{
    public function getModel()
    {
        return model('SeckillJobs');
    }

    public function index()
    {
        $jobs = $this->model->with('goods')->order('id desc')->select();
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
            $job = $this->model->create($data);
            session('job_id', $job->id);
            session('job_name', $job->name);
        }
        $this->assignGoods();
        return $this->fetch('seckill/add_job_goods');
    }

    public function goods()
    {
        $this->assignGoods();
        return $this->fetch('seckill/add_job_goods');

    }

    public function delete()
    {
        $data = checkInput([
            'id' => 'require|number'
        ]);
        $this->model->find($data['id'])->delete();
        return $this->index();
    }

    public function stop()
    {
        $data = checkInput([
            'id' => 'require|number'
        ]);
        $this->model->find($data['id'])->stop();
        return $this->index();
    }

    public function show()
    {
        $data = checkInput([
            'id' => 'require|number'
        ]);
        $job = $this->model->with('goods.info')->find($data['id']);
        $this->assign('job', $job);
        return $this->fetch('seckill/show_job');
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
