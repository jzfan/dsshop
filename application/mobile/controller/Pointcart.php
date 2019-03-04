<?php

namespace app\mobile\controller;

use think\Lang;

class Pointcart extends MobileMember {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'mobile/lang/'.config('default_lang').'/pointcart.lang.php');
        //判断系统是否开启积分和积分兑换功能
        if (config('pointprod_isuse') != 1) {
            ds_json_encode(10001,'未开启积分兑换功能');                      
        }
    }

    /**
     * 购物车添加礼品
     */
    public function add() {
        $pgid = intval(input('post.pgid'));
        $quantity = intval(input('post.quantity'));
        if ($pgid <= 0 || $quantity <= 0) {
            ds_json_encode(10001,'参数错误');
        }

        //验证积分礼品是否存在购物车中
        $pointcart_model = model('pointcart');
        $pointcart_model->delPointcart(array('pmember_id' => $this->member_info['member_id']));
        $check_cart = $pointcart_model->getPointcartInfo(array('pgoods_id' => $pgid, 'pmember_id' => $this->member_info['member_id']));
        if (!empty($check_cart)) {
            ds_json_encode(10000, '',array('done' => 'ok1'));
        }
        //验证是否能兑换
        $data = $pointcart_model->checkExchange($pgid, $quantity, $this->member_info['member_id']);
        if (!$data['state']) {            
            ds_json_encode(10001,$data['msg']);
        }
        $prod_info = $data['data']['prod_info'];

        $insert_arr = array();
        $insert_arr['pmember_id'] = $this->member_info['member_id'];
        $insert_arr['pgoods_id'] = $prod_info['pgoods_id'];
        $insert_arr['pgoods_name'] = $prod_info['pgoods_name'];
        $insert_arr['pgoods_points'] = $prod_info['pgoods_points'];
        $insert_arr['pgoods_choosenum'] = $prod_info['quantity'];
        $insert_arr['pgoods_image'] = $prod_info['pgoods_image_old'];
        $cart_state = $pointcart_model->addPointcart($insert_arr);

        ds_json_encode(10000, '',array('done' => 'ok'));
        die;
    }

    /**
     * 兑换订单流程第一步
     */
    public function step1() {
        //获取符合条件的兑换礼品和总积分
        $data = model('pointcart')->getCartGoodsList($this->member_info['member_id']);
        if (!$data['state']) {            
            ds_json_encode(10001,$data['msg']);
        }

        //实例化收货地址模型（不显示自提点地址）
        $address_list = model('address')->getAddressList(array('member_id' => $this->member_info['member_id']), 'address_is_default desc,address_id desc');

        ds_json_encode(10000, '',array('pointprod_arr' => $data['data'], 'address_info' => $address_list[0]));
    }

    /**
     * 兑换订单流程第二步
     */
    public function step2() {
        $pointcart_model = model('pointcart');
        //获取符合条件的兑换礼品和总积分
        $data = $pointcart_model->getCartGoodsList($this->member_info['member_id']);
        if (!$data['state']) {            
            ds_json_encode(10001,$data['msg']);
        }
        $pointprod_arr = $data['data'];
        unset($data);

        //验证积分数是否足够
        $data = $pointcart_model->checkPointEnough($pointprod_arr['pgoods_pointall'], $this->member_info['member_id']);
        if (!$data['state']) {            
            ds_json_encode(10001,$data['msg']);
        }
        unset($data);

        //创建兑换订单
        $data = model('pointorder')->createOrder($_POST, $pointprod_arr, array('member_id' => $this->member_info['member_id'], 'member_name' => $this->member_info['member_name'], 'member_email' => $this->member_info['member_email']));
        if (!$data['state']) {            
            ds_json_encode(10001,$data['msg']);
        }
        $order_id = $data['data']['order_id'];
        ds_json_encode(10000, '',array('pointprod_arr' => $order_id));
    }
}

?>
