<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-25
 * Time: 19:42
 */

namespace app\common\command;


use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class CancelPointOrder extends Command
{

    public function configure()
    {

    }


    public function execute(Input $input, Output $output)
    {
        //取积分商城订单默认取消时间
        $time = 0;
        Db::startTrans();
        try {
            Db::name('order')->where('order_state', 10)->where('order_type', 20)->chunk(100, function ($orders) use ($time) {
                foreach ($orders as $order) {
                    if ($order['add_time'] >= $time) {
                        // 第一步：更新订单状态为已取消
                        Db::name('order')->where('order_id', $order['order_id'])->save(['order_state' => 0]);

                        // 第二步：回滚积分商品库存
                        $goods_list = Db::name('order_goods')->where('order_id', $order['order_id'])->select();
                        foreach ($goods_list as $goods) {
                            Db::name('pointgoods')->where('goods_id', $goods['goods_id'])->setInc('goods_storage', $goods['goods_number']);
                        }
                    }
                }
            });
            Db::commit();
        } catch (\Exception $exception) {
            Db::rollback();
        }
    }
}