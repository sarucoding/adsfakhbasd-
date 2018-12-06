<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:41:"./template/mobile/default/user\index.html";i:1540531259;s:75:"D:\phpstudy\PHPTutorial\WWW\cxs365\template\mobile\default\public\foot.html";i:1540345525;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>个人中心</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
	<script src="/public/mobile/js/flexible.js"></script>
	<script src="/public/mobile/js/flexible_css.js"></script>
	<link rel="stylesheet" type="text/css" href="/public/mobile/css/base.css">
	<link rel="stylesheet" type="text/css" href="/public/mobile/css/usercenter.css"/>
	<style>
		#order .three-account li{width: 16.66%}
	</style>
</head>
<body>
	<!--用户信息概览-->
	<div id="user" class="user">
		<div class="userinfo1">
			<div class="container">
				<div class="check-ok">
					<?php if($stoer['is_contract'] == 1): ?>
						<img src="/public/mobile/img/usercenter/user_03.png">
					<?php endif; ?>
				</div>
				<div class="name">
					<h3><?php echo $user[realname]; ?></h3>
				</div>
				<div class="avatar">
					<img src="/public/mobile/img/usercenter/avatar.png">
				</div>
			</div>			
		</div>
		<div class="userinfo2 clear-float">
			<div class="left left-float">
				<div><a href="<?php echo U('user/account'); ?>">查看账户明细</a></div>
				<div><?php echo $stoer['store_money']; ?></div>
			</div>
			<div class="right right-float">
				<div><a href="<?php echo U('user/user_setting'); ?>">账户管理<img src="/public/mobile/img/usercenter/user_07.png"></a></div>
			</div>
		</div>
	</div>
	<!--订单信息-->
	<div id="order" class="order">
		<div class="order-container">
			<div class="one clear-float">
				<div class="left-float"><img src="/public/mobile/img/usercenter/user_11.png">我的订单</div>
				<div class="right-float"><a href="<?php echo U('order/index'); ?>">查看全部订单</a><img src="/public/mobile/img/usercenter/user_14.png"></div>
			</div>
			<div class="line"></div>
			<div class="two clear-float">
				<div data-select="way1" class="left-float active">普通订单</div>
				<div data-select="way2" class="right-float">账期订单</div>
			</div>

			<ul style="display: block;" class="three-cash clear-float">
				<li>
					<a href="<?php echo U('order/index',array('navIndex'=>1)); ?>">
						<img src="/public/mobile/img/usercenter/user_50.png">
						<div>待付款</div>
					</a>			
				</li>
				<li>
					<a href="<?php echo U('order/index',array('navIndex'=>2)); ?>">
						<img src="/public/mobile/img/usercenter/user_19.png">
						<div>待发货</div>
					</a>			
				</li>
				<li>
					<a href="<?php echo U('order/index',array('navIndex'=>3)); ?>">
						<img src="/public/mobile/img/usercenter/user_21.png">
						<div>实发数</div>
					</a>
				</li>
				<li>
					<a href="<?php echo U('order/index',array('navIndex'=>4)); ?>">
						<img src="/public/mobile/img/usercenter/user_51.png">
						<div>待付尾款</div>
					</a>			
				</li>
				<li>
					<a href="<?php echo U('order/index',array('navIndex'=>5)); ?>">
						<img src="/public/mobile/img/usercenter/user_23.png">
						<div>待收货</div>
					</a>
				</li>
				<li>
					<a href="<?php echo U('order/index',array('navIndex'=>6)); ?>">
						<img src="/public/mobile/img/usercenter/user_27.png">
						<div>已完成</div>
					</a>
				</li>
			</ul>
			<ul style="display: none;" class="three-account clear-float">
				<li>
					<a href="<?php echo U('order/index',array('navIndex'=>1,'order_type'=>1)); ?>">
						<img src="/public/mobile/img/usercenter/user_19.png">
						<div>待发货</div>
					</a>
				</li>
				<li>
					<a href="<?php echo U('order/index',array('navIndex'=>2,'order_type'=>1)); ?>">
						<img src="/public/mobile/img/usercenter/user_21.png">
						<div>实发数</div>
					</a>
				</li>
				<li>
					<a href="<?php echo U('order/index',array('navIndex'=>3,'order_type'=>1)); ?>">
						<img src="/public/mobile/img/usercenter/user_23.png">
						<div>待收货</div>
					</a>
				</li>
				<li>
					<a href="<?php echo U('order/index',array('navIndex'=>4,'order_type'=>1)); ?>">
						<img src="/public/mobile/img/usercenter/user_23.png">
						<div>待扎账</div>
					</a>
				</li>
				<li>
					<a href="<?php echo U('order/index',array('navIndex'=>5,'order_type'=>1)); ?>">
						<img src="/public/mobile/img/usercenter/user_25.png">
						<div>待付款</div>
					</a>
				</li>
				<li>
					<a href="<?php echo U('order/index',array('navIndex'=>6,'order_type'=>1)); ?>">
						<img src="/public/mobile/img/usercenter/user_27.png">
						<div>已付款</div>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<!--账单信息-->
	<div id="bill" class="bill">
		<div class="bill-container">
			<div class="one clear-float">
				<div class="left-float"><img src="/public/mobile/img/usercenter/user_34.png">我的账单</div>
				<div class="right-float"><!--<a href="">去结算</a><img src="/public/mobile/img/usercenter/user_14.png">--></div>
			</div>
			<div class="line"></div>
			<ul class="two clear-float">
				<li>
					<p>0.00</p>
					<p>本月待还</p>
				</li>
				<li>
					<p>90000.00</p>
					<p>现有账期</p>
				</li>
				<li>
					<p>59999.00</p>
					<p>下月待还</p>
				</li>
			</ul>
			<div class="line2"></div>
			<div class="account">
				<a href="<?php echo U('settlement/index'); ?>">查看账单明细</a>
			</div>
		</div>
	</div>
	<!--其他信息-->
	<div id="otherinfo" class="otherinfo">
		<div class="otherinfo-container">
			<div class="common">
				<a href="<?php echo U('User/address_list'); ?>">收货地址管理</a>
			</div>
			<div class="common">
				<a href="<?php echo U('User/bill'); ?>">我的报价单</a>
			</div>
			<div class="common">
				<a href="<?php echo U('User/store_list'); ?>">我的供应商</a>
			</div>
			<!-- <div class="line"></div>
			<div class="common">
				<a href="">子账户管理</a>
			</div> -->
		</div>
	</div>
	<!--帮助信息-->
	<div id="help" class="help">
		<div class="help-container">
			<div class="helpcenter">
				<a href="<?php echo U('Misc/index'); ?>"><img src="/public/mobile/img/usercenter/user_37.png">帮助中心</a>
			</div>
			<div class="line"></div>
			<div class="help-btn">
				<ul class="clear-float">
					<li>
						<a href="">
							<img src="/public/mobile/img/usercenter/user_41.png">
							<p>常见问题</p>
						</a>
					</li>
					<li>
						<a href="">
							<img src="/public/mobile/img/usercenter/user_44.png">
							<p>服务中心</p>
						</a>
					</li>
					<li>
						<a href="">
							<img src="/public/mobile/img/usercenter/user_47.png">
							<p>在线客服</p>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<!--占位标签，下方的菜单按钮会遮挡部分内容-->
	<div id="nothing" class="nothing"></div>
	<!--页面底部导航菜单-->
