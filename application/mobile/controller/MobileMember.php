<?php

namespace app\mobile\controller;

class MobileMember extends MobileHome {

    public function _initialize() {
        parent::_initialize();
        if (!(request()->controller() == 'Membercart' && request()->action() == 'cart_add') || input('post.key')) {//未登录时记录购物车商品到cookie中
            $mbusertoken_model = model('mbusertoken');
            $key = input('post.key');
            if (empty($key)) {
                $key = input('param.key');
            }
            $mb_user_token_info = $mbusertoken_model->getMbusertokenInfoByToken($key);
            if (empty($mb_user_token_info)) {
                ds_json_encode(10001,'请登录', array('login' => '0'));
            }
            $member_model = model('member');
            $this->member_info = $member_model->getMemberInfoByID($mb_user_token_info['member_id']);
            $meter_second=model('member')->where('member_id',$mb_user_token_info['member_id'])->find();

            if (empty($this->member_info)) {
                ds_json_encode(10001,'参数错误', array('login' => '0'));
            } else {
                $this->member_info['member_clienttype'] = $mb_user_token_info['member_clienttype'];
                $this->member_info['meter_second'] = $meter_second['meter_second'];
                $this->member_info['member_openid'] = $mb_user_token_info['member_openid'];
                $this->member_info['member_token'] = $mb_user_token_info['member_token'];
                $level_name = $member_model->getOneMemberGrade($mb_user_token_info['member_id']);
                $this->member_info['level_name'] = $level_name['level_name'];
                //考虑到模型中session
                if (session('member_id') != $this->member_info['member_id']) {
                    //避免重复查询数据库
                    $member_model->createSession(array_merge($this->member_info, $level_name));
                }
            }
        }
    }

    public function getOpenId() {
        return $this->member_info['member_openid'];
    }

    public function setOpenId($openId) {
        $this->member_info['member_openid'] = $openId;
        model('mbusertoken')->editMemberOpenId($this->member_info['member_token'], $openId);
    }

}

?>
