<?php

namespace app\admin\controller\seckill;

use think\Db;
use app\admin\controller\AdminControl;

class Jobs extends AdminControl
{
    public function getModel()
    {
        return model('SeckillJobs');
    }

    public function index($jobs=null)
    {
        $jobs = $jobs ?? $this->model->has('goods', '>', 0)->order('id desc')->select();
        $this->assign('jobs', $jobs);
        $this->setAdminCurItem('index');
        return $this->fetch('seckill/job/all');
    }

    public function search()
    {
        if (input('?status')) {
            $jobs = $this->model->where('status', input('status'))->order('id desc')->select();
            return $this->index($jobs);
        }
        if (input('?days')) {
            $jobs = $this->model->whereTime('start', input('days'))->order('id desc')->select();
            return $this->index($jobs);
        }
        return $this->index();
    }

    public function form()
    {
        $this->assign('goods', model('SeckillGoods')->with('info')->order('id desc')->select());
        return $this->fetch('seckill/job/form');
    }

    public function store()
    {
        session('error', null);
        if (request()->isPost()) {
            $result = $this->validate(input(), [
                'start' => 'require|date|after:' . date('Y-m-d'),
                'end' => 'require|date|after:' . date('Y-m-d H:i:s'),
                'name' => 'require',
            ]);
            if(true !== $result){
                session('error', $result);
                return $this->fetch('seckill/good/add');
            }
            $job = $this->model->create([
                'start' => input('start'),
                'end' => input('end'),
                'name' => input('name'),
            ]);
            session('job_id', $job->id);
            session('job_name', $job->name);
        }
        $this->assignGoods();
        return $this->fetch('seckill/good/add');
    }

    public function goods()
    {
        $this->assignGoods();
        return $this->fetch('seckill/good/add');

    }

    public function delete()
    {
        $data = checkInput([
            'id' => 'require|number'
        ]);
        Db::transaction(function () use ($data) {
            $this->model->lock(true)
                    ->find($data['id'])
                    ->deleteWithGoods();
        });
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
        // dd($job->goods);
        $this->assign('job', $job);
        return $this->fetch('seckill/job/show');
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
