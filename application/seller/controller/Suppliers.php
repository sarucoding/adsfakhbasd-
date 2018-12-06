<?php

namespace app\seller\controller;

use think\Db;
use think\Loader;
use think\Page;

class Suppliers extends Base
{

    public function index()
    {
        $map = array();
        $map['store_id'] = STORE_ID;
        $suppliers_name = trim(I('suppliers_name'));
        if ($suppliers_name) {
            $map['suppliers_name'] = array('like', "%$suppliers_name%");
        }
        $suppliers_list = M('suppliers')->where($map)->select();
        $sum = M('suppliers')->where($map)->sum('suppliers_money');
        $this->assign('suppliers_list', $suppliers_list);
        $this->assign('sum', $sum);
        return $this->fetch();
    }
    /**
     * 资金手动调节
     */
    function edit_account(){
        if(IS_POST){
            $data = input('post.');
            $validate = $this->validate($data, [
                'suppliers_id' => 'integer|require',
                'log_money' => 'number|gt:0|require',
                'log_info' => 'require',
            ]);
            if ($validate !== true)
                $this->ajaxReturn(['status' => 0, 'msg' => '参数错误']);
            $suppliers = M('suppliers')->where('suppliers_id',$data['suppliers_id'])->find();
            if(!$suppliers){
                $this->ajaxReturn(['status' => 0, 'msg' => '该供应商不存在']);
            }
            $res = suppliersAccountLog($data['suppliers_id'],STORE_ID,-$data['log_money'],$data['log_info'],$data['log_img']);
            if($res)
                $this->ajaxReturn(['status' => 1, 'msg' => '操作成功', 'result' => $data]);
            else
                $this->ajaxReturn(['status' => 0, 'msg' => '操作失败']);
        }
        $suppliers_id = I('suppliers_id/d');
        $suppliers = M('suppliers')->where('suppliers_id',$suppliers_id)->find();
        if(!$suppliers){
            $this->error('该供应商不存在');
        }
        $this->assign('suppliers', $suppliers);
        return $this->fetch();
    }
    /**
     * 交易流水
     */
    function account_log(){
        $suppliers_id = I('suppliers_id/d');
        $suppliers = M('suppliers')->where('suppliers_id',$suppliers_id)->find();
        if(!$suppliers){
            $this->error('该供应商不存在');
        }
        $count = M('suppliers_account_log')->where(['store_id'=>STORE_ID,'suppliers_id'=>$suppliers_id])->count();
        $page = new Page($count, 10);
        $list = M('suppliers_account_log')->where(['store_id'=>STORE_ID,'suppliers_id'=>$suppliers_id])->order('log_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->assign('suppliers', $suppliers);
        return $this->fetch();
    }
}
