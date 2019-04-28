<?php
/**
 * Created by PhpStorm.
 * User: baolinqiang
 * Date: 2019-04-25
 * Time: 16:52
 */

namespace app\common\behavior;


use think\Db;

class MiaomiRed
{

    public function forsaleOrderFinished($order_info)
    {
        $now_time = date('Y-m-d H:i:s');
        $memberorderstatistics_model = model('Memberorderstatistics');

        $get_condition = [
            'member_id' => $order_info['member_id'],
        ];
        $get_data = [
            'unsettled_money' => Db::raw('unsettled_money+' . $order_info['money']),
            'update_at' => $now_time,
        ];
        $memberorderstatistics = $memberorderstatistics_model->updateOrCreateMemberOrderStatistics($get_data, $get_condition);

        if ($memberorderstatistics->unsettled_money < 10000) {
            return FALSE;
        }

        $inviter_array = model('Member')->getUserInviterIds($order_info['member_id']);

        // 剔除直推
        array_shift($inviter_array);

        if (!empty($inviter_string)) {
            // 检测获得者领取条件（前30天消费金额满足1W）
            $column_condition = [
                'member_id'         => ['in',$inviter_array],
                'last_thirty_money' => ['egt',10000]
            ];
            $member_array = $memberorderstatistics_model->getMemberOrderStatisticsUserIdColumn($column_condition);

            if (!$member_array) {

                $give_number = floor($memberorderstatistics->unsettled_money/10000);

                $member_miaomi_array = $this->calculateMemberMiaomi($inviter_array, $member_array, $give_number);

                //第三步：发放秒米,增加秒米发放记录
                foreach ($member_miaomi_array as $inviter_id => $miaomi) {

                }

                $settled_money = $give_number * 10000;
                $update_data = [
                    'unsettled_money' => Db::raw('unsettled_money+' . $settled_money),
                    'settled_money' => Db::raw('unsettled_money+' . $settled_money),
                    'update_at' => $now_time,
                ];
                $memberorderstatistics_model->updateMemberOrderStatistics($update_data, $get_condition);
            }
        }

        return TRUE;
    }


    protected function calculateMemberMiaomi($inviter_array, $member_array, $give_number)
    {
        $ten_member = [];
        foreach ($inviter_array as $member_id) {
            if (count($ten_member) >= 10) {
                break;
            }
            if (!in_array($member_id,$member_array)) {
                $ten_member[] = $member_id;
            }
        }

        list($first, $last) = $this->getMiaomiConfig();

        shuffle($first);
        shuffle($last);

        $member_miaomi_array = array();
        foreach ($ten_member as $key => $value) {
            if ($key < 5) {
                $member_miaomi_array[$value] = $first[$key] * $give_number;
            } else {
                $member_miaomi_array[$value] = $last[$key - 5] * $give_number;
            }
        }
        return $member_miaomi_array;
    }


    protected function getMiaomiConfig()
    {
        $config_model = model('config');
        $first_forsale_red_jackpot = $config_model->getOneConfigByCode('first_forsale_red_jackpot');
        $last_forsale_red_jackpot = $config_model->getOneConfigByCode('last_forsale_red_jackpot');

        $first = explode(',',$first_forsale_red_jackpot);
        $last = explode(',',$last_forsale_red_jackpot);

        for ($i = 0; $i < 5; ++$i) {
            $first[$i] = isset($first[$i]) ? floatval($first[$i]) : 1;
            $last[$i] = isset($last[$i]) ? floatval($last[$i]) : 1;
        }

        return [$first,$last];
    }
}