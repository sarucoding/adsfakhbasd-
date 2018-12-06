(function(){
	//1.配送方式选择
	//1.1打开选择窗口
	$(".confirm-distribution .select-btn").click(function(){
		$(".modaldialog-distribution").addClass("move");
		$(".modalbox-distribution").addClass("active");
	});
	//1.2关闭选择窗口
	$(".modalbox-distribution .close-btn").click(function(){
		$(".modaldialog-distribution").removeClass("move");
		$(".modalbox-distribution").removeClass("active");
	});
	//阻止事件冒泡(点击遮罩层也要关闭弹出窗口)
	$(".modaldialog-distribution").click(function(e){e.stopPropagation();});
	$(".modalbox-distribution").click(function(e){
		$(".modaldialog-distribution").removeClass("move");
		$(".modalbox-distribution").removeClass("active");
	});

	//2.发票类型选择
	//2.1打开窗口
	$(".confirm-invoice .select-btn").click(function(){
		$(".modaldialog-invoice").addClass("move");
		$(".modalbox-invoice").addClass("active");
	});
	//2.2关闭选择窗口
	$(".modalbox-invoice .close-btn").click(function(){
		$(".modaldialog-invoice").removeClass("move");
		$(".modalbox-invoice").removeClass("active");
	});
	//阻止事件冒泡
	$(".modaldialog-invoice").click(function(e){e.stopPropagation();});
	$(".modalbox-invoice").click(function(e){
		$(".modaldialog-invoice").removeClass("move");
		$(".modalbox-invoice").removeClass("active");
	});
	//3.最佳收货时间选择
	var selectBtn = document.getElementById("con-select-btn");
	var data1 = [
		{text: "8:00", value: 1},{text: "9:00", value: 2},{text: "10:00", value: 3},{text: "11:00", value: 4},
		{text: "12:00", value: 5},{text: "13:00", value: 6},{text: "14:00", value: 7},{text: "15:00", value: 8},
		{text: "16:00", value: 9},{text: "17:00", value: 10},{text: "18:00", value: 11}
	];
	var picker = new Picker({
		data: [data1],
		selectedIndex: [5]
	});
	picker.on("picker.select", function(selectedVal, selectedIndex){
		console.log(data1[selectedIndex[0]].text);
		$(".confirm-time .info span").html(data1[selectedIndex[0]].text);
	});
	selectBtn.addEventListener("click", function(){
		picker.show();
	});
	//点击遮罩层关闭窗口
	$(".picker-panel").click(function(e){e.stopPropagation();});
	$(".picker").click(function(){
		$(".picker-mask").removeClass("show");
		$(".picker-panel").removeClass("show");
		var that = this;
		setTimeout(function(){
			$(that).css({display: "none"});
		}, 500)
	})
	//4.点击加减按钮
	//点击加按钮
	$(".confirm-content").on("click", ".add", function(){
		var nowCount = parseFloat($(this).prev().val());
		var finalCount = nowCount + 1;
		$(this).prev().val(finalCount);
		$(this).parent().parent().parent().find(".p-name span:last-child").html("X" + finalCount);
	});
	//点击减按钮
	$(".confirm-content").on("click", ".reduce", function(){
		var nowCount = parseFloat($(this).next().val());
		var finalCount = nowCount - 1;
		$(this).next().val(finalCount);
		$(this).parent().parent().parent().find(".p-name span:last-child").html("X" + finalCount);
		if(finalCount < 1){
			$(this).next().val(1);
			$(this).parent().parent().parent().find(".p-name span:last-child").html("X" + 1);
		}
	});

	//5.点击支付方式切换
	$(".confirm-payway").on("click", "img", function(){
		$(this).attr("src", "img/cart/cart_03.png")
			.prev().prop("checked", true)
			.next().next().css({color: "#00B050"})
			.parent().siblings().find("img").attr("src", "img/cart/cart_15.png")
			.next().css({color: "#8C8C8C"});
	});

	//6.点击配送选择配送方式
	$(".modalbox-distribution .two div").on("click", "span", function(){
		$(this).addClass("active").prev().prop("checked", true).parent().siblings().find("span").removeClass("active");
		// $(".confirm-distribution .info2 p:first-child").html($(this).html());
	});
	//选择好后点击确定按钮
	$(".modalbox-distribution .confirm-btn div").click(function(){
		var html = $(this).parent().prev().find("span.active").html();
		$(".confirm-distribution .info2 p:first-child").html(html);
		$(".modaldialog-distribution").removeClass("move");
		$(".modalbox-distribution").removeClass("active");
	})

	//7.点击发票须知弹出相关内容
	$(".modalbox-invoice .mustknow").click(function(){
		console.log(this)
		$(".modalbox-mustknow").addClass("active");
	})
	$(".modalbox-mustknow .close").click(function(){
		$(".modalbox-mustknow").removeClass("active");
	})
	$(".modaldialog-mustknow").click(function(e){ e.stopPropagation(); })
	$(".modalbox-mustknow").click(function(e){
		console.log(12)
		$(".modalbox-mustknow").removeClass("active");
	})
	//8.点击不开发票和商品明细的切换
	$(".modalbox-invoice .contentrequire").on("click", "span", function(){
		$(this).addClass("active").siblings().removeClass("active");
		if($(this).html() === "商品明细"){
			$(".modalbox-invoice .aboutinvoice").show();
		}
		else{
			$(".modalbox-invoice .aboutinvoice").hide();
		}
		$(".confirm-invoice .info2").html($(this).html());
	});
	//选择好后点击确定按钮
	$(".modalbox-invoice .confirm-btn div").click(function(){
		var html = $(this).parent().prev().find("span.active").html();
		$(".confirm-invoice .info2").html(html);
		$(".modaldialog-invoice").removeClass("move");
		$(".modalbox-invoice").removeClass("active");
	})
})();