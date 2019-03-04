<?php

/**
 * 微信相关接口
 */

namespace app\mobile\controller;

class Wxauto extends MobileMall {

    public function _initialize() {
        parent::_initialize();
        $this->wxconfig = model('wechat')->getOneWxconfig();
        $this->appId = $this->wxconfig['appid'];
        $this->appSecret = $this->wxconfig['appsecret'];
    }

    public function login() {
        $ref = input('get.ref');
        $redirect_uri = MOBILE_SITE_URL . "/Wxauto/checkAuth?ref=" . $ref;
        $code_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appId&redirect_uri=" . urlencode($redirect_uri) . "&response_type=code&scope=snsapi_userinfo&state=dsmall#wechat_redirect"; // 获取code
        if (cookie('key') && cookie('new_cookie')) {
            //已经登陆
            $mbusertoken_model = model('mbusertoken');
            $member_model = model('member');
            $mb_user_token_info = $mbusertoken_model->getMbusertokenInfoByToken(cookie('key'));
            $member_info = $member_model->getMemberInfoByID($mb_user_token_info['member_id']);
            if (empty($member_info)) {
                cookie('username', null);
                cookie('key', null);
                cookie('unionid', null);
                cookie('new_cookie', null);
                ds_json_encode(10001,'系统错误');  
//                $this->redirect($code_url);
            } else {
                ds_json_encode(10001,'已经登录');  
//                $this->redirect($ref);
            }
        } else {
            ds_json_encode(10000, '', $code_url);
//            $this->redirect($code_url);
        }
    }

    //获取openid的统一方法
    public function getOpenid(){
        $code = input('get.code');
        $from = input('get.from');

        if (!empty($code)) {

            if ($from == 'miniprogram') {
                //小程序支付获取openid
                $xcx_appid = $this->wxconfig['xcx_appid'];
                $xcx_appsecret = $this->wxconfig['xcx_appsecret'];
                //说明：https://developers.weixin.qq.com/minigame/dev/document/open-api/login/code2accessToken.html?search-key=jscode2session
                $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $xcx_appid . "&secret=" . $xcx_appsecret . "&js_code=" . $code . "&grant_type=authorization_code";
                if (!$xcx_appid || !$xcx_appsecret) {
                    $return = array('done' => false, 'msg' => '未配置小程序');
                    return $return;
                }
            } else {
                //微信自动登录
                $wxappid = $this->appId;
                $wxappsecret = $this->appSecret;
                $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$wxappid&secret=$wxappsecret&code=$code&grant_type=authorization_code";
                if (!$wxappid || !$wxappsecret) {
                    $return = array('done' => false, 'msg' => '未配置微信');
                    return $return;
                }
            }

            //获取 用户唯一标识，请注意，在未关注公众号时，用户访问公众号的网页，也会产生一个用户和公众号唯一的OpenID
            $res = json_decode(http_request($url), true);
            if (isset($res['errcode'])) {
                $return = array('done' => false, 'msg' => '错误码' . $res['errcode'] . '，' . $res['errmsg']);
                return $return;
            }
            $return = array('done' => true, 'data' => $res);
            return $return;
        } else {
            $return = array('done' => false, 'msg' => '未获得微信用户授权');
            return $return;
        }
    }
    public function autoLogin() {
        //获取openid格外用一个方法，因为有些地方（如小程序支付）不需要获取用户信息，只需要openid
        $return=$this->getOpenid();
        if(!$return['done']){
            return $return;
        }  
        $res=$return['data'];

            
//            $accessToken5 = model('wechat')->getAccessToken($from);
//            if (model('wechat')->error_code != 0) {
//                $return = array('done' => false, 'msg' => '错误码' . model('wechat')->error_code . '，' . model('wechat')->error_message);
//                return $return;
//            }
            
            
            
            // 授权 https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140842
            //获取用户基本信息(UnionID机制) 必须要先关注公众号   https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140839
//            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$accessToken5&openid=" . $res['openid'] . "&lang=zh_CN";    //获取用户信息
            
            
            $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $res['access_token'] . "&openid=" . $res['openid'] . "&lang=zh_CN";
            $userinfo = json_decode(http_request($url), true);
            if (isset($userinfo['errcode'])) {
                $return = array('done' => false, 'msg' => $userinfo['errcode'] . '，' . $userinfo['errmsg']);
                return $return;
            }
            if (empty($userinfo['unionid'])) {
                $return = array('done' => false, 'msg' => '获取unionid失败');
            }

            session('openid', $userinfo['openid']);

            $member_model = model('member');
            $member_info = $member_model->getMemberInfo(array('member_wxunionid' => $userinfo['unionid']));

            if (!empty($member_info)) {
                //更新信息
                $token = $member_model->getBuyerToken($member_info['member_id'], $member_info['member_name'], 'wap');
                cookie('username', $member_info["member_name"], 3600 * 24, '/');
                cookie('key', $token, 3600 * 24, '/');
                //cookie('unionid',$token,3600*24,'/');
                cookie('new_cookie', '100', 3600 * 24, '/');
                $return = array('done' => true, 'data' => array('has_user' => true, 'openid' => $userinfo['openid'], 'unionid' => $userinfo['unionid'], 'username' => $member_info["member_name"], 'key' => $token));
                return $return;
            } else {
                $logic_connect_api = model('connectapi', 'logic');
                //注册会员信息 返回会员信息
                $reg_info = array(
                    'member_wxopenid' => $userinfo['openid'],
                    'member_wxunionid' => $userinfo['unionid'],
                    'nickname' => isset($userinfo['nickname']) ? $userinfo['nickname'] : '',
//                    'headimgurl' => isset($userinfo['headimgurl']) ? $userinfo['headimgurl'] : '',#提高体验暂时不对图片进行处理
                );
                $wx_member = $logic_connect_api->wx_register($reg_info, 'wx');
                if (!empty($wx_member)) {
                    $token = $member_model->getBuyerToken($wx_member['member_id'], $wx_member['member_name'], 'wap');
                    cookie('username', $wx_member['member_name'], 3600 * 24, '/');
                    cookie('key', $token, 3600 * 24, '/');
                    $return = array('done' => true, 'data' => array('has_user' => true, 'openid' => $userinfo['openid'], 'unionid' => $userinfo['unionid'], 'username' => $wx_member["member_name"], 'key' => $token));
                    return $return;
                } else {
                    $return = array('done' => false, 'msg' => '用户注册失败');
                    return $return;
                }
            }

    }

    public function checkAuth() {
        $ref = input('get.ref');
        $return = $this->autoLogin();
        if ($return['done']) {
            if (!$ref) {
                $ref = WAP_SITE_URL;
            }
            $this->redirect(urldecode($ref));
        } else {
            halt($return['msg']);
        }
    }
    
    /**
     * 微信小程序调用接口 用户获取小程序用户信息
     */
    public function getUser(){
        $return=$this->getOpenid();
        if($return['done']){
            ds_json_encode(10000, '', $return['data']);
        }else{
            ds_json_encode(10001,$return['msg']);  
        }
    }

}
