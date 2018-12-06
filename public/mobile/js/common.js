//1.滑动显示按钮的封装函数
function swipefunc(selectorVal, subSelector, remRatio){
    (function(select){
        window.addEventListener('load', function() {
            //获取当前html中字体大小
            var fontSizeVal = parseFloat(document.getElementsByTagName("html")[0].style.fontSize);
            //将DomList转化为数组,以便使用forEach方法遍历dom
            var itembox = Array.prototype.slice.call(document.querySelectorAll(select), 0);
            //使用forEach方法遍历dom
            itembox.forEach(function(item, index, arrSelf){
                var movebox = item; //当前触摸的元素
                var initX; //触摸位置
                var moveX; //滑动时的位置
                var X = 0; //移动距离
                var objX = 0; //目标对象位置
                var remVal = remRatio; //rem的比例值
                movebox.addEventListener('touchstart', function(event) {
                    $(this).siblings().attr("style", "");
                    var obj = this;
                    if(obj.className == subSelector) {
                        initX = event.targetTouches[0].pageX;
                        objX = (obj.style.WebkitTransform.replace(/translateX\(/g, "").replace(/rem\)/g, "")) * 1 * fontSizeVal;
                    }
                    if(objX == 0) {
                        movebox.addEventListener('touchmove', function(event) {
                            var obj = this;
                            if(obj.className == subSelector) {
                                moveX = event.targetTouches[0].pageX;
                                X = moveX - initX;
                                if(X >= 0) {
                                    obj.style.WebkitTransform = "translateX(" + 0 + "rem)";
                                } else if(X < 0) {
                                    var l = Math.abs(X);
                                    obj.style.WebkitTransform = "translateX(" + -l / fontSizeVal + "rem)";
                                    if(l > (remVal * fontSizeVal)) {
                                        l = remVal * fontSizeVal;
                                        obj.style.WebkitTransform = "translateX(" + -remVal + "rem)";
                                    }
                                }
                            }
                        });
                    } 
                    else if(objX < 0) {
                        movebox.addEventListener('touchmove', function(event) {
                            var obj = this;
                            if(obj.className == subSelector) {
                                moveX = event.targetTouches[0].pageX;
                                X = moveX - initX;
                                if(X >= 0) {
                                    var r = -(remVal * fontSizeVal) + Math.abs(X);
                                    obj.style.WebkitTransform = "translateX(" + r / fontSizeVal + "rem)";
                                    if(r > 0) {
                                        r = 0;
                                        obj.style.WebkitTransform = "translateX(" + r + "rem)";
                                    }
                                } else { //向左滑动
                                    obj.style.WebkitTransform = "translateX(" + -remVal + "rem)";
                                }
                            }
                        });
                    }
    
                })
                movebox.addEventListener('touchend', function(event) {
                    var obj = this;
                    if(obj.className == subSelector) {
                        objX = (obj.style.WebkitTransform.replace(/translateX\(/g, "").replace(/rem\)/g, "")) * 1 * fontSizeVal;
                        if(objX > -(remVal * fontSizeVal / 2)) {
                            obj.style.WebkitTransform = "translateX(" + 0 + "rem)";
                            objX = 0;
                        } else {
                            obj.style.WebkitTransform = "translateX(" + -remVal + "rem)";
                            objX = -(remVal * fontSizeVal);
                        }
                    }
                })
            })
        })
    })(selectorVal);
}

//2.加按钮
function addfunc(selectorVal){
    $(selectorVal).on("click", ".add", function(){
        var nowCount = parseFloat($(this).prev().val());
        $(this).prev().val(nowCount + 1);
    });
}

//3.减按钮
function reducefunc(selectorVal){
    $(selectorVal).on("click", ".reduce", function(){
        var nowCount = parseFloat($(this).next().val());
        var finalCount = nowCount - 1;
        $(this).next().val(finalCount);
        if(finalCount < 1){
            $(this).next().val(1);
        }
    });
}

