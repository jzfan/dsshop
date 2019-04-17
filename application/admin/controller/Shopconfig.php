<?php
/**
 * Created by 91go.miaozancn.com
 * @Date: 2019/4/17
 * @Time: 13:53
 * @auther: mark_haibo
 * @email:920793414@qq.com
 * @File:Shopconfig.php
 */

namespace app\admin\controller;

use think\Lang;

class Shopconfig extends AdminControl
{
    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'admin/lang/' . config('default_lang') . '/config.lang.php');
    }

    public function index()
    {
        $model = model('hostconfig');
        $list = $model->getrow('', 10);
        $this->assign('list', $list);
        $this->assign('show_page', $model->page_info->render());
        $this->setAdminCurItem('admin');
        return $this->fetch('index');
    }

    public function add()
    {
        if (!request()->post()) {
            return $this->fetch('add');
        } else {
            $attr = $_POST['at_value'];
            $host = $_POST['type_name'];
            foreach ($attr as $k => $v) {
                if (!empty($v)) {
                    $arr[$k]['key'] = $v[$k];
                    $arr[$k]['value'] = $v['value'];
                    $arr[$k]['host'] = $host;
                }
            }
            $result   =  db('hostconfig')->insertAll($arr);
            if ($result>=0){
                dsLayerOpenSuccess(lang('配置成功'));
            }
        }
    }

    public function view(){
        $arr['host']=input('host');
        $model = model('hostconfig');
        $list = $model->getList($arr, 10);
        $this->assign('list', $list);
        $this->assign('show_page', $model->page_info->render());
        $this->setAdminCurItem('admin');
        return $this->fetch('view');
    }

    public function edit(){
        $arr['host']=input('host');
        $model = model('hostconfig');
        $list = $model->getList($arr, 10);
        $this->assign('list', $list);
        $this->assign('type', $arr);
        $this->setAdminCurItem('admin');
        $this->assign('show_page', $model->page_info->render());
        return $this->fetch('edit');
    }

    public function doit(){
        if (!request()->post()){
            ds_json_encode(10003, '无效请求方式!');
        }else{
                $arr['id']=$_POST['id'];
                $data['key']=$_POST['key'];
                $data['value']=$_POST['value'];
                $mode=model('hostconfig');
                $res=$mode->editAttributevalue($arr,$data);
                if ($res){
                    ds_json_encode(10000, '修改成功!');
                }else{
                    ds_json_encode(10002, '修改成功!');
                }

        }
    }

    public function dodel(){
        if (!request()->post()){
            ds_json_encode(10003, '无效请求方式!');
        }else{
            $arr['id']=$_POST['id'];
            $mode=model('hostconfig');
            $res=$mode->delAttributevalue($arr);
            if ($res){
                ds_json_encode(10000, '删除成功!');
            }else{
                ds_json_encode(10002, '删除成功!');
            }

        }
    }


    protected function getAdminItemList()
    {
//        $menu_array = array(
//            array(
//                'name' => 'view',
//                'text' => '查看配置',
//                'url' => "javascript:dsLayerOpen('" . url('Shopconfig/view') . "','查看配置')"
//            ),
//        );

        if (request()->action() == 'add' || request()->action() == 'index') {
            $menu_array[] = array(
                'name' => 'add',
                'text' => '新增配置',
                'url' => "javascript:dsLayerOpen('" . url('Shopconfig/add') . "','新增配置')"
            );
        }
        if (request()->action() == 'view' ) {
            $menu_array[] = array(
                'name' => 'view',
                'text' => '查看配置',
                'url' => "javascript:dsLayerOpen('" . url('Shopconfig/view') . "','查看配置')"
            );
        }
        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit',
                'text' => '编辑配置',
                'url' => "javascript:dsLayerOpen('" . url('Shopconfig/edit') . "','编辑配置')"
            );
        }

        return $menu_array;
    }
}