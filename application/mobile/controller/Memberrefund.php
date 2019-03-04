<?php
namespace app\mobile\controller;
class Memberrefund extends MobileMember
{
    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * 全部退款获取订单信息
     */
    public function refund_all_form()
    {
        $refundreturn_model = model('refundreturn');
        $member_id = $this->member_info['member_id'];
        $order_id = intval(input('param.order_id'));
        $trade_model = model('trade');
        $order_paid = $trade_model->getOrderState('order_paid');//订单状态20:已付款

        $order_model = model('order');
        $condition = array();
        $condition['buyer_id'] = $member_id;
        $condition['order_id'] = $order_id;
        $condition['order_state'] = $order_paid;

        $order_info = $order_model->getOrderInfo($condition);

        if (!empty($order_info) && is_array($order_info)) {
            $order = $refundreturn_model->getRightOrderList($condition);
            $book_amount = $order_info['refund_amount']; //退款金额
            $order = array();
            $order['order_id'] = $order_info['order_id'];
            //$order['order_type'] = $order_info['order_type'];
            $order['order_amount'] = ds_price_format($order_info['order_amount']);
            $order['order_sn'] = $order_info['order_sn'];
            $order['allow_refund_amount'] = ds_price_format($order_info['order_amount'] - $book_amount);//可退款金额
            $order['book_amount'] = ds_price_format($book_amount);

            $goods_list = array();
            $gift_list = array();
            $order_model = model('order');
            $condition = array();
            $condition['order_id'] = $order_id;
            $order_goods_list = $order_model->getOrdergoodsList($condition);
            foreach ($order_goods_list as $key => $value) {
                $goods = array();
                $goods['goods_id'] = $value['goods_id'];
                $goods['goods_name'] = $value['goods_name'];
                $goods['goods_price'] = $value['goods_price'];
                $goods['goods_num'] = $value['goods_num'];
                //$goods['goods_spec'] = $value['goods_spec'];
                $goods['goods_img_480'] = goods_thumb($value, 480);
                $goods['goods_type'] = get_order_goodstype($value['goods_type']);
                if ($value['goods_type'] == 5) {//赠品商品
                    $gift_list[] = $goods;
                }
                else {
                    $goods_list[] = $goods;
                }
            }
            ds_json_encode(10000, '', array('order' => $order, 'goods_list' => $goods_list, 'gift_list' => $gift_list));
        }
        else {
            ds_json_encode(10001,'参数错误');
        }
    }

    /**
     * 全部退款保存数据
     */
    public function refund_all_post()
    {
        $refundreturn_model = model('refundreturn');
        $member_id = $this->member_info['member_id'];
        $order_id = intval(input('post.order_id'));
        $trade_model = model('trade');
        $order_paid = $trade_model->getOrderState('order_paid');//订单状态20:已付款

        $order_model = model('order');
        $condition = array();
        $condition['buyer_id'] = $member_id;
        $condition['order_id'] = $order_id;
        $condition['order_state'] = $order_paid;
        $order_info = $order_model->getOrderInfo($condition);
        $payment_code = $order_info['payment_code'];//支付方式

        $condition = array();
        $condition['buyer_id'] = $member_id;
        $condition['order_id'] = $order_id;
        $condition['goods_id'] = '0';
        $refund = $refundreturn_model->getRefundreturnInfo($condition);

        if (empty($order_info) || $payment_code == 'offline' || $refund['refund_id'] > 0) {//检查数据,防止页面刷新不及时造成数据错误
            ds_json_encode(10001,'参数错误');
        }
        else {
            $book_amount = $order_info['refund_amount'];//退款金额
            $allow_refund_amount = ds_price_format($order_info['order_amount'] - $book_amount);//可退款金额

            $refund_array = array();
            $refund_array['refund_type'] = '1';//类型:1为退款,2为退货
            $refund_array['order_lock'] = '2';//锁定类型:1为不用锁定,2为需要锁定
            $refund_array['goods_id'] = '0';
            $refund_array['order_goods_id'] = '0';
            $refund_array['reason_id'] = '0';
            $refund_array['reason_info'] = '取消订单，全部退款';
            $refund_array['goods_name'] = '订单商品全部退款';
            $refund_array['refund_amount'] = ds_price_format($allow_refund_amount);
            $refund_array['buyer_message'] = input('post.buyer_message');
            $refund_array['add_time'] = time();

            $pic_array = array();
            $pic_array['buyer'] = $_POST['refund_pic'];//上传凭证
            $info = serialize($pic_array);
            $refund_array['pic_info'] = $info;
            $state = $refundreturn_model->addRefundreturn($refund_array, $order_info);
            if ($state) {
                $refundreturn_model->editOrderLock($order_id);

                ds_json_encode(10000, '', 1);
            }
            else {
                ds_json_encode(10001,'退款申请保存失败');
            }
        }
    }

