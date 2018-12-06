<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * Author: dyr
 * Date: 2016-08-23
 */

namespace app\common\model;

use think\Model;
use think\Db;

class Order extends Model
{
    protected $table='';


    //自定义初始化
    protected function initialize()
    {
        parent::initialize();
        $select_year = select_year(); // 查询 三个月,今年内,2016年等....订单
        $prefix = C('database.prefix');  //获取表前缀
        $this->table = $prefix.'order'.$select_year;
    }

    /**
     * 获取订单商品
     * @return \think\model\relation\HasMany
     */
    public function OrderGoods()
    {
        return $this->hasMany('OrderGoods','order_id','order_id');
    }

    /**
     * 获取订单操作记录
     * @return \think\model\relation\HasMany
     */
    public function OrderAction()
    {
        return $this->hasMany('OrderAction','order_id','order_id')->order('action_id desc');
    }
    /**
     * 获取订单发货单
     * @return \think\model\relation\HasMany
     */
    public function DeliveryDoc()
    {
        return $this->hasMany('DeliveryDoc','order_id','order_id');
    }
    /**
     * 获取订单店铺
     * @return $this
     */
    public function store()
    {
        return $this->hasone('store','store_id','store_id')->field('store_id,store_name,store_qq,store_phone,store_logo,store_avatar,qitian,store_free_price');
    }

    public function users(){
        return $this->hasone('users','user_id','user_id')->field('user_id,nickname');
    }

    /**
     * 获取虚拟订单的兑换码
     * @return \think\model\relation\HasMany
     */
    public function VrOrderCode(){
        return $this->hasMany('vr_order_code','order_id','order_id');
    }

    public function preSell()
    {
        return $this->hasOne('PreSell', 'pre_sell_id', 'prom_id');
    }

    /**
     *  只有在订单为拼团才有用:prom_type = 6
     */
    public function teamActivity()
    {
        return $this->hasOne('app\common\model\team\TeamActivity', 'team_id', 'prom_id');
    }

    public function teamFollow(){
        return $this->hasOne('app\common\model\team\TeamFollow','order_id','order_id');
    }

    public function teamFound(){
        return $this->hasOne('app\common\model\team\TeamFound','order_id','order_id');
    }

    public function getPayStatusDetailAttr($value, $data)
    {
        $pay_status = config('PAY_STATUS');
        return $pay_status[$data['pay_status']];
    }

    public function getShippingStatusDetailAttr($value, $data)
    {
        $shipping_status = config('SHIPPING_STATUS');
        return $shipping_status[$data['shipping_status']];
    }