// order && classify 页面的顶部订单下拉、状态选择下拉
$(function () {
    var cover = $('.order-cover');
    // 订单下拉事件
    $('.ordinary').click(function () {
        if ($(this).find('img').attr('src') === '/public/mobile/img/order/down-arrow2.png'
            && $('.cover-d1').hide()) {
            $(this).find('img').attr('src', '/public/mobile/img/order/up-arrow2.png');
            cover.addClass('cover-color').show().find('.cover-d1')
                .show().next().hide().prev().find('.cover-bill').slideDown(300);
        } else {
            $(this).find('img').attr('src', '/public/mobile/img/order/down-arrow2.png');
            $('.cover-bill').slideUp(300);
            setTimeout(function(){
                cover.addClass('cover-color').removeClass('cover-color');
            }, 400);
        };
    });

    // 下拉箭头选择状态事件
    $('.status-dropdown img:last-child').click(function () {
        cover.addClass('cover-color');
        $('.cover-d2').show().prev().hide();
        $('.cover-menu').slideDown(500);
    });
    // 上拉箭头隐藏状态栏事件
    $('.status-img').click(function () {
        $('.cover-menu').slideUp(300);
        setTimeout(function(){
            $('.cover-status').hide().show();
        }, 330);
        setTimeout(function(){
            cover.addClass('cover-color').removeClass('cover-color');
        }, 350);
    });
    // 阻止事件冒泡,点击子元素会触发cover的隐藏事件，导致看不到子元素的点击事件
    $('.cover-d1,.cover-d2').click(function (e) { e.stopPropagation(); });
    // 点击覆盖层隐藏全部
    cover.click(function () {
        // 订单事件隐藏
        $('.cover-bill').slideUp(300);
        setTimeout(function(){
            cover.addClass('cover-color').removeClass('cover-color');
        }, 400);
        $('.ordinary').find('img').attr('src', '/public/mobile/img/order/down-arrow2.png');
        // 选择事件隐藏
        $('.cover-menu').slideUp(300);
    });
    // 横向滑动
    $('.order-status>ul').on('click', 'li', function () {
        $(this).addClass('head-active').siblings().removeClass('head-active');
    });
    
});

// 滑动菜单 点击li，ul滚动居中
function slither() {
    $(".order-status ul li").on("click", function () {
        var moveX = $(this).position().left + $(this).parent().scrollLeft();
        var pageX = document.documentElement.clientWidth;  //页面被卷进去的宽
        var blockWidth = $(this).width();
        var left = moveX - (pageX / 2) + (blockWidth / 2);
        $(this).addClass('head-active').siblings().addClass('head-active');
        $(".order-status ul").animate({ scrollLeft: left }, 200);
    })
};

// order && classify 一、二级联动
function linkage(){
    // 一级联动
    $(function () {
        var curNavIndex = 0;
        $('.order-menu-one').on('click', 'li', function () {
            var iid = parseInt($(this).attr("data-iid"));
            $("[data-pid=" + iid + "]").addClass("head-active").siblings().removeClass("head-active");
            var li = $('.order-status ul li.head-active');
            var moveX = li.position().left + li.parent().scrollLeft();
            var pageX = document.documentElement.clientWidth;
            var blockWidth = li.width();
            var left = moveX - (pageX / 2) + (blockWidth / 2);
            $(".order-status ul").animate({ scrollLeft: left }, 200);
            if (curNavIndex != iid) {
                curNavIndex = iid;
                $("#order" + curNavIndex).show().siblings('.order-pay').hide();
                $("#content-ul" + curNavIndex).show();
            }
        })
    });

    // 二级联动
    $(function () {
        var curNavIndex = 0;
        /*初始化菜单*/
        $(".com-order ul").on('click', 'li', function () {
            var id = parseInt($(this).attr("data-pid"));
            $("[data-iid=" + id + "]").find('span').addClass("active").parent().siblings().find('span').removeClass("active");
            if (curNavIndex != id) {
                // 更改列表条件
                curNavIndex = id;
                $(this).addClass("head-active").siblings().removeClass("head-active");
                // 显示当前列表，隐藏其他列表
                $("#order" + curNavIndex).show().siblings('.order-pay').hide();
                // 显示对应的列表
                $("#content-ul" + curNavIndex).show();
            }
            if (id == 0){
                curNavIndex = 0;
                $(this).addClass("head-active").siblings().removeClass("head-active");
                // 显示当前列表，隐藏其他列表
                $("#order" + curNavIndex).show().siblings('.order-pay').hide();
                // 显示对应的列表
                $("#content-ul" + curNavIndex).show();
            }
        })
    });
}

