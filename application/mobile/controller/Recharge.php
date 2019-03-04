<?php

namespace app\mobile\controller;

use think\Lang;

class Recharge extends MobileMember {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'mobile/lang/'.config('default_lang').'/recharge.lang.php');
    }

    /**
     * 写充值信息
     */
    public function index() {
        $pdr_amount = abs(floatval(input('post.pdr_amount')));
        if ($pdr_amount <= 0) {
            ds_json_encode(10001,'充值金额不正确');
        } else {
            $predeposit_model = model('predeposit');
            $data = array();
            $data['pdr_sn'] = $pay_sn = makePaySn($this->member_info['member_id']);
            $data['pdr_member_id'] = $this->member_info['member_id'];
            $data['pdr_member_name'] = $this->member_info['member_name'];
            $data['pdr_amount'] = $pdr_amount;
            $data['pdr_addtime'] = TIMESTAMP;
            $insert = $predeposit_model->addPdRecharge($data);
            if ($insert) {
                ds_json_encode(10000, '',array('pay_sn' => $pay_sn));
            } else {
                ds_json_encode(10001,'提交失败');
            }
        }
    }

    /**
     * 申请提现
     */
    public function pd_cash_add() {
        $pdc_amount = abs(floatval(input('post.pdc_amount')));
        
        $validate_data = array(
            'pdc_amount'=>$pdc_amount,
            'pdc_bank_name'=>input('post.pdc_bank_name'),
            'pdc_bank_no'=>input('post.pdc_bank_no'),
            'pdc_bank_user'=>input('post.pdc_bank_user'),
            'password'=>input('post.password'),
            'mobilenum'=>input('post.mobilenum'),
        );

        $recharge_validate = validate('recharge');
        if (!$recharge_validate->scene('pd_cash_add')->check($validate_data)) {
            ds_json_encode(10001,$recharge_validate->getError());
        }

        $predeposit_model = model('predeposit');
        $member_model = model('member');
        $memberinfo = $member_model->getMemberInfoByID($this->member_info['member_id']);
        //验证支付密码
        if (md5(input('post.password')) != $memberinfo['member_paypwd']) {
            ds_json_encode(10001,'支付密码错误');
        }
        //验证金额是否足够
        if (floatval($memberinfo['available_predeposit']) < $pdc_amount) {
            ds_json_encode(10001,'金额不足本次提现');
        }
        //验证手机号码
        $mobilenum = input('post.mobilenum');
        if($mobilenum!=$memberinfo['member_mobile']){
            ds_json_encode(10001,'手机号码错误');
        }
        try {
            $predeposit_model->startTrans();
            $pdc_sn = makePaySn($memberinfo['member_id']);
            $data = array();
            $data['pdc_sn'] = $pdc_sn;
            $data['pdc_member_id'] = $memberinfo['member_id'];
            $data['pdc_member_name'] = $memberinfo['member_name'];
            $data['pdc_amount'] = $pdc_amount;
            $data['pdc_bank_name'] = input('post.pdc_bank_name');
            $data['pdc_bank_no'] = input('post.pdc_bank_no');
            $data['pdc_bank_user'] = input('post.pdc_bank_user');
            $data['pdc_addtime'] = TIMESTAMP;
            $data['pdc_payment_state'] = 0;
            
            $insert = $predeposit_model->addPdcash($data);
            if (!$insert) {
                ds_json_encode(10001,'提交失败');
            }
            //冻结可用预存款
            $data = array();
            $data['member_id'] = $memberinfo['member_id'];
            $data['member_name'] = $memberinfo['member_name'];
            $data['amount'] = $pdc_amount;
            $data['order_sn'] = $pdc_sn;
            $predeposit_model->changePd('cash_apply', $data);
            $predeposit_model->commit();
            ds_json_encode(10000, '',array('status' => 'ok'));
        } catch (Exception $e) {
            $predeposit_model->rollback();
            ds_json_encode(10001,'系统繁忙，提交失败');
        }
    }

    public function recharge_order() {
        $pay_sn = input('param.paysn');
        if (!preg_match('/^\d{20}$/', $pay_sn)) {
            ds_json_encode(10001,'订单号错误');
        }

        //查询支付单信息
        $predeposit_model = model('predeposit');
        $pd_info = $predeposit_model->getPdRechargeInfo(array('pdr_sn' => $pay_sn, 'pdr_member_id' => $this->member_info['member_id']));
        if (empty($pd_info)) {
            ds_json_encode(10001,'订单不存在');
        }
        if (intval($pd_info['pdr_payment_state'])) {
            ds_json_encode(10001,'您的订单已经支付，请勿重复支付!');
        }


        $payment_model = model('payment');

        $condition = array();
        $condition['payment_platform'] = 'h5';
        $payment_list = $payment_model->getPaymentOpenList($condition);
        $payment_array = array();
        if (!empty($payment_list)) {
            foreach ($payment_list as $value) {
                $payment_array[] = array('payment_code' => $value['payment_code'], 'payment_name' => $value['payment_name']);
            }
        } else {
            ds_json_encode(10001,'暂未找到合适的支付方式');
        }
        unset($pd_info['pdr_payment_code']);
        unset($pd_info['pdr_trade_sn']);
        unset($pd_info['pdr_payment_state']);
        unset($pd_info['pdr_paymenttime']);
        unset($pd_info['pdr_admin']);
        ds_json_encode(10000, '',array('payment_list' => $payment_array, 'pdinfo' => $pd_info,'base_site_url'=>BASE_SITE_URL));
    }

    public function member_v() {

        $member_info = array();


        $member_info['user_name'] = $this->member_info['member_name'];

        $member_info['avator'] = get_member_avatar_for_id($this->member_info['member_id']);

        $member_info['point'] = $this->member_info['member_points'];

        $member_gradeinfo = model('member')->getOneMemberGrade(intval($this->member_info['member_exppoints']));

        $member_info['level_name'] = $member_gradeinfo['level_name'];

        $member_info['favorites_goods'] = model('favorites')->getGoodsFavoritesCountByMemberId($this->member_info['member_id']);



        $member_info['member_id'] = $this->member_info['member_id']; //

        $member_info['member_id_64'] = base64_encode(intval($this->member_info['member_id']) * 1); //
        $config_model = model('config');
        $list_setting = $config_model->getConfigList();
        $member_info['vip_1fee'] = $list_setting['vip_1fee'];
        $member_info['vip_2fee'] = $list_setting['vip_2fee'];
        ds_json_encode(10000,'',array('member_info' => $member_info));
    }

    /**
     * 在线升级到会员级别
     */
    public function recharge_vip1() {
        $pdr_amount = abs(floatval(input('post.pdr_amount')));
        $config_model = model('config');
        $list_setting = $config_model->getConfigList();
        if ($pdr_amount <= 0 || $pdr_amount != abs(floatval($list_setting['vip_1fee']))) {

            ds_json_encode(10001,'金额参数错误');
        }

        $predeposit_model = model('predeposit');

        $data = array();

        $data['pdr_sn'] = $pay_sn = makePaySn($this->member_info['member_id']);

        $data['pdr_member_id'] = $this->member_info['member_id'];

        $data['pdr_member_name'] = $this->member_info['member_name'];

        $data['pdr_amount'] = $pdr_amount;

        $data['pdr_addtime'] = TIMESTAMP;

        $data['pdr_vipid'] = '1';

        $insert = $predeposit_model->addVipRecharge($data);

        if ($insert) {
            ds_json_encode(10000, '', array('pay_sn' => $pay_sn));
        } else {
            ds_json_encode(10001,'参数错误');
        }
    }

    public function recharge_vip2() {
        $pdr_amount = abs(floatval(input('post.pdr_amount')));
        $config_model = model('config');
        $list_setting = $config_model->getConfigList();
        if ($pdr_amount <= 0 || $pdr_amount != abs(floatval($list_setting['vip_2fee']))) {
            ds_json_encode(10001,'金额参数错误');
        }

        $predeposit_model = model('predeposit');

        $data = array();

        $data['pdr_sn'] = $pay_sn = makePaySn($this->member_info['member_id']);

        $data['pdr_member_id'] = $this->member_info['member_id'];

        $data['pdr_member_name'] = $this->member_info['member_name'];

        $data['pdr_amount'] = $pdr_amount;

        $data['pdr_addtime'] = TIMESTAMP;

        $data['pdr_vipid'] = '2';

        $insert = $predeposit_model->addVipRecharge($data);

        if ($insert) {
            ds_json_encode(10000, '',array('pay_sn' => $pay_sn));
        } else {
            ds_json_encode(10001,'参数错误');
        }
    }

    public function viprecharge_order() {
        $pay_sn = input('post.paysn');
        if (!preg_match('/^\d{20}$/', $pay_sn)) {
            ds_json_encode(10001,'订单号错误');
        }

        //查询支付单信息
        $predeposit_model = model('predeposit');
        $pd_info = $predeposit_model->getVipRechargeInfo(array('pdr_sn' => $pay_sn, 'pdr_member_id' => $this->member_info['member_id']));
        if (empty($pd_info)) {
            ds_json_encode(10001,'订单不存在');
        }
        if (intval($pd_info['pdr_payment_state'])) {
            ds_json_encode(10001,'您的订单已经支付，请勿重复支付!');
        }


        $payment_model = model('payment');
        $condition = array();
        $condition['payment_platform'] = 'h5';
        $payment_list = $payment_model->getPaymentOpenList($condition);
        $payment_array = array();
        if (!empty($payment_list)) {
            foreach ($payment_list as $value) {
                $payment_array[] = array('payment_code' => $value['payment_code'], 'payment_name' => $value['payment_name']);
            }
        } else {
            ds_json_encode(10001,'暂未找到合适的支付方式');
        }
        unset($pd_info['pdr_payment_code']);
        unset($pd_info['pdr_trade_sn']);
        unset($pd_info['pdr_payment_state']);
        unset($pd_info['pdr_paymenttime']);
        unset($pd_info['pdr_admin']);
        ds_json_encode(10000, '',array('payment_list' => $payment_array, 'pdinfo' => $pd_info));
    }

}

?>
