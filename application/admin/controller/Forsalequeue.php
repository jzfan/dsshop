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
        $where = array();
        $search_goods_name = trim(input('param.search_goods_name'));
        if ($search_goods_name != '') {
            $where['goods_name'] = array('like', '%' . $search_goods_name . '%');
        }
        $search_commonid = intval(input('param.search_commonid'));
        if ($search_commonid > 0) {
            $where['goods_commonid'] = $search_commonid;
        }
        $search_member_phone = intval(input('param.search_member_phone'));
        if ($search_member_phone) {
            $where['member_phone'] = $search_member_phone;
        }

        $memberforsalegoods_model = model('memberforsalegoods');
        $goods_list = $memberforsalegoods_model->getMemberForsaleGoodsList($where,10,"*","sortable asc");

        $this->assign('goods_list', $goods_list);
        $this->assign('show_page', $memberforsalegoods_model->page_info->render());

        $this->assign('search', $where);
        $this->setAdminCurItem('index');
        return $this->fetch();
    }


    public function store()
    {
        $step = input('get.step');
        $forsale_id = input('get.forsale_id');

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