<?php
use think\Model;
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once dirname(dirname(__FILE__))."/lib/WxPay.Api.php";
require_once dirname(dirname(__FILE__))."/lib/WxPay.Notify.php";
require_once 'log.php';

$f = dirname(dirname(__FILE__));
//初始化日志
$logHandler= new CLogFileHandler($f."/logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);
class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		$store_id = $data['attach']; //商家数据包，原样返回
		$paymentPlugin = M('Plugin')->where(['code'=>'weixin' , 'type' => 'payment'])->find(); // 找到微信支付插件的配置
		$sub_merchant_id = M('Store')->where(['store_id'=>$store_id])->getField('sub_merchant_id'); // 找到子商户id
		$config_value = unserialize($paymentPlugin['config_value']); // 配置反序列化
		WxPayConfig::$appid = $config_value['appid']; // * APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
		WxPayConfig::$mchid = $config_value['mchid']; // * MCHID：商户号（必须配置，开户邮件中可查看）
		WxPayConfig::$sub_mchid = $sub_merchant_id; // * MCHID：商户号（必须配置，开户邮件中可查看）
		WxPayConfig::$key = $config_value['key']; // KEY：商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
		WxPayConfig::$appsecret = $config_value['appsecret']; // 公众帐号secert（仅JSAPI支付的时候需要配置)，
		WxPayConfig::$app_type = 'weixin';
		Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array();
		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}         
		$appid = $data['appid']; //公众账号ID
		$order_sn = $out_trade_no = $data['out_trade_no']; //商户系统的订单号，与请求一致。

		
		
		//用户在线充值
		if (stripos($order_sn, 'recharge') !== false)
			$order_amount = M('recharge')->where(['order_sn' => $order_sn, 'pay_status' => 0])->value('account');
		elseif(stripos($order_sn, 'settle') !== false)
			$order_amount = M('settlement')->where(['settle_sn' => $order_sn, 'settle_status' => ['in','0,2']])->value('settle_amount');
		else		
			$order_amount = M('order')->where(['master_order_sn'=>str_replace('tail','',$order_sn)])->whereOr(['order_sn'=>str_replace('tail','',$order_sn)])->sum('order_amount');
		/*if((string)($order_amount * 100) != (string)$data['total_fee'])
			return false; //验证失败*/


		update_pay_status($order_sn,$data["transaction_id"]); // 修改订单支付状态
		
		return true;
	}
}

//Log::DEBUG("begin notify");
//$notify = new PayNotifyCallBack();
//$notify->Handle(false);
