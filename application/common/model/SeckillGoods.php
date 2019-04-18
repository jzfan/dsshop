<?php

namespace app\common\model;

use app\common\ModelTrait;
use think\Db;
use think\Model;

class SeckillGoods extends Model
{
    use ModelTrait;

    public function sku()
    {
        return $this->belongsTo(Goods::class, 'goods_id', 'goods_id');
    }

    public function info()
    {
        return $this->belongsTo(Goods::class, 'goods_id', 'goods_id')->field('goods_id,goods_commonid,goods_name,goods_image');
    }

    public function byId($id)
    {
        return $this->with('info')->find($id);
    }

    public function commonId()
    {
        return $this->sku->goods_commonid;
    }

    public function images()
    {
        return db('goodsimages')->where('goods_commonid', $this->commonId())->order('goodsimage_isdefault,goodsimage_sort')->column('goodsimage_url');
    }

    public function format()
    {
        return [
            'id' => $this->id,
            'name' => $this->info->goods_name,
            'images' => join(',', $this->images()),
            'price' => $this->price,
            'mi' => $this->mi,
            'qty' => $this->qty,
            'sold' => $this->sold,
        ];
    }

}
