<?php

namespace app\mobile\controller;

use think\Lang;

class Memberpointorder extends MobileMember {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'mobile/lang/'.config('default_lang').'/memberpointorder.lang.php');

        //判断系统是否开启积分和积分兑换功能
        if (config('points_isuse') != 1 || config('pointprod_isuse') != 1) {
            ds_json_encode(10001,'未开启积分兑换功能');
        }
    }

    public function index() {
        $this->orderlist();
    }

    /**
     * 兑换信息列表
     */
    public function orderlist() {
        //兑换信息列表
        $where = array();
        $where['point_buyerid'] = $this->member_info['member_id'];

        $pointorder_model = model('pointorder');
        $order_list = $pointorder_model->getPointorderList($where, '*', 10, 0, 'point_orderid desc');
        $order_idarr = array();
        $order_listnew = array();
        if (is_array($order_list) && count($order_list) > 0) {
            foreach ($order_list as $k => $v) {
                $v['state_desc'] = '';
                switch ($v['point_orderstate']) {
                    case '20';
                        $v['state_desc'] = '待发货';
                        break;
                    case '30';
                        $v['state_desc'] = '已发货';
                        break;
                    case '40';
                        $v['state_desc'] = '已收货';
                        break;
                    case '50';
                        $v['state_desc'] = '已完成';
                        break;
                    case '2';
                        $v['state_desc'] = '已取消';
                        break;
                }
                $order_listnew[$v['point_orderid']] = $v;
                $order_idarr[] = $v['point_orderid'];
            }
        }
        $order_listnew1 = array();
        //查询兑换商品
        if (is_array($order_idarr) && count($order_idarr) > 0) {
            $prod_list = $pointorder_model->getPointordergoodsList(array('pointog_orderid' => array('in', $order_idarr)));
            if (is_array($prod_list) && count($prod_list) > 0) {
                foreach ($prod_list as $v) {
                    if (isset($order_listnew[$v['pointog_orderid']])) {
                        $order_listnew[$v['pointog_orderid']]['prodlist'][] = $v;
                    }
                }

                foreach ($order_listnew as $k => $v) {
                    $order_listnew1[] = $v;
                }
            }
        }
        ds_json_encode(10000,'',array('order_list' => $order_listnew1));

    }

    /**
     * 	取消兑换
     */
    public function cancel_order() {
        $pointorder_model = model('pointorder');
        //取消订单
        $data = $pointorder_model->cancelPointorder(input('post.order_id'), $this->member_info['member_id']);
        if ($data['state']) {
            ds_json_encode(10000,'',1);
        } else {
            ds_json_encode(10001,'取消失败');
        }
    }

    /**
     * 确认收货
     */
    public function receiving_order() {
        $data = model('pointorder')->receivingPointorder(input('post.order_id'));
        if ($data['state']) {
            ds_json_encode(10000,'',1);
        } else {
            ds_json_encode(10001,'处理失败');
        }
    }

    /**
     * 兑换信息详细
     */
    public function order_info() {
        $order_id = intval(input('get.order_id'));
        if ($order_id <= 0) {
            ds_json_encode(10001,'订单不正确');
        }
        $pointorder_model = model('pointorder');
        //查询兑换订单信息
        $where = array();
        $where['point_orderid'] = $order_id;
        $where['point_buyerid'] = $this->member_info['member_id'];
        $order_info = $pointorder_model->getPointorderInfo($where);
        if (!$order_info) {
            ds_json_encode(10001,'订单不存在');
        }
        if ($order_info['point_addtime']) {
            $order_info['point_addtime'] = date('Y-m-d H:i:s', $order_info['point_addtime']);
        } else {
            $order_info['point_addtime'] = '';
        }
        if ($order_info['point_shippingtime']) {
            $order_info['point_shippingtime'] = date('Y-m-d H:i:s', $order_info['point_shippingtime']);
        } else {
            $order_info['point_shippingtime'] = '';
        }
        if ($order_info['point_finnshedtime']) {
            $order_info['point_finnshedtime'] = date('Y-m-d H:i:s', $order_info['point_finnshedtime']);
        } else {
            $order_info['point_finnshedtime'] = '';
        }
        //获取订单状态
        $pointorderstate_arr = $pointorder_model->getPointorderStateBySign();

        //查询兑换订单收货人地址
        $orderaddress_info = $pointorder_model->getPointorderAddressInfo(array('pointoa_orderid' => $order_id));

        //兑换商品信息
        $prod_list = $pointorder_model->getPointordergoodsList(array('pointog_orderid' => $order_id));

        $express_info = '';
        //物流公司信息
        if ($order_info['point_shipping_ecode'] != '') {
            $data = model('express')->getExpressInfoByECode($order_info['point_shipping_ecode']);
            if ($data['state']) {
                $express_info = $data['data']['express_info'];
            }
        }
        ds_json_encode(10000, '', array('order_info' => $order_info, 'express_info' => $express_info, 'prod_list' => $prod_list, 'orderaddress_info' => $orderaddress_info, 'pointorderstate_arr' => $pointorderstate_arr));
    }

}

?>
