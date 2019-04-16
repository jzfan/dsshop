<?php
/**
 * Created by 91go.miaozancn.com
 * @Date: 2019/4/14
 * @Time: 16:33
 * @auther: mark_haibo
 * @email:920793414@qq.com
 * @File:Meterlog.php
 */
namespace app\common\model;

use think\Model;
use think\Db;

class Meterlog extends Model{
    public $page_info;

    public function getPdLogList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        if ($pagesize) {
            $pdlog_list_paginate = db('meterlog')->where($condition)->field($fields)->order($order)->paginate($pagesize, false, ['query' => request()->param()]);
            $this->page_info = $pdlog_list_paginate;
            return $pdlog_list_paginate->items();
        } else {
            $pdlog_list_paginate = db('meterlog')->where($condition)->field($fields)->order($order)->limit($limit)->select();
            return $pdlog_list_paginate;
        }
    }

    public function getLogList($condition,$page ) {
        if ($page) {
            $result = db('meterlog')->where($condition)->paginate($page,false,['query' => request()->param()]);
            $this->page_info = $result;
            return $result->items();
        } else {
            $result = db('meterlog')->where($condition)->select();
            return $result;
        }
    }

}