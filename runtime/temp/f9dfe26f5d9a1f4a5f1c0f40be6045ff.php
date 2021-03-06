<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:41:"./template/mobile/default/cart\index.html";i:1541057471;s:75:"D:\phpstudy\PHPTutorial\WWW\cxs365\template\mobile\default\public\foot.html";i:1540345525;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>购物车</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
	<script type="text/javascript" src="/public/mobile/js/flexible.js"></script>
	<script type="text/javascript" src="/public/mobile/js/flexible_css.js"></script>
	<link rel="stylesheet" type="text/css" href="/public/mobile/css/base.css">
	<link rel="stylesheet" type="text/css" href="/public/mobile/css/cart.css">
</head>
<body>
	<!--购物车头部-->
	<div class="threestatus-header cart-header">
		<div class="container">
			<span class="shopping">购物车</span>
			<?php if($cart_goods): ?><a class="edit" href="javascript:void(0)">编辑</a><?php endif; ?>
		</div>
	</div>
	<!--避免头部遮挡店铺名-->
	<div class="head-nothing"></div>
	<?php if(!$cart_goods): ?>
		<!--购物车为空-->
		<div class="cart-empty" style="">
			<div class="empty-content">
				<img src="/public/mobile/img/cart/cartempty.png">
				<span>购物车是空的</span>
			</div>
			<div class="empty-option">
				<a href="">逛逛新品</a>
				<a href="">常用清单</a>
			</div>
		</div>
		<?php else: ?>
		<!--购物车不为空-->
		<div class="cart-notempty" style="display: block;">
		    <!--购物车详情-->
            <div class="cart-detail">
                <div class="differentiate-store">
                    <div class="store">
                        <input class="formcheckall" <?php if($is_all_selected): ?>checked<?php endif; ?> type="checkbox" style="visibility:hidden;position:absolute;top:0;left:0;">
                        <img data-checkall="<?php if($is_all_selected): ?>all<?php endif; ?>" class="checkall check" src="/public/mobile/img/cart/cart_<?php if($is_all_selected): ?>03<?php else: ?>15<?php endif; ?>.png">
                        <a href="#">
                            <img class="store-ico" src="/public/mobile/img/cart/cart_05.png">
                            <span><?php echo $store_name; ?></span>
                        </a>
                    </div>
                    <form id="cart_form">
                        <div class="cart-content">
                             <?php if(is_array($cart_goods) || $cart_goods instanceof \think\Collection || $cart_goods instanceof \think\Paginator): if( count($cart_goods)==0 ) : echo "" ;else: foreach($cart_goods as $k=>$v): ?>
                                <div class="cart-item">
                                    <div class="c-check">
                                        <input style="visibility:hidden;position:absolute;left:0;top:0;" name="id" type="checkbox" value="<?php echo $v['id']; ?>" <?php if($v['selected']): ?>checked<?php endif; ?> >
                                        <img data-check="<?php if($v['selected']): ?>check<?php endif; ?>" src="/public/mobile/img/cart/cart_<?php if($v['selected']): ?>03<?php else: ?>15<?php endif; ?>.png">
                                    </div>
                                    <div class="c-info">
                                        <div class="img-container">
                                            <a href="<?php echo U('goods/goods_info',array('goods_id'=>$v['goods_id'])); ?>"><img src="<?php if($v['spec_img']): ?><?php echo $v['spec_img']; else: ?><?php echo $v['original_img']; endif; ?>"></a>
                                        </div>
                                        <div class="desc">
                                            <div class="p-name"><?php echo $v['goods_name']; ?></div>
                                            <?php if($v['spec_key_name']): ?>
                                                <div class="p-spec"><?php echo $v['spec_key_name']; ?></div>
                                                <div class="p-select" data-key="<?php echo $v['spec_key']; ?>" data-goods_id="<?php echo $v['goods_id']; ?>" style="display: none"><a href="javascript: void(0)"><span><?php echo $v['spec_key_name']; ?></span><img src="/public/mobile/img/cart/arrowdown.png"></a></div>
                                            <?php endif; ?>
                                            <div class="p-price">
                                                <div class="price-container">
                                                    <span class="price">￥<i><?php echo $v['offer_price']; ?></i>/<?php echo $v['goods_unit']; ?></span>
                                                </div>
                                                <div class="count-option-container">
                                                    <div class="count-option" data-id="<?php echo $v['id']; ?>">
                                                        <img class="btn reduce" src="/public/mobile/img/cart/cart_18.png">
                                                        <input type="number" name="goods_num" class="count" value="<?php echo $v['goods_num']; ?>">
                                                        <img class="btn add" src="/public/mobile/img/cart/cart_20.png">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div><div class="common delete">删除</div>
                                </div>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            <!--点击规格弹出选择框-->
            <div id="modalbox" class="modalbox">
                <div class="modaldialog">
                    <div class="detail">
                        <div class="info">
                            <img src="/public/mobile/img/cart/product01.png">
                            <input name="select_goods_id" type="hidden">
                            <input name="cart_id" type="hidden">
                            <div class="price-num">
                                <h2>￥76.00</h2>
                                <h2>商品编号：CXS6500025420</h2>
                            </div>
                        </div>
                        <form id="spec_form">
                            <span id="spec_list">
                                <!--<h1 class="title">质量</h1>
                                <div class="spec">
                                    <div>
                                        <input checked name="specadata" style="" type="radio">
                                        <a class="active" href="javascript: void(0)">500g</a>
                                    </div>
                                    <div>
                                        <input name="specadata" style="visibility: hidden; position: absolute; top: 0; left: 20px;" type="radio">
                                        <a class="" href="javascript: void(0)">700g</a>
                                    </div>
                                    <div>
                                        <input name="specadata" style="visibility: hidden; position: absolute; top: 0; left: 40px;" type="radio">
                                        <a class="cantcheck" href="javascript: void(0)">1kg</a>
                                    </div>
                                    <div>
                                        <input name="specadata" style="visibility: hidden; position: absolute; top: 0; left: 60px;" type="radio">
                                        <a class="cantcheck" href="javascript: void(0)">2kg</a>
                                    </div>
                                </div>-->
                            </span>
                        </form>
                        <a class="modalbox-close-btn" href="javascript: void(0)" title="关闭"><img src="/public/mobile/img/cart/close.png"></a>
                    </div>
                    <div class="confirm">确定</div>
                </div>
            </div>
            <!--点击删除弹出的选项框-->
            <div class="modalbox-delete">
                <div class="modaldialog-delete">
                    <div class="confirm">
                        <div class="alertmsg">确认要删除这2种商品吗？</div>
                        <div class="confirm-btn">确定</div>
                    </div>
                    <div class="cancle">取消</div>
                </div>
            </div>
        </div>
	<?php endif; ?>
	<!--底部固定结算-->
	<div class="footer-container">
		<?php if($cart_goods): ?>
			<div class="settling">
				<div class="total">
					<input class="formcheckall" <?php if($is_all_selected): ?>checked<?php endif; ?> style="visibility:hidden;position: absolute; top: 0; left: 0;" type="checkbox">
					<div class="checkall"><img class="checkimg" data-checkall="<?php if($is_all_selected): ?>all<?php endif; ?>" src="/public/mobile/img/cart/cart_<?php if($is_all_selected): ?>03<?php else: ?>15<?php endif; ?>.png">全选</div>
					<div class="total-price">合计:<i style="color: #FF1F1F">￥0</i></div>
				</div>
				<div class="ok-btn">
					<span>去结算</span><span class="count-num">（<i>0</i>）</span>
				</div>
			</div>
			<!--点击编辑后底部选项栏-->
			<div class="select-option" style="display: none;">
				<div class="selectall">
					<input class="formcheckall" checked style="visibility:hidden;position:absolute;top:0;left:0;" type="checkbox">
					<img data-checkall="all" class="editcheck" src="/public/mobile/img/cart/cart_03.png">
					<span>全选</span>
				</div>
				<div class="other-btn">
					<div class="clearall active"><img src="/public/mobile/img/cart/trashcan.png"><span>清空购物车</span></div>
					<div class="movetolist">移入清单</div>
					<div class="delete">删除</div>
				</div>
			</div>
		<?php endif; ?>
		<div id="footer" class="footer">
  <div class="menu clear-float">
    <a class="index left-float<?php if($footer_show == 0): ?> active<?php endif; ?>" href="<?php echo U('index/index'); ?>">
      <i></i>
      <div>首页</div>
    </a>
    <a class="types left-float<?php if($footer_show == 1): ?> active<?php endif; ?>" href="<?php echo U('Goods/index'); ?>">
      <i></i>
      <div>分类</div>
    </a>
    <a class="list left-float<?php if($footer_show == 2): ?> active<?php endif; ?>" href="<?php echo U('Commonlist/index'); ?>">
      <i></i>
      <div>常用清单</div>
    </a>
    <a class="cart left-float ify-cart<?php if($footer_show == 3): ?> active<?php endif; ?>" href="<?php echo U('Cart/index'); ?>">
      <i></i>
      <div>购物车</div>
    </a>
    <a class="user left-float<?php if($footer_show == 4): ?> active<?php endif; ?>" href="<?php echo U('User/index'); ?>">
      <i></i>
      <div>我的</div>
    </a>
  </div>
