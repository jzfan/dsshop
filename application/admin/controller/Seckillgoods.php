<?php
/**
 * 秒米商品控制器
 */

namespace app\admin\controller;

use think\Db;
use app\common\JsonException;
use app\common\model\SeckillGoods;
use app\admin\controller\AdminControl;

class Seckillgoods extends AdminControl
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

    // public function add()
    // {
    //     $categories = db('goodsclass')->where('gc_parent_id', 0)->select();
    //     return $this->fetch('seckill/add_good', compact('categories'));
    // }

    public function store()
    {
        $data = checkInput([
            'goods_id' => 'require|number',
            'job_id' => 'require|number',
            'qty' => 'require|number',
            'price' => 'require|number',
            'mi' => 'require|number',
            'commend' => 'require|number',
        ]);
        return Db::transaction(function () use ($data){
            $skuGood = model('goods')->where('goods_id', $data['goods_id'])->lock(true)->find();
            if (empty($skuGood)) {
                throw new JsonException("商品ID错误", 422);
            }

            $seckillGood = $this->model->where('goods_id', $data['goods_id'])->find();

            $this->checkStorage($skuGood, $seckillGood, $data['qty']);

            $skuGood->save();
            SeckillGoods::updateOrCreate(['goods_id' => $data['goods_id']], $data);
            return $skuGood->goods_storage;
        });
    }

    // public function store()
    // {
    //     $data = checkInput([
    //         'goods_id' => 'require|number',
    //         'qty' => 'require|number',
    //         'mi' => 'require|number',
    //         'price' => 'require|number',
    //     ]);
    //     $item = db('seckill_goods')->where('goods_id', $data['goods_id'])->find();
    //     if (!empty($item)) {
    //         db('seckill_goods')->where('goods_id', $data['goods_id'])
    //                         ->update($data);
    //         return redirect()->back();
    //     }
    //     db('seckill_goods')->insert($data);
    //     return $this->success('success');
    // }

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