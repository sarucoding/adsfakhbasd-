<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:48:"./template/mobile/default/user\user_setting.html";i:1539655119;}*/ ?>
<!DOCTYPE html>
<html style="background:#f4f4f4;">
<head>
	<meta charset="utf-8">
	<title>个人中心</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
	<script src="/public/mobile/js/flexible.js"></script>
	<script src="/public/mobile/js/flexible_css.js"></script>
	<link rel="stylesheet" type="text/css" href="/public/mobile/css/base.css">
	<link rel="stylesheet" type="text/css" href="/public/mobile/css/usercenter.css">
</head>
<body>
	<!-- 头部、返回、标题 -->
	<div class="twostatus-header" style="padding-bottom: 0.6rem;">
		<div class="container">
			<a class="back" href="javascript:history.go(-1);">
				<img src="/public/mobile/img/register/register1.png">
			</a>
			<span style="margin-left: 35%">账户设置</span>
		</div>
		<!-- 个人信息 -->
		<div class="set-usermsg">
			<a href="<?php echo U('User/edit_userdata'); ?>">
				<!-- 头像 -->
				<img src="<?php echo $user['head_pic']; ?>" class="avater">
				<!-- 信息 -->
				<div class="msg">
					<p class="p1"><?php echo !empty($user['nick_name'])?$user['nick_name']:$user['mobile']; ?></p>
					<p class="p2">用户名:<b style="font-weight:500;"><?php echo $user['mobile']; ?></b></p>
					<img src="/public/mobile/img/cart/bigarrow.png">
				</div>
			</a>
		</div>
	</div>
	<!--其他信息-->
	<div class="set-help">
		<a href="">
			<p>
				<span>实名认证</span>
				<img src="/public/mobile/img/cart/bigarrow.png" class="data-img">
			</p>
		</a>
		<a href="">
			<p>
				<span>账户安全</span>
				<img src="/public/mobile/img/cart/bigarrow.png" class="data-img">
			</p>
		</a>
		<a href="<?php echo U('User/pay_setting'); ?>">
			<p>
				<span>支付设置</span>
				<img src="/public/mobile/img/cart/bigarrow.png" class="data-img">
			</p>
		</a>
			
	</div>
	<!-- 退出 -->
	<div class="set-quit">
		<a onclick="submitverify()" href="###">退出登录</a>
	</div>
	<script src="/public/layui/layui.js"></script>
	<script src="/public/mobile/js/jquery-1.11.3.js"></script>
	<script type="text/javascript">
		document.addEventListener("DOMContentLoaded", function (e) {
				document.getElementsByTagName("html")[0].style.fontSize = window.innerWidth / 10 + "px";
			}, false);

		layui.use('layer',function(){
        	var layer = layui.layer;
      	});
		function submitverify(){
			$.ajax({
				type : 'post',
				url : '/index.php?m=Mobile&c=Reglog&a=out_login&t='+Math.random(),
				data : {out:1},
				dataType : 'json',
				success : function(res){
					if(res.status == 1){
					  layer.msg(res.msg);
					  location.href="<?php echo U('Reglog/login'); ?>";
					}else{
					  layer.msg(res.msg);
					}
				},
				error : function() {
					flag = false;
					layer.msg('网络失败，请刷新页面后重试');
				}
			})

		}


	</script>
</body>
</html>
