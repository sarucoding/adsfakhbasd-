<?php

namespace app\mobile\logic;

use think\Model;
use think\Db;
use app\common\model\Order as OrderModel;

/**
 * 订单逻辑
 * Class OrderLogic
 * @package Mobile\Logic
 */
class OrderLogic extends Model
{
    /**
     * 判断是显示账期订单列表
     * @return bool
     */
    public function has_contract()
    {
        /*$order_count = M('order')->where(['user_id'=>USER_ID,'store_id'=>STORE_ID,'prom_type'=>1])->count();//获取账期订单数量
        if(!$order_count && !$this->user['is_contract']){//既没有账期，也没有账期订单则不显示账期订单列表选项
            return false;
        }*/
        return true;
    }
    /**
     * 获取订单详情
     */
    public function get_order_detail($where){
        $orderModel = new OrderModel();
        $order = $orderModel->relation('OrderGoods')->where($where)->find();
        $order['order_button_new'] = $this->get_button($order);
        return $order;
    }
    /**
     * 获取订单列表
     */
    public function get_order_lists($where = '',$limit = ''){
        $orderModel = new OrderModel();
        $order_lists = $orderModel->relation('OrderGoods')->where($where)->limit($limit)->order('order_id desc')->select();
        foreach($order_lists as $k=>$v){
            $order_btn = $this->get_button($v);
            $order_btn .= '<a href="'.U('order/detail',['order_id'=>$v['order_id']]).'"><span class="pay-border-green">查看详情</span></a>';
            $order_lists[$k]['order_button_new'] = $order_btn;
            $order_lists[$k]['order_status'] = $v['order_status_detail'];
            $order_lists[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
            $order_lists[$k]['type_count'] = count($v['OrderGoods']);
        }
        return $order_lists;
    }
    /**
     * 通过订单按钮数组获取按钮标签代码
     */
    public function get_button($order){
        $button_arr = $order['order_button'];
        $order_btn = '';
        if($button_arr['cancel_btn']){
            $order_btn .= '<a href="javascript:;" onclick="order_handle('.$order['order_id'].',\'确定要取消订单吗？\',\'cancel\')"><span>取消订单</span></a>';
        }
        if($button_arr['receive_btn']){
            $order_btn .= '<a href="javascript:;" onclick="order_handle('.$order['order_id'].',\'您确定收到该订单商品了吗？\',\'receive\')"><span class="pay-border-green">确认收货</span></a>';
        }
        if($button_arr['return_btn']){
            $order_btn .= '<a href="javascript:;" onclick="order_handle('.$order['order_id'].',\'您确定要退货吗？\')"><span>退货</span></a>';
        }
        if($button_arr['pay_btn']){
            $order_btn .= '<a href="'.U('cart/order_pay',['order_sn'=>$order['order_sn']]).'"><span class="pay-border-green">立即付款</span></a>';
        }
        if($button_arr['pay_tail']){
            $order_btn .= '<a href="'.U('order/pay_tail',['order_id'=>$order['order_id']]).'"><span class="pay-border-green">支付尾款</span></a>';
        }
        if($button_arr['ligation_btn']){
            $order_btn .= '<a href="'.U('settlement/index').'"><span class="pay-border-green">去结算</span></a>';
        }
        return $order_btn;
    }
}