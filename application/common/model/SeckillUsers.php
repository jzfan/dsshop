<?php

namespace app\common\model;

use think\Model;
use app\common\ModelTrait;

class SeckillUsers extends Model
{
    use ModelTrait;

    public function buyer()
    {
        return $this->belongsTo(Member::class, 'member_id', 'member_id');
    }

    public function checkLimit($num)
    {
        return $this->limit < $num
    }

}
