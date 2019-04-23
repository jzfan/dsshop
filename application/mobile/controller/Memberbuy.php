<?php

namespace app\mobile\controller;

class Memberbuy extends MobileMember {

    public function _initialize() {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * 购物车、直接购买第一步:选择收获地址和配置方式
     */
    public function buy_step1() {

        $cart_id = explode(',', input('param.cart_id'));

        $logic_buy = model('buy', 'logic');

        //得到会员等级
        $member_model = model('member');
        $member_info = $member_model->getMemberInfoByID($this->member_info['member_id']);
        if (!$member_info['is_buylimit']) {
            ds_json_encode(10001,'您没有商品购买的权限,如有疑问请联系客服人员');
        }
        /*
          if ($member_info) {
          $member_gradeinfo = $member_model->getOneMemberGrade(intval($member_info['member_exppoints']));
          $member_discount = $member_gradeinfo['orderdiscount'];
          $member_level = $member_gradeinfo['level'];
          }
          else {
          $member_discount = $member_level = 0;
          } */

        //得到购买数据
        $ifcart = !empty(input('param.ifcart')) ? true : false;

        //商品类型
        $goods_type = input('param.goods_type',30);

        //额外数据用来处理拼团等其他活动
        $pintuan_id = intval(input('param.pintuan_id'));
        $extra = array();
        if ($pintuan_id >= 0) {
            $extra['pintuan_id'] = $pintuan_id; #拼团ID
            #是否为开团订单
            $extra['pintuangroup_id'] = empty(input('param.pintuangroup_id')) ? 0 : intval(input('param.pintuangroup_id'));
        }
        $extra['goods_type'] = $goods_type;

        $result = $logic_buy->buyStep1($cart_id, $ifcart, $this->member_info['member_id'], $extra);

        if (!$result['code']) {
            ds_json_encode(10001,$result['msg']);
        } else {
            $result = $result['data'];
        }

        if (intval(input('post.address_id')) > 0) {
            $result['address_info'] = model('address')->getDefaultAddressInfo(array('address_id' => intval(input('post.address_id')), 'member_id' => $this->member_info['member_id']));
        }
        if ($result['address_info']) {
            $data_area = $logic_buy->changeAddr($result['freight_list'], $result['address_info']['city_id'], $result['address_info']['area_id'], $this->member_info['member_id']);
            if (!empty($data_area) && $data_area['state'] == 'success') {
                if (is_array($data_area['content'])) {
                    $data_area['content'] = ds_price_format($value);
                }
            } else {
                ds_json_encode(10001,'地区请求失败');
            }
        }

        //整理数据
        $cart_list = array();
        $total_list = $result['goods_total'];
        foreach ($result['cart_list'] as $key => $value) {
            $cart_list['goods_list'][$key] = $value;
            $cart_list['goods_total'] = $result['goods_total'][$key];

            $cart_list['mansong_rule_list'] = isset($result['mansong_rule_list']) ? $result['mansong_rule_list'] : '';

            if ($cart_list['mansong_rule_list'] && $cart_list['mansong_rule_list']['discount'] > 0) {
                $total_list -= $cart_list['mansong_rule_list']['discount'];
            }

            if (is_array($result['voucher_list']) && count($result['voucher_list']) > 0) {
                current($result['voucher_list'][$key]);
                $cart_list['voucher_info'] = reset($result['voucher_list'][$key]);
                $cart_list['voucher_info']['voucher_price'] = ds_price_format($cart_list[$key]['voucher_info']['voucher_price']);
                $cart_list['voucher_info']['voucher_enddate_text'] = date('Y年m月d日', $cart_list[$key]['voucher_info']['voucher_enddate']);
                $total_list -= $cart_list[$key]['voucher_info']['voucher_price'];
            } else {
                $cart_list['voucher_info'] = array();
            }

            $cart_list['voucher_list'] = $result['voucher_list'];
            if (!empty($result['cancel_calc_sid_list'][$key])) {
                $cart_list['freight'] = '0';
                $cart_list['freight_message'] = $result['cancel_calc_sid_list'][$key]['desc'];
            }
        }

        $buy_list = array();
        $buy_list['cart_list'] = $cart_list;
        $buy_list['cart_list_api'] = array_values($cart_list);
        $buy_list['freight_hash'] = $result['freight_list'];
        $buy_list['address_info'] = $result['address_info'];
        $buy_list['ifshow_offpay'] = $result['ifshow_offpay'];
        $buy_list['vat_hash'] = $result['vat_hash'];
        $buy_list['inv_info'] = $result['inv_info'];
        $buy_list['available_predeposit'] = isset($result['available_predeposit']) ? $result['available_predeposit'] : array();
        $buy_list['available_rc_balance'] = isset($result['available_rc_balance']) ? $result['available_rc_balance'] : array();
        $buy_list['zk_list'] = isset($result['zk_list']) ? $result['zk_list'] : array();

        if (isset($data_area['content']) && $data_area['content']) {
            $total_list = model('buy_1', 'logic')->reCalcGoodsTotal($total_list, $data_area['content'], 'freight');
        }
        $buy_list['order_amount'] = ds_price_format($total_list);
        $buy_list['address_api'] = (isset($data_area) && $data_area) ? $data_area : '';

        $buy_list['final_total_list'] = $total_list;
        ds_json_encode(10000, '', $buy_list);
    }

    /**
     * 购物车、直接购买第二步:保存订单入库，产生订单号，开始选择支付方式
     *
     */
    public function buy_step2() {
        $param = array();
        $param['ifcart'] = input('post.ifcart');
        $param['cart_id'] = explode(',', input('post.cart_id'));
        $param['address_id'] = input('post.address_id');
        $param['vat_hash'] = input('post.vat_hash');
        $param['offpay_hash'] = input('post.offpay_hash');
        $param['offpay_hash_batch'] = input('post.offpay_hash_batch');
        $param['pay_name'] = input('post.pay_name');
        $param['invoice_id'] = input('post.invoice_id');

        $param['pintuan_id'] = input('post.pintuan_id');
        $param['pintuangroup_id'] = input('post.pintuangroup_id');

        //商品类型
        $param['goods_type'] = input('post.goods_type',30);

        //处理代金券
        $voucher = array();
        $post_voucher = explode(',', input('post.voucher'));

        if ($post_voucher != [''] && !empty($post_voucher)) {
            foreach ($post_voucher as $value) {
                list($vouchertemplate_id, $store_id, $voucher_price) = explode('|', $value);
                $voucher = $value;
            }
        }
        $param['voucher'] = $voucher;

        $pay_message = trim(input('post.pay_message'), ',');
        $pay_message = explode(',', $pay_message);
        $param['pay_message'] = array();
        if (is_array($pay_message) && $pay_message) {
            foreach ($pay_message as $v) {
                if (strpos($v, '|') !== false) {
                    $v = explode('|', $v);
                    $param['pay_message'][$v[0]] = $v[1];
                }
            }
        }
        $param['pd_pay'] = input('post.pd_pay');
        $param['rcb_pay'] = input('post.rcb_pay');
        $param['password'] = input('post.password');
        $param['fcode'] = input('post.fcode');
        $param['order_from'] = 2;
        $logic_buy = model('buy', 'logic');

        //得到会员等级
        /* $member_model = model('member');
          $member_info = $member_model->getMemberInfoByID($this->member_info['member_id']);
          if ($member_info) {
          $member_gradeinfo = $member_model->getOneMemberGrade(intval($member_info['member_exppoints']));
          $member_discount = $member_gradeinfo['orderdiscount'];
          $member_level = $member_gradeinfo['level'];
          }
          else {
          $member_discount = $member_level = 0;
          } */

        $result = $logic_buy->buyStep2($param, $this->member_info['member_id'], $this->member_info['member_name'], $this->member_info['member_email']);
        if (!$result['code']) {
            ds_json_encode(10001,$result['msg']);
        }
        $order_info = current($result['data']['order_list']);
        $res = array('pay_sn' => $result['data']['pay_sn'], 'payment_code' => $order_info['payment_code']);
        ds_json_encode(10000, '', $res);
    }

    /**
     * 验证密码
     */
    public function check_password() {
        if (empty(input('post.password'))) {
            ds_json_encode(10001,'参数错误');
        }

        $member_model = model('member');

        $member_info = $member_model->getMemberInfoByID($this->member_info['member_id']);
        if ($member_info['member_paypwd'] == md5(input('post.password'))) {
            ds_json_encode(10000, '', 1);
        } else {
            ds_json_encode(10001,'密码错误');
        }
    }

    /**
     * 更换收货地址
     */
    public function change_address() {
        $logic_buy = model('buy', 'logic');
        $city_id = input('post.city_id');
        $area_id = input('post.area_id');
        if (empty($city_id)) {
            $city_id = $area_id;
        }

        $data = $logic_buy->changeAddr(input('post.freight_hash'), $city_id, $area_id, $this->member_info['member_id']);
        if (!empty($data) && $data['state'] == 'success') {
            ds_json_encode(10000, '', $data);
        } else {
            ds_json_encode(10001,'地址修改失败');
        }
    }

    /**
     * 实物订单支付(新接口)
     */
    public function pay() {
        $pay_sn = input('post.pay_sn');
        if (!preg_match('/^\d{20}$/', $pay_sn)) {
            ds_json_encode(10001,'该订单不存在');
        }

        //查询支付单信息
        $order_model = model('order');
        $pay_info = $order_model->getOrderpayInfo(array(
            'pay_sn' => $pay_sn, 'buyer_id' => $this->member_info['member_id']
                ), true);
        if (empty($pay_info)) {
            ds_json_encode(10001,'该订单不存在');
        }

        //取子订单列表
        $condition = array();
        $condition['pay_sn'] = $pay_sn;
        $condition['order_state'] = array('in', array(ORDER_STATE_NEW, ORDER_STATE_PAY));
        $order_list = $order_model->getOrderList($condition, '', '*', '', '', array(), true);
        if (empty($order_list)) {
            ds_json_encode(10001,'未找到需要支付的订单');
        }

        //定义输出数组
        $pay = array();
        //支付提示主信息
        //订单总支付金额(不包含货到付款)
        $pay['pay_amount'] = 0;
        //充值卡支付金额(之前支付中止，余额被锁定)
        $pay['payed_rcb_amount'] = 0;
        //预存款支付金额(之前支付中止，余额被锁定)
        $pay['payed_pd_amount'] = 0;
        //还需在线支付金额(之前支付中止，余额被锁定)
        $pay['pay_diff_amount'] = 0;
        //账户可用金额
        $pay['member_available_pd'] = 0;
        $pay['member_available_rcb'] = 0;

        $logic_order = model('order', 'logic');

        //计算相关支付金额
        foreach ($order_list as $key => $order_info) {
            if (!in_array($order_info['payment_code'], array('offline', 'chain'))) {
                if ($order_info['order_state'] == ORDER_STATE_NEW) {
                    $pay['payed_rcb_amount'] += $order_info['rcb_amount'];
                    $pay['payed_pd_amount'] += $order_info['pd_amount'];
                    $pay['pay_diff_amount'] += $order_info['order_amount'] - $order_info['rcb_amount'] - $order_info['pd_amount'];
                }
            }
        }
        if (isset($order_info['chain_id']) && $order_info['payment_code'] == 'chain') {
            $order_list[0]['order_remind'] = '下单成功，请在' . CHAIN_ORDER_PAYPUT_DAY . '日内前往门店提货，逾期订单将自动取消。';
            $flag_chain = 1;
        }

        //如果线上线下支付金额都为0，转到支付成功页
        if (empty($pay['pay_diff_amount'])) {
            ds_json_encode(10001,'订单重复支付');
        }

        $condition = array();
        $condition['payment_platform'] = 'h5';
        $payment_list = model('payment')->getPaymentOpenList($condition);

        if (!empty($payment_list)) {
            foreach ($payment_list as $k => $value) {
                unset($payment_list[$k]['payment_config']);
                unset($payment_list[$k]['payment_state']);
                unset($payment_list[$k]['payment_state_text']);
            }
        }
        if (in_array($this->member_info['member_clienttype'], array('ios', 'android'))) {
            foreach ($payment_list as $k => $value) {
                if (!strpos($payment_list[$k]['payment_code'], 'app')) {
                    unset($payment_list[$k]);
                }
            }
        }
        //显示预存款、支付密码、充值卡
        $pay['member_available_pd'] = $this->member_info['available_predeposit'];
        $pay['member_available_rcb'] = $this->member_info['available_rc_balance'];
        $pay['member_paypwd'] = $this->member_info['member_paypwd'] ? true : false;
        $pay['pay_sn'] = $pay_sn;
        $pay['payed_amount'] = ds_price_format($pay['payed_rcb_amount'] + $pay['payed_pd_amount']);
        unset($pay['payed_pd_amount']);
        unset($pay['payed_rcb_amount']);
        $pay['pay_amount'] = ds_price_format($pay['pay_diff_amount']);
        unset($pay['pay_diff_amount']);
        $pay['member_available_pd'] = ds_price_format($pay['member_available_pd']);
        $pay['member_available_rcb'] = ds_price_format($pay['member_available_rcb']);
        $pay['payment_list'] = $payment_list ? array_values($payment_list) : array();
        ds_json_encode(10000, '', array('pay_info' => $pay));
    }

    /**
     * AJAX验证支付密码
     */
    public function check_pd_pwd() {
        if (empty(input('post.password'))) {
            ds_json_encode(10001,'支付密码格式不正确');
        }
        $buyer_info = model('member')->getMemberInfoByID($this->member_info['member_id']);
        if ($buyer_info['member_paypwd'] != '') {
            if ($buyer_info['member_paypwd'] === md5(input('post.password'))) {
                ds_json_encode(10000, '', 1);
            }
        }
        ds_json_encode(10001,'支付密码验证失败');
    }

    /**
     * F码验证
     */
    public function check_fcode() {
        $goods_id = intval(input('post.goods_id'));
        if ($goods_id <= 0) {
            ds_json_encode(10001,'商品ID格式不正确');
        }
        if (input('post.fcode') == '') {
            ds_json_encode(10001,'F码格式不正确');
        }
        $result = model('buy', 'logic')->checkFcode($goods_id, trim(input('post.fcode')));
        if ($result['code']) {
            ds_json_encode(10000, '', 1);
        } else {
            ds_json_encode(10001,'F码验证失败');
        }
    }

}
