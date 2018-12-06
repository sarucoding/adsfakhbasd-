// pay页面
$(function(){
  $('.pay-bottom').click(function(){
    $('.pay-cover').addClass("pay-cover-color");
    $('.cover-child').addClass("cover-move")
      .find(".pwd-input").focus();// 让input弹出就获取焦点
    $('.shut').click(function(){
      $('.cover-child').removeClass("cover-move")
      .find(".pwd-input").val("").blur()
      .next().find('input').val("");
      setTimeout(() => {
        $('.pay-cover').removeClass("pay-cover-color");
      }, 350);
    });
  });
  //输入密码框; 实现思路是输入的值还是在.pwd - input 里，再用循环将每次的值取出放在div的每个input里
  var $input = $(".fake-box input");
  $(".pwd-input").on("input", function () {
    var pwd = $(this).val().trim(); //trim() 去掉首尾的空格
    for (var i = 0, len = pwd.length; i < len; i++) {
      $input.eq("" + i + "").val(pwd[i]);//eq()查找下标为?的元素
    }
    $input.each(function () { //each(function(index,element)) 语法
      var index = $(this).index();
      if (index >= len) {
        $(this).val("");
      }
    });
    if (len == 6) {
      //执行其他操作
    }
  });

});
