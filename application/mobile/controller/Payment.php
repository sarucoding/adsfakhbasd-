<?php
namespace app\mobile\controller;
/**
 * Class Payment
 * @package app\mobile\controller
 * 收银台类
 */
class Payment extends Base{

    public $payment; //  具体的支付类
    public $pay_code; //  具体的支付code

    /**
     * 析构流函数
     */
    public function  __construct() {
        parent::__construct();
        $pay_radio = $_REQUEST['pay_radio'];
        if(!empty($pay_radio))
        {
            $pay_radio = parse_url_param($pay_radio);
            $this->pay_code = $pay_radio['pay_code']; // 支付 code
        }
        else // 第三方 支付商返回
        {
            $_GET = I('get.');
            $this->pay_code = I('get.pay_code');
            unset($_GET['pay_code']); // 用完之后删除, 以免进入签名判断里面去 导致错误
        }
        if(empty($this->pay_code))
            exit('pay_code 不能为空');
        // 导入具体的支付类文件
        include_once  "plugins/payment/{$this->pay_code}/{$this->pay_code}.class.php";
        $code = '\\'.$this->pay_code;
        $this->payment = new $code();
    }

    /**
     * 提交支付方式
     */
    public function getCode(){
        header("Content-type:text/html;charset=utf-8");
        // 修改订单的支付方式
        $payment_arr = M('Plugin')->where("`type` = 'payment'")->getField("code,name");
        $order_id = I('order_id/d',0); // 订单id
        $order_where['order_id'] = $order_id;
        $order = M('Order')->where($order_where)->find();
        if(empty($order) || $order['order_status'] > 1){
            $this->error('非法操作！',U("Index/index"));
        }
        if($order['pay_status'] == 1){
            $this->error('此订单，已完成支付!');
        }
        M('order')->where("order_id", $order_id)->save(array('pay_code'=>$this->pay_code,'pay_name'=>$payment_arr[$this->pay_code]));

        //微信JS支付
        if($this->pay_code == 'weixin' && $_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
            $code_str = $this->payment->getJSAPI($order);
            $this->ajaxReturn(['status'=>1,'msg'=>'成功','result'=>$code_str]);
        }
        else
        {
            $code_str = $this->payment->get_code($order);
        }
        $this->assign('code_str', $code_str);
        $this->assign('order_id', $order['order_id']);
        return $this->fetch('payment');  // 分跳转 和不 跳转
    }
    /**
     * 尾款支付
     */
    public function getTailCode(){
        header("Content-type:text/html;charset=utf-8");
        // 修改订单的支付方式
        $payment_arr = M('Plugin')->where("`type` = 'payment'")->getField("code,name");
        $order_id = I('order_id/d',0); // 订单id
        $order_where['order_id'] = $order_id;
        $order_where['prom_type'] = 0;//只能是普通订单
        $order = M('Order')->where($order_where)->find();
        if(empty($order) || $order['order_status'] != 2 || $order['pay_status'] != 1){
            $this->error('非法操作！',U("Index/index"));
        }
        if($order['pay_status'] == 2){
            $this->error('此订单，已完成支付!');
        }
        M('order')->where("order_id", $order_id)->save(array('pay_code'=>$this->pay_code,'pay_name'=>$payment_arr[$this->pay_code]));
        $order['order_sn'] = 'tail'.$order['order_sn'];
        //微信JS支付
        if($this->pay_code == 'weixin' && $_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
            $code_str = $this->payment->getJSAPI($order);
            $this->ajaxReturn(['status'=>1,'msg'=>'成功','result'=>$code_str]);
        }
        else
        {
            $code_str = $this->payment->get_code($order);
        }
        $this->assign('code_str', $code_str);
        $this->assign('order_id', $order['order_id']);
        return $this->fetch('payment');  // 分跳转 和不 跳转
    }
    /**
     * 账单支付
     * @return mixed
     */
    public function getSettleCode(){
        header("Content-type:text/html;charset=utf-8");
        $settle_id = I('settle_id/d'); // 订单id
        // 修改订单的支付方式
        $payment_arr = M('Plugin')->where("`type` = 'payment'")->getField("code,name");
        M('settlement')->where("settle_id", $settle_id)->save(array('pay_code'=>$this->pay_code,'pay_name'=>$payment_arr[$this->pay_code]));
        $settlement = M('settlement')->where("settle_id", $settle_id)->find();
        if($settlement['settle_status'] == 1){
            $this->error('此账单已完成支付!');
        }
        $order['order_amount'] = $settlement['settle_amount'];
        $order['order_sn'] = $settlement['settle_sn'];
        //微信JS支付
        if($this->pay_code == 'weixin' && $_SESSION['openid'] && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
            $code_str = $this->payment->getJSAPI($order);
            $this->ajaxReturn(['status'=>1,'msg'=>'成功','result'=>$code_str]);
        }else{
            $code_str = $this->payment->get_code($order);
        }
        $this->assign('code_str', $code_str);
        $this->assign('settle_id', $settle_id);
        return $this->fetch('recharge'); //分跳转 和不 跳转
    }

    //支付完成异步通知回调地址
    public function notifyUrl(){
        $this->payment->response();
        exit();
    }

    //支付完成同步跳转地址
    public function returnUrl(){
        // $result['order_sn'] = '201512241425288593';
        $result = $this->payment->respond2();
        if (stripos($result['order_sn'], 'recharge') !== false) {
            $order = M('recharge')->where("order_sn", $result['order_sn'])->find();
            $this->assign('order', $order);
            if ($result['status'] == 1) {
                return $this->fetch('recharge_success');
            } else {
                return $this->fetch('recharge_error');
            }
        }

        // 先查看一下 是不是 合并支付的主订单号
        $sum_order_amount = M('order')->where("master_order_sn", $result['order_sn'])->sum('order_amount');
        if ($sum_order_amount) {
            $this->assign('master_order_sn', $result['order_sn']); // 主订单号
            $this->assign('sum_order_amount', $sum_order_amount); // 所有订单应付金额
        } else {
            $order = M('order')->where("order_sn", $result['order_sn'])->find();
            $this->assign('order', $order);
        }

        if($result['status'] == 1){
            return $this->fetch('success');
        }else{
            return $this->fetch('error');
        }
    }

    public function refundBack(){
        $this->payment->refund_respose();
        exit();
    }

    public function transferBack(){
        $this->payment->transfer_response();
        exit();
    }
}
