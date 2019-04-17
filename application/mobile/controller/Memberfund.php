<?php
namespace app\mobile\controller;
class Memberfund extends MobileMember {

    public function _initialize() {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * 预存款日志列表
     */
    public function predepositlog() {
        $predeposit_model = model('predeposit');
        $where = array();
        $where['lg_member_id'] = $this->member_info['member_id'];
        $where['lg_av_amount'] = array('neq', 0);
        $list = $predeposit_model->getPdLogList($where, $this->pagesize, '*', 'lg_id desc');
        if ($list) {
            foreach ($list as $k => $v) {
                $v['lg_addtime_text'] = @date('Y-m-d H:i:s', $v['lg_addtime']);
                $list[$k] = $v;
            }
        }
        $result= array_merge(array('list' => $list), mobile_page($predeposit_model->page_info));
        ds_json_encode(10000, '',$result);
    }

    /**
     * 充值卡余额变更日志
     */
    public function rcblog() {
        $rcblog_model = model('rcblog');
        $where = array();
        $where['member_id'] = $this->member_info['member_id'];
        $log_list = $rcblog_model->getRechargecardBalanceLogList($where, $this->pagesize, 'rcblog_id desc');
        if ($log_list) {
            foreach ($log_list as $k => $v) {
                $v['add_time_text'] = @date('Y-m-d H:i:s', $v['add_time']);
                $log_list[$k] = $v;
            }
        }
        $result= array_merge(array('log_list' => $log_list), mobile_page($rcblog_model->page_info));
        ds_json_encode(10000, '',$result);
    }

    /**
     * 充值明细
     */
    public function pdrechargelist() {
        $where = array();
        $where['pdr_member_id'] = $this->member_info['member_id'];
        $predeposit_model = model('predeposit');
        $list = $predeposit_model->getPdRechargeList($where, $this->pagesize, '*', 'pdr_id desc');
        if ($list) {
            foreach ($list as $k => $v) {
                $v['pdr_addtime_text'] = @date('Y-m-d H:i:s', $v['pdr_addtime']);
                $v['pdr_payment_name'] = get_order_payment_name($v['pdr_payment_code']);
                $v['pdr_payment_state_text'] = $v['pdr_payment_state'] == 1 ? '已支付' : '未支付';
                $list[$k] = $v;
            }
        }
        $result= array_merge(array('list' => $list), mobile_page($predeposit_model->page_info));
        ds_json_encode(10000, '',$result);
    }

    /**
     * 提现记录
     */
    public function pdcashlist() {
        $where = array();
        $where['pdc_member_id'] = $this->member_info['member_id'];
        $predeposit_model = model('predeposit');
        $list = $predeposit_model->getPdcashList($where, $this->pagesize, '*', 'pdc_id desc');
        if ($list) {
            foreach ($list as $k => $v) {
                $v['pdc_addtime_text'] = @date('Y-m-d H:i:s', $v['pdc_addtime']);
                $v['pdc_payment_time_text'] = @date('Y-m-d H:i:s', $v['pdc_payment_time']);
                $v['pdc_payment_state_text'] = $v['pdc_payment_state'] == 1 ? '已支付' : '未支付';
                $list[$k] = $v;
            }
        }
        $result= array_merge(array('list' => $list), mobile_page($predeposit_model->page_info));
        ds_json_encode(10000, '',$result);
    }

    /**
     * 充值卡充值
     */
    public function rechargecard_add() {
        $rc_sn = trim(input('post.rc_sn'));

        if (!$rc_sn) {
            ds_json_encode(10001,'请输入平台充值卡号');
        }
        $res = model('predeposit')->addRechargecard($rc_sn, array('member_id' => $this->member_info['member_id'], 'member_name' => $this->member_info['member_name']));
        if ($res['message'])
            ds_json_encode(10001,$res['message']);
        ds_json_encode(10000,'',1);
    }

    /**
     * 预存款提现记录详细
     */
    public function pdcashinfo() {
        $pdc_id = intval(input('get.pdc_id'));
        if ($pdc_id <= 0) {
            ds_json_encode(10001,'参数错误');
        }
        $where = array();
        $where['pdc_member_id'] = $this->member_info['member_id'];
        $where['pdc_id'] = $pdc_id;
        $info = model('predeposit')->getPdcashInfo($where);
        if (!$info) {
            ds_json_encode(10001,'参数错误');
        }
        $info['pdc_addtime_text'] = $info['pdc_addtime'] ? @date('Y-m-d H:i:s', $info['pdc_addtime']) : '';
        $info['pdc_payment_time_text'] = $info['pdc_payment_time'] ? @date('Y-m-d H:i:s', $info['pdc_payment_time']) : '';
        $info['pdc_payment_state_text'] = $info['pdc_payment_state'] == 1 ? '已支付' : '未支付';
        ds_json_encode(10000, '',array('info' => $info));
    }

    /**
     * 充值列表
     */
    public function index() {
        $condition = array();
        $condition['pdr_member_id'] = $this->member_info['member_id'];
        $pdr_sn = input('get.pdr_sn');
        if (!empty($pdr_sn)) {
            $condition['pdr_sn'] = $pdr_sn;
        }

        $predeposit_model = model('predeposit');
        $list = $predeposit_model->getPdRechargeList($condition, 20, '*', 'pdr_id desc');
        foreach ($list as $key => $value) {
            $list[$key]['pdr_addtime_text'] = date('Y-m-d H:i:s', $value['pdr_addtime']);
        }
        $result= array_merge(array('list' => $list), mobile_page($predeposit_model->page_info));
        ds_json_encode(10000, '',$result);
    }

    /**
     * 我的积分 我的余额
     */
    public function my_asset() {
        $point = $this->member_info['member_points'];
        ds_json_encode(10000, '',array('point' => $point));
    }


    protected function getMemberAndGradeInfo($is_return = false) {
        $member_info = array();
        //会员详情及会员级别处理
        if ($this->member_info['member_id']) {
            $member_model = model('member');
            $member_info = $member_model->getMemberInfoByID($this->member_info['member_id']);
            if ($member_info) {
                $member_gradeinfo = $member_model->getOneMemberGrade(intval($member_info['member_exppoints']));
                $member_info = array_merge($member_info, $member_gradeinfo);
                $member_info['security_level'] = $member_model->getMemberSecurityLevel($member_info);
            }
        }
        if ($is_return == true) {//返回会员信息
            return $member_info;
        } else {//输出会员信息
            $this->assign('member_info', $member_info);
        }
    }

    /**
     * AJAX验证
     *
     */
    protected function check() {
        if (captcha_check(input('post.captcha'))) {
            return true;
        } else {
            return false;
        }
    }

}