    /**
     * 部分退款获取订单信息
     */
    public function refund_form()
    {
        $refundreturn_model = model('refundreturn');
        $condition = array();
        $reason_list = $refundreturn_model->getReasonList($condition, '', '', 'reason_id,reason_info');//退款退货原因

        $member_id = $this->member_info['member_id'];
        $order_id = intval(input('param.order_id'));
        $goods_id = intval(input('param.order_goods_id'));//订单商品表编号

        $order_model = model('order');
        $condition = array();
        $condition['buyer_id'] = $member_id;
        $condition['order_id'] = $order_id;
        $order_info = $refundreturn_model->getRightOrderList($condition, $goods_id);
        $refund_state = $refundreturn_model->getRefundState($order_info);//根据订单状态判断是否可以退款退货
        if ($refund_state == 1 && $goods_id > 0) {
            $order = array();
            $order['order_id'] = $order_info['order_id'];
            $order['order_amount'] = ds_price_format($order_info['order_amount']);
            $order['order_sn'] = $order_info['order_sn'];

            $goods = array();
            $goods_list = $order_info['goods_list'];
            $goods_info = $goods_list[0];

            $goods['order_goods_id'] = $goods_info['rec_id'];
            $goods['goods_id'] = $goods_info['goods_id'];
            $goods['goods_name'] = $goods_info['goods_name'];
            $goods['goods_type'] = get_order_goodstype($goods_info['goods_type']);
            $goods['goods_img_480'] = goods_thumb($goods_info, 480);
            $goods['goods_price'] = ds_price_format($goods_info['goods_price']);
            //$goods['goods_spec'] = $goods_info['goods_spec'];
            $goods['goods_num'] = $goods_info['goods_num'];

            $goods_pay_price = $goods_info['goods_pay_price'];//商品实际成交价
            $order_amount = $order_info['order_amount'];//订单金额
            $order_refund_amount = $order_info['refund_amount'];//订单退款金额
            if ($order_amount < ($goods_pay_price + $order_refund_amount)) {
                $goods_pay_price = $order_amount - $order_refund_amount;
            }
            $goods['goods_pay_price'] = ds_price_format($goods_pay_price);
            $reason_list=array_values($reason_list);
            ds_json_encode(10000, '', array('order' => $order, 'goods' => $goods, 'reason_list' => $reason_list));
        }
        else {
            ds_json_encode(10001,'参数错误');
        }
    }

