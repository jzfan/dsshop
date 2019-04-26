<?php
/**
 * 秒米商品控制器
 */

namespace app\admin\controller\seckill;

use think\Db;
use app\common\Formula;
use app\common\JsonException;
use app\admin\controller\AdminControl;

class Goods extends AdminControl
{
    public function getModel()
    {
        return model('SeckillGoods');
    }

    public function index()
    {
        $this->setAdminCurItem('index');
        $goods = $this->model->with('info')->select();
        $this->assign('goods', $goods);
        return $this->fetch('seckill/goods');
    }

    public function store()
    {
        $data = checkInput([
            'goods_id' => 'require|number|gt:0',
            'job_id' => 'require|number|gt:0',
            'qty|数量' => 'require|number|gt:0',
            'price|价格' => 'require|number|gt:0',
            // 'mi' => 'require|number',
            'commend' => 'require|number',
            'return_rate|收益率' => 'require|number|gt:0'
        ]);
        $data['mi'] = Formula::miByInput($data);
        return Db::transaction(function () use ($data){
            $skuGood = model('goods')->where('goods_id', $data['goods_id'])->lock(true)->find();
            if (empty($skuGood)) {
                throw new JsonException("商品ID错误", 422);
            }

            $seckillGood = $this->model->byGoodsId($data['goods_id']);

            $this->checkStorage($skuGood, $seckillGood, $data['qty']);

            $skuGood->save();
            return $this->model->create($data);
        });
    }

    protected function checkStorage($skuGood, $seckillGood, $qty)
    {
        if (empty($seckillGood)) {
            if ($qty > $skuGood->goods_storage) {
                throw new JsonException("商品数量不足", 422);
            }
            return $skuGood->goods_storage -= $qty;
        }
        $storage = (int)$skuGood->goods_storage + (int)$seckillGood->qty;
        if ($storage < $qty) {
            throw new JsonException("商品数量不足", 422);
        }
        return $skuGood->goods_storage = $storage - $qty;
    }
}