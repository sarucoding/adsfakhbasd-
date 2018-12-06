<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:43:"./template/mobile/default/reglog\login.html";i:1540883585;s:77:"D:\phpstudy\PHPTutorial\WWW\cxs365\template\mobile\default\public\header.html";i:1540375599;}*/ ?>
<!DOCTYPE html>
<html>
<head>
 <meta charset="UTF-8">
    <title></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <link rel="stylesheet" type="text/css" href="/public/mobile/css/userlogin.css">
    <link rel="stylesheet" type="text/css" media="all" href="/public/layui/css/layui.css">
    <link rel="stylesheet" href="/public/address/css/ydui.css?rev=@@hash"/>
	<link rel="stylesheet" type="text/css" href="/public/mobile/css/base.css">
	<link rel="stylesheet" type="text/css" href="/public/mobile/css/cart.css"/>
    <link rel="stylesheet" type="text/css" href="/public/mobile/css/address.css"/>
    <script type="text/javascript" src="/public/mobile/js/flexible.js"></script>
    <script type="text/javascript" src="/public/mobile/js/flexible_css.js"></script>
    <script type="text/javascript" src="/public/layui/layui.js"></script>
    <script type="text/javascript" src="/public/mobile/js/jquery-1.11.3.js"></script>
    <script type="text/javascript" src="/public/mobile/js/area.js"></script>
</head>
<body>
	<style>
		body{
			background:#fff;
		}
	</style>
	<div class="welcome">Welcome</div>
	<div class="login">
		<form class="login-form" onsubmit="return false;"  method="post">
			<div class="border">
				<img src="/public/mobile/img/register/portrait.png"/>
				<input type="text" id="username" name="username" value="" placeholder="账号、手机号">
			</div>
			<div class="border">
				<img src="/public/mobile/img/register/upwd.png">
				<input type="password" id="password" name="password" value="" placeholder="密码">
			</div>
			<div class="login-btn">
				<input type="button"  onclick="submitverify()" name="submit" value="登录">
				<input type="hidden" name="referurl" id="referurl" value="<?php echo $referurl; ?>">
				<div>
					<span>
						<a href="<?php echo U('/index.php?m=Mobile&c=Reglog&a=reg'); ?>" style="text-decoration: none;">立即注册</a>
					</span>
					<span>
						<a href="<?php echo U('/index.php?m=Mobile&c=Reglog&a=retrieve'); ?>" style="text-decoration: none;">忘记密码?</a>
					</span>
				</div>
			</div>
		</form>
	</div>
<script type="text/javascript">

	 function submitverify()
    {	layui.use('layer',function(){
	    layer = layui.layer;
        var username = $.trim($('#username').val());
        var password = $.trim($('#password').val());
        var referurl = $('#referurl').val();
        if(username == ''){
            layer.msg('用户名不能为空!');
            return false;
        }
        if(password == ''){
            layer.msg('密码不能为空!');
            return false;
        }
     
        var data = {username:username,password:password};
        $.ajax({
            type : 'post',
            url : '/index.php?m=Mobile&c=Reglog&a=do_login&t='+Math.random(),
            data : data,
            dataType : 'json',
            success : function(res){
                if(res.status == 1){
                    var url = "<?php echo U('user/index'); ?>"
                    window.location.href = url;
                }else{
                    layer.msg(res.msg);
                }
            },
            error : function(XMLHttpRequest, textStatus, errorThrown) {
                layer.msg('网络失败，请刷新页面后重试');
            }
        })
    });
    }
</script>
</body>
</html>