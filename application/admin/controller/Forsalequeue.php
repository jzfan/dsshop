<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-24
 * Time: 17:38
 */

namespace app\admin\controller;


class Forsalequeue extends AdminControl
{

    public function index()
    {
        $memberforsalegoods_model = model('memberforsalegoods');
        $where['goods_state'] = 1;
        $search_goods_name = trim(input('param.search_goods_name'));
        if ($search_goods_name != '') {
            $where['goods_commonid'] = ['like',"%{$search_goods_name}%"];
        }
        $search_commonid = intval(input('param.search_commonid'));
        if ($search_commonid > 0) {
            $where['goods_commonid'] = $search_commonid;
        }
        $search_phone = input('param.search_phone');
        if ($search_phone > 0) {
            $where['member_phone'] = $search_phone;
        }

        $goods_list = $memberforsalegoods_model->getMemberForsaleGoodsList($where,10,"*","sortable asc");

        foreach ($goods_list as &$goods) {
            switch ($goods['goods_state']) {
                case 0:
                    $goods['goods_state'] = "等待挂售";
                    break;
                case 1:
                    $goods['goods_state'] = "挂售中";
                    break;
                case 2:
                    $goods['goods_state'] = "挂售完成";
                    break;
            }
        }

        $this->assign('goods_list', $goods_list);
        $this->assign('show_page', $memberforsalegoods_model->page_info->render());

        $this->assign('search', $where);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }

    public function queue()
    {
        $memberforsalegoods_model = model('memberforsalegoods');
        $where['goods_state'] = 1;
        $search_commonid = intval(input('param.goods_commonid'));
        if ($search_commonid > 0) {
            $where['goods_commonid'] = $search_commonid;
        }

        $goods_list = $memberforsalegoods_model->getMemberForsaleGoodsList($where,10,"*","sortable asc");

        foreach ($goods_list as &$goods) {
            switch ($goods['goods_state']) {
                case 0:
                    $goods['goods_state'] = "等待挂售";
                    break;
                case 1:
                    $goods['goods_state'] = "挂售中";
                    break;
                case 2:
                    $goods['goods_state'] = "挂售完成";
                    break;
            }
        }
        $this->assign('goods_list', $goods_list);
        $this->assign('show_page', $memberforsalegoods_model->page_info->render());

        $this->assign('search', $where);
        $this->setAdminCurItem('queue');
        return $this->fetch();
    }


    public function store()
    {
        $type = intval(input('get.type'));
        $forsale_id = intval(input('get.forsale_id'));

        if ($forsale_id <= 0 || !in_array($type,[-1,0,1])) {
            $this->error('参数错误');
        }

        $memberforsalegoods_model = model("memberforsalegoods");

        $memberforsalegoods = $memberforsalegoods_model->get($forsale_id);
        if (is_null($memberforsalegoods)) {
            $this->error('参数错误');
        }

        switch($type) {
            case 1:
                $step = $memberforsalegoods->sortable - 1;
                break;
            case -1:
                $step = $memberforsalegoods->sortable + 1;
                break;
            default:
                $step = 1;
                break;
        }

        if ($memberforsalegoods->sortable == 1 && $step == 0) {
            $this->error('该商品已经是第一位，无需修改');
        }

        $last = $memberforsalegoods_model->where("goods_commonid",$memberforsalegoods->goods_commonid)->order('sortable','desc')->find();
        if ($last->sortable < $step) {
            $this->error('该商品已经是最后一位，无需修改');
        }

        $bool = model("memberforsalegoods")->updateMemberForsaleGoodsSortable($forsale_id, $step);
        if ($bool) {
            $this->success('编辑成功');
        }
        $this->error('编辑失败');
    }


    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => lang('挂售商品列表'),
                'url' => url('Forsalequeue/index')
            ),
        );
        return $menu_array;
    }
}