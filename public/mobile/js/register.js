/*点击显示密码*/
$(".isshow").click(function(){
	if($(this).find("img").attr("src") === "img/register/register2.png"){
		$(this).find("img").attr("src", "img/register/register3.png");
		$(this).prev().find("input").attr("type", "text");
	}
	else{
		$(this).find("img").attr("src", "img/register/register2.png");
		$(this).prev().find("input").attr("type", "password");
	}
})