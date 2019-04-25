<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-25
 * Time: 16:44
 */

namespace app\common\model;


use think\Model;

class Memberorderstatistics extends Model
{

    public function updateOrCreateMemberOrderStatistics($data,$condition)
    {
        $memberorderstatistics = self::get($condition);
        if (is_null($memberorderstatistics)) {
            $this->createMemberOrderStatistics($condition);
        }
        return $this->updateMemberOrderStatistics($data,$condition);
    }


    public function updateMemberOrderStatistics($data,$condition)
    {
        return self::update($data,$condition);
    }


    public function createMemberOrderStatistics($condition)
    {
        return self::create([
            'member_id'         =>  $condition['member_id'],
            'total_money'       =>  0,
            'last_thirty_money' =>  0,
            'settled_money'     =>  0,
            'unsettled_money'   =>  0,
            'updated_at'        =>  date('Y-m-d H:i:s'),
            'created_at'        =>  date('Y-m-d H:i:s'),
        ]);
    }


    public function getMemberOrderStatisticsUserIdColumn($condition)
    {
        return self::where($condition)->column('member_id');
    }

}