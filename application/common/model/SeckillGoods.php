<?php

namespace app\common\model;

use think\Model;
use app\common\ModelTrait;

class SeckillGoods extends Model
{
    use ModelTrait;

    public $page_info;

    public function sku()
    {
        return $this->belongsTo(Goods::class, 'goods_id', 'goods_id');
    }

    public function info()
    {
        return $this->belongsTo(Goods::class, 'goods_id', 'goods_id')->field('goods_id,goods_commonid,goods_name,goods_image');
    }

    public function limit()
    {
        return \Cache::remember("seckill_{$this->job_id}_good_{$this->id}_limit", function () {
            return $this->limit;
        });
    }

    public function byId($id)
    {
        return $this->with('info')->find($id);
    }

    public function checkLimit($log, $num)
    {
        if(!$log) {
            if ($num > $this->limit) {
                throw new JsonException("购买商品数量超过限制", 422);
            }
            return true;
        }
        if ($num + $log->purchased > $this->limit) {
            throw new JsonException("购买商品数量超过限制", 422);
        }
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
    	$images = $this->images();
        return [
            'id' => $this->id,
            'name' => $this->info->goods_name,
            'default_image' => array_shift($images),
            'images' => join(',', $images),
            'price' => $this->price,
            'mi' => $this->mi,
            'qty' => $this->qty,
            'sold' => $this->sold,
        ];
    }

    public function getGoodsInfoAndPromotionById($goods_id)
    {
        $goods = db('goods')->where("goods_id",$goods_id)->find();
        $seckill_good = db('seckill_goods')->where("goods_id",$goods_id)->find();

        if ($seckill_good) {
            return arary_merge($seckill_good->toArray(),[
                'goods_type' => 40
            ]);
        }

        return array();
    }

}
