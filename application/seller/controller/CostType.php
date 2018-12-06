<?php

namespace app\seller\controller;

use think\Db;
use think\Loader;
use think\Page;

class CostType extends Base
{

    /**
     * 支出类别列表
     */
    function index(){
        $model = M('cost_type');
        $count = $model->where('store_id', STORE_ID)->count();
        $page = new Page($count, 10);
        $list = $model->where('store_id',STORE_ID)->order('ct_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        $this->assign('page', $page);
        $this->assign('list', $list);
        return $this->fetch();
    }
    
    public function info()
    {
        $ct_id = I('get.ct_id/d');
        if ($ct_id > 0) {
            $info = M('cost_type')->where(array('ct_id' => $ct_id, 'store_id' => STORE_ID))->find();
            if (!$info) {
                $this->error('数据不存在');
            }
            $this->assign('info', $info);
        }
        $act = empty($ct_id) ? 'add' : 'edit';
        $this->assign('act', $act);
        return $this->fetch();
    }

    public function postHandle(){
        $data = I('post.');
        if($data['act'] == 'add'){
            unset($data['ct_id']);
            $data['ct_add_time'] = time();
            $data['store_id'] = STORE_ID;
            if(M('cost_type')->where("ct_name='".$data['ct_name']."' and store_id = ".STORE_ID)->count()){
                $this->ajaxReturn(['status'=>0,'msg'=>'此类型名已存在，请更换']);
            }else{
                $r = M('cost_type')->add($data);
            }
        }

        if($data['act'] == 'edit'){
            $r = M('cost_type')->where('ct_id='.$data['ct_id'])->save($data);
        }

        if($data['act'] == 'del' && $data['del_id']>0){
            $count = M('cost')->where(['ct_id'=>$data['del_id']])->count();
            if($count>0){
                exit(json_encode('请先删除该分类下的费用支出'));
            }
            $r = M('cost_type')->where('ct_id='.$data['del_id'])->delete();
            exit(json_encode(1));
        }

        if($r){
            $this->ajaxReturn(['status'=>1,'msg'=>'操作成功','url'=>U('index')]);
        }else{
            $this->ajaxReturn(['status'=>0,'msg'=>'操作失败']);
        }
    }
}
