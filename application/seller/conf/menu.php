<?php
return array(
	'user' =>array('name' => '客户管理', 'icon'=>'ico-user', 'child' => array(
			array('name' => '新增客户', 'act'=>'add', 'op'=>'User'),
			array('name' => '客户管理', 'act'=>'index', 'op'=>'User'),
	)),
	'offer'=>array('name' => '报价管理', 'icon'=>'ico-order', 'child' => array(
			array('name' => '快速报价', 'act'=>'add', 'op'=>'Offer'),
			array('name' => '询价管理', 'act'=>'inquiry_lists', 'op'=>'Offer'),
			array('name' => '报价单列表', 'act'=>'index', 'op'=>'Offer'),
	)),
	'goods' =>array('name' => '商品管理', 'icon'=>'ico-goods', 'child' => array(
			array('name' => '商品发布', 'act'=>'addStepOne', 'op'=>'Goods'),
			//array('name' => '淘宝导入', 'act'=>'index', 'op'=>'Import'), //临时屏蔽淘宝商品导入
			//array('name' => '商品Excel导入/导出', 'act'=>'excel_index', 'op'=>'Import'),
			array('name' => '上架的商品', 'act'=>'goodsList', 'op'=>'Goods'),
			array('name' => '下架的商品', 'act'=>'goods_offline', 'op'=>'Goods'),
			//array('name' => '库存管理', 'act'=>'stock_list', 'op'=>'Goods'),
			//array('name' => '商品规格', 'act' => 'specList', 'op'=>'Goods'),
			array('name' => '品牌管理', 'act'=>'brandList', 'op'=>'Goods'),
	    	//array('name' => '相册管理', 'act'=>'albumList', 'op'=>'Goods'),
	)),
	'stock' => array('name' => '库存管理', 'icon'=>'ico-store', 'child' => array(
			array('name' => '进货入库', 'act'=>'add', 'op'=>'Stock'),
			array('name' => '入库单', 'act'=>'index', 'op'=>'Stock'),
			array('name' => '库存列表', 'act'=>'goods_stock_lists', 'op'=>'Stock'),
			array('name' => '库存日志', 'act'=>'stock_all_log', 'op'=>'Stock'),
			array('name' => '供应商', 'act'=>'suppliers_list', 'op'=>'Stock'),
	)),
	'order'=>array('name' => '订单物流', 'icon'=>'ico-order', 'child' => array(
			array('name' => '订单列表', 'act'=>'index', 'op'=>'Order'),
			array('name' => '快速开单', 'act'=>'add_order', 'op'=>'Order'),
			//array('name' => '虚拟订单', 'act'=>'virtual_list', 'op'=>'Order'),
			//array('name' => '拼团列表', 'act'=>'team_list', 'op'=>'Order'),
			//array('name' => '拼团订单', 'act'=>'team_order', 'op'=>'Order'),
			array('name' => '发货商品', 'act'=>'delivery_list', 'op'=>'Order'),
			//array('name' => '运费模板', 'act'=>'index', 'op'=>'Freight'),
			//array('name' => '商品评论','act'=>'index','op'=>'Comment'),
			//array('name' => '发票列表','act'=>'index','op'=>'Invoice'),
			//array('name' => '快递公司', 'act'=>'index', 'op'=>'Shipping'),
	)),
	'finance' => array('name' => '财务管理', 'icon'=>'ico-finance', 'child' => array(
			array('name' => '应收账单管理', 'act'=>'index', 'op'=>'Settlement'),
			array('name' => '发票列表', 'act'=>'index', 'op'=>'Invoice'),
			array('name' => '供应商货款管理', 'act'=>'index', 'op'=>'Suppliers'),
			array('name' => '税/费支出管理', 'act'=>'index', 'op'=>'Cost'),
			array('name' => '税/费支出类型', 'act'=>'index', 'op'=>'CostType'),
			array('name' => '提现申请管理', 'act'=>'index', 'op'=>'Withdrawals'),
	)),
	'consult' => array('name' => '售后服务', 'icon'=>'ico-store', 'child' => array(
			array('name' => '咨询管理', 'act'=>'ask_list', 'op'=>'Service'),
			array('name' => '退货换货', 'act'=>'refund_list', 'op'=>'Service'),
		//array('name' => '投诉管理', 'act'=>'complain_list', 'op'=>'Service'),
	)),
	'account' => array('name' => '员工管理', 'icon'=>'ico-account', 'child' => array(
			array('name' => '员工列表', 'act'=>'index', 'op'=>'Admin'),
			array('name' => '岗位管理', 'act'=>'role', 'op'=>'Admin'),
			array('name' => '账号日志', 'act'=>'log', 'op'=>'Admin'),
		//array('name' => '店铺消费', 'act'=>'store_cost', 'op'=>'cost_list'),
		//array('name' => '门店账号', 'act'=>'store_account', 'op'=>'index'),
	)),
	'store' => array('name' => '店铺设置', 'icon'=>'ico-store', 'child' => array(
			array('name' => '店铺设置', 'act'=>'store_setting', 'op'=>'Store'),
			//array('name' => '店铺装修', 'act'=>'store_decoration', 'op'=>'Store'),
			//array('name' => '移动端店铺首页', 'act'=>'template_index', 'op'=>'Store'),
			//array('name' => '店铺自定义页面', 'act'=>'mobile_template', 'op'=>'Store'),
			//array('name' => '店铺导航', 'act'=>'navigation_list', 'op'=>'Store'),
//			array('name' => '经营类目', 'act'=>'bind_class_list', 'op'=>'Store'),
			//array('name' => '关联版式', 'act'=>'plate_list', 'op'=>'Store'),
			array('name' => '店铺信息', 'act'=>'store_info', 'op'=>'Store'),
			array('name' => '店铺分类', 'act'=>'goods_class', 'op'=>'Store'),
			//array('name' => '供货商', 'act'=>'suppliers_list', 'op'=>'Store'),
			//array('name' => '店铺关注', 'act'=>'store_collect', 'op'=>'Store'),
			//array('name' => '消费者保障服务', 'act'=>'index', 'op'=>'Guarantee'),
			//array('name' => '地址管理', 'act'=>'index', 'op'=>'Address'),
	)),
	'statistics' => array('name' => '运营统计', 'icon'=>'ico-statistics', 'child' => array(
			//array('name' => '提现申请', 'act'=>'withdrawals', 'op'=>'Finance'),
			//array('name' => '汇款列表', 'act'=>'remittance', 'op'=>'Finance'),
			//array('name' => '店铺结算记录', 'act'=>'order_statis', 'op'=>'Finance'),
			//array('name' => '未结算订单', 'act'=>'order_no_statis', 'op'=>'Finance'),
			array('name' => '店铺概况', 'act'=>'index', 'op'=>'Report'),
			array('name' => '运营报告', 'act'=>'finance', 'op'=>'Report'),
			array('name' => '销售排行', 'act'=>'saleTop', 'op'=>'Report'),
			//array('name' => '流量统计', 'act'=>'visit', 'op'=>'Report'),
	)),

	/*'message' => array('name' => '客服消息', 'icon'=>'ico-message', 'child' => array(
			array('name' => '客服设置', 'act'=>'store_service', 'op'=>'Index'),
			array('name' => '系统消息', 'act'=>'store_msg', 'op'=>'Index'),
			//array('name' => '聊天记录查询', 'act'=>'store_im', 'op'=>'store'),
	)),*/

	/*'service' => array('name' => '分销', 'icon'=>'ico-live', 'child' => array(
			array('name' => '分销商品', 'act'=>'goods_list', 'op'=>'Distribut'),
			array('name' => '分销设置', 'act'=>'distribut', 'op'=>'Distribut'),
			array('name' => '分成记录', 'act'=>'rebate_log', 'op'=>'Distribut'),
	)),*/
);