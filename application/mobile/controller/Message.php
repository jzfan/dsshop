<?php
/**
 * Created by 91go.miaozancn.com
 * @Date: 2019/4/24
 * @Time: 15:07
 * @auther: mark_haibo
 * @email:920793414@qq.com
 * @File:Message.php
 */

namespace app\mobile\controller;


class Message extends MobileHome
{
    public function index()
    {
        $result = array();
        $key = input('param.key');
        //消息
        if ($key != '') {
            $article_model = model('article');
            $condition = array();
            $condition['article_show'] = '1';
            $condition['ac_id'] = '1';
            $article_list = $article_model->getArticleList($condition, $this->pagesize);
            foreach ($article_list as $k => $item) {
                $article_list[$k]['article_time'] = date('Y-m-d H:i:s', $item['article_time']);
            }
            $result['article'] = array_merge(array('article_list' => $article_list, 'article_list_name' => '商城公告'), mobile_page(is_object($article_model->page_info) ? $article_model->page_info : ''));
            $this->isLogin();//判断是否登录
            $con['to_member_id'] = $this->member_info['member_id'];
            $con['message_state'] = 0;
            $con['message_type'] = 0;
            $message = model('message');
            $message_list = $message->getMessageList($con, $this->pagesize);
            foreach ($message_list as $k => $v) {
                $message_list[$k]['message_time'] = date('Y-m-d H:i:s', $v['message_time']);
                $message_list[$k]['message_update_time'] = date('Y-m-d H:i:s', $v['message_update_time']);
                unset($message_list[$k]['message_parent_id']);
                unset($message_list[$k]['message_open']);
                unset($message_list[$k]['del_member_id']);
                unset($message_list[$k]['message_ismore']);
            }
            $name = $message_list[0]['message_title'];
            $result['message'] = array_merge(array('message_list' => $message_list, 'message_list_name' => $name), mobile_page(is_object($message->page_info) ? $message->page_info : ''));
        } else {
            //公告
            $article_model = model('article');
            $condition = array();
            $condition['article_show'] = '1';
            $condition['ac_id'] = '1';
            $article_list = $article_model->getArticleList($condition, $this->pagesize);
            foreach ($article_list as $k => $item) {
                $article_list[$k]['article_time'] = date('Y-m-d H:i:s', $item['article_time']);
            }
            $result = array_merge(array('notice' => $article_list, 'notice_name' => '商城公告'), mobile_page(is_object($article_model->page_info) ? $article_model->page_info : ''));
        }
        ds_json_encode(10000, '获取成功', $result);
    }

    public function getmessagelist()
    {
        $this->isLogin();
        $con['to_member_id'] = $this->member_info['member_id'];
        $message = model('message');
        $message_list = $message->getMessageList($con, $this->pagesize);
        foreach ($message_list as $k => $v) {
            $message_list[$k]['message_time'] = date('Y-m-d H:i:s', $v['message_time']);
            $message_list[$k]['message_update_time'] = date('Y-m-d H:i:s', $v['message_update_time']);
            if($v['message_state']==3){
                $message_list[$k]['is_read']=1;
            }else{
                $message_list[$k]['is_read']=0;
            }
            unset($message_list[$k]['message_parent_id']);
            unset($message_list[$k]['message_open']);
            unset($message_list[$k]['del_member_id']);
            unset($message_list[$k]['message_ismore']);
        }
        $result = array_merge(array('list' => $message_list), mobile_page(is_object($message->page_info) ? $message->page_info : ''));
        ds_json_encode(10000, '获取成功', $result);
    }

    public function getdetail()
    {
        $this->isLogin();
        $message = model('message');
        $con['message_id'] = input('param.message_id');
        $res = $message->where('message_id', $con['message_id'])->find();
        if($res['message_state']==3){
            $res['is_read']=1;
        }else{
            $res['is_read']=0;
        }
        $res['message_time'] = date('Y-m-d H:i:s', $res['message_time']);
        if (!empty($res)) {

            ds_json_encode(10000, '获取成功', $res);
        } else {
            ds_json_encode(10001, '获取失败');
        }
    }

    public function updatestatus()
    {
        $this->isLogin();
        $message = model('message');
        $con['to_member_id'] = $this->member_info['member_id'];
        $con['message_id'] = input('param.message_id');
        $data['read_member_id'] = $con['to_member_id'];
        $data['message_state'] = 3;
        $data['message_update_time'] = time();
        $res = $message->where('message_id', $con['message_id'])->isUpdate($data);
        if ($res > 0) {
            ds_json_encode(10000, '更新成功');
        } else {
            ds_json_encode(10001, '更新失败');
        }
    }

    protected function isLogin()
    {
        $mbusertoken_model = model('mbusertoken');
        $key = input('key');
        $mb_user_token_info = $mbusertoken_model->getMbusertokenInfoByToken($key);
        if (empty($mb_user_token_info)) {
            ds_json_encode(10001, '请登录', array('login' => '0'));
        }
        $member_model = model('member');
        $this->member_info = $member_model->getMemberInfoByID($mb_user_token_info['member_id']);
        $meter_second = model('member')->where('member_id', $mb_user_token_info['member_id'])->find();
        if (empty($this->member_info)) {
            ds_json_encode(10001, '参数错误', array('login' => '0'));
        } else {
            $this->member_info['member_clienttype'] = $mb_user_token_info['member_clienttype'];
            $this->member_info['meter_second'] = $meter_second['meter_second'];
            $this->member_info['member_openid'] = $mb_user_token_info['member_openid'];
            $this->member_info['member_token'] = $mb_user_token_info['member_token'];
            $level_name = $member_model->getOneMemberGrade($mb_user_token_info['member_id']);
            $this->member_info['level_name'] = $level_name['level_name'];
            //考虑到模型中session
            if (session('member_id') != $this->member_info['member_id']) {
                //避免重复查询数据库
                $member_model->createSession(array_merge($this->member_info, $level_name));
            }
        }
    }
}