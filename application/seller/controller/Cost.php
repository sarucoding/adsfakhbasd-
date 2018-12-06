<?php

namespace app\seller\controller;

use think\Db;
use think\Loader;
use think\Page;

class Cost extends Base
{

    /**
     * 费用支出列表
     */
    function index(){
        $cost = M('cost');
        $keywords = I('keywords','');
        $where = 'a.store_id = '.STORE_ID;
        if($keywords){
            $where .= ' and (cost_name like "%'.$keywords.'%" or cost_remark like "%'.$keywords.'%")';
        }
        $count = $cost->alias('a')->join('__COST_TYPE__ b','a.ct_id = b.ct_id')->where($where)->count();
        $page = new Page($count, 10);
        $list = $cost->alias('a')->join('__COST_TYPE__ b','a.ct_id = b.ct_id')->where($where)->order('cost_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->assign('keywords', $keywords);
        return $this->fetch();
    }

    public function info()
    {
        $cost_id = I('get.cost_id/d');
        if ($cost_id > 0) {
            $info = M('cost')->where(array('cost_id' => $cost_id, 'store_id' => STORE_ID))->find();
            if (!$info) {
                $this->error('数据不存在');
            }
            $this->assign('info', $info);
        }
        $act = empty($cost_id) ? 'add' : 'edit';
        $ct_lists = M('cost_type')->where('store_id',STORE_ID)->select();
        if(!$ct_lists){
            $this->error('请先添加支出类型');
        }
        $this->assign('act', $act);
        $this->assign('ct_lists', $ct_lists);
        return $this->fetch();
    }

    public function postHandle(){
        $data = I('post.');
        if($data['act'] == 'add'){
            unset($data['cost_id']);
            $data['cost_add_time'] = time();
            $data['store_id'] = STORE_ID;
            $data['seller_id'] = session('seller.seller_id');
            $r = M('cost')->add($data);
        }

        if($data['act'] == 'edit'){
            $r = M('cost')->where('cost_id='.$data['cost_id'])->save($data);
        }

        if($data['act'] == 'del' && $data['del_id']>0){
            $r = M('cost')->where('cost_id='.$data['del_id'])->delete();
            exit(json_encode(1));
        }

        if($r){
            $this->ajaxReturn(['status'=>1,'msg'=>'操作成功','url'=>U('index')]);
        }else{
            $this->ajaxReturn(['status'=>0,'msg'=>'操作失败']);
        }
    }
}
