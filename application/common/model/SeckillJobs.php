<?php

namespace app\common\model;

use app\common\ModelTrait;
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
            $this->status = 2;
            $this->save();
            return true;
        }
        return false;
    }

    public function isWaiting()
    {
        if (strtotime($this->start) > time() + 60) {
        	return true;
        }
        return false;
    }

    public function isActive()
    {
    	$diff = time() - strtotime($this->start);
    	if ($diff <= 60 && $diff > 0) {
    		$this->status = 1;
    		$this->save();
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
            $good->push($this->id);
        }
    }

    public function duration($now=true)
    {
        $start = $now ? time() : strtotime($this->start);
        return strtotime($this->end) - $start;
    }
}
