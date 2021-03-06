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
        $pointgoods_model = model('Pointgoods');
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

        $goods_list = $pointgoods_model->getGoodsCommonList($where, '*', 15);
        $this->assign('goods_list', $goods_list);
        $this->assign('show_page', $pointgoods_model->page_info->render());

        $storage_array = $pointgoods_model->calculateStorage($goods_list);
        $this->assign('storage_array', $storage_array);
        // 品牌
        $brand_list = model('brand')->getBrandPassedList(array());

        $this->assign('search', $where);
        $this->assign('brand_list', $brand_list);

        $this->assign('state', array('1' => '出售中', '0' => '仓库中'));

        $this->setAdminCurItem('index');
        return $this->fetch();
    }


    public function get_goods_list()
    {
        $common_id = input('param.commonid');
        if (empty($common_id)) {
            $this->error(lang('param_error'));
        }

        $map['goods_commonid'] = $common_id;

        $goods_model = model('goods');
        $common_info = $goods_model->getGoodeCommonInfo($map,'spec_name');

        $pointgoods_model = model('Pointgoods');
        $goods_list = $pointgoods_model->getGoodsList($map);

        $spec_name = array_values((array) unserialize($common_info['spec_name']));
        foreach ($goods_list as $key => $val) {
            $goods_spec = array_values((array) unserialize($val['goods_spec']));
            $spec_array = array();
            foreach ($goods_spec as $k => $v) {
                $spec_array[] = '<div class="goods_spec">' . $spec_name[$k] . ':' . '<em title="' . $v . '">' . $v . '</em>' . '</div>';
            }
            $goods_list[$key]['goods_image'] = goods_cthumb($val['goods_image']);
            $goods_list[$key]['goods_spec'] = implode('', $spec_array);
            $goods_list[$key]['url'] = url('Home/Goods/index', array('goods_id' => $val['goods_id']));
        }
        return json_encode($goods_list);
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
        $common_id = input("param.common_id");
        //检测商品
        $goods_model = model("Goods");
        $goods_list = $goods_model->getGoodsList(array("goods_commonid"=>$common_id,"goods_state"=>1));
        if (!$goods_list) {
            //商品不存在或已下架
            $this->error("商品不存在或以下架");
        }

        if(request()->isPost()) {
            $pointgoods_model = model("Pointgoods");
            $pointgoods_validate = validate('Pointgoods');
            $params = input("param.");
            if (!$pointgoods_validate->scene("add_pointgoods")->check($params)) {
                $this->error($pointgoods_validate->getError());
            }
            \think\Db::startTrans();
            try{
                //添加或者更新积分商品
                $pointgoods_model->addOrUpdatePointGoods($params);
                // 扣除商品库库存

                \think\Db::commit();
                dsLayerOpenSuccess("添加成功");
            } catch (\Exception $exception) {
                \think\Db::rollback();
            }
            $this->error("添加失败");
        }
        $this->assign("goods_list",$goods_list);
        return $this->fetch();
    }


    public function edit_pointgoods()
    {
        $common_id = input("param.common_id");
        //检测商品
        $goods_model = model("Pointgoods");
        $goods_list = $goods_model->getGoodsList(array("goods_commonid"=>$common_id,"goods_state"=>1));
        if (!$goods_list) {
            //商品不存在或已下架
            $this->error("商品不存在或以下架");
        }

        if(request()->isPost()) {
            $pointgoods_model = model("Pointgoods");
            $pointgoods_validate = validate('Pointgoods');
            $params = input("param.");
            if (!$pointgoods_validate->scene("add_pointgoods")->check($params)) {
                $this->error($pointgoods_validate->getError());
            }
            \think\Db::startTrans();
            try{
                //添加或者更新积分商品
                $pointgoods_model->addOrUpdatePointGoods($params);
                // 扣除商品库库存

                \think\Db::commit();
                dsLayerOpenSuccess("添加成功");
            } catch (\Exception $exception) {
                \think\Db::rollback();
            }
            $this->error("添加失败");
        }
        $this->assign("goods_list",$goods_list);
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