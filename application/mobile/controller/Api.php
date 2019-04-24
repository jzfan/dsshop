<?php
/**
 *第三方api处理
 */

namespace app\mobile\controller;


class Api extends MobileMall
{
    /* QQ登录 */
    public function oa_qq()
    {
        include(PLUGINS_PATH . '/login/qq_h5/oauth/qq_login.php');
    }

    /* QQ登录回调 */
    public function oa_qq_callback()
    {
        include PLUGINS_PATH . '/login/qq_h5/oauth/qq_callback.php';
    }

    /**
     *新浪微博登录
     */
    public function oa_sina()
    {
        if (input('param.step') == 'callback') {
            include PLUGINS_PATH . '/login/sina_h5/callback.php';
        } else {
            include PLUGINS_PATH . '/login/sina_h5/index.php';
        }
    }


    /**
     *  获取商户配置
     */
    public function getconfig()
    {
        $host = trim(input('host'));
        if (empty($host)) {
            $arr = ['miaomi' => '秒米', 'good_type' => '待售'];
            ds_json_encode(10000, '', $arr);
        } else {
            $list = db('hostconfig')->where('host', $host)->select();
            foreach ($list as $kk => $v) {
                $arr[$v['key']] = $v['value'];
            }
            ds_json_encode(10000, '', $arr);
        }
    }
}