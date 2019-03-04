<?php
namespace app\mobile\controller;
class Memberfeedback extends MobileMember
{
    public function _initialize()
    {
        parent::_initialize(); 
    }

    /**
     * 添加反馈
     */
    public function feedback_add()
    {
        $mbfeedback_model = model('mbfeedback');

        $param = array();
        $param['mbfb_content'] = input('post.feedback');
        $param['mbfb_type'] = $this->member_info['member_clienttype'];
        $param['mbfb_time'] = TIMESTAMP;
        $param['member_id'] = $this->member_info['member_id'];
        $param['member_name'] = $this->member_info['member_name'];

        $result = $mbfeedback_model->addMbfeedback($param);

        if ($result) {
            ds_json_encode(10000,'',1);
        }
        else {
            ds_json_encode(10001,'保存失败');
        }
    }
}