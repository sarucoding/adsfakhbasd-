<?php

use think\Model;
use think\Request;
use app\admin\logic\RefundLogic;

/**
 * 支付 逻辑定义
 * Class AlipayPayment
 * @package Home\Payment
 */
header("Content-type: text/html; charset=utf-8");
class alipay extends Model
{
    public $tableName = 'plugin'; // 插件表
    public $alipay_config = array();// 支付宝支付配置参数
    public $store;// 店铺

    /**
     * 析构流函数
     */
    public function  __construct()
    {
        parent::__construct();

        $this->store = M('Store')->where(['store_id' => STORE_ID])->find(); // 商户配置
        $this->alipay_config['app_id'] = $this->store['app_id'];
        $this->alipay_config['merchant_private_key'] = $this->store['merchant_private_key'];
        $this->alipay_config['notify_url'] = SITE_URL . '/index.php/Mobile/Payment/notifyUrl/pay_code/alipay';
        $this->alipay_config['return_url'] = SITE_URL . '/index.php/Mobile/Payment/returnUrl/pay_code/alipay';
        $this->alipay_config['charset'] = "UTF-8";
        $this->alipay_config['sign_type'] = "RSA2";
        $this->alipay_config['gatewayUrl'] = "https://openapi.alipay.com/gateway.do";
        $this->alipay_config['alipay_public_key'] = $this->store['alipay_public_key'];

    }

    /**
     * 生成支付代码
     * @param   array $order 订单信息
     * @param   array $config_value 支付方式信息
     */
    function get_code($order)
    {
        require_once 'wappay/service/AlipayTradeService.php';
        require_once 'wappay/buildermodel/AlipayTradeWapPayContentBuilder.php';
        $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
        $payRequestBuilder->setBody('alipay');
        $payRequestBuilder->setSubject($this->store['store_name']);
        $payRequestBuilder->setOutTradeNo($order['order_sn']);
        $payRequestBuilder->setTotalAmount($order['order_amount']);
        $payResponse = new AlipayTradeService($this->alipay_config);
        $result = $payResponse->wapPay($payRequestBuilder, $this->alipay_config['return_url'], $this->alipay_config['notify_url']);//这里就不用返回值了，直接跳转到了支付宝网页。
        return $result;
    }

    /**
     * 服务器点对点响应操作给支付接口方调用
     *
     */
    function response()
    {
        require_once 'wappay/service/AlipayTradeService.php';
        $arr=$_POST;
        $alipaySevice = new AlipayTradeService($this->alipay_config);
        $alipaySevice->writeLog(var_export($_POST,true));
        $result = $alipaySevice->check($arr);

        if ($result) //验证成功
        {
            $order_sn = $out_trade_no = $_POST['out_trade_no']; //商户订单号
            $trade_no = $_POST['trade_no']; //支付宝交易号
            $trade_status = $_POST['trade_status']; //交易状态

            //用户在线充值
            if (stripos($order_sn, 'recharge') !== false)
                $order_amount = M('recharge')->where(['order_sn' => $order_sn, 'pay_status' => 0])->value('account');
            elseif(stripos($order_sn, 'settle') !== false)
                $order_amount = M('settlement')->where(['settle_sn' => $order_sn, 'settle_status' => ['in','0,2']])->value('settle_amount');
            else
                $order_amount = M('order')->where(['master_order_sn' => str_replace('tail','',$order_sn)])->whereOr(['order_sn' => str_replace('tail','',$order_sn)])->sum('order_amount');


            if ($order_amount != $_POST['price'])
                exit("fail"); //验证失败

            // 支付宝解释: 交易成功且结束，即不可再做任何操作。
            if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                update_pay_status($order_sn, $trade_no); // 修改订单支付状态
            } //支付宝解释: 交易成功，且可对该交易做操作，如：多级分润、退款等。
            elseif ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                update_pay_status($order_sn, $trade_no); // 修改订单支付状态
            }
            echo "success"; // 告诉支付宝处理成功
        } else {
            echo "fail"; //验证失败
        }
    }

    /**
     * 页面跳转响应操作给支付接口方调用
     */
    function respond2()
    {
        require_once 'wappay/service/AlipayTradeService.php';
        $arr=$_POST;
        $alipaySevice = new AlipayTradeService($this->alipay_config);
        $alipaySevice->writeLog(var_export($_POST,true));
        $result = $alipaySevice->check($arr);

        if ($result) //验证成功
        {
            $order_sn = $out_trade_no = $_GET['out_trade_no']; //商户订单号
            $trade_no = $_GET['trade_no']; //支付宝交易号
            $trade_status = $_GET['trade_status']; //交易状态

            if ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                return array('status' => 1, 'order_sn' => $order_sn);//跳转至成功页面
            } else {
                return array('status' => 0, 'order_sn' => $order_sn); //跳转至失败页面
            }
        } else {
            return array('status' => 0, 'order_sn' => $_GET['out_trade_no']);//跳转至失败页面
        }
    }

}