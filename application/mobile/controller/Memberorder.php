<?php

namespace app\mobile\controller;

use think\Lang;

class Memberorder extends MobileMember
{

    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'mobile/lang/' . config('default_lang') . '/memberorder.lang.php');
    }

    /**
     * 订单列表
     */
    public function order_list()
    {
        $order_model = model('order');
        $condition = array();
        $state_type = input('post.state_type');
        if ($state_type == '') {
            $condition['order_state'] = '';
        } else {
            $condition = $this->order_type_no(input('post.state_type'));
        }
        $condition['buyer_id'] = $this->member_info['member_id'];
        $condition['delete_state'] = 0; #订单未被删除
        $order_sn = input('post.order_key');
        if ($order_sn != '') {
            $condition['order_sn'] = array('like', '%' . $order_sn . '%');
        }
        $order_list_array = $order_model->getOrderList($condition, 5, '*', 'order_id desc,add_time desc', '', array('order_common', 'order_goods'));
        $order_group_list = $order_pay_sn_array = array();
        foreach ($order_list_array as $k => $value) {
            //$value['zengpin_list'] = false;
            //获取订单类型名称
            $value['order_type_name'] = $this->get_type_name($value['order_type']);
            //显示取消订单
            $value['if_cancel'] = $order_model->getOrderOperateState('buyer_cancel', $value);
            //显示退款取消订单
            $order_info['if_refund_cancel'] = $order_model->getOrderOperateState('refund_cancel', $value);
            //显示收货
            $value['if_receive'] = $order_model->getOrderOperateState('receive', $value);
            //显示锁定中
            $value['if_lock'] = $order_model->getOrderOperateState('lock', $value);
            //显示物流跟踪
            $value['if_deliver'] = $order_model->getOrderOperateState('deliver', $value);
            $value['if_evaluation'] = $order_model->getOrderOperateState('evaluation', $value);
            $value['if_delete'] = $order_model->getOrderOperateState('delete', $value);
            //显示代售订单
            $order['sideline'] = $order_model->getOrderOperateState('sideline', $value);
            $value['zengpin_list'] = false;
            if (isset($value['extend_order_goods'])) {
                foreach ($value['extend_order_goods'] as $val) {
                    if ($val['goods_type'] == 5) {
                        $value['zengpin_list'][] = $val;
                    }
                }
            }

            //商品图
            if (isset($value['extend_order_goods'])) {
                foreach ($value['extend_order_goods'] as $k => $goods_info) {

                    if ($goods_info['goods_type'] == 5) {
                        unset($value['extend_order_goods'][$k]);
                    } else {
                        $value['extend_order_goods'][$k] = $goods_info;
                        $value['extend_order_goods'][$k]['goods_image_url'] = goods_cthumb($goods_info['goods_image'], 240);
                    }
                }
            }
            $order_group_list[$value['pay_sn']]['order_list'][] = $value;


            //如果有在线支付且未付款的订单则显示合并付款链接
            if ($value['order_state'] == ORDER_STATE_NEW) {
                if (!isset($order_group_list[$value['pay_sn']]['pay_amount'])) {
                    $order_group_list[$value['pay_sn']]['pay_amount'] = 0;
                }
                $order_group_list[$value['pay_sn']]['pay_amount'] += $value['order_amount'] - $value['rcb_amount'] - $value['pd_amount'];
            }
            $order_group_list[$value['pay_sn']]['add_time'] = $value['add_time'];

            //记录一下pay_sn，后面需要查询支付单表
            $order_pay_sn_array[] = $value['pay_sn'];
        }
        //print_r($order_list_array);die;
        $new_order_group_list = array();
        foreach ($order_group_list as $key => $value) {
            $value['pay_sn'] = strval($key);
            $new_order_group_list[] = $value;
        }
        $result = array_merge(array('order_group_list' => $new_order_group_list), mobile_page($order_model->page_info));
        ds_json_encode(10000, '', $result);
    }


    private function order_type_no($stage)
    {
        $condition = array();
        switch ($stage) {
            case 'state_new':
                $condition['order_state'] = '10';
                break;
            case 'state_pay':
                $condition['order_state'] = '20';
                break;
            case 'state_send':
                $condition['order_state'] = '30';
                break;
            case 'state_noeval':
                $condition['order_state'] = '40';
                break;
            case 'state_sideline':
                $condition['order_state'] = '50';
                break;
        }
        return $condition;
    }

    /**
     * 取消订单
     */
    public function order_cancel()
    {
        $order_model = model('order');
        $logic_order = model('order', 'logic');
        $order_id = intval(input('post.order_id'));

        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        //$condition['order_type'] = 1;
        $order_info = $order_model->getOrderInfo($condition);
        $if_allow = $order_model->getOrderOperateState('buyer_cancel', $order_info);
        if (!$if_allow) {
            ds_json_encode(10001, '无权操作');
        }
        if (TIMESTAMP - 86400 < $order_info['add_time']) {
            $_hour = ceil(($order_info['add_time'] + 86400 - TIMESTAMP) / 3600);
            //ds_json_encode(10001, '该订单曾尝试使用第三方支付平台支付，须在' . $_hour . '小时以后才可取消');
        }
        $result = $logic_order->changeOrderStateCancel($order_info, 'buyer', $this->member_info['member_name'], '其它原因');
        if (!$result['code']) {
            ds_json_encode(10001, $result['msg']);
        } else {
            ds_json_encode(10000, '', 1);
        }
    }

    /**
     * 订单确认收货
     */
    public function order_receive()
    {
        $order_model = model('order');
        $logic_order = model('order', 'logic');
        $order_id = intval(input('post.order_id'));

        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_info = $order_model->getOrderInfo($condition);
        $if_allow = $order_model->getOrderOperateState('receive', $order_info);
        if (!$if_allow) {
            ds_json_encode(10001, '无权操作');
        }

        $result = $logic_order->changeOrderStateReceive($order_info, 'buyer', $this->member_info['member_name'], '签收了货物');
        if (!$result['code']) {
            ds_json_encode(10001, $result['msg']);
        } else {
            ds_json_encode(10000, '', 1);
        }
    }

    /**
     * 回收站
     */
    public function order_delete()
    {
        $order_model = model('order');
        $logic_order = model('order', 'logic');
        $order_id = intval(input('post.order_id'));

        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_info = $order_model->getOrderInfo($condition);
        $if_allow = $order_model->getOrderOperateState('delete', $order_info);
        if (!$if_allow) {
            ds_json_encode(10001, '无权操作');
        }

        $result = $logic_order->changeOrderStateRecycle($order_info, 'buyer', 'delete');
        if (!$result['code']) {
            ds_json_encode(10001, $result['msg']);
        } else {
            ds_json_encode(10000, '', 1);
        }
    }

    /**
     * 物流跟踪
     */
    public function search_deliver()
    {
        $order_id = intval(input('post.order_id'));
        if ($order_id <= 0) {
            ds_json_encode(10001, '订单不存在');
        }

        $order_model = model('order');
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_info = $order_model->getOrderInfo($condition, array('order_common', 'order_goods'));
        //print_r($order_info);die;
        if (empty($order_info) || !in_array($order_info['order_state'], array(ORDER_STATE_SEND, ORDER_STATE_SUCCESS))) {
            ds_json_encode(10001, '订单不存在');
        }

        $express = rkcache('express', true);
        $express_code = $express[$order_info['extend_order_common']['shipping_express_id']]['express_code'];
        $express_name = $express[$order_info['extend_order_common']['shipping_express_id']]['express_name'];

        $deliver_info = $this->_get_express($express_code, $order_info['shipping_code']);
        ds_json_encode(10000, '', array('express_name' => $express_name, 'shipping_code' => $order_info['shipping_code'], 'deliver_info' => $deliver_info));
    }

    /**
     * 订单详情
     */
    public function order_info()
    {
        $order_id = intval(input('order_id'));
        if ($order_id <= 0) {
            ds_json_encode(10001, '订单不存在');
        }
        $order_model = model('order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_info = $order_model->getOrderInfo($condition, array('order_goods', 'order_common'));
        if (empty($order_info) || $order_info['delete_state'] == ORDER_DEL_STATE_DROP) {
            ds_json_encode(10001, '订单不存在');
        }

        $refundreturn_model = model('refundreturn');
        $order_list = array();
        $order_list[$order_id] = $order_info;
        $order_list = $refundreturn_model->getGoodsRefundList($order_list, 1); //订单商品的退款退货显示
        $order_info = $order_list[$order_id];
        $refund_all = isset($order_info['refund_list'][0]) ? $order_info['refund_list'][0] : '';
        if (!empty($refund_all)) {//订单全部退款商家审核状态:1为待审核,2为同意,3为不同意
            $result['refund_all'] = $refund_all;
        }

        if ($order_info['payment_time']) {
            $order_info['payment_time'] = date('Y-m-d H:i:s', $order_info['payment_time']);
        } else {
            $order_info['payment_time'] = '';
        }
        if ($order_info['finnshed_time']) {
            $order_info['finnshed_time'] = date('Y-m-d H:i:s', $order_info['finnshed_time']);
        } else {
            $order_info['finnshed_time'] = '';
        }
        if ($order_info['add_time']) {
            $order_info['add_time'] = date('Y-m-d H:i:s', $order_info['add_time']);
        } else {
            $order_info['add_time'] = '';
        }

        if ($order_info['extend_order_common']['order_message']) {
            $order_info['order_message'] = $order_info['extend_order_common']['order_message'];
        }
//        if(!empty($order_info['extend_order_common']['invoice_info'])) {
//            $order_info['invoice'] = $order_info['extend_order_common']['invoice_info']['类型'] . $order_info['extend_order_common']['invoice_info']['抬头'] . $order_info['extend_order_common']['invoice_info']['内容'];
//        }


        $order_info['reciver_phone'] = $order_info['extend_order_common']['reciver_info']['phone'];
        $order_info['reciver_name'] = $order_info['extend_order_common']['reciver_name'];
        $order_info['reciver_addr'] = $order_info['extend_order_common']['reciver_info']['address'];

        $order_info['promotion'] = array();
        //显示锁定中
        $order_info['if_lock'] = $order_model->getOrderOperateState('lock', $order_info);

        //显示取消订单
        $order_info['if_buyer_cancel'] = $order_model->getOrderOperateState('buyer_cancel', $order_info);

        //显示退款取消订单
        $order_info['if_refund_cancel'] = $order_model->getOrderOperateState('refund_cancel', $order_info);

        //显示收货
        $order_info['if_receive'] = $order_model->getOrderOperateState('receive', $order_info);

        //显示物流跟踪
        $order_info['if_deliver'] = $order_model->getOrderOperateState('deliver', $order_info);
        //显示评价
        $order_info['if_evaluation'] = $order_model->getOrderOperateState('evaluation', $order_info);
        //显示系统自动取消订单日期
        if ($order_info['order_state'] == ORDER_STATE_NEW) {
            $order_info['order_cancel_day'] = intval($order_info['add_time'] )+ intval((config('order_auto_cancel_day') * 24 * 3600));
        }
        //显示快递信息
        if ($order_info['shipping_code'] != '') {
            $order_info['if_deliver'] = true;
            $express = rkcache('express', true);
            $order_info['express_info']['express_code'] = $express[$order_info['extend_order_common']['shipping_express_id']]['express_code'];
            $order_info['express_info']['express_name'] = $express[$order_info['extend_order_common']['shipping_express_id']]['express_name'];
            $order_info['express_info']['express_url'] = $express[$order_info['extend_order_common']['shipping_express_id']]['express_url'];
        }


        //显示系统自动收获时间
        if ($order_info['order_state'] == ORDER_STATE_SEND) {
            $order_info['order_confirm_day'] = $order_info['delay_time'] + config('order_auto_receive_day') * 24 * 3600;
        }

        //如果订单已取消，取得取消原因、时间，操作人
        if ($order_info['order_state'] == ORDER_STATE_CANCEL) {
            $close_info = $order_model->getOrderlogInfo(array('order_id' => $order_info['order_id']), 'log_id desc');
            $order_info['close_info'] = $close_info;
            $order_info['state_desc'] = $close_info['log_orderstate'];
            $order_info['order_tips'] = $close_info['log_msg'];
        }
        foreach ($order_info['extend_order_goods'] as $value) {
            $value['image_240_url'] = goods_cthumb($value['goods_image'], 240);
            $value['image_url'] = goods_cthumb($value['goods_image'], 240);
            $value['goods_type_cn'] = get_order_goodstype($value['goods_type']);
            $value['goods_url'] = url('Goods/index', array('goods_id' => $value['goods_id']));
            if ($value['goods_type'] == 5) {
                $order_info['zengpin_list'][] = $value;
            } else {
                $order_info['goods_list'][] = $value;
            }
        }

        if (empty($order_info['zengpin_list'])) {
            $order_info['goods_count'] = count($order_info['goods_list']);
        } else {
            $order_info['goods_count'] = count($order_info['goods_list']) + 1;
        }

        $order_info['real_pay_amount'] = $order_info['order_amount'] + $order_info['shipping_fee'];
        //取得其它订单类型的信息000--------------------------------
        //$order_model->getOrderExtendInfo($order_info);


        $order_info['zengpin_list'] = array();
        if (is_array($order_info['extend_order_goods'])) {
            foreach ($order_info['extend_order_goods'] as $val) {
                if ($val['goods_type'] == 5) {
                    $order_info['zengpin_list'][] = $val;
                }
            }
        }
        $result['order_info'] = $order_info;
        ds_json_encode(10000, '', $result);
    }

    /**
     * 订单详情
     */
    public function get_current_deliver()
    {
        $order_id = intval(input('post.order_id'));
        if ($order_id <= 0) {
            ds_json_encode(10001, '订单不存在');
        }

        $order_model = model('order');
        $condition['order_id'] = $order_id;
        $condition['buyer_id'] = $this->member_info['member_id'];
        $order_info = $order_model->getOrderInfo($condition, array('order_common', 'order_goods'));
        if (empty($order_info) || !in_array($order_info['order_state'], array(ORDER_STATE_SEND, ORDER_STATE_SUCCESS))) {
            ds_json_encode(10001, '订单不存在');
        }

        $express = rkcache('express', true);
        if (!empty($order_info['extend_order_common']['shipping_express_id'])) {
            $express_code = $express[$order_info['extend_order_common']['shipping_express_id']]['express_code'];
            $express_name = $express[$order_info['extend_order_common']['shipping_express_id']]['express_name'];
        } else {
            $express_code = '';
            $express_name = '';
        }

        $deliver_info = $this->_get_express($express_code, $order_info['shipping_code']);


        $data = array();
        $data['deliver_info']['context'] = $express_name;
        $data['deliver_info']['time'] = $deliver_info['0'];

        ds_json_encode(10000, '', $data);
    }

    /**
     * 从第三方取快递信息
     *
     */
    public function _get_express($express_code, $shipping_code)
    {

//        $url = 'http://www.kuaidi100.com/query?type=' . $express_code . '&postid=' . $shipping_code . '&id=1&valicode=&temp=' . random(4) . '&sessionid=&tmp=' . random(4);
//        $content = http_request($url);
//        $content = json_decode($content, true);
//
//        if ($content['status'] != 200) {
//            ds_json_encode(10001, '物流信息查询失败');
//        }
//        $content['data'] = array_reverse($content['data']);
//        $output = array();
//        if (is_array($content['data'])) {
//            foreach ($content['data'] as $k => $v) {
//                if ($v['time'] == '')
//                    continue;
//                //$output[] = $v['time'] . '&nbsp;&nbsp;' . $v['context'];
//                $output[$k]['time'] = $v['time'];
//                $output[$k]['context'] = $v['context'];
//            }
//        }
//        if (empty($output))
//            exit(json_encode(false));
//        return $output;


        $res = curl_get('http://tracequery.sto-express.cn/track.aspx?billcode=' . $shipping_code);
        $res = iconv("GB2312", "utf-8//IGNORE", $res);
        try {
            $obj = simplexml_load_string(str_replace('GB2312', 'UTF-8', $res));
            $jsonStr = json_encode($obj);
            $arr = json_decode($jsonStr, true);
            if (!isset($arr['track']['detail'])) {
                $arr['status'] = 0;
            } else {
                $arr['status'] = 1;
                $arr['track']['detail'] = array_reverse($arr['track']['detail']);
            }
        } catch (\Exception $e) {
            $arr['status'] = 0;
        }
        return $arr;
    }


    public function get_type_name($type)
    {
        switch ($type) {
            case 20:
                $name = '积分订单';
                break;
            case 30:
                $name = '91购订单';
                break;
            case 40:
                $name = '秒杀订单';
                break;

        }
        return $name;
    }


    //得到代售订单
    public function getmemberforsaleorder()
    {
        $goods_state = input('state_type');
        if ($goods_state == 1) {
            $wheere['goods_state'] = 0;
        } elseif ($goods_state == 2) {
            $wheere['goods_state'] = 1;
        } else {
            $wheere['goods_state'] = 2;
        }
        $wheere['member_id'] = $this->member_info['member_id'];
        $res = model('memberforsalegoods');
        $member_info = $res->getlist($wheere, $this->pagesize);
        if (!empty($member_info)) {
            foreach ($member_info as $k => $value) {
                $member_info[$k]['good_img'] = $res->getpic($value['goods_id']);
                $member_info[$k]['goods_name'] = $res->getname($value['goods_id']);
                $member_info[$k]['specs'] = $res->getgoodsinfo($value['goods_commonid']);//规格
                $member_info[$k]['goods_state'] = $res->getStatus($value['goods_state']);//状态
                $member_info[$k]['goods_type'] = '代售商品';
            }
            $result = array_merge(array('orderlist' => $member_info), mobile_page(is_object($res->page_info) ? $res->page_info : ''));
            ds_json_encode(10000, '获取成功', $result);
        } else {
            $result['orderlist'] = [];
            $result['page_total'] = 1;
            $result['hasmore'] = false;
            ds_json_encode(10001, '该用户没有代售订单', $result);
        }
    }

    //得到代售详情列表
    public function getmemberforsaledetail()
    {
        $member_id = input('member_id');
        if ($member_id != '') {
            $wheere['member_id'] = $member_id;
        } else {
            $wheere['member_id'] = $this->member_info['member_id'];
        }
        $wheere['goods_id'] = input('goods_id');
        //$wheere['created_at'] = input('created_at');
        $res = model('memberforsalegoods');
        $member_info = db('memberforsalegoods')->where($wheere)->find();
        //print_r($member_info);die;
        if (!empty($member_info)) {
            $member_info['good_img'] = $res->getpic($member_info['goods_id']);
            $member_info['goods_name'] = $res->getname($member_info['goods_id']);
            $member_info['specs'] = $res->getgoodsinfo($member_info['goods_commonid']);//规格
            $member_info['goods_state'] = $res->getStatus($member_info['goods_state']);//状态
            $member_info['goods_type'] = '代售商品';
            //秒杀记录
            $order = model('order');
            $con['buyer_id'] = $this->member_info['member_id'];
            //去重
            $con['order_type'] = 40;
            $orderlist = $order->getsaleorderlist($con, $this->pagesize, '');
            if (!empty($orderlist)) {
                foreach ($orderlist as $k => $v) {
                    $orderlist[$k]['count'] = $res->getnum($v['order_sn']);
                    $orderlist[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                }
                $result['goods_kill'] = array_merge(array('data' => $orderlist), mobile_page(is_object($order->page_info) ? $order->page_info : ''));
            } else {
                $result['goods_kill']['page_total'] = 1;
                $result['goods_kill']['hasmore'] = false;
                ds_json_encode(10001, '该用户没有秒杀记录');
            }
            //代售记录
            $memberforsaleorder = model('memberforsaleorder');
            $condition['member_id'] = $this->member_info['member_id'];
            $sale = $memberforsaleorder->getmemberforsaleorder($condition, $this->pagesize, '');
            foreach ($sale as $k => $v) {
                $sale[$k]['service_fee'] = 100;
            }
            $result['goods_sale'] = array_merge(array('data' => $sale), mobile_page(is_object($order->page_info) ? $order->page_info : ''));
            $result['goods_info'] = $member_info;
            ds_json_encode(10000, '获取成功', $result);
        } else {
            ds_json_encode(10001, '数据错误');
        }
    }

    //秒杀记录
    public function getgoodskill()
    {
        $res = model('memberforsalegoods');
        $order = model('order');
        $con['buyer_id'] = $this->member_info['member_id'];
        $con['order_type'] = 40;
        $orderlist = $order->getsaleorderlist($con, $this->pagesize, '');
        if (!empty($orderlist)) {
            foreach ($orderlist as $k => $v) {
                $orderlist[$k]['count'] = $res->getnum($v['order_sn']);
                $orderlist[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            }
            $result['goods_kill'] = array_merge(array('data' => $orderlist), mobile_page(is_object($order->page_info) ? $order->page_info : ''));
        } else {
            $result['page_total'] = 1;
            $result['hasmore'] = false;
            $result['data'] = [];
            ds_json_encode(10001, '该用户没有秒杀记录', $result);
        }
    }

    //代售记录
    public function getsalegoods()
    {
        $order = model('order');
        $memberforsaleorder = model('memberforsaleorder');
        $condition['member_id'] = $this->member_info['member_id'];
        $sale = $memberforsaleorder->getmemberforsaleorder($condition, $this->pagesize, '');
        if (!empty($sale)) {
            ds_json_encode(10000, '获取成功', $sale);
        } else {
            $result['page_total'] = 1;
            $result['hasmore'] = false;
            $result['data'] = [];
            ds_json_encode(10001, '该用户没有挂售记录', $result);
        }
    }

    public  function  test(){
        $this->_get_express('yunda','3995310750466');
    }

}

?>
