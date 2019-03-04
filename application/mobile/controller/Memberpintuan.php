<?php

/**
 * 查看我发起的拼团,用户查看参团以及开团的信息,以及分享
 */

namespace app\mobile\controller;
use think\Lang;
class Memberpintuan extends MobileMember {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/'.config('default_lang').'/memberpintuan.lang.php');
    }
    
    /*
     * 查看我发起的拼团
     */
    public function pintuangroup()
    {
        $condition['pintuangroup_headid'] = $this->member_info['member_id'];
        $ppintuangroup_model = model('ppintuangroup');
        $ppintuanorder_model = model('ppintuanorder');
        $ppintuangroup_list = $ppintuangroup_model->getPpintuangroupList($condition, 10); #获取开团信息
        foreach ($ppintuangroup_list as $key => $ppintuangroup) {
            //获取开团订单下的参团订单
            $condition = array();
            $condition['pintuangroup_id'] = $ppintuangroup['pintuangroup_id'];
            $ppintuangroup_list[$key]['pintuangroup_starttime_text'] = date('Y-m-d H:i',$ppintuangroup['pintuangroup_starttime']);
            $ppintuangroup_list[$key]['pintuangroup_endtime_text'] = date('Y-m-d H:i',$ppintuangroup['pintuangroup_endtime']);
            $ppintuangroup_list[$key]['order_list'] = $ppintuanorder_model->getPpintuanorderList($condition);
        }
        $pintuangroup_state_array = $ppintuangroup_model->getPintuangroupStateArray();
        $result= array_merge(array('list' => $ppintuangroup_list), mobile_page($ppintuangroup_model->page_info));
        ds_json_encode(10000, '',$result);
    }
    
}