<?php

namespace app\mobile\controller;

class Brand extends MobileMall {
    
    public function _initialize() {
        parent::_initialize();
    }
    
    public function recommend_list() {
        $brand_list = model('brand')->getBrandPassedList(array('brand_recommend' => '1'), 'brand_id,brand_name,brand_pic');
        if (!empty($brand_list)) {
            foreach ($brand_list as $key => $val) {
                $brand_list[$key]['brand_pic'] = brand_image($val['brand_pic']);
            }
        }
        ds_json_encode(10000, '', array('brand_list' => $brand_list));

    }

}
