<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-15
 * Time: 14:09
 */

class pingan
{
    private $pingan_config = array();

    public function __construct($param = array()) {
        if (!empty($param)) {
            $this->pingan_config = array(
            );
        }
    }


    public function get_payform($order_info)
    {
        $order_trade_no = $order_info['pay_sn'];
        $trade_no = 1001;
        $order_type = $order_info['order_type'];
        $payment_code = 'pingan';

        header("Location:http://test.91gogogo.com/mobile/payment/notify/order_trade_no/$order_trade_no/trade_no/$trade_no/order_type/$order_type/payment_code/$payment_code");
        exit;
    }
}