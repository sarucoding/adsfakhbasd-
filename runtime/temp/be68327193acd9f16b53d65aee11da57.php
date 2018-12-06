<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:81:"D:\phpstudy\PHPTutorial\WWW\cxs365/application/mobile_admin\view\index\index.html";i:1543989850;s:81:"D:\phpstudy\PHPTutorial\WWW\cxs365\application\mobile_admin\view\public\base.html";i:1544002267;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>修改订单信息</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <script type="text/javascript" src="/public/mobile_admin/js/flexible.min.js"></script>
    <link rel="stylesheet" href="/public/mobile_admin/css/common.css">
    <link rel="stylesheet" href="/public/mobile_admin/css/mobile_admin.css">
    
</head>
<body class="bg-deep-gray">

<div class="main-header clear-flow">
    <div class="header-info">
        <div class="avatar bg-light-gray"><img src="/public/mobile_admin/img/avatar.png" alt=""></div>
        <div class="account text-white text-big">15730373998管理员</div>
    </div>
    <div class="header-nav">
        <ul class="text-none">
            <li class="text-small">
                <a class="text-white" href="">
                    <span>100000.00</span>
                    <span>今日销售额</span>
                </a>
            </li>
            <li class="text-small">
                <a class="text-white" href="">
                    <span>0.00</span>
                    <span>今日订单数</span>
                </a>
            </li>
            <li class="text-small">
                <a class="text-white" href="">
                    <span>0.00</span>
                    <span>待发货订单</span>
                </a>
            </li>
        </ul>
    </div>
</div>
<div class="main-order">
    <div class="order-all text-none">
        <a href="./order.html" class="text-none text-black">
            <div class="common title text-big">全部订单</div>
            <div class="common go-arrow text-deepgray text-small">查看全部订单</div>
        </a>
    </div>
    <div class="order-btns">
        <ul>
            <li class="bottom">
                <a href="">
                    <em class="con"></em>
                    <span>待确认</span>
                </a>
                <div class="number text-xsmall">99</div>
            </li>
            <li class="bottom">
                <a href="">
                    <em class="send"></em>
                    <span>待发货</span>
                </a>
                <div class="number text-xsmall">99</div>
            </li>
            <li class="bottom">
                <a href="">
                    <em class="num"></em>
                    <span>待确认实发数</span>
                </a>
                <div class="number text-xsmall">98</div>
            </li>
            <li class="bottom">
                <a href="">
                    <em class="last-mon"></em>
                    <span>待付尾款</span>
                </a>
                <div class="number text-xsmall">97</div>
            </li>
            <li>
                <a href="">
                    <em class="wait-take"></em>
                    <span>待收货</span>
                </a>
                <div class="number text-xsmall">96</div>
            </li>
            <li>
                <a href="">
                    <em class="take"></em>
                    <span>已收货</span>
                </a>
                <div class="number text-xsmall">95</div>
            </li>
            <li>
                <a href="">
                    <em class="cancle"></em>
                    <span>已取消</span>
                </a>
                <div class="number text-xsmall">94</div>
            </li>
            <li>
                <a href="">
                    <em class="delete"></em>
                    <span>已作废</span>
                </a>
                <div class="number text-xsmall">93</div>
            </li>
        </ul>
    </div>
</div>

<div class="main-footer text-none">
    <div class="com footer-home">
        <a class="text-black" href="<?php echo U('index'); ?>">
            <em class="home"></em>
            <span class="text-xsmall">首页</span>
        </a>
    </div>
    <div class="com footer-statistics">
        <a class="text-black" href="<?php echo U('store/charts'); ?>">
            <em class="statistics"></em>
            <span class="text-xsmall">统计</span>
        </a>
    </div>
    <div class="com footer-exit">
        <a class="text-black" href="<?php echo U('admin/logout'); ?>">
            <em class="exit"></em>
            <span class="text-xsmall">退出</span>
        </a>
    </div>
</div>

</body>
</html>