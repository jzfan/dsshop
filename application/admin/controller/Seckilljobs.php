<?php

namespace app\admin\controller;

class Seckilljobs extends AdminControl
{
    public function index()
    {
        $jobs = db('seckill_jobs')->order('id desc')->select();
        return $this->fetch('seckill/jobs');
    }

    public function add()
    {
        return $this->fetch('seckill/add_job');
    }
}
