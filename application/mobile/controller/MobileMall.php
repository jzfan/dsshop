<?php

namespace app\mobile\controller;

class MobileMall extends MobileHome {

    public function _initialize() {
        parent::_initialize();
    }

    /**
     * 通过 key 获取 用户ID
     * @return int
     */
    protected function getMemberIdIfExists() {
        $key = input('post.key');
        if (empty($key)) {
            $key = input('get.key');
        }

        $mbusertoken_model = model('mbusertoken');
        $mb_user_token_info = $mbusertoken_model->getMbusertokenInfoByToken($key);
        if (empty($mb_user_token_info)) {
            return 0;
        }

        return $mb_user_token_info['member_id'];
    }

}

?>
