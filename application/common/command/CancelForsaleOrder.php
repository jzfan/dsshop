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

class CancelForsaleOrder extends Command
{

    public function configure()
    {
        $this->setName('cancel:forsaleorder')->setDescription('取消挂售商品订单');
    }


    public function execute(Input $input, Output $output)
    {
        //取91商城订单默认取消时间
        $config = model('common/config')->getOneConfigByCode('forsale_order_auto_cancel_time');
        $forsale_order_auto_cancel_time = intval($config['value']);
        $time = time() - $forsale_order_auto_cancel_time * 60;
        Db::startTrans();
        try {
            Db::name('order')->where('order_state', 10)->where('order_type', 30)->chunk(100, function ($orders) use ($time) {
                foreach ($orders as $order) {
                    if ($order['add_time'] <= $time) {
                        // 第一步：更新订单状态为已取消
                        Db::name('order')->where('order_id', $order['order_id'])->update(['order_state' => 0]);

                        // 第二步：回滚91商品库存
                        $goods_list = Db::name('ordergoods')->where('order_id', $order['order_id'])->select();
                        foreach ($goods_list as $goods) {
                            $forsalegoods_data = [
                                'goods_storage' => Db::raw('goods_storage+' . $goods['goods_num']),
                                'sale_number' => Db::raw('sale_number-' . $goods['goods_num']),
                            ];
                            Db::name('forsalegoods')->where('goods_id', $goods['goods_id'])->update($forsalegoods_data);
                        }

                        // 第三步：解冻挂售订单库存
                        $forsaleorder_list = Db::name('memberforsaleorder')->where('order_id', $order['order_id'])->select();
                        foreach ($forsaleorder_list as $forsaleorder) {
                            $update_data = [
                                'freeze_number' => Db::raw('freeze_number-' . $forsaleorder['goods_number']),
                            ];
                            Db::name('memberforsalegoods')->where('goods_id', $forsaleorder['goods_id'])
                                ->where('member_id',$forsaleorder['member_id'])->update($update_data);
                        }
                    }
                }
            });
            Db::commit();
        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage() . $exception->getLine() . $exception->getFile());
            Db::rollback();
        }
    }

}