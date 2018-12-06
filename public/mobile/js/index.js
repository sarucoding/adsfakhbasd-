//轮播图和文字
(function(){
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
})();
//多商品点击出现详细商品信息按钮
(function(){
	$(".foryou-container").on("click", ".switch-btn", function(){
		if($(this).attr("src") === "img/switchdown.png"){
			$(this).attr("src", "img/switchup.png");
			$(this).parent().parent().parent().find(".sub-product").slideDown(1000);
		}
		else{
			$(this).attr("src", "img/switchdown.png");
			$(this).parent().parent().parent().find(".sub-product").slideUp(1000);
		}
	});
})();
//购买按钮
(function(){
	$(".foryou-container").on("click", ".count-first", function(){
		$(this).hide().prev().addClass("active").find(".count").val(1);
	});
	//点击加按钮
	$(".foryou-container").on("click", ".add", function(){
		var nowCount = parseFloat($(this).prev().val());
		$(this).prev().val(nowCount + 1);
	});
	//点击减按钮
	$(".foryou-container").on("click", ".reduce", function(){
		var nowCount = parseFloat($(this).next().val());
		var finalCount = nowCount - 1;
		$(this).next().val(finalCount);
		if(finalCount < 1){
			$(this).next().val(1);
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
	$(".foryou-container").on("click", "span", function(){
		if($(this).attr("data-status")){
			$(this).html("取消").attr("data-status", "").prev().prop("checked", true);
		}
		else{
			$(this).html("询价").attr("data-status", "consult").prev().prop("checked", false);
		}
	});
})();
