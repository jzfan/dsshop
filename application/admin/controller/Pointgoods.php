<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-16
 * Time: 13:03
 */

namespace app\admin\controller;


class Pointgoods extends AdminControl
{

    public function index()
    {
        $goods_model = model('goods');
        /**
         * 处理商品分类
         */
        $choose_gcid = ($t = intval(input('param.choose_gcid'))) > 0 ? $t : 0;
        $gccache_arr = model('goodsclass')->getGoodsclassCache($choose_gcid, 3);
        $this->assign('gc_json', json_encode($gccache_arr['showclass']));
        $this->assign('gc_choose_json', json_encode($gccache_arr['choose_gcid']));

        /**
         * 查询条件
         */
        $where = array();
        $search_goods_name = trim(input('param.search_goods_name'));
        if ($search_goods_name != '') {
            $where['goods_name'] = array('like', '%' . $search_goods_name . '%');
        }
        $search_commonid = intval(input('param.search_commonid'));
        if ($search_commonid > 0) {
            $where['goods_commonid'] = $search_commonid;
        }
        $b_id = intval(input('param.b_id'));
        if ($b_id > 0) {
            $where['brand_id'] = $b_id;
        }
        if ($choose_gcid > 0) {
            $where['gc_id_' . ($gccache_arr['showclass'][$choose_gcid]['depth'])] = $choose_gcid;
        }
        $goods_state = input('param.goods_state');
        if (in_array($goods_state, array('0', '1'))) {
            $where['goods_state'] = $goods_state;
        }

        $goods_list = $goods_model->getGoodsCommonList($where);

        $this->assign('goods_list', $goods_list);
        $this->assign('show_page', $goods_model->page_info->render());

        $storage_array = $goods_model->calculateStorage($goods_list);
        $this->assign('storage_array', $storage_array);

        // 品牌
        $brand_list = model('brand')->getBrandPassedList(array());

        $this->assign('search', $where);
        $this->assign('brand_list', $brand_list);

        $this->assign('state', array('1' => '出售中', '0' => '仓库中'));

        $this->setAdminCurItem('index');
        return $this->fetch();
    }


    public function goods_search()
    {
        $goods_model = model('goods');
        /**
         * 处理商品分类
         */
        $choose_gcid = ($t = intval(input('param.choose_gcid'))) > 0 ? $t : 0;
        $gccache_arr = model('goodsclass')->getGoodsclassCache($choose_gcid, 3);
        $this->assign('gc_json', json_encode($gccache_arr['showclass']));
        $this->assign('gc_choose_json', json_encode($gccache_arr['choose_gcid']));

        /**
         * 查询条件
         */
        $where = array();
        $search_goods_name = trim(input('param.search_goods_name'));
        if ($search_goods_name != '') {
            $where['goods_name'] = array('like', '%' . $search_goods_name . '%');
        }
        $search_commonid = intval(input('param.search_commonid'));
        if ($search_commonid > 0) {
            $where['goods_commonid'] = $search_commonid;
        }
        $b_id = intval(input('param.b_id'));
        if ($b_id > 0) {
            $where['brand_id'] = $b_id;
        }
        if ($choose_gcid > 0) {
            $where['gc_id_' . ($gccache_arr['showclass'][$choose_gcid]['depth'])] = $choose_gcid;
        }
        $goods_state = input('param.goods_state');
        if (in_array($goods_state, array('0', '1'))) {
            $where['goods_state'] = $goods_state;
        }

        $goods_list = $goods_model->getGoodsCommonList($where);

        $this->assign('goods_list', $goods_list);
        $this->assign('show_page', $goods_model->page_info->render());

        $storage_array = $goods_model->calculateStorage($goods_list);
        $this->assign('storage_array', $storage_array);

        // 品牌
        $brand_list = model('brand')->getBrandPassedList(array());

        $this->assign('search', $where);
        $this->assign('brand_list', $brand_list);

        $this->assign('state', array('1' => '出售中', '0' => '仓库中'));

        $this->setAdminCurItem('goods_search');
        return $this->fetch();
    }


    public function add_pointgoods()
    {
        $goods_id = input("param.goods_id");
        //检测商品
        $goods_model = model("Goods");
        $goods = $goods_model->getGoodsInfo(array("goods_id"=>$goods_id,"goods_state"=>1));
        if (!$goods) {
            //商品不存在或已下架
        }

        if(request()->isPost()) {
            $pointgoods_model = model("Pointgoods");
            $pointgoods_validate = validate('Pointgoods');
            $params = input("param.");

            if (!$pointgoods_validate->scene("add_pointgoods")->check($params)) {

            }
            \think\Db::startTrans();
            try {
                //添加积分商品
                $pointgoods_model->add_pointgoods($params);
                //更新积分商品规格
                $pointgoods_model->updateGoodsSpecValue($goods_id);
                \think\Db::commit();

            } catch (\Exception $exception) {
                \think\Db::rollback();
            }

        }
        $this->assign("goods",$goods);
        return $this->fetch();
    }


    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index',
                'text' => lang('积分商品管理'),
                'url' => url('Pointgoods/index')
            ),
            array(
                'name' => 'goods_search',
                'text' => lang('添加积分商品'),
                'url' => url('Pointgoods/goods_search')
            )
        );
        return $menu_array;
    }
}