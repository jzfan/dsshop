<?php

namespace app\common\model;

use think\Db;
use think\Model;
use app\common\ModelTrait;

class SeckillGoods extends Model
{
	use ModelTrait;

	public function sku()
	{
		return $this->belongsTo(Goods::class, 'goods_id', 'goods_id');	
	}

	public function info()
	{
		return $this->belongsTo(Goods::class, 'goods_id', 'goods_id')->field('goods_id,goods_name,goods_image');
	}

	public function byId($id)
	{
		return $this->with('info')->find($id)->format();
	}

	public function format()
	{
		return [
	        'id' => $this->id,
	        'name' => $this->info->goods_name,
	        'image' => $this->info->goods_image,
	        'qty' => $this->qty,
	        'sold' => $this->sold
	    ];
	}

}