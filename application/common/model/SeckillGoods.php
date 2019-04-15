<?php

namespace app\common\model;

use think\Model;
use think\Db;

class SeckillGoods extends Model
{
    protected $table;

    public function __construct()
    {
        $this->table = db('seckill_goods');
    }

    public function add()
    {
        return $this->fetch();
    }


}