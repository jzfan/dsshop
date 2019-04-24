<?php

namespace app\mobile\controller;

use think\Lang;
use think\Validate;
class Member extends MobileMember
{

    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'mobile/lang/' . config('default_lang') . '/member.lang.php');
    }

    public function index()
    {
        $member_model = model('member');
        $member_info = $member_model->getMemberInfoByID($this->member_info['member_id']);
        if ($member_info) {
            $member_gradeinfo = $member_model->getOneMemberGrade(intval($member_info['member_exppoints']));
            $member_info = array_merge($member_info, $member_gradeinfo);
            //代金券数量
            $member_info['voucher_count'] = model('voucher')->getCurrentAvailableVoucherCount(session('member_id'));
            $member_info['member_avatar'] = get_member_avatar_for_id($this->member_info['member_id']);
        }
        // 交易提醒
        $order_model = model('order');
        $refundreturn_model = model('refundreturn');
        $member_info['order_nopay_count'] = $order_model->getOrderCountByID($this->member_info['member_id'], 'NewCount');
        $member_info['order_noreceipt_count'] = $order_model->getOrderCountByID($this->member_info['member_id'], 'SendCount');
        $member_info['order_noeval_count'] = $order_model->getOrderCountByID($this->member_info['member_id'], 'EvalCount');
        $member_info['order_noship_count'] = $order_model->getOrderCountByID($this->member_info['member_id'], 'PayCount');
        $member_info['order_refund_count'] = $refundreturn_model->getRefundreturnCount(array('buyer_id' => $this->member_info['member_id'], 'refund_state' => array('<>', 3)));

        ds_json_encode(10000, '', array('member_info' => $member_info, 'inviter_open' => config('inviter_open')));
    }

    public function my_asset()
    {
        $fields_arr = array('miaomi', 'point', 'predepoit', 'available_rc_balance', 'voucher');
        $fields_str = trim(input('fields'));
        if ($fields_str) {
            $fields_arr = explode(',', $fields_str);
        }
        $member_info = array();
        if (in_array('point', $fields_arr)) {
            $member_info['point'] = $this->member_info['member_points'];
        }
        if (in_array('miaomi', $fields_arr)) {
            $member_info['miaomi'] = $this->member_info['meter_second'];
        }
        if (in_array('predepoit', $fields_arr)) {
            $member_info['predepoit'] = $this->member_info['available_predeposit'];
        }
        if (in_array('available_rc_balance', $fields_arr)) {
            $member_info['available_rc_balance'] = $this->member_info['available_rc_balance'];
        }
        if (in_array('voucher', $fields_arr)) {
            $member_info['voucher'] = model('voucher')->getCurrentAvailableVoucherCount($this->member_info['member_id']);
        }
        ds_json_encode(10000, '', $member_info);
    }

    /**
     * 用户首页基本信息显示
     */
    public function information()
    {
        $member_model = model('member');
        $condition['member_id'] = $this->member_info['member_id'];
        $member_info = $member_model->getMemberInfo($condition);
        $member_info['member_birthday'] = date('Y-m-d', $member_info['member_birthday']);
        $member_info['member_avatar'] = get_member_avatar_for_id($member_info['member_id']);
        ds_json_encode(10000, '', $member_info);
    }

    /**
     * 用户基本信息修改
     */
    public function edit_information()
    {
        $data = array(
            'member_truename' => input('param.member_truename'),
            'member_sex' => input('param.member_sex'),
            'member_birthday' => strtotime(input('param.member_birthday')),
        );
        $member_model = model('member');
        $condition['member_id'] = $this->member_info['member_id'];
        $result = $member_model->editMember($condition, $data);
        if ($result) {
            ds_json_encode(10000, '修改成功');
        } else {
            ds_json_encode(10001, '修改失败');
        }
    }

    /**
     * 更新用户头像
     */
    public function edit_memberavatar()
    {
        $file = request()->file('memberavatar');
        $upload_file = BASE_UPLOAD_PATH . DS . ATTACH_AVATAR . DS;
        $avatar_name = 'avatar_' . $this->member_info['member_id'] . '.jpg';
        $info = $file->validate(['ext' => ALLOW_IMG_EXT])->move($upload_file, $avatar_name);
        //生成缩略图
        $image = \think\Image::open($upload_file . '/' . $avatar_name);
        $image->thumb(120, 120, \think\Image::THUMB_CENTER)->save($upload_file . '/' . $avatar_name);
        if ($info) {
            ds_json_encode(10000, '', get_member_avatar_for_id($this->member_info['member_id']));
        } else {
            // 上传失败获取错误信息
            ds_json_encode(10001, $file->getError());
        }
    }

    /*
     * 秒米明细
     * */
    public function get_member_miao()
    {
        $condition['lg_member_id'] = $this->member_info['member_id'];
        $meter_log = model('meterlog');
        $list_log = $meter_log->getLogList($condition, $this->pagesize);
        foreach ($list_log as $k => $v) {
            $list_log[$k]['lg_addtime'] = date('Y-m-d H:i:s', $v['lg_addtime']);
        }
        $result = array_merge(array('log' => $list_log), mobile_page(is_object($meter_log->page_info) ? $meter_log->page_info : ''));
        ds_json_encode(10000, '获取成功', $result);
    }

    /*
     * 获取二维码
    */
    public function get_inviter_code()
    {
        $host=$_SERVER['SERVER_NAME'];
        $member_id = $this->member_info['member_id'];
        $qrcode_path = BASE_UPLOAD_PATH . '/' . ATTACH_INVITER . '/' . $member_id . '.png';
        if (!file_exists($qrcode_path)) {
            import('qrcode.phpqrcode', EXTEND_PATH);
            \QRcode::png(WAP_SITE_URL . '/member/register.html?inviter_id=' . $member_id, $qrcode_path);
        }
        $arr['qcode_url'] = $host . '/uploads/' . ATTACH_INVITER . '/' . $member_id . '.png';
        $arr['title'] = '扫码即送100积分';
        $arr['uid'] = $member_id;
        $arr['points'] = intval(config('points_invite'));
        ds_json_encode(10000, '获取成功', $arr);
    }

}

?>
