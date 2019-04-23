<?php

namespace app\mobile\controller;

use think\Lang;

class Membercart extends MobileMember {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'mobile/lang/'.config('default_lang').'/membercart.lang.php');
    }

    /**
     * 购物车列表
     */
    public function cart_list() {
        $cart_model = model('cart');

        $condition = array('buyer_id' => $this->member_info['member_id']);
        $cart_list = $cart_model->getCartList('db', $condition);

        // 购物车列表 [得到最新商品属性及促销信息]
        $cart_list = model('buy_1','logic')->getGoodsCartList($cart_list);
        $goods_model = model('goods');

        $sum = 0;
        $cart_a = array();
        $k=0;
        foreach ($cart_list as $key => $val) {
//            $goods_data = $goods_model->getGoodsOnlineInfoForShare($val['goods_id']);

            $cart_a[0]['goods'][$key]['cart_id'] = $val['cart_id'];
            $cart_a[0]['goods'][$key]['goods_type'] = $val['goods_type'];
            $cart_a[0]['goods'][$key]['goods_point'] = isset($val['goods_point']) ? $val['goods_point'] : 0;
            $cart_a[0]['goods'][$key]['goods_name'] = $val['goods_name'];
            $cart_a[0]['goods'][$key]['goods_price'] = $val['goods_price'];
            $cart_a[0]['goods'][$key]['goods_num'] = $val['goods_num'];
            $cart_a[0]['goods'][$key]['goods_image_url'] = goods_cthumb($val['goods_image']);
//            if (isset($goods_data['goods_promotion_type'])) {
//                $cart_a[$val['store_id']]['goods'][$key]['goods_price'] = $goods_data['goods_promotion_price'];
//            }
            $cart_a[0]['goods'][$key]['gift_list'] = isset($val['gift_list'])?$val['gift_list']:'';
            $cart_list[$key]['goods_sum'] = ds_price_format($val['goods_price'] * $val['goods_num']);
            $sum += $cart_list[$key]['goods_sum'];
            $k++;
        }
        
        
        foreach ($cart_a as $key => $value) {
            $value['goods'] = array_values($value['goods']);
            $cart_l[] = $value;
        }
        if(isset($cart_l)){
            $cart_b=array_values($cart_l);
        }else{
            $cart_b=array();
        }

        ds_json_encode(10000, '',array('cart_list' => $cart_a, 'sum' => ds_price_format($sum), 'cart_count' => count($cart_list),'cart_val'=>$cart_b));
    }

    /**
     * 购物车添加
     */
    public function cart_add() {
        if(isset($this->member_info) && !$this->member_info['is_buylimit']){
            ds_json_encode(10001,'您没有商品购买的权限,如有疑问请联系客服人员');
        }
        $goods_id = intval(input('post.goods_id'));
        $quantity = intval(input('post.quantity'));

        $goods_type = input('post.goods_type',20);

        if ($goods_id <= 0 || $quantity <= 0) {
            ds_json_encode(10001,'参数错误');
        }

        $goods_model = model('goods');
        $cart_model = model('cart');
        $logic_buy_1 = model('buy_1','logic');

        $goods_info = $goods_model->_getGoodsOnlineInfoAndPromotionById($goods_type,$goods_id);

        //验证是否可以购买
        if (empty($goods_info)) {
            ds_json_encode(10001,'商品已下架或不存在');
        }

        //抢购
        $logic_buy_1->getGroupbuyInfo($goods_info, $quantity);

        //限时折扣
        $logic_buy_1->getXianshiInfo($goods_info, $quantity);

//        if (isset($this->member_info['member_id'])==1) {
//            ds_json_encode(10001,'不能购买自己发布的商品');
//        }
        if (intval($goods_info['goods_storage']) < 1 || intval($goods_info['goods_storage']) < $quantity) {
            ds_json_encode(10001,'库存不足');
        }

        $param = array();
        $param['buyer_id'] = isset($this->member_info)?$this->member_info['member_id']:0;
        $param['goods_id'] = $goods_info['goods_id'];
        $param['goods_name'] = $goods_info['goods_name'];
        $param['goods_price'] = $goods_info['goods_price'];
        $param['goods_image'] = $goods_info['goods_image'];
        $param['goods_type'] = $goods_info['goods_type'];

        $result = $cart_model->addCart($param, isset($this->member_info)?'db':'cookie', $quantity);
        if ($result) {
            ds_json_encode(10000,'',1);
        } else {
            ds_json_encode(10001,'加入购物车失败');
        }
    }

    /**
     * 购物车删除
     */
    public function cart_del() {
        $cart_id = intval(input('post.cart_id'));

        $cart_model = model('cart');

        if ($cart_id > 0) {
            $condition = array();
            $condition['buyer_id'] = $this->member_info['member_id'];
            $condition['cart_id'] = $cart_id;

            $cart_model->delCart('db', $condition);
        }

        ds_json_encode(10000,'',1);
    }

    /**
     * 更新购物车购买数量
     */
    public function cart_edit_quantity() {
        $cart_id = intval(abs(input('post.cart_id')));
        $quantity = intval(abs(input('post.quantity')));
        if (empty($cart_id) || empty($quantity)) {
            ds_json_encode(10001,'参数错误');
        }

        $cart_model = model('cart');
        $cart_info = $cart_model->getCartInfo(array('cart_id' => $cart_id, 'buyer_id' => $this->member_info['member_id']));
        //检查是否为本人购物车
        if ($cart_info['buyer_id'] != $this->member_info['member_id']) {
            ds_json_encode(10001,'参数错误');
        }
        
        //检查库存是否充足
        if (!$this->_check_goods_storage($cart_info, $quantity, $this->member_info['member_id'])) {
            ds_json_encode(10001,'超出限购数或库存不足');
        }

        $data = array();
        $data['goods_num'] = $quantity;
        $data['goods_price'] = $cart_info['goods_price'];

        $where['cart_id']= $cart_id;
        $where['buyer_id']=$cart_info['buyer_id'];

        $update = $cart_model->editCart($data, $where);
        if ($update) {
            $return = array();
            $return['quantity'] = $quantity;
            $return['goods_price'] = ds_price_format($cart_info['goods_price']);
            $return['total_price'] = ds_price_format($cart_info['goods_price'] * $quantity);
            ds_json_encode(10000, '', $return);
        } else {
            ds_json_encode(10001,'修改失败');
        }
    }

    /**
     * 检查库存是否充足
     */
    private function _check_goods_storage(& $cart_info, $quantity, $member_id) {
        $goods_model = model('goods');
        $pbundling_model = model('pbundling');
        $logic_buy_1 = model('buy_1','logic');

        if ($cart_info['bl_id'] == '0') {
            //普通商品
            $goods_info = $goods_model->getGoodsOnlineInfoAndPromotionById($cart_info['goods_id']);

            //抢购
            $logic_buy_1->getGroupbuyInfo($goods_info, $quantity);
            if (isset($goods_info['ifgroupbuy'])) {
                if ($goods_info['upper_limit'] && $quantity > $goods_info['upper_limit']) {
                    return false;
                }
            }
            //限时折扣
            $logic_buy_1->getXianshiInfo($goods_info, $quantity);
            if (intval($goods_info['goods_storage']) < $quantity) {
                return false;
            }
            
            $goods_info['cart_id'] = $cart_info['cart_id'];
            $goods_info['buyer_id'] = $cart_info['buyer_id'];
            $cart_info = $goods_info;
        } else {
            //优惠套装商品
            $bl_goods_list = $pbundling_model->getBundlingGoodsList(array('bl_id' => $cart_info['bl_id']));
            $goods_id_array = array();
            foreach ($bl_goods_list as $goods) {
                $goods_id_array[] = $goods['goods_id'];
            }
            $bl_goods_list = $goods_model->getGoodsOnlineListAndPromotionByIdArray($goods_id_array);

            //如果有商品库存不足，更新购买数量到目前最大库存
            foreach ($bl_goods_list as $goods_info) {
                if (intval($goods_info['goods_storage']) < $quantity) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 检查购物车数量
     */
    public function cart_count() {
        $cart_model = model('cart');
        $count = $cart_model->getCartCountByMemberId($this->member_info['member_id']);
        $data['cart_count'] = $count;
        ds_json_encode(10000, '', $data);
    }

}

?>