</div>
	</div>
	<!--底部占位空白，避免底部固定处遮挡内容-->
	<div class="cart-nothing"></div>
	<script type="text/javascript">
		document.addEventListener("DOMContentLoaded", function(e){
			document.getElementsByTagName("html")[0].style.fontSize = window.innerWidth / 10 + "px";
		}, false);
	</script>
	<script type="text/javascript" src="/public/layui/layui.js"></script>
	<script type="text/javascript" src="/public/mobile/js/jquery-1.11.3.js"></script>
	<script type="text/javascript" src="/public/mobile/js/common.js"></script>
	<script type="text/javascript">
		layui.use(['layer', 'form'], function(){
			var layer = layui.layer;
			//有商品的时候才初始化价格
			<?php if($cart_goods)echo "AsyncUpdateCart();"; ?>
		});
		//数量输入框弹出框
		inputNum("[name=goods_num]",inputNum_callback);
		//1.滑动菜单侧边出现选项按钮
		swipefunc(".cart-content .cart-item", "cart-item", 1.133);
		$(document).ajaxStart(function(){
			layer.load(2,{shade: 0.1});
		})
		$(document).ajaxStop(function(){
			layer.closeAll('loading');
		})
		//数字键盘回调函数
		function inputNum_callback(obj,num){
			var cart_id = $(obj).parents('.cart-item').find("input[name='id']").val();
			change_cart_num(cart_id,num);
		}
		//购物车对象
		function CartItem(id, goods_num,selected) {
			this.id = id;
			this.goods_num = goods_num;
			this.selected = selected;
		}
		//计算购物车价格
		function AsyncUpdateCart(){
			var cart = new Array();
			$("input[name='id']").each(function(){
				var id = $(this).val();
				var goods_num = $(this).parent().parent().find("input[name='goods_num']").val();
				if($(this).is(':checked')){
					cart.push(new CartItem(id,goods_num,1));
				}else{
					cart.push(new CartItem(id,goods_num,0));
				}
			});
			$.ajax({
				type : "POST",
				url:"<?php echo U('Mobile/Cart/AsyncUpdateCart'); ?>",
				dataType:'json',
				data: {cart: cart},
				success: function(data){
					if(data.status == 1){
						console.log(data);
						$('.total-price').find('i').html('￥'+data.result.total_fee);
						$('.count-num i').html(data.result.goods_num);
					}else{
						layer.msg(data.msg,{icon:2});
					}
				}
			});
		}
		//改变购物车数量
		function change_cart_num(cart_id,num){
			if(num <= 0){
				modal_alert("确认要删除这个商品吗？",cart_id);
			}else{
				$.ajax({
					type: "POST",
					url: "<?php echo U('Mobile/Cart/ajax_goods_num'); ?>",
					dataType: 'json',
					data: {id:cart_id,num:num},
					success:function(result){
						if(result.status != 1){
							layer.msg(result.msg,{icon:2});
						}else{
							AsyncUpdateCart();
						}
					}
				})
			}
		}
		//弹框
		function modal_alert(msg,ids){
			$(".modalbox-delete .confirm .alertmsg").html(msg);
			$(".modalbox-delete").addClass("active");
			setTimeout(function(){
				$(".modaldialog-delete").addClass("move");
			}, 200);
			$('.modaldialog-delete .confirm-btn').attr('onclick','ajax_cart_del('+ids+')');
		}
		//ajax删除购物车
		function ajax_cart_del(data){
			$.ajax({
				type: "POST",
				url: "<?php echo U('Mobile/Cart/ajax_cart_del'); ?>",
				dataType: 'json',
				data: {id: data},
				success: function (data) {
					if(data.status == 1){
						layer.msg('删除成功');
						location.reload();
					}else{
						layer.msg(data.msg,{icon:2});
					}
				}
			})

		}
		//获取规格列表
		function ajax_spec_list(goods_id,key){
			$.ajax({
				type: "POST",
				url: "<?php echo U('Mobile/Cart/ajax_spec_list'); ?>",
				dataType: 'json',
				data: {id:goods_id,key:key},
				success: function (data){
					if(data.goods.spec_img){
						$('.detail .info img').attr('src',data.goods.spec_img);
					}else{
						$('.detail .info img').attr('src',data.goods.original_img);

					}
					$('.detail .info .price-num h2:eq(0)').html('￥'+data.goods.offer_price+'/'+data.goods.goods_unit);
					$('.detail .info .price-num h2:eq(1)').html('商品编号：'+data.goods.goods_sn);
					var html_str = '';
					$.each(data.spec_list,function(idx,item){
						html_str += '<h1 class="title">'+idx+'</h1><div class="spec">';
						$.each(item,function(i,val){
							var the_class = '';
							if(val.is_selected == 1){
								the_class = 'active';
							}
							if(val.can_select == 0){
								the_class += ' cantcheck';
							}
							html_str += `<div>
							<a class="${the_class}" data-key="${val.item_id}" href="javascript: void(0)">${val.item}</a>
									</div>`;
						});
						html_str += '</div>'
					});

					$('#spec_list').html(html_str);
					$(".modalbox").addClass("active");
					$(".modaldialog").addClass("move");
				}
			})
		}
		//2.模态窗口产品规格选择
		//2.1打开规格选择窗口
		$(".cart-item .c-info").on("click", ".p-select", function(e){
			var goods_id = $(this).attr('data-goods_id');
			var cart_id = $(this).parents('.cart-item').find("input[name='id']").val();
			var key = $(this).attr('data-key');
			e.preventDefault();
			$("input[name='select_goods_id']").val(goods_id);
			$("input[name='cart_id']").val(cart_id);
			ajax_spec_list(goods_id,key);
		})
		//2.2关闭规格选择窗口
		$(".modalbox .modaldialog").on("click", ".modalbox-close-btn", function(e){
			e.preventDefault();
			e.stopPropagation();
			$(".modaldialog").removeClass("move");
			setTimeout(function(){
				$(".modalbox").removeClass("active");
			}, 500);
		})
		//阻止事件冒泡
		$(".modalbox .modaldialog").click(function(e){e.stopPropagation();});
		$(".modalbox").click(function(){
			$(".modaldialog").removeClass("move");
			setTimeout(function(){
				$(".modalbox").removeClass("active");
			}, 500);
		})
		//7.选择规格时的切换
		$('#spec_list').on("click", ".spec div a", function(e){
			e.preventDefault();
			if($(this).hasClass('active')){
				return false;
			}
			$(this).parents('.spec').find('a').removeClass("active");
			$(this).addClass("active");
			var key_arr = new Array();
			$('#spec_list').find('.active').each(function(){
				key_arr.push($(this).attr('data-key'));
			})
			var key = key_arr.join('_');//组合规格
			var goods_id = $("input[name='select_goods_id']").val();
			ajax_spec_list(goods_id,key);
		})
		//选择好后点击确定按钮
		$(".modalbox .confirm").click(function(){
			var goods_id = $("input[name='select_goods_id']").val();
			var cart_id = $("input[name='cart_id']").val();
			var spec_key_arr = new Array();
			$(".spec .active").each(function(){
				spec_key_arr.push($(this).attr('data-key'));
			})
			var spec_key = spec_key_arr.join('_');
			$.ajax({
				type: "POST",
				url: "<?php echo U('Mobile/Cart/change_spec'); ?>",
				dataType: 'json',
				data: {cart_id:cart_id,spec_key:spec_key,goods_id:goods_id},
				success: function (data){
					if(data.status == 1){
						location.reload();
					}else{
						layer.msg(data.msg,{icon:2});
					}
				}
			})
		});

		//3.模态窗口是否删除选择
		//3.1点击删除按钮显示对话框
		$(".select-option .delete").click(function(){
			var $checkEle = $(".cart-detail .cart-content [data-check]");
			var countCheck = 0;
			$checkEle.each(function(index, value, objSelf){
				if($(value).attr("data-check")){
					countCheck++;
				}
			});
			if(countCheck == 0){
				layer.msg('请选择要删除的商品');
				return false;
			}
			var data = $('#cart_form').serialize();
			var id_arr = new Array();
			$("input[name='id']:checked").each(function(){
				id_arr.push($(this).val());
			});
			var ids = id_arr.join(',');
			modal_alert("确认要删除这" + countCheck + "种商品吗？",ids);
		})
		//3.2点击取消隐藏对话框
		$(".modalbox-delete .cancle").click(function(){
			$(".modaldialog-delete").removeClass("move");
			setTimeout(function(){
				$(".modalbox-delete").removeClass("active");
			}, 500)
		})
		//阻止事件冒泡
		$(".modaldialog-delete").click(function(e){e.stopPropagation();});
		$(".modalbox-delete").click(function(e){
			$(".modaldialog-delete").removeClass("move");
			setTimeout(function(){
				$(".modalbox-delete").removeClass("active");
			}, 500)
		})
		//4.点击编辑和完成进行切换操作
		//4.1点击编辑
		$(".cart-header .edit").click(function(e){
			e.preventDefault();
			if($(this).html() === "编辑"){
				if($(".cart-detail").length != 0){
					$(".p-spec").hide().next().show();
					$(".settling").hide().next().show();
				}
				$(this).html("完成");
			}
			else if($(this).html() === "完成"){
				if($(".cart-detail").length == 0){
					$(".cart-empty").show();
					$(".cart-notempty").hide();
					$(".settling").hide();
					$(".select-option").hide();
				}
				else{
					$(".p-spec").show().next().hide();
					$(".settling").show().next().hide();
				}
				$(this).html("编辑");
			}
		})
		//5.点击加减按钮
		//点击加按钮
		$(".cart-detail .cart-content").on("click", ".add", function(){
			//1.改变商品的数量
			var nowCount = parseFloat($(this).prev().val());
			$(this).prev().val(nowCount + 1);
			var cart_id = $(this).parent().attr('data-id');
			change_cart_num(cart_id,nowCount+1);
		});

		//点击减按钮
		$(".cart-detail .cart-content").on("click", ".reduce", function(){
			//1.改变商品的数量
			var nowCount = parseFloat($(this).next().val());
			var finalCount = nowCount - 1;
			if(finalCount <= 0){
				finalCount = 0;
			}else{
				$(this).next().val(finalCount);
			}
			var cart_id = $(this).parent().attr('data-id');
			change_cart_num(cart_id,finalCount);
		});

		//6.是否勾选按钮
		$(".cart-content").on("click", ".c-check img", function(){
			//获取选择的商品的打勾
			var $checkNum = $("[data-check]");
			//打勾数目
			var checkNum = 0;
			if($(this).attr("data-check")){
				$(this).attr("src", "/public/mobile/img/cart/cart_15.png").attr("data-check", "").prev().prop("checked", false);
				//计算选择的数目
				$checkNum.each(function(index, domEle){
					if($(domEle).attr("data-check")){
						checkNum += 1;
					}
				});
				//如果一个都没勾选，则去结算按钮呈现灰色状态
				if(checkNum == 0){
					$(".settling .ok-btn").css({backgroundColor: "#D6D6D6", pointerEvents: "none"});
				}
				//如果有未选中则全选按钮不能选中
				$(".store .checkall, .settling .checkall .checkimg, .select-option .editcheck")
					.attr("src", "/public/mobile/img/cart/cart_15.png").attr("data-checkall", "");
				$(".formcheckall").prop("checked", false);
			}
			else{
				$(this).attr("src", "/public/mobile/img/cart/cart_03.png").attr("data-check", "check").prev().prop("checked", true);
				$checkNum.each(function(index, domEle){
					if($(domEle).attr("data-check")){
						checkNum += 1;
					}
				});
				//如果勾选了，则去结算按钮呈现绿色状态
				$(".settling .ok-btn").css({backgroundColor: "#00B050", pointerEvents: "auto"});
				//如果全部选中则全选按钮也要选中
				if(checkNum == $checkNum.length){
					$(".store .checkall, .settling .checkall .checkimg, .select-option .editcheck")
						.attr("src", "/public/mobile/img/cart/cart_03.png").attr("data-checkall", "all");
					$(".formcheckall").prop("checked", true);
				}
			}
			AsyncUpdateCart();
		});
		//预处理结算按钮
		if($("input[name=id]:checked").length == 0){
			$(".settling .ok-btn").css({backgroundColor: "#D6D6D6", pointerEvents: "none"});
		}
		//勾选全部
		$("body").on("click",".store .checkall, .settling .checkall .checkimg, .select-option .editcheck",function(){
			//获取选择的商品的打勾
			var $checkNum = $("[data-check]");
			//打勾数目
			var checkNum = 0;
			if($(this).attr("data-checkall")){
				$(".store .checkall, .settling .checkall .checkimg, .select-option .editcheck")
				.attr("src", "/public/mobile/img/cart/cart_15.png").attr("data-checkall", "");
				//将单选不打勾
				$checkNum.each(function(index, domEle){
					$(domEle).attr("src", "/public/mobile/img/cart/cart_15.png").attr("data-check", "").prev().prop("checked", false);
					if($(domEle).attr("data-check")){
						checkNum += 1;
					}
				});
				$(".formcheckall").prop("checked", false);
				//如果一个都没勾选，则去结算按钮呈现灰色状态
				$(".settling .ok-btn").css({backgroundColor: "#D6D6D6", pointerEvents: "none"});
			}else{
				$(".store .checkall, .settling .checkall .checkimg, .select-option .editcheck")
				.attr("src", "/public/mobile/img/cart/cart_03.png").attr("data-checkall", "all");
				//将单选不打勾
				$checkNum.each(function(index, domEle){
					$(domEle).attr("src", "/public/mobile/img/cart/cart_03.png").attr("data-check", "check").prev().prop("checked", true);
					if($(domEle).attr("data-check")){
						checkNum += 1;
					}
				});
				$(".formcheckall").prop("checked", true);
				//如果勾选了，则去结算按钮呈现绿色状态
				$(".settling .ok-btn").css({backgroundColor: "#00B050", pointerEvents: "auto"});
			}
			AsyncUpdateCart();
		});


		//8.点击删除按钮
		$(".cart-detail .cart-content").on("click", ".delete", function(){
            var cart_id = $(this).parent().find("input[name='id']").val();
            modal_alert('确认要删除这个商品吗？',cart_id);
		});
		//9.编辑状态的下点击清空购物车按钮
		$(".select-option .other-btn .clearall").click(function(){
			modal_alert('确认要清空购物车吗？',0);
		});
		$('.ok-btn').click(function(){
			var num = $(this).find('.count-num').find('i').text();
			if(num > 0){
				location.href = "<?php echo U('confirm_order'); ?>";
			}
		})
	</script>
</body>
</html>