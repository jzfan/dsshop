<?php

namespace app\mobile\controller;

use think\Lang;

class Goodsclass extends MobileMall {

    public function _initialize() {
        parent::_initialize();
        Lang::load(APP_PATH . 'mobile/lang/'.config('default_lang').'/category.lang.php');
    }

    public function index() {
        $gc_id = intval(input('get.gc_id'));
        if ($gc_id > 0) {
            $date = $this->_get_class_list($gc_id);
            ds_json_encode(10000, '',$date);
        } else {
            $this->_get_root_class();
        }
    }

    /**
     * 返回一级分类列表
     */
    private function _get_root_class() {
        $goodsclass_model = model('goodsclass');
        

        $goods_class_array = model('goodsclass')->getGoodsclassForCacheModel();

        $class_list = $goodsclass_model->getGoodsclassListByParentId(0);
        
        foreach ($class_list as $key => $value) {
            

            $class_list[$key]['text'] = '';
            if(isset($goods_class_array[$value['gc_id']]['child'])){
            $child_class_string = $goods_class_array[$value['gc_id']]['child'];
            $child_class_array = explode(',', $child_class_string);
            foreach ($child_class_array as $child_class) {
                $class_list[$key]['text'] .= $goods_class_array[$child_class]['gc_name'] . '/';
            }
        }
            $class_list[$key]['text'] = rtrim($class_list[$key]['text'], '/');
        }
        ds_json_encode(10000, '',array('class_list' => $class_list));
    }

    /**
     * 根据分类编号返回下级分类列表
     */
    private function _get_class_list($gc_id) {
        $goods_class_array = model('goodsclass')->getGoodsclassForCacheModel();

        $goods_class = $goods_class_array[$gc_id];

        if (empty($goods_class['child'])) {
            //无下级分类返回0
            return array('class_list' => array());
        } else {
            //返回下级分类列表
            $class_list = array();
            $child_class_string = $goods_class_array[$gc_id]['child'];
            $child_class_array = explode(',', $child_class_string);
            
            foreach ($child_class_array as $child_class) {
                $class_item = array();
                $class_item['gc_id'] = '';
                $class_item['gc_name'] = '';
                $class_item['gc_id'] .= $goods_class_array[$child_class]['gc_id'];
                $class_item['gc_name'] .= $goods_class_array[$child_class]['gc_name'];
                $pic_name = BASE_UPLOAD_PATH . '/' . ATTACH_COMMON . '/category-pic-' . $goods_class_array[$child_class]['gc_id'] . '.jpg';
                $class_item['image']='';
                if (file_exists($pic_name)) {
                    $class_item['image'] = UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/category-pic-' . $goods_class_array[$child_class]['gc_id'] . '.jpg';
                }
                $class_list[] = $class_item;
            }
            return array('class_list' => $class_list);
        }
    }

    /**
     * 获取全部子集分类
     */
    public function get_child_all() {
        $gc_id = intval(input('get.gc_id'));
        $data = array();
        if ($gc_id > 0) {
            $data = $this->_get_class_list($gc_id);
            $mbcategory_model = model('mbcategory');
            $mb_categroy = $mbcategory_model->getMbcategoryList(array());
            $mb_categroy = array_under_reset($mb_categroy, 'gc_id');
            if (!empty($mb_categroy[$gc_id])) {
                $data['image'] = UPLOAD_SITE_URL . DS . ATTACH_MOBILE . DS . 'category' . DS . $mb_categroy[$gc_id]['gc_thumb'];
            } else {
                $data['image'] = '';
            }
            if (!empty($data['class_list'])) {
                foreach ($data['class_list'] as $key => $val) {
                    $d = $this->_get_class_list($val['gc_id']);
                    $data['class_list'][$key]['child'] = $d['class_list'];
                }
            }
        }
        ds_json_encode(10000, '',$data);
    }

}

?>
