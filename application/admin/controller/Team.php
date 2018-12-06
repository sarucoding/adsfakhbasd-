<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 商业用途务必到官方购买正版授权, 使用盗版将严厉追究您的法律责任。
 * 采用最新Thinkphp5助手函数特性实现单字母函数M D U等简写方式
 * ============================================================================
 * Author: 当燃
 * 专题管理
 * Date: 2016-06-09
 *  拼团控制器
 */

namespace app\admin\controller;

use app\common\model\Order;
use app\common\model\team\TeamActivity;
use app\common\model\team\TeamFound;
use think\Loader;
use think\Db;
use think\Page;

class Team extends Base
{
	public function _initialize() {
		parent::_initialize();
		$this->assign('time_begin',date('Y-m-d', strtotime("-3 month")+86400));
		$this->assign('time_end',date('Y-m-d', strtotime('+1 days')));
	}
	public function index()
	{	
		header("Content-type: text/html; charset=utf-8");
exit("请联系TPshop官网客服购买高级版支持此功能");
	}

	public function info()
	{
		header("Content-type: text/html; charset=utf-8");
exit("请联系TPshop官网客服购买高级版支持此功能");
	}

	/**
	 * 审核
	 */
	public function examine(){
		header("Content-type: text/html; charset=utf-8");
exit("请联系TPshop官网客服购买高级版支持此功能");
	}

	public function found_order_list(){
		header("Content-type: text/html; charset=utf-8");
exit("请联系TPshop官网客服购买高级版支持此功能");
	}

	public function order_list(){
		header("Content-type: text/html; charset=utf-8");
exit("请联系TPshop官网客服购买高级版支持此功能");
	}

	/**
	 * 团长佣金
	 */
	public function bonus(){
		header("Content-type: text/html; charset=utf-8");
exit("请联系TPshop官网客服购买高级版支持此功能");
	}

	public function doBonus(){
		header("Content-type: text/html; charset=utf-8");
exit("请联系TPshop官网客服购买高级版支持此功能");
	}
}