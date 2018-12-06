// order-detail页面的商品列表、判断、隐藏、点击显示隐藏商品
$(function(){
	var order_li = $(".detail-ul>li");
	var order_div = $(".order-list-hidden");
	var order_ul = $(".detail-li");
	// 判断一共有多少商品，i<3 不显示隐藏块 , i>3 显示块和隐藏商品
	$.each(order_li,function(i){
		if(i<3){
			order_div.hide();
		}else{
			order_div.show();
			order_ul.hide();
		}
	});
	// 点击块，显示隐藏的商品；slideDown()往下增加高度。
	order_div.click(function(){
		$(this).hide();
		order_ul.slideDown(500);
	});
});
// order页面的顶部订单下拉、状态选择下拉
$(function(){
	var cover = $('.order-cover');
	// 订单下拉事件
	$('.ordinary').click(function(){
		if ($(this).find('img').attr('src') === 'img/order/down-arrow2.png'
			&& $('.cover-d1').hide() ){
			$(this).find('img').attr('src', 'img/order/up-arrow2.png');
			cover.addClass('cover-color').show().find('.cover-d1')
				.show().next().hide().prev().find('.cover-bill').slideDown(300);
		}else{
			$(this).find('img').attr('src', 'img/order/down-arrow2.png');
			$('.cover-bill').slideUp(300);
			setTimeout(() => {
				cover.addClass('cover-color').removeClass('cover-color');
			}, 400);
		};
		});
	// 账期订单点击显示事件
	$('.my-account').click(function(){
		console.log('我是账期订单');
		$('.cover-bill').slideUp(200);
		setTimeout(() => {
			cover.addClass('cover-color').removeClass('cover-color');
		}, 250);
		$('.order-header-ordinary').hide().next().show().find('.ordinary img')
			.attr('src', 'img/order/down-arrow2.png');
	});
	// 普通订单点击显示事件
	$('.my-ordinary').click(function () {
		console.log('我是普通订单');
		$('.cover-bill').slideUp(200);
		setTimeout(() => {
			cover.addClass('cover-color').removeClass('cover-color');
		}, 250);
		$('.order-header-ordinary').show().next().hide();
		$('.order-header-ordinary').find('.ordinary img').attr('src', 'img/order/down-arrow2.png');
	});
	// 下拉箭头选择状态事件
	$('.status-dropdown').click(function(){
		cover.addClass('cover-color');
		$('.cover-d2').show()
		.prev().hide();
		$('.cover-menu').slideDown(300);
	});
	// 上拉箭头隐藏状态栏事件
	$('.status-img').click(function(){
		$('.cover-menu').slideUp(300);
		setTimeout(() => {
			$('.cover-status').hide().show();
		}, 330);
		setTimeout(() => {
			cover.addClass('cover-color').removeClass('cover-color');
		}, 350);
	});
	// 阻止事件冒泡,点击子元素会触发cover的隐藏事件，导致看不到子元素的点击事件
	$('.cover-d1,.cover-d2').click(function(e){ e.stopPropagation(); });
	// 点击覆盖层隐藏全部
	cover.click(function(){
		// 订单事件隐藏
			$('.cover-bill').slideUp(300);
			setTimeout(() => {
				cover.addClass('cover-color').removeClass('cover-color');
			}, 400);
			$('.ordinary').find('img').attr('src', 'img/order/down-arrow2.png');
		// 选择事件隐藏
			$('.cover-menu').slideUp(300);
	});
});
// ify页面
$(function(){
	// 点击切换商品列表
	$('.ify-option>ul').on('click','li',function(){
		$(this).addClass('opt-active').siblings().removeClass('opt-active');
		console.log(this);
		if ($(this).is(".li1") == true){
			console.log(0);
			$('.china-food').show().siblings().hide();
		} else if ($(this).is(".li2") == true){
			console.log(1);
			$('.japan-food').show().siblings().hide();
		} else if ($(this).is(".li3") == true) {
			console.log(2);
			$('.usa-food').show().siblings().hide();
		} else if ($(this).is(".li4") == true) {
			console.log(3);
			$('.hot-pot').show().siblings().hide();
		} else if ($(this).is(".li5") == true) {
			console.log(4);
			$('.freeze').show().siblings().hide();
		} else if ($(this).is(".li6") == true) {
			console.log(5);
			$('.other-food').show().siblings().hide();
		}
	});
	// 点击显示筛选框
	var cover = $('.ify-cover');
	$('.two-menu-opt').click(function(){
		cover.addClass('cover-color').show()
			.find('.ify-cover-opt').addClass('ify-cover-move');
	});
	// 筛选框 添加class
	$('.ify-cover-opt ul').on('click','li',function(){
		$(this).addClass('ify-cover-active').siblings().removeClass('ify-cover-active');
	});
	// 点击 “确认” 隐藏遮挡层
	$('.cover-confirm').click(function(){
		$('.ify-cover-opt').removeClass('ify-cover-move')
			.parent().removeClass('cover-color');
	});
	// 点击 "重置" ul返回默认状态
	$('.cover-reset').click(function(){
		
		$(this).parent().prev().find('li').eq(0).addClass('ify-cover-active').siblings().removeClass('ify-cover-active');
	})
	// 选择框 添加class ;选择框内选中后，隐藏遮挡层
	$('.order-cover .cover-menu ul').on('click','li',function(){
		$(this).find('span').addClass('active').parent().siblings()
		.find('span').removeClass('active');
		setTimeout(() => {
			$('.order-cover').removeClass('cover-color');
		}, 200);
	});
	// 遮挡层 点击隐藏
	cover.click(function(){
		$('.ify-cover-opt').removeClass('ify-cover-move');
		$(this).removeClass('cover-color');
	});
	// 左侧子元素中动态添加占位div
	$('.commonlist-content .foryou-container').append('<div style="height:5rem;"></div>');
	// 阻止事件冒泡
	$('.ify-cover-opt , .twostatus-header').click(function(e){ e.stopPropagation(); })
	// 点击多品下拉箭头单自下拉
	$(".foryou-container").on("click", ".switch-btn", function () {
		if ($(this).attr("src") === "img/switchdown.png") {
			$(this).attr("src", "img/switchup.png");
			$(this).parent().parent().parent().find(".sub-product").slideDown(1000);
		} else {
			$(this).attr("src", "img/switchdown.png");
			$(this).parent().parent().parent().find(".sub-product").slideUp(1000);
		};
	});
	//1.多品购买处
	$(".foryou-container .count-first").click(function () {
		$(this).hide().prev().addClass("active").find(".count").val(1);
	});
	//点击加按钮
	$(".foryou-container").on("click", ".add", function () {
		var nowCount = parseFloat($(this).prev().val());
		$(this).prev().val(nowCount + 1);
	});
	//点击减按钮
	$(".foryou-container").on("click", ".reduce", function () {
		var nowCount = parseFloat($(this).next().val());
		var finalCount = nowCount - 1;
		$(this).next().val(finalCount);
		if (finalCount < 1) {
			$(this).next().val(1);
		}
	});
	// 滑动菜单 横拉条
	$('#header .order-status>ul').on("click","li",function(){
		$(this).addClass('head-active').siblings()
		.removeClass('head-active');
	});
	// 筛选框 选中字体颜色变化
	$('#header .two-menu').on('click','.menu-font',function(){
		$(this).addClass('menu-active').siblings().removeClass('menu-active');
		// 销量img箭头
		if ($(this).hasClass("sales-updown") ) {
			if ($(this).find("img").attr("src") == "img/classify/screen2.png") {
				$(this).find("img").attr("src","img/classify/screen3.png");
			}else{
				$(this).find("img").attr("src", "img/classify/screen2.png");
			}
		}else{
			$('.sales-updown>img').attr("src", "img/classify/screen2.png");
		}
	});
	// 询价按钮点击
	$('.switch').on('click','.ify-inquiry',function(){
		$(this).hide().siblings().show();
	})
});








