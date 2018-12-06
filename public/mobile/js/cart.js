//1.滑动菜单侧边出现选项按钮
swipefunc(".cart-content .cart-item", "cart-item", 1.133);

//2.模态窗口产品规格选择
//2.1打开规格选择窗口
$(".cart-item .c-info").on("click", ".p-select", function(e){
	e.preventDefault();
	$(".modalbox").addClass("active");
	$(".modaldialog").addClass("move");
})
//2.2关闭规格选择窗口
$(".modalbox .modaldialog").on("click", ".modalbox-close-btn", function(e){
	e.preventDefault();
	e.stopPropagation();
	$(".modalbox").removeClass("active");
	$(".modaldialog").removeClass("move");
})
//阻止事件冒泡
$(".modalbox .modaldialog").click(function(e){e.stopPropagation();});
$(".modalbox").click(function(){
	$(".modalbox").removeClass("active");
	$(".modaldialog").removeClass("move");
})

//3.模态窗口是否删除选择
//3.1点击删除按钮显示对话框
$(".select-option .delete").click(function(){
	$(".modalbox-delete").addClass("active");
	$(".modaldialog-delete").addClass("move");
})
//3.2点击取消隐藏对话框
$(".modalbox-delete .cancle").click(function(){
	$(".modalbox-delete").removeClass("active");
	$(".modaldialog-delete").removeClass("move");
})
//阻止事件冒泡
$(".modaldialog-delete").click(function(e){e.stopPropagation();});
$(".modalbox-delete").click(function(e){
	$(".modalbox-delete").removeClass("active");
	$(".modaldialog-delete").removeClass("move");
})
//4.点击编辑和完成进行切换操作
//4.1点击编辑
$(".cart-header .edit").click(function(e){
	e.preventDefault();
	if($(this).html() === "编辑"){
		$(".p-spec").hide().next().show();
		$(".cart-recommend").hide();
		$(".settling").hide().next().show();
		$(this).html("完成");
	}
	else if($(this).html() === "完成"){
		$(".p-spec").show().next().hide();
		$(".cart-recommend").show();
		$(".settling").show().next().hide();
		$(this).html("编辑");
	}
})

//5.点击加减按钮
//点击加按钮
addfunc(".cart-detail .cart-content");

//点击减按钮
reducefunc(".cart-detail .cart-content");

//6.是否勾选按钮
$(".cart-content").on("click", ".c-check img", function(){
	if($(this).attr("data-check")){
		$(this).attr("src", "img/cart/cart_15.png").attr("data-check", "");
		$(".store .checkall, .settling .checkall .checkimg, .select-option .editcheck")
			.attr("src", "img/cart/cart_15.png").attr("data-checkall", "");
		
		$(".formcheckall").prop("checked", false);
	}
	else{
		$(this).attr("src", "img/cart/cart_03.png").attr("data-check", "check");
		var $all = $("[data-check]");
		var isCheckEvery = 0;
		$all.each(function(index, value){
			if($(value).attr("data-check")){
				isCheckEvery += 1;
			}
		});
		if(isCheckEvery == $all.length){
			$(".store .checkall, .settling .checkall .checkimg, .select-option .editcheck")
			.attr("src", "img/cart/cart_03.png").attr("data-checkall", "all");

			$(".formcheckall").prop("checked", true);
		}
	}
});
//勾选全部
$(".cart-notempty").on("click",
	".store .checkall, .settling .checkall .checkimg, .select-option .editcheck",
	function(){
		var $all = $("[data-check]");
		if($(this).attr("data-checkall")){
			$(".store .checkall, .settling .checkall .checkimg, .select-option .editcheck")
			.attr("src", "img/cart/cart_15.png").attr("data-checkall", "");
			$all.each(function(index, value){
				$(value).attr("src", "img/cart/cart_15.png").attr("data-check", "");
			});

			$(".formcheckall").prop("checked", false);
		}
		else{
			$(".store .checkall, .settling .checkall .checkimg, .select-option .editcheck")
			.attr("src", "img/cart/cart_03.png").attr("data-checkall", "all").prev;
			$all.each(function(index, value){
				$(value).attr("src", "img/cart/cart_03.png").attr("data-check", "check");
			});

			$(".formcheckall").prop("checked", true);
		}
})

//7.选择规格时的切换
$(".modalbox .spec div").on("click", "a", function(e){
	e.preventDefault();
	$(this).addClass("active").prev().prop("checked", true).parent().siblings().find("a").removeClass("active");
})
//选择好后点击确定按钮
$(".modalbox .confirm").click(function(){
	var html = $(this).prev().find(".spec a.active").html();
	$(".cart-item .p-select a span").text(html).parent().parent().prev().text(html);
	$(".modalbox").removeClass("active");
	$(".modaldialog").removeClass("move");
});