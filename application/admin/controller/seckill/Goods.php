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
            'price|秒杀价格' => 'require|number|gt:0',
            'goods_price|代售价格' => 'require|number|gt:0',
            'commend' => 'require|number',
            'return_rate|收益率' => 'require|number|gt:0'
        ]);
        $data['mi'] = Formula::miByInput($data);
        if ($data['mi'] <= 0) {
            throw new JsonException("秒米计算结果小于0", 422);
        }
        $good = Db::transaction(function () use ($data){
            $skuGood = model('goods')->where('goods_id', $data['goods_id'])->lock(true)->find();
            if (empty($skuGood)) {
                throw new JsonException("商品ID错误", 422);
            }
            if ($data['qty'] > $skuGood->goods_storage) {
                throw new JsonException("商品数量不足", 422);
            }
            if ($this->model->where('goods_id', $data['goods_id'])
                        ->where('job_id', $data['job_id'])
                        ->count() != 0) {
                throw new JsonException("该商品已经添加", 422);
            }
            $skuGood->goods_storage -= $data['qty'];
            $skuGood->save();
            return $this->model->create($data);
        });
        return json($this->model->with('info')->find($good->id));
    }
}