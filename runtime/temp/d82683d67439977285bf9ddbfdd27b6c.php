<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:42:"./template/mobile/default/index\index.html";i:1541059663;s:75:"D:\phpstudy\PHPTutorial\WWW\cxs365\template\mobile\default\public\foot.html";i:1540345525;}*/ ?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>欢迎来到主页</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
		<script src="/public/mobile/js/flexible.js"></script>
		<script src="/public/mobile/js/flexible_css.js"></script>
		<link rel="stylesheet" type="text/css" href="/public/mobile/css/base.css">
		<link rel="stylesheet" type="text/css" href="/public/mobile/css/index.css"/>
		<link rel="stylesheet" type="text/css" href="/public/mobile/css/swiper.css">
	</head>
	<body>
		<!--头部栏目-->
		<div id="header" class="header">
			<!--搜索框-->
			<div class="search clear-float">
				<div class="search-container">
					<a class="common left left-float" href="javascript:void(0)"><img src="/public/mobile/img/nav.png"/></a>
					<a href="<?php echo U('goods/search'); ?>">
						<div class="keyword-container">
							<img src="/public/mobile/img/search.png" alt="" />
							<input class="" type="text" name="keyword" id="keyword" value="" placeholder="搜索商品名称"/>
						</div>
					</a>
					<a class="common right right-float" href="javascript:void(0)"><img src="/public/mobile/img/message.png"/></a>
					<div class="shortcut-menu" style="top: 1.48rem;left: 0.1rem;">
						<a class="common-item" href="<?php echo U('index/index'); ?>">
							<i>首页</i>
						</a>
						<a class="common-item" href="<?php echo U('commonlist/index'); ?>">
							<i>常用清单</i>
						</a>
						<a class="common-item" href="<?php echo U('cart/index'); ?>">
							<i>购物车</i>
						</a>
						<a class="common-item" href="<?php echo U('user/index'); ?>">
							<i>个人中心</i>
						</a>
						<!--三角箭头-->
						<div class="triangle_border_up" style="left:5px;">
							<span class="triangle_border_inner"></span>
						</div>
					</div>
				</div>		
			</div>
			<!--轮播图-->
			<div class="swiperone">
				<div class="swiper-container1">
					<div class="swiper-wrapper">
					<?php if(is_array($slide) || $slide instanceof \think\Collection || $slide instanceof \think\Paginator): if( count($slide)==0 ) : echo "" ;else: foreach($slide as $key=>$vo): ?>
						<div class="swiper-slide"><a href="<?php echo $vo['url']; ?>"><img src="<?php echo $vo['img']; ?>"></a></div>
						<?php endforeach; endif; else: echo "" ;endif; ?>
					</div>
					<div class="swiper-pagination"></div>
				</div>
			</div>
			<!--选项按钮-->
			<div class="option">
				<ul class="clear-float">
					<li>
						<a href="<?php echo U('order/index'); ?>">
							<div class="img-contain"><img src="/public/mobile/img/order.png" alt="" /></div>
							<p>我的订单</p>
						</a>						
					</li>
					<!-- <li>
						<a href="">
							<div class="img-contain"><img src="/public/mobile/img/recommand.png" alt="" /></div>
							<p>精品推荐</p>
						</a>						
					</li> -->
					<li>
						<a href="<?php echo U('commonlist/index'); ?>">
							<div class="img-contain"><img src="/public/mobile/img/list.png" alt="" /></div>
							<p>常用清单</p>
						</a>
					</li>
					<li>
						<a href="<?php echo U('User/index'); ?>">
							<div class="img-contain"><img src="/public/mobile/img/center.png" alt="" /></div>
							<p>个人中心</p>
						</a>				
					</li>
					<li>
						<a href="<?php echo U('User/inquiry'); ?>">
							<div class="img-contain"><img src="/public/mobile/img/select.png" alt="" /></div>
							<p>产品询价</p>
						</a>
					</li>
				</ul>
				<div class="notice-container">
					<div class="notice clear-float">
						<div class="left left-float">
							<span>商菜</span>
							<span>快讯</span>
							<span>:</span>
						</div>
						<div class="swiper-container2 middle left-float">
							<div class="swiper-wrapper">
							<?php if(is_array($misc_list) || $misc_list instanceof \think\Collection || $misc_list instanceof \think\Paginator): if( count($misc_list)==0 ) : echo "" ;else: foreach($misc_list as $key=>$vo): ?>
								<p class="swiper-slide description"><a style="float: none; color:#ff1f1f" href="<?php echo U('Misc/article_info',array('id'=>$vo['article_id'])); ?>"><?php echo $vo['title']; ?></a></p>
							<?php endforeach; endif; else: echo "" ;endif; ?>
							</div>
						</div>
						<div class="right left-float">
							<a href="<?php echo U('Misc/article'); ?>">更多</a>
						</div>						
					</div>
				</div>
			</div>
		</div>
		<!--内容栏目-->
		<div id="content" class="content">
			<!--精品推荐-->
			<div class="title1">
				<hr class="left left-float" />
				<div class="text">精品推荐</div>				
				<hr class="right right-float" />
			</div>
			<div class="nice-container">
				<!--第一行-->
				<div class="first-row">
					<ul class="clear-float">
						<li>

							<a href="<?php echo U('Goods/goods_info',array('goods_id'=>$goos_recommend[0]['goods_id'])); ?>">
								<div class="img-con"><img src="<?php echo $goos_recommend[0]['original_img'];?>" alt="" /></div>
								<p><?php echo $goos_recommend[0]['goods_name'];?></p>
							</a>
						</li>
						<li>
							<a href="<?php echo U('Goods/goods_info',array('goods_id'=>$goos_recommend[1]['goods_id'])); ?>">
								<div class="img-con"><img src="<?php echo $goos_recommend[1]['original_img'];?>" alt="" /></div>
								<p><?php echo $goos_recommend[1]['goods_name'];?></p>
							</a>
						</li>
						<li>
							<a href="<?php echo U('Goods/goods_info',array('goods_id'=>$goos_recommend[2]['goods_id'])); ?>">
								<div class="img-con"><img src="<?php echo $goos_recommend[2]['original_img'];?>" alt="" /></div>
								<p><?php echo $goos_recommend[2]['goods_name'];?></p>
							</a>
						</li>
					</ul>
				</div>
				<!--第二行-->
				<div class="second-row">
					<ul class="clear-float">	
						<li>
							<a href="<?php echo U('Goods/goods_info',array('goods_id'=>$goos_recommend[3]['goods_id'])); ?>">
								<div class="img-con"><img src="<?php echo $goos_recommend[3]['original_img'];?>" alt="" /></div>
								<p><?php echo $goos_recommend[3]['goods_name'];?></p>
							</a>
						</li>
						<li>
							<a href="<?php echo U('Goods/goods_info',array('goods_id'=>$goos_recommend[4]['goods_id'])); ?>">
								<div class="img-con"><img src="<?php echo $goos_recommend[4]['original_img'];?>" alt="" /></div>
								<p><?php echo $goos_recommend[4]['goods_name'];?></p>
							</a>
						</li>
						<li>
							<a href="<?php echo U('Goods/goods_info',array('goods_id'=>$goos_recommend[5]['goods_id'])); ?>">
								<div class="img-con"><img src="<?php echo $goos_recommend[5]['original_img'];?>" alt="" /></div>
								<p><?php echo $goos_recommend[5]['goods_name'];?></p>
							</a>
						</li>
						<li>
							<a href="<?php echo U('Goods/goods_info',array('goods_id'=>$goos_recommend[6]['goods_id'])); ?>">
								<div class="img-con"><img src="<?php echo $goos_recommend[6]['original_img'];?>" alt="" /></div>
								<p><?php echo $goos_recommend[6]['goods_name'];?></p>
							</a>
						</li>
					</ul>
				</div>
			</div>
			<!--新品推荐-->
			<div class="title2">
				<div>
					<img src="/public/mobile/img/new.png"/>
				</div>
			</div>
			<div class="new-container">
				<ul class="clear-float">
				 <?php if(is_array($goods_new) || $goods_new instanceof \think\Collection || $goods_new instanceof \think\Paginator): if( count($goods_new)==0 ) : echo "" ;else: foreach($goods_new as $key=>$vo): ?>
					<li>
					<a href="<?php echo U('Goods/goods_info',array('goods_id'=>$vo['goods_id'])); ?>">
						<img class="tag" src="/public/mobile/img/newtag.png">
						<div class="img-con"><img class="common" src="<?php echo $vo['original_img']; ?>" /></div>
						<!-- <div class="star-container">
							<i><img src="/public/mobile/img/star_red.png"></i>
							<i><img src="/public/mobile/img/star_red.png"></i>
							<i><img src="/public/mobile/img/star_red.png"></i>
							<i><img src="/public/mobile/img/star_black.png"></i>
							<i><img src="/public/mobile/img/star_black.png"></i>
						</div> -->
						<span><?php echo $vo['goods_name']; ?></span>
						<!-- <a href=""><?php echo $vo['goods_name']; ?></a> -->
						</a>
					</li>
					<?php endforeach; endif; else: echo "" ;endif; ?>
				</ul>
			</div>
			<!--热销榜-->
			<div class="title3">
				<div><img src="/public/mobile/img/hot.png"></div>
			</div>
			<div class="hot-container">
				<ul class="clear-float">
					<li class="common middle">
						<ul class="clear-float">
							<li class="one">
								<a href="<?php echo U('Goods/goods_info',array('goods_id'=>$goods_hot[0]['goods_id'])); ?>">
									<img src="<?php echo $goods_hot[0]['original_img'];?>">
									<p><?php echo $goods_hot[0]['goods_name'];?></p>
								</a>
							</li>
							<li class="two">
								<a href="<?php echo U('Goods/goods_info',array('goods_id'=>$goods_hot[1]['goods_id'])); ?>">
									<img src="<?php echo $goods_hot[1]['original_img'];?>">
									<p><?php echo $goods_hot[1]['goods_name'];?></p>
								</a>
							</li>
							<li class="three">
								<a href="<?php echo U('Goods/goods_info',array('goods_id'=>$goods_hot[2]['goods_id'])); ?>">
									<img src="<?php echo $goods_hot[2]['original_img'];?>">
									<p><?php echo $goods_hot[2]['goods_name'];?></p>
								</a>
							</li>
							<li class="four">
								<a href="<?php echo U('Goods/goods_info',array('goods_id'=>$goods_hot[3]['goods_id'])); ?>">
									<img src="<?php echo $goods_hot[3]['original_img'];?>">
									<p><?php echo $goods_hot[3]['goods_name'];?></p>
								</a>
							</li>
							<li class="five">
								<a href="<?php echo U('Goods/goods_info',array('goods_id'=>$goods_hot[4]['goods_id'])); ?>">
									<img src="<?php echo $goods_hot[4]['original_img'];?>">
									<p><?php echo $goods_hot[4]['goods_name'];?></p>
								</a>
							</li>
							<li class="six">
								<a href="<?php echo U('Goods/goods_info',array('goods_id'=>$goods_hot[5]['goods_id'])); ?>">
									<img src="<?php echo $goods_hot[5]['original_img'];?>">
									<p><?php echo $goods_hot[5]['goods_name'];?></p>
								</a>
							</li>
						</ul>
					</li>
				</ul>
			</div>
			<!--为你推荐-->
			<div class="title4">
				<hr class="left left-float" />
				<div class="text">为你推荐</div>				
				<hr class="right right-float" />
			</div>
			<div class="foryou-container">
				<div class="product-detail clear-float">
					<!--多品询价-->
					<div class="multitype clear-float index-flyer">
						<div class="father-container clear-float">
							<div class="left left-float">
								<img src="/public/mobile/img/sweetwine.png">
							</div>
							<div class="right right-float">
								<div class="product-title">百利甜酒(英文名:BAILEYS®)</div>
								<div class="switch">
									<div class="isaskprice">
										<input style="position:absolute;visibility:hidden;top:0;left:0;" type="radio" name="consult" value="">
										<span class="ask-btn" data-status="consult">询价</span>
									</div>
									<img data-clickbtn="" class="switch-btn right-float" src="/public/mobile/img/switchdown.png">
								</div>
							</div>
						</div>
						
						<div class="sub-product left-float">
							<div class="sub clear-float">
								<div class="sub-right right-float">
									<div class="sub-title">百利甜酒(英文名:BAILEYS®)<span>600ml</span>/瓶</div>
									<div style="" class="sub-buy clear-float">
										<span class="sub-price"><span>￥889.00</span>/瓶</span>
										<div class="count-option right-float">
											<img class="btn reduce" src="/public/mobile/img/cart/cart_18.png">
											<input class="count" type="number" name="countproduct" value="0">
											<img class="btn add" src="/public/mobile/img/cart/cart_20.png">
										</div>
										<div class="count-first right-float">
											<img class="btn first-add" src="/public/mobile/img/add.png" alt="">
										</div>	
									</div>
								</div>
							</div>
							<div class="sub clear-float">
								<div class="sub-right right-float">
									<div class="sub-title">百利甜酒(英文名:BAILEYS®)<span>1000ml</span>/瓶</div>
									<div style="" class="sub-buy clear-float">
										<span class="sub-price"><span>￥1359.00</span>/瓶</span>
										<div class="count-option right-float">
											<img class="btn reduce" src="/public/mobile/img/cart/cart_18.png">
											<input class="count" type="number" name="countproduct" value="100">
											<img class="btn add" src="/public/mobile/img/cart/cart_20.png">
										</div>
										<div class="count-first right-float">
											<img class="btn first-add" src="/public/mobile/img/add.png" alt="">
										</div>	
									</div>
								</div>
							</div>
						</div>
					</div>
	
					<!--单品询价-->
					<div class="multitype clear-float index-flyer">
						<div class="father-container clear-float">
							<div class="left left-float">
								<img src="/public/mobile/img/sweetwine2.png">
							</div>
							<div class="right right-float">
								<div class="product-title">百利甜酒(英文名:BAILEYS®)</div>
								<div class="switch">
									<div style="display: none;" class="isaskprice">
										<input style="position:absolute;visibility:hidden;top:0;left:0;" type="radio" name="consult" value="">
										<span class="ask-btn" data-status="consult">询价</span>
									</div>
									<div >
										<span>￥889.00/瓶</span>
										<div class="count-option right-float">
											<img class="btn reduce" src="/public/mobile/img/cart/cart_18.png">
											<input class="count" type="number" name="countproduct" value="100">
											<img class="btn add" src="/public/mobile/img/cart/cart_20.png">
										</div>
										<div class="count-first right-float">
											<img class="btn first-add" src="/public/mobile/img/add.png" alt="">
										</div>
									</div>
								</div>

							</div>
						</div>
					</div>				
				</div>
			</div>
			<div class="nothing"></div>
		</div>
		<!--页面底部-->
		<div id="footer" class="footer">
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
		</div>
		<script type="text/javascript">
			document.addEventListener("DOMContentLoaded", function(e){
				document.getElementsByTagName("html")[0].style.fontSize = window.innerWidth / 10 + "px";
			}, false);
		</script>
		<script type="text/javascript" src="/public/mobile/js/jquery-1.11.3.js"></script>
		<script type="text/javascript" src="/public/mobile/js/swiper.js"></script>
		<script type="text/javascript" src="/public/mobile/js/jquery.fly.min.js"></script>
		<script type="text/javascript" src="/public/mobile/js/common.js"></script>
		<script type="text/javascript">
			//点击点状按钮弹出菜单导航
			//显示导航
			$(".search-container a.left").click(function(e){
				e.preventDefault();
				e.stopPropagation();
				// $(".shortcut-menu").css({display: "block"});
				$(".shortcut-menu").toggle();
			})
			$(document).click(function(){
				// $(".shortcut-menu").css({display: "none"});
				$(".shortcut-menu").hide();
			});
			//弹出输入数量框
        	inputNum("[name=countproduct]");
			//轮播图和文字
			$(function(){
				var mySwiper1 = new Swiper(".swiper-container1", {
					direction: "horizontal",
					autoplay: {
						delay: 3000,
						stopOnLastSlide: false,
						disableOnInteraction: false
					},
					loop: false,
					pagination: {
						el: ".swiper-pagination"
					}
				})
				var mySwiper2 = new Swiper(".swiper-container2", {
					direction: "vertical",
					autoplay: {
						delay: 3000,
						stopOnLastSlide: false,
						disableOnInteraction: false
					},
					loop: false,
					autoHeight: true
				})
			});
			//多商品点击出现详细商品信息按钮
			(function(){
				$(document).on("click", ".switch-btn", function(){
					if(!$(this).attr("data-clickbtn")){
						$(this).attr("src", "/public/mobile/img/switchup.png");
						$(this).attr("data-clickbtn", "ok");
						$(this).parent().parent().parent().parent().find(".sub-product").slideDown(300);
					}
					else{
						$(this).attr("src", "/public/mobile/img/switchdown.png");
						$(this).attr("data-clickbtn", "")
						$(this).parent().parent().parent().parent().find(".sub-product").slideUp(300);
					}
				});
			})();

			var input = $('input[name="keyword"]');
			input.keyup(function(e){
			// 如果键盘点击回车 获取传值input的值
			if( e.keyCode === 13 && input.val() != ""){
			  $('#theForm').submit();
			  // console.log(value);
			}
			});
			//购买按钮
			(function(){
				$(document).on("click", ".count-first", function(){
					var nowthis = this;       
					var cartOffset = $(".ify-cart").offset();
					var addimg = $(this).parents('.index-flyer').find('.left.left-float').find('img').attr('src');
					var $flyer = $("<img class='flyer' src='" + addimg +"'>");
					$flyer.fly({
						start: {
							left: event.pageX,
							top: event.clientY
						},
						end: {
							left: cartOffset.left + 40,
							top: window.innerHeight - 40,
							width: 0,
							height: 0
						},
						speed: 1.1,
						vertex_Rtop: 70,
						onEnd: function () {
							var nowCount = parseFloat($(nowthis).prev().find(".count").val());
							$(nowthis).hide().prev().addClass("active").find(".count").val(nowCount + 1);
							this.destroy();
						}
					});
				});
				//点击加按钮
				$(document).on("click", ".add", function(){
					var nowCount = parseFloat($(this).prev().val());
					$(this).prev().val(nowCount + 1);
				});
				//点击减按钮
				$(document).on("click", ".reduce", function(){
					var nowCount = parseFloat($(this).next().val());
					var finalCount = nowCount - 1;
					$(this).next().val(finalCount);
					if(finalCount == 0){
						$(this).parent().removeClass("active").next().show();
					}
				});
			})();
			//向下滚动搜索框变色
			(function(){
				$(window).scroll(function(){
					var scrollTop = $(window).scrollTop();
					if(scrollTop >= 60 && scrollTop <= 200){
						val = scrollTop / 200;
						$(".search").css({backgroundColor: "rgba(77, 188, 125, "+val+")"});
					}
					if(scrollTop < 60){
						$(".search").css({backgroundColor: "rgba(0, 0, 0, 0.3)"});
					}
					if(scrollTop > 200){
						$(".search").css({backgroundColor: "rgba(77, 188, 125, 1)"});
					}
				})
			})();
			//点击询价
			(function(){
				$(".foryou-container").on("click", ".ask-btn", function(){
					if($(this).attr("data-status")){
						$(this).html("等待报价").css({backgroundColor: "#D6D6D6"}).attr("data-status", "").prev().prop("checked", true);
					}
				});
			})();
			//页面加载完后若有购买数则显示完整加减按钮
			$(window).load(function(){
				var $allProducts = $(".multitype .count-option input");
				$allProducts.each(function(index, domEle){
					if(parseFloat($(this).val()) > 0){
						$(this).parent().addClass("active").next().hide();
					}
				});
			});
		</script>
	</body>
</html>
