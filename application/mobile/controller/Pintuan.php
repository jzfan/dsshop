<?php

namespace app\mobile\controller;

use think\Lang;

class Pintuan extends MobileMall {
    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'mobile/lang/'.config('default_lang').'/pintuan.lang.php');
    }
    
    /*
     * 获取拼团列表
     */
    public function index()
    {
        $ppintuan_model = model('ppintuan');
        $condition = array(
            'pintuan_starttime'=>array('lt',TIMESTAMP),
            'pintuan_end_time'=>array('gt',TIMESTAMP),
            'pintuan_state'=>1,
        );
        $pintuan_list = $ppintuan_model->getPintuanList($condition, 10, 'pintuan_state desc, pintuan_end_time desc');
        foreach ($pintuan_list as $key => $pintuan) {
            $pintuan_list[$key]['pintuan_image'] = goods_cthumb($pintuan['pintuan_image'],240);
        }
        $page_count = $ppintuan_model->page_info;

        $result= array_merge(array('pintuan_list' => $pintuan_list), mobile_page($page_count));
        ds_json_encode(10000, '',$result);
    }
    
    
    
}