<?php

namespace app\seller\controller;

use think\Db;
use think\Loader;
use think\Page;
use app\seller\logic\OfferLogic;

class Offer extends Base
{

    /**
     * 报价单列表
     */
    function index(){
        $offer = M('offer_bill');
        $keywords = I('keywords','');
        $where = ['a.store_id'=>STORE_ID];
        if($keywords){
            $where['a.ob_sn|b.realname'] = ['like','%'.$keywords.'%'];
        }
        $join = [
            ['__USERS__ b','a.user_id = b.user_id'],
            ['__USER_RELATION_STORE__ c','a.user_id = c.user_id'],
        ];
        $count = $offer->alias('a')->join($join)->where($where)->count();
        $page = new Page($count, 10);
        $list = $offer->alias('a')->join($join)->where($where)->order('ob_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->assign('keywords', $keywords);
        return $this->fetch();
    }

    /**
     * 报价明细
     */
    public function offer_lists()
    {
        $ob_id = I('get.ob_id/d');
        $where = ['ob_id'=>$ob_id];
        $count = M('offer')->where($where)->count();
        $Page = new Page($count, 10);
        $list = M('offer')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('offer_id desc')->select();
        $this->assign('page', $Page);
        $this->assign('list', $list);
        return $this->fetch();
    }
    /**
     * 询价单列表
     */
    public function inquiry_lists()
    {
        $keywords = input('keywords');
        $where = ['store_id'=>STORE_ID];
        $on = '';
        if($keywords){
            $on .= ' and b.realname like "%'.$keywords.'%"';
        }
        $count = M('inquiry')->alias('a')->join('__USERS__ b','a.user_id = b.user_id'.$on)->where($where)->count();
        $Page = new Page($count, 10);
        $list = M('inquiry')->alias('a')->join('__USERS__ b','a.user_id = b.user_id'.$on)->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('inquiry_id desc')->select();
        $this->assign('page', $Page);
        $this->assign('list', $list);
        $this->assign('keywords', $keywords);
        return $this->fetch();
    }
    /**
     * 添加报价
     */
    public function add(){
        if(IS_POST){
            $user_id = input('user_id/d');
            $offer_type = input('offer_type/d');
            $goods_id = input('goods_id/a');
            $goods_name = input('goods_name/a');
            $spec_key_name = input('spec_key_name/a');
            $offer_price = input('offer_price/a');
            $spec_key = input('spec_key/a');
            $inquiry_id = input('inquiry_id/d');
            if(!$user_id){
                $err['user_id'] = '必须选择客户';
            }
            if($err){
                $this->ajaxReturn(['status' => 0, 'msg' => '提交失败', 'result' => $err]);
            }
            $goods_count = 0;
            foreach($goods_id as $k=>$v){
                $goods_count++;//计算商品数
            }
            $bill_data = [
                'user_id' => $user_id,
                'store_id' => STORE_ID,
                'seller_id' => $_SESSION['seller']['seller_id'],
                'seller_name' => $_SESSION['seller']['real_name'],
                'ob_sn' => build_serial_number(),
                'ob_goods_num' => $goods_count,
                'ob_add_time' => time(),
            ];//构造报价单数据
            $bill_id = M('offer_bill')->add($bill_data);
            if(!$bill_id){
                $this->ajaxReturn(['status' => 0, 'msg' => '提交失败', 'result' => $bill_id]);
            }
            $offer_data = [];
            foreach($goods_id as $k=>$v){//构造报价明细数据
                $offer_data[] = [
                    'ob_id' => $bill_id,
                    'goods_id' => $v,
                    'spec_key' => $spec_key[$k],
                    'goods_name' => $goods_name[$k],
                    'spec_key_name' => $spec_key_name[$k],
                    'offer_price' => $offer_price[$k],
                    'offer_type' => $offer_type,
                    'offer_add_time' => time(),
                    'user_id' => $user_id,
                    'store_id' => STORE_ID,
                    'seller_id' => $_SESSION['seller']['seller_id'],
                    'seller_name' => $_SESSION['seller']['real_name'],
                ];
            }
            $res = M('offer')->insertAll($offer_data);
            if(!$res){
                $this->ajaxReturn(['status' => 0, 'msg' => '操作失败', 'result' => '']);
            }else{
                if($inquiry_id){//如果带有询价单，则更改询价单状态
                    Db::name('inquiry')->where(['inquiry_id'=>$inquiry_id])->update(['inquiry_status'=>1,'ob_id'=>$bill_id]);
                }
                $this->ajaxReturn(['status' => 1, 'msg' => '操作成功', 'result' => '']);
            }
        }else{
            $inquiry_id = input('inquiry_id/d');
            if($inquiry_id){
                $inquiry = M('inquiry')->where(['inquiry_id'=>$inquiry_id])->find();
                if(!$inquiry || $inquiry['inquiry_status'] == 1){
                    $this->error('询价单不存在或已报价');
                }
                $offer_logic = new OfferLogic();
                $res = $offer_logic->get_offer_goods($inquiry['goods_ids'],$inquiry['user_id']);
                if($res['status'] == 0){
                    $this->error($res['msg']);
                }
                $user = D('user_relation_store')->get_user_info1($inquiry['user_id']);
                $user['type'] = $user['is_contract'] == 1 ? '此客户为账期客户，建议选择固定报价' : '此客户为非账期客户，建议选择浮动报价';
                $this->assign('user',$user);
                $this->assign('inquiry_id',$inquiry_id);
                $this->assign('goods_html',$res['result']);
            }
            return $this->fetch();
        }
    }
    /**
     * ajax获取多个产品的单品列表
     */
    public function ajax_goods_lists(){
        $goods_ids = input('goods_ids');
        $user_id = input('user_id/d');
        $offer_logic = new OfferLogic();
        $res = $offer_logic->get_offer_goods($goods_ids,$user_id);
        $this->ajaxReturn(['status'=>1,'msg'=>'请求成功','data'=>$res['result']]);
    }
}