    /**
     * 获取订单状态对应的中文
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getOrderStatusDetailAttr($value, $data)
    {
        $data_status_arr = C('ORDER_STATUS_DESC');
        // 账期订单
        if ($data['prom_type'] == 1) {
            // 待发货
            if (($data['order_status'] == 0 || $data['order_status'] == 1) && $data['shipping_status'] == 0) {
                return $data_status_arr['WAITSEND']; //'待发货',
            }
            //待确认实发数
            if ($data['shipping_status'] == 1 && $data['order_status'] == 1) {
                return $data_status_arr['WAITCONFIRM']; //'实发数',
            }
            //待收货
            if ($data['shipping_status'] == 1 && $data['order_status'] == 2 && $data['is_ligation'] == 0) {
                return $data_status_arr['WAITRECEIVE'];
            }
            //待扎账
            if ($data['shipping_status'] == 1 && $data['order_status'] == 3 && $data['is_ligation'] == 0) {
                return $data_status_arr['WAITLIGATION'];
            }
            //待付款
            if (($data['order_status'] == 2 || $data['order_status'] == 3) && $data['is_ligation'] > 0) {
                return $data_status_arr['WAITPAY'];
            }
            //已完成
            if ($data['pay_status'] == 2 && $data['is_ligation'] > 0) {
                return $data_status_arr['FINISH'];
            }
        } else {
            // 非账期订单
            // 待支付
            if ($data['pay_status'] == 0 && $data['order_status'] == 0) {
                return $data_status_arr['WAITPAY'];
            }
            // 待发货
            if ($data['pay_status'] == 1 && ($data['order_status'] == 0 || $data['order_status'] == 1) && $data['shipping_status'] == 0) {
                return $data_status_arr['WAITSEND']; //'待发货',
            }
            //实发数
            if ($data['pay_status'] == 1 && $data['order_status'] == 1 && $data['shipping_status'] == 1) {
                return $data_status_arr['WAITCONFIRM']; //'实发数',
            }
            //待付尾款
            if ($data['pay_status'] == 1 && $data['order_status'] == 2 && $data['shipping_status'] == 1) {
                return $data_status_arr['WAITPAYTAIL'];
            }
            //待收货
            if ($data['pay_status'] == 2 && $data['order_status'] == 2 && $data['shipping_status'] == 1) {
                return $data_status_arr['WAITRECEIVE'];
            }
            //已完成
            if ($data['pay_status'] == 2 && $data['order_status'] == 3 && $data['shipping_status'] == 1) {
                return $data_status_arr['FINISH'];
            }
        }
        //已取消
        if ($data['order_status'] == 4) {
            return $data_status_arr['CANCEL'];
        }
        //已取消
        if ($data['order_status'] == 6) {
            return $data_status_arr['CANCELLED'];
        }
        return $data_status_arr['OTHER'];
    }

    /**
     * 获取订单状态的 显示按钮
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getOrderButtonAttr($value, $data)
    {
        /**
         *  订单用户端显示按钮
         * 去支付     AND pay_status=0 AND order_status=0 AND pay_code ! ="cod"
         * 取消按钮  AND pay_status=0 AND shipping_status=0 AND order_status=0
         * 确认收货  AND shipping_status=1 AND order_status=0
         * 评价      AND order_status=1
         * 查看物流  if(!empty(物流单号))
         */
        $btn_arr = array(
            'pay_btn' => 0, // 去支付按钮
            'pay_tail' => 0, // 支付尾款按钮
            'cancel_btn' => 0, // 取消按钮
            'receive_btn' => 0, // 确认收货
            'ligation_btn' => 0, // 去结算
            //'comment_btn' => 0, // 评价按钮
            'shipping_btn' => 0, // 查看物流
            'return_btn' => 0, // 退货按钮 (联系客服)
            'return_overdue' => 0, // 是否过了售后时间
            //'del_btn' => 0, // 删除订单
        );
        $auto_service_date = tpCache('shopping.auto_service_date'); //申请售后时间段
        $confirm_time = strtotime ( "-$auto_service_date day" );
        // 三个月(90天)内的订单才可以进行有操作按钮. 三个月(90天)以外的过了退货 换货期, 即便是保修也让他联系厂家, 不走线上
        if (time() - $data['add_time'] > (86400 * 90)) {
            return $btn_arr;
        }
        if($data['confirm_time']<$confirm_time){
            $btn_arr['return_overdue']=1;
        }
        // 账期订单
        if ($data['prom_type'] == 1) {
            // 待发货
            if (($data['order_status'] == 0 || $data['order_status'] == 1) && $data['shipping_status'] == 0) {
                 if($data['order_status'] == 0)
                    $btn_arr['cancel_btn'] = 1; // 取消按钮
            }
            //待确认实发数
            if ($data['shipping_status'] == 1 && $data['order_status'] == 1) {

            }
            //待收货
            if ($data['shipping_status'] == 1 && $data['order_status'] == 2 && $data['is_ligation'] == 0) {
                $btn_arr['receive_btn'] = 1;  // 确认收货
                $btn_arr['return_btn'] = 1; // 退货按钮
            }
            //待扎账
            if ($data['shipping_status'] == 1 && $data['order_status'] == 3 && $data['is_ligation'] == 0) {
                $btn_arr['return_btn'] = 1; // 退货按钮
            }
            //待付款
            if (($data['order_status'] == 2 || $data['order_status'] == 3) && $data['is_ligation'] > 0) {
                if($data['order_status'] == 2)
                    $btn_arr['receive_btn'] = 1;  // 确认收货
                $btn_arr['ligation_btn'] = 1;  // 去结算
            }
        } else {
            // 非账期订单
            // 待支付
            if ($data['pay_status'] == 0 && $data['order_status'] == 0) {
                $btn_arr['pay_btn'] = 1; // 去支付按钮
                $btn_arr['cancel_btn'] = 1; // 取消按钮
            }
            // 待发货
            if ($data['pay_status'] == 1 && ($data['order_status'] == 0 || $data['order_status'] == 1) && $data['shipping_status'] == 0) {
                //$btn_arr['cancel_btn'] = 1; // 取消按钮
            }
            //实发数
            if ($data['pay_status'] == 1 && $data['order_status'] == 1 && $data['shipping_status'] == 1) {

            }
            //待付尾款
            if ($data['pay_status'] == 1 && $data['order_status'] == 2 && $data['shipping_status'] == 1) {
                $btn_arr['pay_tail'] = 1;  // 支付尾款
            }
            //待收货
            if ($data['pay_status'] == 2 && $data['order_status'] == 2 && $data['shipping_status'] == 1) {
                $btn_arr['receive_btn'] = 1;  // 确认收货
            }
            //已完成
            if ($data['pay_status'] == 2 && $data['order_status'] == 3 && $data['shipping_status'] == 1) {
                $btn_arr['return_btn'] = 1;  // 退货按钮
            }
        }
        /*if ($data['order_status'] == 4) {
            $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
        }
        if ($data['order_status'] == 2) {
            if ($data['is_comment'] == 0) $btn_arr['comment_btn'] = 1;  // 评价按钮
            $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
        }
        if ($data['shipping_status'] > 0 && $data['order_status'] == 1) {
            $btn_arr['shipping_btn'] = 1; // 查看物流
        }
        if ($data['order_status'] == 1 && $data['shipping_status'] == 1) {
            $btn_arr['return_btn'] = 1; //确认订单也可以申请售后&物流状态必须为已发货(部分发货暂时不考虑)
        }*/

