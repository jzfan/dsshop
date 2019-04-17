<?php

namespace app\common\model;

use think\Model;

class Hostconfig extends Model {

    public $page_info;

    public function getList($condition, $page = '', $field = '*',$order='id desc') {
        if ($page) {
            $result = db('hostconfig')->where($condition)->field($field)->order($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            $list_type = db('hostconfig')->field($field)->where($condition)->order($order)->select();
            return $list_type;
        }
    }
    public function getrow($condition, $page = '', $field = '*',$order='host') {
        if ($page) {
            $result = db('hostconfig')->where($condition)->field($field)->group($order)->paginate($page,false,['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            $list_type = db('hostconfig')->field($field)->where($condition)->group($order)->select();
            return $list_type;
        }
    }

    public function editAttributevalue($condition,$data){
        return db('hostconfig')->where($condition)->update($data);
    }

    public function delAttributevalue($condition){
        return db('hostconfig')->delete($condition);
    }
}

?>
