<?php

namespace app\mobile\controller;

use think\Lang;

class Goods extends MobileMall {

    private $PI = 3.14159265358979324;
    private $x_pi = 0;

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'mobile/lang/'.config('default_lang').'/goods.lang.php');
        $this->x_pi = 3.14159265358979324 * 3000.0 / 180.0;
    }

    /**
     * 商品列表
     */
    public function goods_list() {
        $goods_model = model('goods');
        $search_model = model('search');

        //查询条件
        $condition = array();
        $gc_id = intval(input('get.gc_id'));
        $keyword = input('get.keyword');
        $barcode = input('get.barcode');
        $b_id = intval(input('get.b_id'));
        if ($gc_id > 0) {
            $condition['gc_id'] = $gc_id;
        } elseif (!empty($keyword)) {
            $condition['goods_name|goods_advword'] = array('like', '%' . $keyword . '%');
            if (cookie('hisSearch') == '') {
                $his_sh_list = array();
            } else {
                $his_sh_list = explode('~', cookie('hisSearch'));
            }
            if (strlen($keyword) <= 20 && !in_array($keyword, $his_sh_list)) {
                if (array_unshift($his_sh_list, $keyword) > 8) {
                    array_pop($his_sh_list);
                }
            }
            cookie('hisSearch', implode('~', $his_sh_list), 2592000);
        } elseif (!empty($barcode)) {
            $condition['goods_barcode'] = $barcode;
        } elseif ($b_id > 0) {
            $condition['brand_id'] = $b_id;
        }
        $price_from = input('get.price_from');
        $price_to = input('get.price_to');
        $price_from = preg_match('/^[\d.]{1,20}$/', $price_from) ? $price_from : null;
        $price_to = preg_match('/^[\d.]{1,20}$/', $price_to) ? $price_to : null;

        //所需字段
        $fieldstr = "goods_id,goods_name,goods_commonid,goods_advword,goods_price,goods_promotion_price,goods_promotion_type,goods_marketprice,goods_image,goods_salenum,evaluation_good_star,evaluation_count";

        $fieldstr .= ',is_virtual,is_presell,is_goodsfcode,is_have_gift,goods_advword';

        //排序方式
        $order = $this->_goods_list_order(input('get.key'), input('get.order'));

        //全文搜索搜索参数
        $indexer_searcharr = input('get.');
        //搜索消费者保障服务
        $search_ci_arr = array();
        $ci = trim(input('get.ci'), '_');
        if ($ci && $ci != 0 && is_string($ci)) {
            //处理参数
            $search_ci = $ci;
            $search_ci_arr = explode('_', $search_ci);
            $indexer_searcharr['search_ci_arr'] = $search_ci_arr;
        }
        if (input('get.own_shop') == 1) {
            $indexer_searcharr['type'] = 1;
        }
        $indexer_searcharr['price_from'] = $price_from;
        $indexer_searcharr['price_to'] = $price_to;

        //优先从全文索引库里查找
        list($goods_list, $indexer_count) = $search_model->indexerSearch($indexer_searcharr, $this->pagesize);
        if (!is_null($goods_list)) {
            $goods_list = array_values($goods_list);
        } else {

            if ($price_from && $price_to) {
                $condition['goods_promotion_price'] = array('between', "{$price_from},{$price_to}");
            } elseif ($price_from) {
                $condition['goods_promotion_price'] = array('egt', $price_from);
            } elseif ($price_to) {
                $condition['goods_promotion_price'] = array('elt', $price_to);
            }
            if (input('get.gift') == 1) {
                $condition['is_have_gift'] = 1;
            }
            if (intval(input('get.area_id')) > 0) {
                $condition['areaid_1'] = intval(input('get.area_id'));
            }

            //抢购和限时折扣搜索
            $_tmp = array();
            if (input('get.groupbuy') == 1) {
                $_tmp[] = 1;
            }
            if (input('get.xianshi') == 1) {
                $_tmp[] = 2;
            }
            if ($_tmp) {
                $condition['goods_promotion_type'] = array('in', $_tmp);
            }
            unset($_tmp);

            //虚拟商品
            if (input('get.virtual') == 1) {
                $condition['is_virtual'] = 1;
            }

            $goods_list = model("pointgoods")->getPointGoodsList($condition, $fieldstr, $this->pagesize, $order);
        }
        //处理商品列表(抢购、限时折扣、商品图片)
        $goods_list = $this->_goods_list_extend($goods_list);

        $result= array_merge(array('goods_list' => $goods_list), mobile_page(is_object($goods_model->page_info)?$goods_model->page_info:''));
        ds_json_encode(10000, '',$result);
    }

    /**
     * 商品列表排序方式
     */
    private function _goods_list_order($key, $order) {
        $result = 'goods_id desc';
        if (!empty($key)) {

            $sequence = 'desc';
            if ($order == 1) {
                $sequence = 'asc';
            }

            switch ($key) {
                //销量
                case '1' :
                    $result = 'goods_salenum' . ' ' . $sequence;
                    break;
                //浏览量
                case '2' :
                    $result = 'goods_click' . ' ' . $sequence;
                    break;
                //价格
                case '3' :
                    $result = 'goods_price' . ' ' . $sequence;
                    break;
            }
        }
        return $result;
    }

    private function _goods_list_extend($goods_list) {
        //获取商品列表编号数组
        $commonid_array = array();
        $goodsid_array = array();
        foreach ($goods_list as $key => $value) {
            $commonid_array[] = $value['goods_commonid'];
            $goodsid_array[] = $value['goods_id'];
        }

        //促销
        $groupbuy_list = model('groupbuy')->getGroupbuyListByGoodsCommonIDString(implode(',', $commonid_array));
        $xianshi_list = model('pxianshigoods')->getXianshigoodsListByGoodsString(implode(',', $goodsid_array));
        foreach ($goods_list as $key => $value) {
            //抢购
            if (isset($groupbuy_list[$value['goods_commonid']])) {
                $goods_list[$key]['goods_price'] = $groupbuy_list[$value['goods_commonid']]['groupbuy_price'];
                $goods_list[$key]['group_flag'] = true;
            } else {
                $goods_list[$key]['group_flag'] = false;
            }

            //限时折扣
            if (isset($xianshi_list[$value['goods_id']]) && !$goods_list[$key]['group_flag']) {
                $goods_list[$key]['goods_price'] = $xianshi_list[$value['goods_id']]['xianshigoods_price'];
                $goods_list[$key]['xianshi_flag'] = true;
            } else {
                $goods_list[$key]['xianshi_flag'] = false;
            }

            //商品图片url
            $goods_list[$key]['goods_image_url'] = goods_cthumb($value['goods_image'], 480);

            unset($goods_list[$key]['goods_commonid']);
            unset($goods_list[$key]['nc_distinct']);
        }

        return $goods_list;
    }


    /**
     * 商品详细页
     */
    public function goods_detail() {
        $goods_id = intval(input('get.goods_id'));
        $area_id = intval(input('get.area_id'));
        // 商品详细信息
        $goods_model = model('goods');
        $goods_detail = $goods_model->getGoodsDetail($goods_id);
        //halt($goods_detail);
        if (empty($goods_detail)) {
            ds_json_encode(10001,'商品不存在');
        }

        foreach($goods_detail['gift_array'] as $k => $v){
            $goods_detail['gift_array'][$k]['gift_goodsimage_url']=goods_cthumb($v['gift_goodsimage'], '240', $goods_detail['goods_info']['store_id']);
        }
        //$goods_list = $goods_model->getGoodsContract(array(0=>$goods_detail['goods_info']));
        //$goods_detail['goods_info'] = $goods_list[0];
        //推荐商品
        $hot_sales = $goods_model->getGoodsCommendList(6);
        $goodsid_array = array();
        foreach ($hot_sales as $value) {
            $goodsid_array[] = $value['goods_id'];
        }
        $goods_commend_list = array();
        foreach ($hot_sales as $value) {
            $goods_commend = array();
            $goods_commend['goods_id'] = $value['goods_id'];
            $goods_commend['goods_name'] = $value['goods_name'];
            $goods_commend['goods_price'] = $value['goods_price'];
            $goods_commend['goods_promotion_price'] = $value['goods_promotion_price'];
            $goods_commend['goods_image_url'] = goods_cthumb($value['goods_image'], 240);
            $goods_commend_list[] = $goods_commend;
        }

        $goods_detail['goods_commend_list'] = $goods_commend_list;

        //商品详细信息处理
        $goods_detail = $this->_goods_detail_extend($goods_detail);
        
        $goods_common_info = $goods_model->getGoodeCommonInfoByID($goods_detail['goods_info']['goods_commonid']);
        $goods_detail['mb_body']=array();
        if ($goods_common_info['mobile_body'] != '') {
            $goods_detail['mb_body'] = unserialize($goods_common_info['mobile_body']);
        }
        // 如果已登录 判断该商品是否已被收藏&&添加浏览记录
        if ($member_id = $this->getMemberIdIfExists()) {
            $c = (int) model('favorites')->getGoodsFavoritesCountByGoodsId($goods_id, $member_id);
            $goods_detail['is_favorate'] = $c > 0;


            if (!$goods_detail['goods_info']['is_virtual']) {
                // 店铺优惠券
                $condition = array();
                $condition['vouchertemplate_state'] = 1;
                $condition['vouchertemplate_enddate'] = array('gt', time());
                $voucher_template = model('voucher')->getVouchertemplateList($condition);
                if (!empty($voucher_template)) {
                    foreach ($voucher_template as $val) {
                        $param = array();
                        $param['vouchertemplate_id'] = $val['vouchertemplate_id'];
                        $param['vouchertemplate_price'] = $val['vouchertemplate_price'];
                        $param['vouchertemplate_limit'] = $val['vouchertemplate_limit'];
                        $param['vouchertemplate_enddate'] = date('Y年m月d日', $val['vouchertemplate_enddate']);
                        $goods_detail['voucher'][] = $param;
                    }
                }
            }
        }

        // 评价列表
        $goods_eval_list = model('evaluategoods')->getEvaluategoodsList(array('geval_goodsid' => $goods_id),'3');
        //$goods_eval_list = model('memberevaluate','logic')->evaluateListDity($goods_eval_list);
        $goods_detail['goods_eval_list'] = $goods_eval_list;

        //评价信息
        $goods_evaluate_info = model('evaluategoods')->getEvaluategoodsInfoByGoodsID($goods_id);
        $goods_detail['goods_evaluate_info'] = $goods_evaluate_info;

        $goods_detail['goods_hair_info'] = $this->_calc(0, $goods_id);
        
        $goods_detail['goods_info']['pintuangroup_share_id'] = intval(input('get.pintuangroup_share_id'));#获取分享拼团的用户ID
        $inviter_model=model('inviter');
        $goods_detail['inviter_amount']=0;
        if(config('inviter_show') && config('inviter_open') && $goods_detail['goods_info']['inviter_open'] && $member_id && $inviter_model->getInviterInfo('i.inviter_id='.$member_id.' AND i.inviter_state=1')){
            $inviter_amount=round($goods_detail['goods_info']['inviter_ratio_1'] / 100 * $goods_detail['goods_info']['goods_price'], 2);
            if($inviter_amount>0){
                $goods_detail['inviter_amount']=$inviter_amount;
            }
        }
        ds_json_encode(10000, '', $goods_detail);
    }

    /**
     * 记录浏览历史
     */
    public function addbrowse() {
    	$goods_id = intval(input('param.gid'));
    	model('goodsbrowse')->addViewedGoods($goods_id, session('member_id'));
    	exit();
    }
    /**
     * 商品详细信息处理
     */
    private function _goods_detail_extend($goods_detail) {
        //整理商品规格
        unset($goods_detail['spec_list']);
        $goods_detail['spec_list'] = $goods_detail['spec_list_mobile'];
        unset($goods_detail['spec_list_mobile']);

        //整理商品图片
        unset($goods_detail['goods_image']);
        $goods_detail['goods_image'] = implode(',', $goods_detail['goods_image_mobile']);
        unset($goods_detail['goods_image_mobile']);

        //商品链接
        $goods_detail['goods_info']['goods_url'] = url('Goods/index', array('goods_id' => $goods_detail['goods_info']['goods_id']));

        //整理数据
//        unset($goods_detail['goods_info']['goods_commonid']);
        unset($goods_detail['goods_info']['gc_id']);
        unset($goods_detail['goods_info']['gc_name']);
        unset($goods_detail['goods_info']['brand_id']);
        unset($goods_detail['goods_info']['brand_name']);
        unset($goods_detail['goods_info']['type_id']);
        unset($goods_detail['goods_info']['goods_image']);
        unset($goods_detail['goods_info']['goods_body']);
        unset($goods_detail['goods_info']['goods_state']);
        unset($goods_detail['goods_info']['goods_stateremark']);
        unset($goods_detail['goods_info']['goods_lock']);
        unset($goods_detail['goods_info']['goods_addtime']);
        unset($goods_detail['goods_info']['goods_edittime']);
        unset($goods_detail['goods_info']['goods_shelftime']);
        unset($goods_detail['goods_info']['goods_show']);
        unset($goods_detail['goods_info']['goods_commend']);
        unset($goods_detail['goods_info']['explain']);
        unset($goods_detail['goods_info']['buynow_text']);
        unset($goods_detail['groupbuy_info']);
        unset($goods_detail['xianshi_info']);

        return $goods_detail;
    }

    /**
     * 商品详细页
     */
    public function goods_body() {
        header("Access-Control-Allow-Origin:*");
        $goods_id = intval(input('get.goods_id'));

        $goods_model = model('goods');

        $goods_info = $goods_model->getGoodsInfoByID($goods_id);
        $goods_common_info = $goods_model->getGoodeCommonInfoByID($goods_info['goods_commonid']);
        $goods_common_info['mb_body']=array();
        if ($goods_common_info['mobile_body'] != '') {
            $goods_common_info['mb_body'] = unserialize($goods_common_info['mobile_body']);
        }
        
        $this->assign('goods_common_info', $goods_common_info);
        echo $this->fetch('goods_body');
    }

    public function goods_evaluate() {
        $goods_id = intval(input('get.goods_id'));
        $type = intval(input('get.type'));

        $condition = array();
        $condition['geval_goodsid'] = $goods_id;
        switch ($type) {
            case '1':
                $condition['geval_scores'] = array('in', '5,4');
                break;
            case '2':
                $condition['geval_scores'] = array('in', '3,2');
                break;
            case '3':
                $condition['geval_scores'] = array('in', '1');
                break;
            case '4':
                //$condition['geval_image|geval_image_again'] = array('neq', '');  //追加评价带后续处理
                $condition['geval_image'] = array('neq', '');
                break;
            case '5':
                $condition['geval_content_again'] = array('neq', '');
                break;
        }

        //查询商品评分信息
        $evaluategoods_model = model('evaluategoods');
        $goods_eval_list = $evaluategoods_model->getEvaluategoodsList($condition, 10);
        foreach ($goods_eval_list as $k=>$val){
			if($val['geval_isanonymous']){
                $goods_eval_list[$k]['member_avatar']=get_member_avatar_for_id(0);
                $goods_eval_list[$k]['geval_frommembername']=str_cut($val['geval_frommembername'],2).'***';
            }
            if(!empty($goods_eval_list[$k]['geval_image'])) {
            $goods_eval_list[$k]['geval_image']=explode(',',$goods_eval_list[$k]['geval_image']);
                foreach ($goods_eval_list[$k]['geval_image'] as $kk => $vv) {
                    $goods_eval_list[$k]['geval_image'][$kk] = UPLOAD_SITE_URL . '/' . ATTACH_MALBUM . '/' . $vv;
                }
            }
        }
        $goods_eval_list = model('memberevaluate','logic')->evaluateListDity($goods_eval_list);
        $result= array_merge(array('goods_eval_list' => $goods_eval_list), mobile_page(is_object($evaluategoods_model->page_info)?$evaluategoods_model->page_info:0));
        ds_json_encode(10000, '',$result);
    }

    /**
     * 商品详细页运费显示
     *
     * @return unknown
     */
    public function calc() {
        $area_id = intval(input('get.area_id'));
        $goods_id = intval(input('get.goods_id'));
        ds_json_encode(10000, '',$this->_calc($area_id, $goods_id));
    }

    public function _calc($area_id, $goods_id) {
        $goods_info = model('goods')->getGoodsInfo(array('goods_id' => $goods_id), 'transport_id,goods_freight');
        $config['deliver_region'] = config('deliver_region');
        $config['free_price'] = config('free_price');
		$if_deliver=true;
        $area_name='';
        if ($area_id <= 0) {
            if (strpos($config['deliver_region'], '|')) {
                $config['deliver_region'] = explode('|', $config['deliver_region']);
                $config['deliver_region_ids'] = explode(' ', $config['deliver_region'][0]);
            }
            if(isset($config['deliver_region_ids'])){
                $area_id = intval($config['deliver_region_ids'][0]);
                $area_name = $config['deliver_region'][1];
            }
        }
        if ($goods_info['transport_id']) {
            $freight_total = model('transport')->calcTransport(intval($goods_info['transport_id']), $area_id);
            if ($freight_total > 0) {
                if ($config['free_price'] > 0) {
                    if ($freight_total >= $config['free_price']) {
                        $freight_total = '免运费';
                    } else {
                        $freight_total = '运费：' . $freight_total . ' 元，店铺满 ' . $config['free_price'] . ' 元 免运费';
                    }
                } else {
                    $freight_total = '运费：' . $freight_total . ' 元';
                }
            } else {
                if ($freight_total === false) {
                    $if_deliver = false;
                }
                $freight_total = '免运费';
            }
        } else {
            $freight_total = $goods_info['goods_freight'] > 0 ? '运费：' . $goods_info['goods_freight'] . ' 元' : '免运费';
        }
        return array('content' => $freight_total, 'if_deliver_cn' => $if_deliver === false ? '无货' : '有货', 'if_deliver' => $if_deliver === false ? false : true, 'area_name' => $area_name ? $area_name : '全国');
    }


    public function auto_complete() {
        try {
            require(EXTEND_PATH . 'xs/lib/XS.php');
            $obj_doc = new \XSDocument();
            $obj_xs = new \XS(config('fullindexer.appname'));
            $obj_index = $obj_xs->index;
            $obj_search = $obj_xs->search;
            $obj_search->setCharset(CHARSET);
            $corrected = $obj_search->getExpandedQuery(input('param.term'));
            if (count($corrected) !== 0) {
                $data = array();
                foreach ($corrected as $word) {
                    $row['id'] = $word;
                    $row['label'] = $word;
                    $row['value'] = $word;
                    $data[] = $row;
                }
                ds_json_encode(10000, '',array('list' => $data));
            }
        } catch (XSException $e) {
            if (is_object($obj_index)) {
                $obj_index->flushIndex();
            }
        }
    }

    /**
     * 经纬度转换
     * @param unknown $bdLat
     * @param unknown $bdLon
     * @return multitype:number
     */
    public function bd_decrypt($bdLat, $bdLon) {
        $x = $bdLon - 0.0065;
        $y = $bdLat - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $this->x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $this->x_pi);
        $gcjLon = $z * cos($theta);
        $gcjLat = $z * sin($theta);
        return array('lat' => $gcjLat, 'lon' => $gcjLon);
    }

    /**
     *  @desc 根据两点间的经纬度计算距离
     *  @param float $lat 纬度值
     *  @param float $lng 经度值
     */
    private function getDistance($lat1, $lng1, $lat2, $lng2) {
        $earthRadius = 6367000; //approximate radius of earth in meters

        /*
          Convert these degrees to radians
          to work with the formula
         */

        $lat1 = ($lat1 * pi() ) / 180;
        $lng1 = ($lng1 * pi() ) / 180;

        $lat2 = ($lat2 * pi() ) / 180;
        $lng2 = ($lng2 * pi() ) / 180;

        /*
          Using the
          Haversine formula

          http://en.wikipedia.org/wiki/Haversine_formula

          calculate the distance
         */

        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;

        return round($calculatedDistance);
    }

    private function parseDistance($num = 0) {
        $num = floatval($num);
        if ($num >= 1000) {
            $num = $num / 1000;
            return str_replace('.0', '', number_format($num, 1, '.', '')) . 'km';
        } else {
            return $num . 'm';
        }
    }

}