        /*    if ($data['shipping_status'] == 2 && $data['order_status'] == 1) // 部分发货
            {
                $btn_arr['return_btn'] = 1; // 退货按钮 (联系客服)
            }
        if ($data['order_status'] == 3 && ($data['pay_status'] == 1 || $data['pay_status'] == 4 || $data['pay_status'] == 3)) {
            $btn_arr['cancel_info'] = 1; // 取消订单详情
        }*/
        return $btn_arr;
    }

    /**
     * 商家可操作订单按钮
     * 操作按钮汇总 ：付款、设为未付款、确认、取消确认、无效、去发货、确认收货、申请退货
     * @param $value
     * @param $data
     * @return array
     */
    public function getSellerButtonAttr($value, $data)
    {
        // 三个月以前订单无任何操作按钮
        if(time() - $data['add_time'] > (86400 * 90)){
            return array();
        }
        $os = $data['order_status'];//订单状态
        $ss = $data['shipping_status'];//发货状态
        $ps = $data['pay_status'];//支付状态
        $pt = $data['prom_type'];//订单类型：0普通1账期
        $btn = array();
        if($pt == '1') {
            if($os == 0 && $ss == 0){
                $btn['confirm'] = '确认';
            }elseif($os == 1 && $ss == 0 ){
                $btn['delivery'] = '去发货';
                $btn['cancel'] = '取消确认';
            }elseif($ss == 1 && $os == 1 && $ps == 0){
                //$btn['pay'] = '付款';
                $btn['confirm_num'] = '确认实发数';
            }/*elseif($ps == 0 && $ss == 1 && $os == 2){
                if($pt != 6){
                    $btn['pay_cancel'] = '设为未付款';
                }
            }*/elseif($os == 1 && $ss == 2){
                $btn['delivery'] = '去发货';
            }
        }else{
            if($ps == 0 && $os == 0){
                $btn['pay'] = '付款';
            }elseif($os == 0 && $ps == 1){
                $btn['pay_cancel'] = '设为未付款';
                $btn['confirm'] = '确认';
                /*if($pt == 4){
                    $pre_sell = Db::name('pre_sell')->where('pre_sell_id',$data['prom_id'])->find();
                    if($pre_sell['is_finished'] == 2){
                        $btn['confirm'] = '确认';
                    }
                }*/
            }elseif($os == 1 && $ps == 1 && $ss==0){
                $btn['cancel'] = '取消确认';
                $btn['delivery'] = '去发货';
            }elseif($os == 1 && $ps == 1 && $ss==2){
                $btn['delivery'] = '去发货';
            }elseif($ss == 1 && $os == 1 && $ps == 1){
                $btn['confirm_num'] = '确认实发数';
            }elseif($ss == 1 && $os == 2 && $ps == 1){
                $btn['pay_tail'] = '付尾款';
            }
        }

        if($ss == 1 && $os == 2 && $ps == 1){
            $btn['delivery_confirm'] = '确认收货';
            $btn['refund'] = '申请退货';
        }elseif($os == 3|| $os == 5){
            $btn['refund'] = '申请退货';
        }
        if($os != 6 && $ps != 1 && $ps != 2){
            $btn['invalid'] = '无效';
        }
        if ($os < 2) {
            $btn['edit'] = '修改订单'; // 修改订单
                /*$select_year = getTabByOrderId($data['order_id']);
                $c = Db::name('order_goods' . $select_year)->where('order_id', $data['order_id'])->sum('goods_num');
                if ($c >= 2 && $ps == 1) {
                    $btn['split'] = '拆分订单'; // 拆分订单
                }*/
        }
        return $btn;
    }

    public function getVirtualOrderButtonAttr($value, $data){
        $vr_order_code = Db::name('vr_order_code')->where(['order_id'=>$data['order_id']])->find();
        $Virtual_btn_arr = array(
            'pay_btn' => 0, // 去支付按钮
            'cancel_btn' => 0, // 取消按钮
            'receive_btn' => 0, // 确认收货
            'comment_btn' => 0, // 评价按钮
        );
        if ($data['pay_status'] == 0 && $data['order_status'] == 0) {   // 待支付
            $Virtual_btn_arr['pay_btn'] = 1; // 去支付按钮
            $Virtual_btn_arr['cancel_btn'] = 1; //未支付取消按钮
        }
        if(!empty($vr_order_code)){
            if ($data['pay_status']==1 && $data['order_status']<2 && $vr_order_code['vr_state']!=1 && $vr_order_code['refund_lock']<1)
            {
                if ($vr_order_code['vr_indate'] > time() ) {
                    $Virtual_btn_arr['cancel_btn'] = 2; // 已支付取消按钮
                }
                if ($vr_order_code['vr_indate'] < time() && $vr_order_code['vr_invalid_refund'] == 1)
                {
                    $Virtual_btn_arr['cancel_btn'] = 2; // 已支付取消按钮
                    M('vr_order_code')->where(array('order_id'=>$data['order_id']))->update(['vr_state'=>2]);
                }
                $Virtual_btn_arr['receive_btn'] = 1;
            }
            if ($data['order_status'] == 2) {
                if ($data['is_comment'] == 0) $Virtual_btn_arr['comment_btn'] = 1;  // 评价按钮
            }
        }
        return $Virtual_btn_arr;
    }

    public function getAddressRegionAttr($value, $data){
        $regions = Db::name('region')->where('id', 'IN', [$data['province'], $data['city'], $data['district'],$data['twon']])->order('level desc')->select();
        $address = '';
        if($regions){
            foreach($regions as $regionKey=>$regionVal){
                $address = $regionVal['name'] . $address;
            }
        }
        return $address;
    }

    public function getFinallyPayTimeAttr($value, $data){
        return $data['add_time'] + config('finally_pay_time');
    }

    public function getTotalFeeAttr($value, $data){
        return $data['goods_price'] + $data['shipping_price'] - $data['integral_money'] - $data['coupon_price'] - $data['discount'];
    }
    public function getFullAddressAttr($value, $data){
        $region = Db::name('region')->where('id', 'IN', [$data['province'], $data['city'], $data['district']])->column('name');
        return implode('', $region) . $data['address'];
    }

    public function getPromTypeDescAttr($value, $data){
        if($data['prom_type'] == 4){
            return '预售订单';
        }elseif($data['prom_type'] == 5){
            return '虚拟订单';
        }elseif($data['prom_type'] == 6){
            return '拼团订单';
        }else{
            return '普通订单';
        }
    }
    /**
     * 获取订单状态对(商家)
     *
        定义一个变量, 用于前端UI显示订单5个状态进度. 1: 提交订单;2:订单支付; 3: 商家发货; 4: 确认收货; 5: 订单完成
        此判断依据根据来源于 Common的config.phpz中的"订单用户端显示状态" @{
        '1'=>' AND pay_status = 0 AND order_status = 0 AND pay_code !="cod" ', //订单查询状态 待支付
        '2'=>' AND (pay_status=1 OR pay_code="cod") AND shipping_status !=1 AND order_status in(0,1) ', //订单查询状态 待发货
        '3'=>' AND shipping_status=1 AND order_status = 1 ', //订单查询状态 待收货
        '4'=> ' AND order_status=2 ', // 待评价 已收货     //'FINISHED'=>'  AND order_status=1 ', //订单查询状态 已完成
        '5'=> ' AND order_status = 4 ', // 已完成
     * @param $value
     * @param $data
     * @return mixed
     */
    public function getShowStatusAttr($value, $data)
    {
        $show_status = 1;
        if ($data['pay_status'] == 0 && $data['order_status'] == 0 && $data['pay_code'] != 'cod') {
            $show_status = 1;
        } else if (($data['pay_status'] == 1 || $data['pay_code'] == "cod") && $data['shipping_status'] != 1 && ($data['order_status'] == 0 || $data['order_status'] == 1)) {
            $show_status = 2;
        } else if (($data['shipping_status'] == 1 AND $data['order_status'] == 1)) {
            $show_status = 3;
        }  else if ($data['is_comment'] == 1) {//评论
            $show_status = 5;
        } else if ($data['order_status'] == 2) {
            $show_status = 4;
        }
        return $show_status;
    }

    /**
     * 是否显示支付到期时间FinallyPayTime
     * @param $value
     * @param $data
     * @return bool
     */
    public function getPayBeforeTimeShowAttr($value, $data)
    {
        if($data['prom_type'] == 4){
            return false;
        }
        return true;
    }

    /**
     * 付款URL
     * @param $value
     * @param $data
     * @return string
     */
    public function getPayUrlAttr($value, $data)
    {
        if($data['prom_type'] == 4){
            return U('Cart/cart4',['master_order_sn'=>$data['master_order_sn']]);
        }else{
            return U('Cart/cart4',['order_id'=>$data['order_id']]);
        }
    }
    /**
     * 订单发票
     * @return string
     */
    public function invoice()
    {
        return $this->hasOne('invoice','order_id','order_id');
    }
}