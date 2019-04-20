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

    public function byGoodsId($id)
    {
        return $this->with('info')->where('goods_id', $id)->find();
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
        // $goods = db('goods')->where("goods_id",$goods_id)->find();
        $seckill_good = model('seckillgoods')->where("goods_id",$goods_id)->find();
        $sku = $seckill_good->sku;
        if ($seckill_good) {
            return array_merge($seckill_good->toArray(),[
                'goods_type' => 40,
                'is_goodsfcode' => 1,
                'goods_commonid' => $sku->goods_commonid,
                'gc_id' => $sku->gc_id,
                'goods_name' => $sku->goods_name,
                'goods_price' => $seckill_good->price,
                'goods_image' => $sku->goods_image,
                'transport_id' => $sku->transport_id,
                'goods_freight' => $sku->goods_freight,
                'goods_vat' => $sku->goods_vat,
                'goods_storage' => $seckill_good->qty,
                'goods_storage_alarm' => 0,
                'is_have_gift' => 0,
                
            ]);
        }

        return array();
    }

    /**
     * 查询出售中的商品详细信息及其促销信息
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品ID
     * @return array
     */
    public function getGoodsOnlineInfoAndPromotionById($goods_id) {
        return $this->getGoodsInfoAndPromotionById($goods_id);
    }

    /**
     * 取得商品最新的属性及促销[立即购买]
     * @param type $goods_id
     * @param type $quantity
     * @param type $extra  
     * @return array
     */
    public function getGoodsOnlineInfo($goods_id, $quantity, $extra = array()) {
        return $this->getGoodsInfoAndPromotionById($goods_id);
    }

}
