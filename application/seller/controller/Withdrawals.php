<?php

namespace app\seller\controller;

use think\Db;
use think\Loader;
use think\Page;

class Withdrawals extends Base
{

    /**
     * 提现列表
     */
    function index(){
        $withdrawals = M('withdrawals');
        $keywords = I('keywords','');
        $where = 'a.store_id = '.STORE_ID;
        if($keywords){
            $where .= ' and realname like "%'.$keywords.'%"';
        }
        $count = $withdrawals->alias('a')->join('__USERS__ b','a.user_id = b.user_id')->where($where)->count();
        $page = new Page($count, 10);
        $list = $withdrawals->alias('a')->join('__USERS__ b','a.user_id = b.user_id')->where($where)->field('a.*,b.realname as user_name')->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->assign('keywords', $keywords);
        return $this->fetch();
    }

    public function info()
    {
        $id = I('get.id/d');
        if (!$id) {
            $this->error('参数错误');
        }
        $info = M('withdrawals')->alias('a')->join('__USERS__ b','a.user_id = b.user_id')->where(array('id' => $id, 'store_id' => STORE_ID))->field('a.*,b.realname as user_name')->find();
        if (!$info) {
            $this->error('数据不存在');
        }
        $this->assign('info', $info);
        return $this->fetch();
    }

    public function postHandle(){
        $id = I('id/d');
        $status = I('status/d');
        $remark = I('remark');
        if(!in_array($status,[1,2])){
            $this->ajaxReturn(['status'=>0,'msg'=>'参数错误']);
        }
        $with = M('withdrawals')->where(['id'=>$id,'store_id'=>STORE_ID,'status'=>0])->find();
        if(!$with){
            $this->ajaxReturn(['status'=>0,'msg'=>'提现记录不存在或已被处理']);
        }
        $r = M('withdrawals')->where(['id'=>$id])->save(['status'=>$status,'remark'=>$remark]);
        if($r){
            if($status == 2){
                $re_id = M('user_relation_store')->where(['user_id'=>$with['user_id'],'store_id'=>STORE_ID])->getField('re_id');
                $res = userStoreAccountLog($re_id,STORE_ID,$with['money'],'提现申请审核未通过，提现金额返回至余额');
                if(!$res){
                    $this->ajaxReturn(['status'=>0,'msg'=>'操作失败']);
                }
            }
            $this->ajaxReturn(['status'=>1,'msg'=>'操作成功','url'=>U('index')]);
        }else{
            $this->ajaxReturn(['status'=>0,'msg'=>'操作失败']);
        }
    }
}
