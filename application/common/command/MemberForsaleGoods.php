<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-26
 * Time: 14:37
 */

namespace app\common\command;


use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class MemberForsaleGoods extends Command
{

    public function configure()
    {
        $this->setName('forsale:membergoods')->setDescription('自动挂售会员商品');
    }


    public function execute(Input $input, Output $output)
    {
        //秒杀订单自动挂售
        $config = model('common/config')->getOneConfigByCode('secondkill_order_auto_forsale');
        $secondkill_order_auto_forsale = intval($config['value']);
        $time = time() - $secondkill_order_auto_forsale * 60;

        Db::startTrans();
        try {
            Db::name('memberforsalegoods')->where('goods_state', 0)->chunk(100, function ($goods_list) use ($time) {
                foreach ($goods_list as $goods) {
                    if ($goods['created_time'] <= $time) {
                        // 第一步：更新商品为挂售中
                        Db::name('memberforsalegoods')->where('id', $goods['id'])->update(['goods_state' => 1]);

                        // 第二步：增加商品库存
                        Db::name('forsalegoods')->where('goods_id', $goods['goods_id'])->setInc('goods_storage',$goods['goods_number']);
                    }
                }
            });
            Db::commit();
        } catch (\Exception $exception) {
            Db::rollback();
        }
    }
}