    /**
     * 部分退款保存数据
     */
    public function refund_post()
    {
        $member_id = $this->member_info['member_id'];
        $order_id = intval(input('post.order_id'));
        $goods_id = intval(input('post.order_goods_id'));//订单商品表编号

        $order_model = model('order');
        $refundreturn_model = model('refundreturn');

        $condition = array();
        $condition['buyer_id'] = $member_id;
        $condition['order_id'] = $order_id;
        $order_info = $refundreturn_model->getRightOrderList($condition, $goods_id);
        $refund_state = $refundreturn_model->getRefundState($order_info);//根据订单状态判断是否可以退款退货
        $condition = array();
        $condition['buyer_id'] = $member_id;
        $condition['order_id'] = $order_id;
        $condition['order_goods_id'] = $goods_id;
        $refund = $refundreturn_model->getRefundreturnInfo($condition);
        if ($refund_state == 1 && $goods_id > 0 && empty($refund)) {
            $goods_list = $order_info['goods_list'];
            $goods_info = $goods_list[0];
            $refund_array = array();
            $goods_pay_price = $goods_info['goods_pay_price'];//商品实际成交价
            $order_amount = $order_info['order_amount'];//订单金额
            $order_refund_amount = $order_info['refund_amount'];//订单退款金额
            if ($order_amount < ($goods_pay_price + $order_refund_amount)) {
                $goods_pay_price = $order_amount - $order_refund_amount;
            }
            $refund_amount = floatval(input('post.refund_amount'));//退款金额
            if (($refund_amount < 0) || ($refund_amount > $goods_pay_price)) {
                $refund_amount = $goods_pay_price;
            }
            $goods_num = !empty(input('post.goods_num'))?intval(input('post.goods_num')):1;//退货数量
            if (($goods_num < 0) || ($goods_num > $goods_info['goods_num'])) {
                $goods_num = 1;
            }
            $reason_list = $refundreturn_model->getReasonList(array(), '', '', 'reason_id,reason_info');//退款退货原因
            $refund_array['reason_info'] = '';
            $reason_id = intval(input('post.reason_id'));//退货退款原因
            $refund_array['reason_id'] = $reason_id;
            $reason_array = array();
            $reason_array['reason_info'] = '其他';
            $reason_list[0] = $reason_array;
            if (!empty($reason_list[$reason_id])) {
                $reason_array = $reason_list[$reason_id];
                $refund_array['reason_info'] = $reason_array['reason_info'];
            }

            $pic_array = array();
            $pic_array['buyer'] = $_POST['refund_pic'];//上传凭证
            $info = serialize($pic_array);
            $refund_array['pic_info'] = $info;

            $trade_model = model('trade');
            $order_shipped = $trade_model->getOrderState('order_shipped');//订单状态30:已发货
            $refund_array['order_lock'] = '1';
            if ($order_info['order_state'] == $order_shipped) {
                $refund_array['order_lock'] = '2';//锁定类型:1为不用锁定,2为需要锁定
            }
            $refund_array['refund_type'] = input('post.refund_type');//类型:1为退款,2为退货
            $refund_array['return_type'] = '2';//退货类型:1为不用退货,2为需要退货
            if ($refund_array['refund_type'] != '2') {
                $refund_array['refund_type'] = '1';
                $refund_array['return_type'] = '1';
            }
            $refund_array['refund_amount'] = ds_price_format($refund_amount);
            $refund_array['goods_num'] = $goods_num;
            $refund_array['buyer_message'] = input('post.buyer_message');
            $refund_array['add_time'] = time();

            $state = $refundreturn_model->addRefundreturn($refund_array, $order_info, $goods_info);
            if ($state) {
                if ($order_info['order_state'] == $order_shipped) {
                    $refundreturn_model->editOrderLock($order_id);
                }
                ds_json_encode(10000,'',1);
            }
            else {
                ds_json_encode(10001,'退款退货申请保存失败');
            }
        }
        else {
            ds_json_encode(10001,'参数错误');
        }
    }

    /**
     * 上传凭证
     */
    public function upload_pic()
    {

        if (!empty($_FILES['refund_pic']['name'])) {
            $file_path= BASE_UPLOAD_PATH . DS .ATTACH_PATH . DS . 'refund' . DS;
            $file = request()->file('refund_pic');
            $result=$file->rule('uniqid')->validate(['ext'=>ALLOW_IMG_EXT])->move($file_path);
        }
        if ($result) {
            $file_name = $result->getFilename();
            $pic = UPLOAD_SITE_URL . '/' . ATTACH_PATH . '/refund/' . $file_name;
            ds_json_encode(10000, '', array('file_name' => $file_name, 'pic' => $pic));
        }
        else {
            ds_json_encode(10001,'图片上传失败');
        }
    }

