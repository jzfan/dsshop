<?php
/**
 * Created by 91go.miaozancn.com
 * @Date: 2019/4/14
 * @Time: 15:42
 * @auther: mark_haibo
 * @email:920793414@qq.com
 * @File:Meter.php
 */

namespace app\admin\controller;

use think\Lang;

class Meter extends AdminControl
{
    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/' . config('default_lang') . '/predeposit.lang.php');
    }


    public function pdrecharge_list() {
        $condition = array();
        $stime = input('get.stime');
        $etime = input('get.etime');
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $stime);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/', $etime);
        $start_unixtime = $if_start_date ? strtotime($stime) : null;
        $end_unixtime = $if_end_date ? strtotime($etime) : null;
        if ($start_unixtime || $end_unixtime) {
            $condition['lg_addtime'] = array('between', array($start_unixtime, $end_unixtime));
        }
        $mname = input('get.mname');
        if (!empty($mname)) {
            $condition['lg_member_name'] = $mname;
        }
        $aname = input('get.aname');
        if (!empty($aname)) {
            $condition['lg_admin_name'] = $aname;
        }
        $predeposit_model = model('meter_log');
        $list_log = $predeposit_model->getPdLogList($condition, 10, '*', 'lg_id desc');
        $this->assign('show_page', $predeposit_model->page_info->render());
        $this->assign('list_log', $list_log);

        $this->assign('filtered', $condition ? 1 : 0); //是否有查询条件

        $this->setAdminCurItem('pdrecharge_list');
        return $this->fetch();
    }

    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => '秒米明细',
                'url' => url('Meter/pdrecharge_list')
            ),
            array(
                'name' => 'miao_add',
                'text' => '秒米调整',
                'url' => "javascript:dsLayerOpen('".url('Predeposit/miao_add')."','秒米调整')"
            )
        );
        return $menu_array;
    }


}