//数量输入框
function inputNum(selector, callback){
    var htmlContent = `<div class="add-pay">
        <div class="numb_box">
        <div class="new-num">
            <ul class="receive-num clear-float">
            <li></li>
            </ul>
            <input type="hidden" value="" name="buy-count" class="receive-input">
            <span class="btn-common back-img">退格</span>
            <span class="btn-common x-img">清空</span>
        </div>
        <ul class="keyboard">
            <li><a href="javascript:void(0);" class="key-num">1</a></li>
            <li><a href="javascript:void(0);" class="key-border key-num">2</a></li>
            <li><a href="javascript:void(0);" class="key-num">3</a></li>
            <li><a href="javascript:void(0);" class="key-num">4</a></li>
            <li><a href="javascript:void(0);" class="key-border key-num">5</a></li>
            <li><a href="javascript:void(0);" class="key-num">6</a></li>
            <li><a href="javascript:void(0);" class="key-num">7</a></li>
            <li><a href="javascript:void(0);" class="key-border key-num">8</a></li>
            <li><a href="javascript:void(0);" class="key-num">9</a></li>
            <li><a href="javascript:void(0);" class="key-num">.</a></li>
            <li><a href="javascript:void(0);" class="key-border key-num">0</a></li>
            <li><a href="javascript:void(0);" class="key-confirm">确认</a></li>
        </ul>
        <!--弹出提示框-->
        <div class="alert-con" style="display:none;">
            请输入合法的数量,小数点后保留一位
        </div>
        </div>
    </div>`;
    $("body").append(htmlContent);
    var nowEle;
    $(document).on("click", selector, function () {//出现浮动层
    	document.activeElement.blur();
    	nowEle = this;
        $(".add-pay").addClass('add-pay-show');
        $(".numb_box").addClass("move");
    });
    //给数字和小数点绑定点击事件
    $(document).on("click", ".keyboard li .key-num", function () {// 传键盘的值给隐藏input
        var regExp = /^([1-9][0-9]*(\.{1}))$|^([1-9][0-9]*)$|^([0]{1}\.{1})$|^([0])$|^([1-9][0-9]*(\.\d{1})?)$|^([0]{1}\.{1}\d{1})$/;
        var $li = $(".receive-num li");
        var originVal = $li.text();
        var nowVal = $(this).text();
        if(parseFloat(originVal + nowVal) > 999){
            $(".alert-con").html("输入的数不能超过999").fadeIn().delay(1500).fadeOut();
            return;
        }
        if(!regExp.test(originVal + nowVal)){
            $(".alert-con").html("请输入合法的数量,小数点后保留一位").fadeIn().delay(1500).fadeOut();
            return;
        }
        $li.html(originVal + nowVal);
    });
    //给确认按钮绑定点击事件
    $(document).on("click", ".keyboard .key-confirm", function() {
        //使用正则表达式验证输入值得合法性
        var regExp = /^([1-9][0-9]*(\.\d{1})?)$|^([0]{1}\.{1}\d{1})$/;
        if(!regExp.test($(".receive-num li").text())){
            $(".alert-con").html("请输入合法的数量,小数点后保留一位").fadeIn().delay(1500).fadeOut();
            return;
        }
        else{
          //获取当前参数值
          var nowCount = $(".receive-num li").text();
          var oldNum = $(nowEle).val();
          $(nowEle).val(nowCount);
          $(".receive-input").val(nowCount);
          callback(nowEle,nowCount);//回调处理
          //这个是针对商品详情页的判断
          if($(nowEle).attr("class") === "pro-count"){
            $(".detail-buynav .cart .countnum").html( parseFloat($(".detail-buynav .cart .countnum").html()) + parseFloat(nowCount) - parseFloat(oldNum) );
          }
          
        } 
        $(".numb_box").removeClass("move");
        setTimeout(function(){
            $(".add-pay").removeClass('add-pay-show');
        }, 300);
        $(".receive-num li").text(""); //清空所输数据
    });
    //点击外部非键盘部分隐藏浮动层
    $(document).on("click",".numb_box", function(e){e.stopPropagation();});
    $(document).on("click", ".add-pay", function () {
        $(".numb_box").removeClass("move");    
        setTimeout(function(){
        $(".add-pay").removeClass('add-pay-show');
        }, 300)

        $(".receive-num li").text(""); //清空所输数据
    })
    //点击清空按钮
    $(document).on("click", ".add-pay .x-img", function(){
    	$(".receive-num li").text("");
    });
    //点击退格按钮
    $(document).on("click", ".add-pay .back-img", function(){
        var oldText = $(".receive-num li").text();
        var nowText = "";
        //将字符串转换成数组
        var tempArray = Array.prototype.slice.call(oldText, 0);

        tempArray.pop();
        tempArray.forEach(function(val, index, arrSelf){
            nowText += val;
        });
        $(".receive-num li").text(nowText);
    });
}

//确定和取消
function selectBtn(descString, callback){
    $('.modalbox-delete').remove();
    var htmlContent = `<div class="modalbox-delete">
        <div class="modaldialog-delete">
            <div class="confirm">
                <div class="alertmsg">${descString}</div>
                <div class="confirm-btn">确定</div>
            </div>
            <div class="cancle">取消</div>
        </div>
    </div>`;
    $("body").append(htmlContent);
    $(".modalbox-delete").addClass("active");
    setTimeout(function(){
        $(".modaldialog-delete").addClass("move");
    },100);

    //点击删除弹出确认对话框点击确认按钮
    $(".modalbox-delete .confirm-btn").click(function(){
        callback();
        $(".modaldialog-delete").removeClass("move");
        setTimeout(function(){
            $(".modalbox-delete").removeClass("active");
        }, 500)
    });
    //3.2点击取消隐藏对话框
    $(".modalbox-delete .cancle").click(function(){
        $(".modaldialog-delete").removeClass("move");
        setTimeout(function(){
            $(".modalbox-delete").removeClass("active");
        }, 500)
    });
    //阻止事件冒泡
    $(".modaldialog-delete").click(function(e){e.stopPropagation();});
    $(".modalbox-delete").click(function(e){
        $(".modaldialog-delete").removeClass("move");
        setTimeout(function(){
            $(".modalbox-delete").removeClass("active");
        }, 500)
    });
}