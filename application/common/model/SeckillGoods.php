<?php

namespace app\common\model;

use app\common\DsRedis;
use app\common\ModelTrait;
use app\common\model\SeckillJobs;
use think\Db;
use think\Model;

class SeckillGoods extends Model
{
    use ModelTrait;

    protected $redis;

    public function initialize()
    {
        parent::initialize();
        $this->redis = DsRedis::getInstance();
    }

    public function job()
    {
        return $this->belongsTo(SeckillJobs::class, 'job_id', 'id');
    }

    public function sku()
    {
        return $this->belongsTo(Goods::class, 'goods_id', 'goods_id');
    }

    public function info()
    {
        return $this->belongsTo(Goods::class, 'goods_id', 'goods_id')->field('goods_id,goods_commonid,goods_name,goods_image,goods_advword');
    }

    public function limit()
    {
        return \think\Cache::remember("seckill:{$this->job_id}_good:{$this->id}_limit", function () {
            return $this->limit;
        });
    }

    public function byGoodsId($id)
    {
        return $this->with('info')->where('goods_id', $id)->find();
    }

    public function checkLimit($log, $num)
    {
        if (!$log) {
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

    // public function images()
    // {
    //     return db('goodsimages')->where('goods_commonid', $this->commonId())->order('goodsimage_isdefault,goodsimage_sort')->column('goodsimage_url');
    // }

    public function format()
    {
        // $images = empty($this->images()) ? [$this->info->goods_image] : $this->images();
        return [
            'id' => $this->id,
            'goods_id' => $this->goods_id,
            'name' => $this->info->goods_name,
            'goods_advword' => $this->info->goods_advword,
            'image' => $this->info->goods_image,
            'price' => $this->price,
            'mi' => $this->mi,
            'qty' => $this->qty,
            'sold' => $this->sold,
            'goods_type' => 40,
        ];
    }

    public function push($n = null)
    {
        $n = $n ?? $this->qty;
        foreach (range(1, $n) as $i) {
            $this->redis->lpush($this->listKey(), $i);
        }
        return $this;
    }

    public function pop($n)
    {
        for ($n; $n--;) {
            $this->redis->rpop($this->listKey());
        }
    }

    public function offShelve()
    {
        $this->delete($this->listKey());
    }

    public function getGoodsInfoAndPromotionById($goods_id)
    {
        // $goods = db('goods')->where("goods_id",$goods_id)->find();
        $seckill_good = self::where("goods_id", $goods_id)->find();
        $sku = $seckill_good->sku;
        if ($seckill_good) {
            return array_merge($seckill_good->toArray(), [
                'goods_type' => 40,
                'is_goodsfcode' => 0,
                'goods_commonid' => $sku->goods_commonid,
                'gc_id' => $sku->gc_id,
                'goods_name' => $sku->goods_name,
                'goods_price' => $seckill_good->price,
                'mi' => $seckill_good->mi,
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

    public function incrMemberLimit($num, $member_id)
    {
        $key = $this->limitKey($member_id);
        $ex = $this->job->duration();

        $this->redis->incr($key, $num);
        $this->redis->setTimeout($key, $ex);
        return $this;
    }

    /**
     * 判断用户是否达到秒杀商品购买限额
     * @param int $member_id 购买者ID
     * @return bool
     */
    public function isMemberOverLimit($member_id)
    {
        $purchased = $this->redis->get($this->limitKey($member_id));
        if (!$purchased) {
            return false;
        }
        return $purchased >= $this->limit();
    }

    protected function limitKey($member_id)
    {
        return "seckill:{$this->job_id}_goods:{$this->goods_id}_member:{$member_id}";
    }

    protected function listKey()
    {
        return "seckill:{$this->job_id}:good:{$this->goods_id}";
    }

    protected function lockKey()
    {
        return "lock_job:{$this->job_id}_good:{$this->goods_id}";
    }

    /**
     * 上锁出队，如果库存不足抛出异常
     */
    public function lockForPop($num)
    {
        $key = $this->lockKey();

        $this->redis->setLock($key);

        if (!$this->isQtyEnough($num)) {
            $this->redis->releaseLock($key);
            exception("秒杀商品数量不足");
        }

        $this->pop($num);
        $this->redis->releaseLock($key);
        return $this;
    }

    /**
     * 判断库存是否足够
     */
    protected function isQtyEnough($num)
    {
        return $this->redis->llen($this->listKey()) >= $num;
    }

    // 订单取消，超时后，增加库存，减少销量
    public function unSold($num)
    {
        Db::transaction(function () use ($num) {
            $this->lock(true)->find();
            $this->qty += $num;
            $this->sold -= $num;
            $this->save();
        });
        return $this;
    }

    // 下单后减少库存，增加销量
    public function sold($num)
    {
        Db::transaction(function () use ($num) {
            $this->lock(true)->find();
            $this->qty -= $num;
            $this->sold += $num;
            $this->save();
        });
        return $this;
    }
    /**
     * 查询出售中的商品详细信息及其促销信息
     * @access public
     * @author csdeshang
     * @param int $goods_id 商品ID
     * @return array
     */
    public function getGoodsOnlineInfoAndPromotionById($goods_id)
    {
        return $this->getGoodsInfoAndPromotionById($goods_id);
    }

    /**
     * 取得商品最新的属性及促销[立即购买]
     * @param type $goods_id
     * @param type $quantity
     * @param type $extra
     * @return array
     */
    public function getGoodsOnlineInfo($goods_id, $quantity, $extra = array())
    {
        return $this->getGoodsInfoAndPromotionById($goods_id);
    }

}