    /**
     * 退款记录列表
     */
    public function get_refund_list()
    {
        $order_model = model('order');
        $refundreturn_model = model('refundreturn');
        $member_id = $this->member_info['member_id'];
        $refund_list = array();
        $condition = array();
        $condition['buyer_id'] = $member_id;
        $keyword_type = array('order_sn', 'refund_sn', 'goods_name');
        if (trim(input('param.k')) != '' && in_array(input('param.type'), $keyword_type)) {
            $type = input('param.type');
            $condition[$type] = array('like', '%' . input('param.k') . '%');
        }
        if (trim(input('param.add_time_from')) != '' || trim(input('param.add_time_to')) != '') {
            $add_time_from = strtotime(trim(input('param.add_time_from')));
            $add_time_to = strtotime(trim(input('param.add_time_to')));
            if ($add_time_from !== false || $add_time_to !== false) {
                $condition['add_time'] = array('time', array($add_time_from, $add_time_to));
            }
        }
        $list = $refundreturn_model->getRefundList($condition, $this->pagesize);

        if (!empty($list) && is_array($list)) {
            $admin_state = $this->getRefundStateArray('admin');
            foreach ($list as $k => $v) {
                $val = array();
                $val['refund_id'] = $v['refund_id'];
                $val['order_id'] = $v['order_id'];
                $val['refund_amount'] = ds_price_format($v['refund_amount']);
                $val['refund_sn'] = $v['refund_sn'];
                $val['order_sn'] = $v['order_sn'];
                $val['add_time'] = date("Y-m-d H:i:s", $v['add_time']);
                $val['admin_state_v'] = $v['refund_state'];
                $val['admin_state'] = $v['refund_state']==3 ? '已完成' : '处理中';
                $goods_list = array();
                if ($v['goods_id'] > 0) {
                    $goods = array();
                    $goods['goods_id'] = $v['goods_id'];
                    $goods['goods_name'] = $v['goods_name'];

                    $condition = array();
                    $condition['rec_id'] = $v['order_goods_id'];
                    $order_goods_list = $order_model->getOrdergoodsList($condition);
                    //$goods['goods_spec'] = $order_goods_list[0]['goods_spec'];

                    $goods['goods_img_480'] = goods_thumb($v, 480);
                    $goods_list[] = $goods;
                }
                else {
                    $condition = array();
                    $condition['order_id'] = $v['order_id'];
                    $order_goods_list = $order_model->getOrdergoodsList($condition);
                    foreach ($order_goods_list as $key => $value) {
                        $goods = array();
                        $goods['goods_id'] = $value['goods_id'];
                        $goods['goods_name'] = $value['goods_name'];
                        //$goods['goods_spec'] = $value['goods_spec'];
                        $goods['goods_img_480'] = goods_thumb($value, 480);
                        $goods_list[] = $goods;
                    }
                }
                $val['goods_list'] = $goods_list;
                $refund_list[] = $val;
            }
        }
        $result= array_merge(array('refund_list' => $refund_list), mobile_page($refundreturn_model->page_info));
        ds_json_encode(10000, '',$result);

    }

    /**
     * 查看退款信息
     *
     */
    public function get_refund_info()
    {
        $refundreturn_model = model('refundreturn');
        $member_id = $this->member_info['member_id'];
        $condition = array();
        $condition['buyer_id'] = $member_id;
        $condition['refund_id'] = intval(input('param.refund_id'));
        $refund_info = $refundreturn_model->getRefundreturnInfo($condition);
        if (!empty($refund_info) && is_array($refund_info)) {
            $admin_state = $this->getRefundStateArray('admin');
            $refund = array();
            $refund['refund_id'] = $refund_info['refund_id'];
            $refund['goods_id'] = $refund_info['goods_id'];
            $refund['goods_name'] = $refund_info['goods_name'];
            $refund['order_id'] = $refund_info['order_id'];
            $refund['refund_amount'] = ds_price_format($refund_info['refund_amount']);
            $refund['refund_sn'] = $refund_info['refund_sn'];
            $refund['order_sn'] = $refund_info['order_sn'];
            $refund['add_time'] = date("Y-m-d H:i:s", $refund_info['add_time']);
            $refund['goods_img_480'] = goods_thumb($refund_info, 480);
            $refund['admin_state'] =  $refund_info['refund_state'] == 3 ? '已完成' : '处理中';
            $refund['reason_info'] = $refund_info['reason_info'];
            $refund['buyer_message'] = $refund_info['buyer_message'];
            $refund['admin_message'] = $refund_info['admin_message'];

            $info['buyer'] = array();
            if (!empty($refund_info['pic_info'])) {
                $info = unserialize($refund_info['pic_info']);
            }
            $pic_list = array();
            if (is_array($info['buyer'])) {
                foreach ($info['buyer'] as $k => $v) {
                    if (!empty($v)) {
                        $pic_list[] = UPLOAD_SITE_URL . '/' . ATTACH_PATH . '/refund/' . $v;
                    }
                }
            }
            ds_json_encode(10000, '', array('refund' => $refund, 'pic_list' => $pic_list));
        }
        else {
            ds_json_encode(10001,'参数错误');
        }
    }
    /*退款审核状态*/
    function getRefundStateArray($type = '') {
        if ($type=='admin') {
            $admin_array = array(
                '1' => '处理中', '2' => '待处理', '3' => '已完成'
            ); //确认状态:1为买家或卖家处理中,2为待平台管理员处理,3为退款退货已完成
            return $admin_array;
        }
    }
}