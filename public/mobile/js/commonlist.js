//多商品点击出现详细商品信息按钮
(function(){
	$(".switch-btn").click(function(e){
		if($(this).attr("src") === "img/commonlist/switchdown.png"){
			$(this).attr("src", "img/commonlist/switchup.png");
			// $(".sub-product").animate({height: "3.0rem"}, 1000);
			$(".sub-product").slideDown(1000);
		}
		else{
			$(this).attr("src", "img/commonlist/switchdown.png");
			// $(".sub-product").animate({height: "0.0rem"}, 1000);
			$(".sub-product").slideUp(1000);
		}
	})
})();
//点击加减按钮
(function(){
	//1.多品购买处
	$(".multitype .count-first").click(function(){
		$(this).hide().prev().addClass("active").find(".count").val(1);
	});
	//点击加按钮
	$(".multitype").on("click", ".add", function(){
		var nowCount = parseFloat($(this).prev().val());
		$(this).prev().val(nowCount + 1);
	});
	//点击减按钮
	$(".multitype").on("click", ".reduce", function(){
		var nowCount = parseFloat($(this).next().val());
		var finalCount = nowCount - 1;
		$(this).next().val(finalCount);
		if(finalCount < 1){
			$(this).next().val(1);
		}
	});
	//2.单品购买处
	$(".singletype .count-first").click(function(){
		$(this).hide().prev().addClass("active").find(".count").val(1);
	});
	//点击加按钮
	$(".singletype").on("click", ".add", function(){
		var nowCount = parseFloat($(this).prev().val());
		$(this).prev().val(nowCount + 1);
	});
	//点击减按钮
	$(".singletype").on("click", ".reduce", function(){
		var nowCount = parseFloat($(this).next().val());
		var finalCount = nowCount - 1;
		$(this).next().val(finalCount);
		if(finalCount < 1){
			$(this).next().val(1);
		}
	});
})();