<?php

namespace app\seller\controller;

use think\Db;
use think\Loader;
use think\Page;

class Settlement extends Base
{

    public function index()
    {
        $settlement = M('settlement');
        $where = 'store_id = '.STORE_ID;
        $on = 'a.user_id = b.user_id';
        $keywords = I('keywords','');
        if($keywords){
            $on .= ' and b.realname like "%'.$keywords.'%"';
        }
        $count = $settlement->alias('a')->join('__USERS__ b',$on)->where($where)->count();
        $Page = new Page($count, 10);
        $list = $settlement->alias('a')->join('__USERS__ b',$on)->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('settle_id desc')->select();
        $this->assign('page', $Page);
        $this->assign('list', $list);
        $this->assign('keywords', $keywords);
        return $this->fetch();
    }

    /**
     * 冲账和折让post都可以
     */
    public function charge(){
        if(IS_POST){
            $data['sl_money'] = I('sl_money/d');
            $data['settle_id'] = I('settle_id/d');
            $data['sl_info'] = I('sl_info');
            $data['sl_img'] = I('sl_img');
            $data['type'] = I('type/d',0);
            $settle = M('settlement')->where(['settle_id'=>$data['settle_id']])->find();
            if(!$settle){
                $this->ajaxReturn(['status'=>0,'msg'=>'非法操作']);
            }
            if($data['sl_money'] <= 0){
                $this->ajaxReturn(['status'=>0,'msg'=>'金额有误']);
            }
            if($settle['settle_amount'] < $data['sl_money']){
                $this->ajaxReturn(['status'=>0,'msg'=>'金额不能大于应收金额']);
            }
            $res = settlementPayLog($settle,$data['sl_money'],$data['type'],$data['sl_info'],$data['sl_img']);
            if($res){
                $this->ajaxReturn(['status'=>1,'msg'=>'操作成功']);
            }else{
                $this->ajaxReturn(['status'=>0,'msg'=>'操作失败']);
            }
        }
        $this->assign('settle_id',I('settle_id/d'));
        return $this->fetch();
    }
    /**
     * 交易流水
     */
    function pay_log(){
        $settle_id = I('settle_id/d');
        $settle = M('settlement')->where('settle_id',$settle_id)->find();
        if(!$settle){
            $this->error('该账单不存在');
        }
        $count = M('settlement_pay_log')->where(['store_id'=>STORE_ID,'settle_id'=>$settle_id])->count();
        $page = new Page($count, 10);
        $list = M('settlement_pay_log')->where(['store_id'=>STORE_ID,'settle_id'=>$settle_id])->order('sl_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        $this->assign('page', $page);
        $this->assign('list', $list);
        return $this->fetch();
    }
    /**
     * 关联订单
     */
    function order_lists(){
        $settle_id = I('settle_id/d');
        $settle = M('settlement')->where('settle_id',$settle_id)->find();
        if(!$settle){
            $this->error('该账单不存在');
        }
        $count = M('order')->where(['store_id'=>STORE_ID,'order_id'=>['in',$settle['order_ids']]])->count();
        $page = new Page($count, 10);
        $list = M('order')->where(['store_id'=>STORE_ID,'order_id'=>['in',$settle['order_ids']]])->order('order_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        $order_status = C('ORDER_STATUS');
        $shipping_status = C('SHIPPING_STATUS');
        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->assign('shipping_status', $shipping_status);
        $this->assign('order_status', $order_status);
        return $this->fetch();
    }
    /**
     * 货款折让
     */
    public function discount(){
        $this->assign('settle_id',I('settle_id/d'));
        return $this->fetch();
    }
}
