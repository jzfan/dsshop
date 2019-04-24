<?php

namespace app\script\controller;

use app\common\DsRedis;
use app\common\model\Order;
use think\Controller;

class Orderlistener extends Controller
{
    // 2分钟订单超时
    const EXPIRE_SECONDS = 120;

    protected $redis;

    protected function _initialize()
    {
        $this->redis = DsRedis::getInstance();
    }

    // public function listen()
    // {
    //     $this->redis->psubscribe(['__keyevent@0__:expired'], [$this, 'callBack']);
    // }

    public function handle()
    {
        foreach ($this->getExpiredOrders() as $order) {
            $order->cancel();
        }
        echo '订单被取消';
        exit;
    }

    public function callBack($redis, $pattern, $chan, $msg)
    {
        echo "Pattern: $pattern\n";
        echo "Channel: $chan\n";
        echo "Payload: $msg\n\n";
    }

    protected function getExpiredOrders()
    {
        return Order::where('order_state', 10)
            ->where('add_time', '<', time() - self::EXPIRE_SECONDS)
            ->where(function ($q) {
                $q->where('order_type', 40)
                    ->whereOr('order_type', 41);
            })->select();
    }

}
