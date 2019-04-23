<?php

namespace app\mobile\controller;

use think\Lang;

class Index extends MobileMall {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'mobile/lang/'.config('default_lang').'/index.lang.php');
    }

    /**
     * 首页
     */
    
    public function index() {
        $datas = rcache("wap_index_data");
        if (empty($datas)) {
            $condition['a.ap_id'] = array('in', array('15', '14', '13'));
            $condition['n.ap_isuse'] = '1';
            $data = model('adv')->mbadvlist($condition, 'a.adv_sort desc');
            $adname = array('15' => 'nav_list', '14' => 'adv_list', '13' => 'home1'); //预留一部分扩充adv_list/home1/home2/home3/home4
            foreach ($data as $key => $val) {
                $val['adv_code'] = adv_image($val['adv_code']);
                $datas[$adname[$val['ap_id']]][] = $val;
            }


            //限时折扣

            $promotion_list = model('pxianshigoods')->getXianshigoodsCommendList(6);
            foreach ($promotion_list as $k => $val) {
                $promotion_list[$k]['xianshigoods_price'] = floatval($val['xianshigoods_price']);
                $promotion_list[$k]['goods_price'] = floatval($val['goods_price']);
                $promotion_list[$k]['goods_image'] = goods_cthumb($val['goods_image'], 240);
            }
            $datas['promotion_list'] = $promotion_list;


            $datas['forsalegoods_list'] = model('goods')->_getGoodsList(30, array(), '*', 0, 'goods_commend desc,goods_id desc', 5, 'goods_commonid');
        
        
            $extral = array();
            //一级分类
            $extral['goodsclass_list'] = db('goodsclass')->where('gc_parent_id', 0)->where('gc_show', 1)->order('gc_sort asc')->select();

            $recommend_list = array();
            //获取每个分类下的商品
            foreach ($extral['goodsclass_list'] as $goodsclass) {
                $gc_list = model('goods')->_getGoodsList(20, array('gc_id' => $goodsclass['gc_id']), '*', 0, 'goods_commend desc,goods_id desc', 10, 'goods_commonid');
                foreach ($gc_list as $k => $val) {
                    $gc_list[$k]['goods_image'] = goods_cthumb($val['goods_image'], 240);
                }
                $recommend_list[] = array(
                    'gc_name' => $goodsclass['gc_name'],
                    'gc_id' => $goodsclass['gc_id'],
                    'gc_list' => $gc_list
                );
            }


            $datas['recommend_list'] = $recommend_list;


            $datas['extral'] = $extral;
            wcache('wap_index_data', $datas);
        }

        ds_json_encode(10000, '',$datas);
    }

    /**
     * 默认搜索词列表
     */
    public function search_key_list() {
        $list = @explode(',', config('hot_search'));
        if (!$list || !is_array($list)) {
            $list = array();
        }
        if (cookie('hisSearch') != '') {
            $his_search_list = explode('~', cookie('hisSearch'));
        }else{
            $his_search_list = array();
        }
        if (!is_array($his_search_list)) {
            $his_search_list = array();
        }
        ds_json_encode(10000, '',array('list' => $list, 'his_list' => $his_search_list));
    }

    /**
     * 热门搜索列表
     */
    public function search_hot_info() {
        if (config('rec_search') != '') {
            $rec_search_list = @unserialize(config('rec_search'));
        }else{
            $rec_search_list = array();
        }
        $rec_search_list = is_array($rec_search_list) ? $rec_search_list : array();
        ds_json_encode(10000, '',array('hot_info' => $rec_search_list ? $rec_search_list : array()));
    }

    /**
     * 高级搜索
     */
    public function search_adv() {
        $gc_id = intval(input('get.gc_id'));
        $area_list = model('area')->getAreaList(array('area_deep' => 1), 'area_id,area_name');
        ds_json_encode(10000, '',array('area_list' => $area_list ? $area_list : array()));
    }

    /**
     * 获取百度AK
     */
    public function get_baidu_ak() {
        ds_json_encode(10000, '',array('baidu_ak' => config('baidu_ak')));
    }
}

?>
