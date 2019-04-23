<?php

namespace app\mobile\controller;

use think\Lang;

class Login extends MobileMall
{

    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'mobile/lang/' . config('default_lang') . '/login.lang.php');
    }

    /**
     * 登录
     */
    public function index()
    {
        $username = input('param.username');
        $password = input('param.password');
        $client = input('param.client');

        if (empty($username) || empty($password) || !in_array($client, $this->client_type_array)) {
            ds_json_encode(10001, '登录失败');
        }

        $member_model = model('member');

        $array = array();
        $array['member_name'] = $username;
        $array['member_password'] = md5($password);
        $member_info = $member_model->getMemberInfo($array);
        if (empty($member_info) && preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $username)) {//根据会员名没找到时查手机号
            $array = array();
            $array['member_mobile'] = $username;
            $array['member_password'] = md5($password);
            $member_info = $member_model->getMemberInfo($array);
        }

        if (empty($member_info) && (strpos($username, '@') > 0)) {//按邮箱和密码查询会员
            $array = array();
            $array['member_email'] = $username;
            $array['member_password'] = md5($password);
            $member_info = $member_model->getMemberInfo($array);
        }
        if ($member_info['member_state'] == 0) {
            ds_json_encode(10001, '您的账户被禁用!请联系平台处理');
        }
        if (is_array($member_info) && !empty($member_info)) {
            $token = $member_model->getBuyerToken($member_info['member_id'], $member_info['member_name'], $client);
            if ($token) {
                $logindata = array(
                    'username' => $member_info['member_name'], 'userid' => $member_info['member_id'], 'key' => $token
                );
                ds_json_encode(10000, '', $logindata);
            } else {
                ds_json_encode(10001, '登录失败');
            }
        } else {
            ds_json_encode(10001, '用户名密码错误');
        }
    }


    public function get_inviter()
    {
        $inviter_id = intval(input('get.inviter_id'));
        $member = db('member')->where('member_id', $inviter_id)->field('member_id,member_name')->find();

        ds_json_encode(10000, '', array('member' => $member));
    }


    /**
     * 注册 重复注册验证
     */
    public function register()
    {
        $username = input('param.username');
        $password = input('param.password');
        $password_confirm = input('param.password_confirm');
        $email = input('param.email');
        $client = input('param.client');
        $inviter_id = intval(input('param.inviter_id'));
        if ($password_confirm != $password) {
            ds_json_encode(10001, '密码不一致');
        }
        $member_model = model('member');
        $register_info = array();
        $register_info['member_name'] = $username;
        $register_info['member_password'] = $password;
        $register_info['email'] = $email;
        //添加奖励积分
        if ($inviter_id) {
            $register_info['inviter_id'] = $inviter_id;
        }

        $member_info = $member_model->register($register_info);
        if (!isset($member_info['error'])) {
            $token = $member_model->getBuyerToken($member_info['member_id'], $member_info['member_name'], $client);
            if ($token) {
                $result = array(
                    'username' => $member_info['member_name'], 'userid' => $member_info['member_id'],
                    'key' => $token
                );
                ds_json_encode(10000, '', $result);
            } else {
                ds_json_encode(10001, '注册失败');
            }
        } else {
            ds_json_encode(10001, $member_info['error']);
        }
    }
}

?>