<div id="footer" class="footer">
  <div class="menu clear-float">
    <a class="index left-float<?php if($footer_show == 0): ?> active<?php endif; ?>" href="<?php echo U('index/index'); ?>">
      <i></i>
      <div>首页</div>
    </a>
    <a class="types left-float<?php if($footer_show == 1): ?> active<?php endif; ?>" href="<?php echo U('Goods/index'); ?>">
      <i></i>
      <div>分类</div>
    </a>
    <a class="list left-float<?php if($footer_show == 2): ?> active<?php endif; ?>" href="<?php echo U('Commonlist/index'); ?>">
      <i></i>
      <div>常用清单</div>
    </a>
    <a class="cart left-float ify-cart<?php if($footer_show == 3): ?> active<?php endif; ?>" href="<?php echo U('Cart/index'); ?>">
      <i></i>
      <div>购物车</div>
    </a>
    <a class="user left-float<?php if($footer_show == 4): ?> active<?php endif; ?>" href="<?php echo U('User/index'); ?>">
      <i></i>
      <div>我的</div>
    </a>
  </div>
</div>
	<script type="text/javascript">
		document.addEventListener("DOMContentLoaded", function(e){
			document.getElementsByTagName("html")[0].style.fontSize = window.innerWidth / 10 + "px";
		}, false);
	</script>
	<script src="/public/mobile/js/jquery-1.11.3.js"></script>
	<script src="/public/mobile/js/usercenter.js"></script>
</body>
</html>
