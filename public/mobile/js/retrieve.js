$(function(){
  $('#npwd-img').click(function(){
    if ($(this).attr("src") === "img/register/register2.png"){
      $(this).attr("src","img/register/register3.png");
      $(this).prev().attr("type","text");
    }else{
      $(this).attr("src", "img/register/register2.png");
      $(this).prev().attr("type", "password");
    }
  })
})