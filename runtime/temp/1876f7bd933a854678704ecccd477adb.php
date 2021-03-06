<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:81:"D:\phpstudy\PHPTutorial\WWW\cxs365/application/mobile_admin\view\admin\login.html";i:1544059457;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>欢迎登录</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
  <script type="text/javascript" src="/public/mobile_admin/js/flexible.min.js"></script>
  <script type="text/javascript" src="/public/mobile_admin/js/jquery-1.11.3.js"></script>
  <link rel="stylesheet" href="/public/mobile_admin/css/common.css">
  <link rel="stylesheet" href="/public/mobile_admin/css/mobile_admin.css">
  <style>
    .login-header{
      text-align: center;
      padding: 0.35rem 0;
    }
    .login-header div{
      color:#fff;
      font-family: 'Microsoft YaHei';
      font-size: 18px;
    }
    .login-logo{
      text-align: center;
      margin-top: 2rem;
      margin-bottom: 1rem;
    }
    .login-content{
      padding: 0 0.2rem;
    }
    .login-content .user-pwd{
      position: relative;
      width: 100%;
      border: 1px solid #ddd;
      border-radius: 3px;
      margin-bottom: 0.2rem;
      background: #fff;
    }
    .login-content .user-pwd input{
      height: 1rem;
      padding-left: 1.5rem;
      width: 70%;
      outline: none;
      font-size: 14px;
    }
    .login-content i{
      background-image: url(/public/mobile_admin/img/user.png);
      background-size: cover;
      width: 1.2rem;
      height: 1rem;
      display: inline-block;
      position: absolute;
      left: -0.03rem;
      top: 0;
      border-radius: 3px 0 0 3px;
      background-repeat: no-repeat;
      background-color: #ddd;
    }
    .login-content i.i2{
      background-image: url(/public/mobile_admin/img/pwd.png);
    }
    .login-content .login-submit{
      width: 100%;
      display: block;
      text-align: center;
      color: #fff;
      height: 40px;
      line-height: 40px;
      font-size: 16px;
      border: none;
      border-radius: 4px;
      outline: none;
    }
  </style>
</head>
<body>
  <header class="login-header bg-default">
    <div>登录</div>
  </header>
  <div class="login-logo">
    <img src="/public/mobile_admin/img/logo.png" width="240">
  </div>
  <form action="" method="" name="">
    <div class="login-content">
      <div class="user-pwd">
        <i></i>
        <input type="text" name="seller_name" placeholder="请输入用户名">
      </div>
      <div class="user-pwd">
        <i class="i2"></i>
        <input type="password" name="password" placeholder="请输入密码">
      </div>
      <div>
        <input type="button" value="登录"  class="login-submit bg-default">
      </div>
    </div>
    <?php echo token(); ?>
  </form>
  <script>
    $(function(){
      $('.login-submit').click(function(){
        var seller_name = $('[name="seller_name"]').val();
        var password = $('[name="password"]').val();
        var token = $('[type="hidden"]').val();
        var param = {'seller_name':seller_name,'password':password,'__token__':token};
        $.post('<?php echo U("admin/login"); ?>',param,function(res){
          var data = JSON.parse(res);
          switch (data.status){
            case 0:
              //失败信息处理
              console.log(data.msg);
              break;
            case 1:
              //登录验证通过，msg为需要跳转的URL
              location.href=data.msg;
              break;
            default:
          }
        })
      });
    });
  </script>
</body>
</html>