<?php

namespace app\mobile\controller;

use think\Lang;

class Pointprod extends MobileMall {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'mobile/lang/'.config('default_lang').'/pointprod.lang.php');
        //判断系统是否开启积分兑换功能
        if (config('pointprod_isuse') != 1) {
            ds_json_encode(10001,'积分兑换功能为开启');
        }
    }

    public function index() {
        $this->plist();
    }

    /**
     * 积分服务列表
     */
    public function plist() {

        $pointprod_model = model('pointprod');

        //展示状态
        $pgoodsshowstate_arr = $pointprod_model->getPgoodsShowState();
        //开启状态
        $pgoodsopenstate_arr = $pointprod_model->getPgoodsOpenState();

        $member_model = model('member');
        //查询会员等级
        $membergrade_arr = $member_model->getMemberGradeArr();

        //查询兑换服务列表
        $where = array();
        $where['pgoods_show'] = $pgoodsshowstate_arr['show'][0];
        $where['pgoods_state'] = $pgoodsopenstate_arr['open'][0];

        if (!empty(input('keyword'))) {
            $where['pgoods_name|pgoods_keywords'] = array('like', '%' . input('keyword') . '%');
            if ($_COOKIE['hisSearch2'] == '') {
                $his_sh_list = array();
            } else {
                $his_sh_list = explode('~', $_COOKIE['hisSearch2']);
            }
            if (strlen(input('keyword')) <= 20 && !in_array(input('keyword'), $his_sh_list)) {
                if (array_unshift($his_sh_list, input('keyword')) > 8) {
                    array_pop($his_sh_list);
                }
            }
            setcookie('hisSearch2', implode('~', $his_sh_list), time() + 2592000, '/', '', false);
        }
        //会员级别
        $level_filter = array();
        if (input('level')) {
            $level_filter['search'] = intval(input('level'));
        }
        if (input('isable') == 1) {
            if ($memberid = $this->getMemberIdIfExists()) {
                $member_infotmp = model('member')->getMemberInfoByID($memberid);
                //当前登录会员等级信息
                $membergrade_info = $member_model->getOneMemberGrade($member_infotmp['member_exppoints'], true);
                $this->member_info = array_merge($member_infotmp, $membergrade_info);
            }
        }
        if (input('isable') == 1 && isset($this->member_info['level'])) {
            $level_filter['isable'] = intval($this->member_info['level']);
        }
        if (count($level_filter) > 0) {
            if (isset($level_filter['search']) && isset($level_filter['isable'])) {
                $where['pgoods_limitmgrade'] = array(array('eq', $level_filter['search']), array('elt', $level_filter['isable']), 'and');
            } elseif (isset($level_filter['search'])) {
                $where['pgoods_limitmgrade'] = $level_filter['search'];
            } elseif (isset($level_filter['isable'])) {
                $where['pgoods_limitmgrade'] = array('elt', $level_filter['isable']);
            }
        }


        //查询仅我能兑换和所需积分
        $points_filter = array();
        if (input('isable') == 1 && isset($this->member_info['level'])) {
            $points_filter['isable'] = $this->member_info['member_points'];
        }
        if (input('points_min') > 0) {
            $points_filter['min'] = intval(input('points_min'));
        }
        if (input('points_max') > 0) {
            $points_filter['max'] = intval(input('points_max'));
        }
        if (count($points_filter) > 0) {
            asort($points_filter);
            if (count($points_filter) > 1) {
                $points_filter = array_values($points_filter);
                $where['pgoods_points'] = array('between', array($points_filter[0], $points_filter[1]));
            } else {
                if ($points_filter['min']) {
                    $where['pgoods_points'] = array('egt', $points_filter['min']);
                } elseif ($points_filter['max']) {
                    $where['pgoods_points'] = array('elt', $points_filter['max']);
                } elseif ($points_filter['isable']) {
                    $where['pgoods_points'] = array('elt', $points_filter['isable']);
                }
            }
        }


        //排序
        switch (input('orderby')) {
            case 'stimedesc':
                $orderby = 'pgoods_starttime desc,';
                break;
            case 'stimeasc':
                $orderby = 'pgoods_starttime asc,';
                break;
            case 'pointsdesc':
                $orderby = 'pgoods_points desc,';
                break;
            case 'pointsasc':
                $orderby = 'pgoods_points asc,';
                break;
            default:
                $orderby='';
        }
        $orderby .= 'pgoods_sort asc,pgoods_id desc';
        $filed = 'pgoods_id,pgoods_name,pgoods_price,pgoods_points,pgoods_image,pgoods_tag,pgoods_serial,pgoods_storage,pgoods_commend,pgoods_keywords,pgoods_salenum,pgoods_view,pgoods_limitnum,pgoods_islimittime';
        $pageSize = 10;
        $pointprod_list = $pointprod_model->getPointProdList($where, $filed, $orderby, '', $pageSize);
        $page_count = $pointprod_model->page_info;

        $result= array_merge(array('goods_list' => $pointprod_list, 'grade_list' => $membergrade_arr, 'ww' => json_encode($where)), mobile_page($page_count));
        ds_json_encode(10000, '',$result);
    }

    /**
     * 积分礼品详细
     */
    public function pinfo() {
        $pid = intval(input('id'));
        if (!$pid) {
            ds_json_encode(10001,'参数错误');
        }
        $pointprod_model = model('pointprod');
        //查询兑换礼品详细
        $prodinfo = $pointprod_model->getOnlinePointProdInfo(array('pgoods_id' => $pid));
        if (empty($prodinfo)) {
            ds_json_encode(10001,'商品参数错误');
        }

        //更新礼品浏览次数
        $tm_tm_visite_pgoods = cookie('tm_visite_pgoods');
        $tm_tm_visite_pgoods = $tm_tm_visite_pgoods ? explode(',', $tm_tm_visite_pgoods) : array();
        if (!in_array($pid, $tm_tm_visite_pgoods)) {//如果已经浏览过该服务则不重复累计浏览次数 
            $result = $pointprod_model->editPointProdViewnum($pid);
            if ($result['state'] == true) {//累加成功则cookie中增加该服务ID
                $tm_tm_visite_pgoods[] = $pid;
                cookie('tm_visite_pgoods', implode(',', $tm_tm_visite_pgoods));
            }
        }

        //查询兑换信息
        $pointorder_model = model('pointorder');
        $pointorderstate_arr = $pointorder_model->getPointorderStateBySign();
        $where = array();
        $where['point_orderstate'] = array('neq', $pointorderstate_arr['canceled'][0]);
        $where['pointog_goodsid'] = $pid;
        $orderprod_list = $pointorder_model->getPointorderAndGoodsList($where, '*',  'pointsordergoods.pointog_recid desc');
        if ($orderprod_list) {
            $buyerid_arr = array();
            foreach ($orderprod_list as $k => $v) {
                $buyerid_arr[] = $v['point_buyerid'];
            }
            $memberlist_tmp = model('member')->getMemberList(array('member_id' => array('in', $buyerid_arr)), 'member_id,member_avatar');
            $memberlist = array();
            if ($memberlist_tmp) {
                foreach ($memberlist_tmp as $v) {
                    $memberlist[$v['member_id']] = $v;
                }
            }
            foreach ($orderprod_list as $k => $v) {
                $v['member_avatar'] = ($t = $memberlist[$v['point_buyerid']]['member_avatar']) ? UPLOAD_SITE_URL . DS . ATTACH_AVATAR . DS . $t : UPLOAD_SITE_URL . DS . ATTACH_COMMON . DS . config('default_user_portrait');
                $orderprod_list[$k] = $v;
            }
        }

        //热门积分兑换服务
        $recommend_pointsprod = $pointprod_model->getRecommendPointProd(5);

        ds_json_encode(10000, '',array('goods_commend_list' => $orderprod_list, 'goods_info' => $prodinfo));
    }

}

?>
