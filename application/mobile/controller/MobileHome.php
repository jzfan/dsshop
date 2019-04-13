<?php

namespace app\mobile\controller;

use think\Controller;
use think\exception\ValidateException;

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


    protected function validateInput($rules, $input=null)
    {
        $input = $input ?? input();
        
        $v = \think\Loader::validate();
        $v->rule($rules);

        if (!$v->check($input)) {
            throw new \app\common\JsonException($v->getError(), 422);
        }
        $keys = array_map(function ($row) {
            return explode('|', $row)[0];
        }, array_keys($rules));
        return request()->only($keys);

    }
    
}

?>
