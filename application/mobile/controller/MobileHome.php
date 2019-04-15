<?php

namespace app\mobile\controller;

use think\Controller;

/*
 * 基类
 */

class MobileHome extends Controller
{

    //客户端类型
    protected $client_type_array = array('android', 'wap', 'wechat', 'ios', 'windows', 'jswechat');
    //列表默认分页数
    protected $pagesize = 5;

    public function _initialize()
    {
        parent::_initialize();

        //分页数处理
        $pagesize = intval(input('get.pagesize'));
        if ($pagesize > 0) {
            $this->pagesize = $pagesize;
        }
        else {
            $this->pagesize = 10;
        }
        /*加入配置信息*/
        $config_list = rkcache('config', true);
        config($config_list);
    }
    
}

?>
