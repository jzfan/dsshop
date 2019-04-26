<?php

namespace app\common\model;

use app\common\ModelTrait;
use think\Db;
use think\Model;

class SeckillJobs extends Model
{
    use ModelTrait;

    public function goods()
    {
        return $this->hasMany(SeckillGoods::class, 'job_id');
    }

    public function scopeUnCompleted($query)
    {
        $query->where('status', 0)->whereOr('status', 1)->order('start');
    }

    public function active()
    {
        return $this->where('status', 1)->find();
    }

    public function isOver()
    {
        if (strtotime($this->end) < time()) {
            if ($this->status != 2) {
                $this->setStatus(2);
            }
            return true;
        }
        return false;
    }

    public function isWaiting()
    {
        if (strtotime($this->start) > time()) {
            if ($this->status != 2) {
                $this->setStatus(2);
            }
            return true;
        }
        return false;
    }

    public function isActive()
    {
        if (time() - strtotime($this->start) <= 60 &&
            time() - strtotime($this->start) >= 0) {
            if ($this->status == 0) {
                $this->setStatus(1);
            }
            return true;
        }
        return false;
    }

    public function formatGoods()
    {
        $job_goods = $this->goods()->with('info')->order('commend desc')->select();
        return array_map(function ($good) {
            return $good->format();
        }, $job_goods);
    }

    public function pushGoods()
    {
        foreach ($this->goods as $good) {
            $good->push();
        }
    }

    public function duration($now = true)
    {
        $start = $now ? time() : strtotime($this->start);
        return strtotime($this->end) - $start;
    }

    public function stop()
    {
        Db::transaction(function () {
            $this->status = 3;
            $this->save();
            foreach ($this->goods as $good) {
                $good->offShelve();
            }
        });
    }

    protected function setStatus($n)
    {
        $this->status = $n;
        return $this->save();
    }
}
