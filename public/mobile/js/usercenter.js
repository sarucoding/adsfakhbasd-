//账期订单和现金订单切换
(function(){
	$("#order .two").on("click", "div", function(){
		$(this).addClass("active").siblings().removeClass("active");
		console.log($(this).attr("data-select"));
		if($(this).attr("data-select") === "way1"){
			$("#order .three-cash").css({display: "block"}).next().css({display: "none"});
		}
		if($(this).attr("data-select") === "way2"){
			$("#order .three-cash").css({display: "none"}).next().css({display: "block"});
		}
	})
})()