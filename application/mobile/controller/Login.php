<?php

namespace app\mobile\controller;

use think\Cache;
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
        if (is_array($member_info) && !empty($member_info)) {
            if ($member_info['member_state'] == 0) {
                ds_json_encode(10001, '您的账户被禁用!请联系平台处理');
            }
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
        $code = input('param.code');
        $username = input('param.username');
        if (!preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $username)) {
            ds_json_encode(10001, '请输入正确的手机号!');
        }
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
        $register_info['member_name'] = $this->getstr();
        $register_info['member_mobile'] = $username;
        $cache_code = Cache::get($username);
        if ($cache_code != $code) {
            ds_json_encode(10001, '验证码填写错误');
        }
        $register_info['member_provinceid'] = input('param.provinceid');
        $register_info['member_cityid'] = input('param.cityid');
        $register_info['member_areainfo']=input('param.provincename').''.input('param.cityname');;
        $register_info['member_password'] = $password;
        $register_info['email'] = $email;
        $register_info['member_mobilebind'] = 1;
        //添加奖励积分
        if ($inviter_id!=''){
            $is_null=model('member')->where('member_id',$inviter_id)->find();
            if (!empty($is_null)) {
                $register_info['inviter_id'] = $inviter_id;
            }else{
                ds_json_encode(10001, '该推荐人不是我们平台会员!');
            }
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

    protected function getstr()
    {
        $strs = "QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm";
        $name = substr(str_shuffle($strs), mt_rand(0, strlen($strs) - 11), 8);
        return $name;
    }

    //获取验证码
    public function getcode()
    {
        $model = model('sms');
        $phone = input('param.username');
        if (Cache::get($phone)) {
            ds_json_encode(10001, '请勿多次获取验证码');
        } else {
            $model->send($phone);
        }
    }

    public function get_express()
    {
        $num = input('param.code');
        $res = curl_get('http://tracequery.sto-express.cn/track.aspx?billcode=' . $num);
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

    public function get_area()
    {

        $pid = intval(input('param.pid'));
        $area_mod = model('area');
        $regions = $area_mod->getAreaList(array('area_parent_id' => $pid));
        foreach ($regions as $key => $region) {
            $result[$key]['area_name'] = htmlspecialchars($region['area_name']);
            $result[$key]['area_id'] = $region['area_id'];
        }
        ds_json_encode(10000,'',$result);
    }
}

?>
