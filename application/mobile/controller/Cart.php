<?php
namespace app\mobile\controller;

use think\AjaxPage;
use think\Loader;
use think\Db;
use think\Page;
use app\admin\logic\UsersLogic;
use app\common\logic\UserRelationStoreLogic;
use app\mobile\logic\GoodsLogic;
use app\mobile\logic\CartLogic;
use app\common\logic\PriceLogic;
use app\common\logic\Pay;
use app\common\util\TpshopException;
use app\common\logic\PlaceOrder;
use app\common\logic\GoodsLogic as GoodsLogica;


class Cart extends Base
{


    function _initialize()
    {
        parent::_initialize();

    }

    //购物车
    public function index()
    {
        $cartLogic = new CartLogic();
        $cartLogic->setUserId(USER_ID);
        $cartLogic->setStoreId(STORE_ID);
        $cat_goods = $cartLogic->getCartList();
        $is_all_selected = 1;
        foreach($cat_goods as $k=>$v){
            if($v['selected'] == 0){
                $is_all_selected = 0;
                break;
            }
        }
        $store_name = M('store')->where(['store_id' => STORE_ID])->getField('store_name');
        $this->assign('store_name', $store_name);
        $this->assign('cart_goods', $cat_goods);
        $this->assign('is_all_selected', $is_all_selected);
        $this->assign('footer_show', 3);
        return $this->fetch();
    }

    //加入购物
    public function ajax_add_cart()
    {
        $goods_num = I('num/f');
        $goods_key = I('key');
        $goods_id = I('goods_id/d');
        $where = [
            'a.goods_id' => $goods_id,
            'is_on_sale' => 1,
        ];
        $field = 'a.goods_id,a.goods_name,b.key_name,b.key,a.goods_unit,a.brand_id,a.original_img';
        $goods_data = M('goods')->alias('a')->join('__SPEC_GOODS_PRICE__ b', 'a.goods_id = b.goods_id and b.key = "' . $goods_key . '"', 'LEFT')->where($where)->field($field)->find();
        if (!$goods_data) {
            exit(json_encode(array('status' => -1, 'msg' => '商品参数错误')));
        }
        try {
            $price_logic = new PriceLogic();
            $price_logic->setUser(USER_ID, STORE_ID);
            $result = $price_logic->getGoodsPrice($goods_data['goods_id'], $goods_data['key']);
        } catch (TpshopException $t) {
            $error = $t->getErrorArr();
            exit(json_encode(array('status' => -1, 'msg' => $error['msg'])));
        }
        if ($result['result'] === '') {
            exit(json_encode(array('status' => -1, 'msg' => '非法操作')));
        }
        $where = [
            'user_id' => USER_ID,
            'goods_id' => $goods_id,
            'spec_key' => $goods_data['key'],
            'store_id' => STORE_ID,
        ];
        $cat_goods = M('cart')->where($where)->find();
        $data = [
            'user_id' => USER_ID,
            'goods_id' => $goods_id,
            'goods_name' => $goods_data['goods_name'],
            'goods_price' => $result['result'],
            'goods_num' => $goods_num,
            'spec_key' => $goods_data['key'],
            'spec_key_name' => $goods_data['key_name'],
            'add_time' => time(),
            'store_id' => STORE_ID,
        ];
        if (!$cat_goods) {
            if (!M('cart')->add($data)) {
                exit(json_encode(array('status' => -1, 'msg' => '失败')));
            }
        } else {
            if ($goods_num == '0') {
                if (!M('cart')->where(['id' => $cat_goods['id']])->delete()) {
                    exit(json_encode(array('status' => -1, 'msg' => '失败')));
                }
            } elseif (!M('cart')->where(['id' => $cat_goods['id']])->update($data)) {
                exit(json_encode(array('status' => -1, 'msg' => '失败')));
            }
        }
        exit(json_encode(array('status' => 1, 'msg' => '成功')));
    }

    /**
     * 移除购物车
     * @param id 为0时全部删除
     */
    public function ajax_cart_del()
    {
        $ids = I("id");
        /*if (empty($ids)) {
            exit(json_encode(array('status' => -1, 'msg' => '参数错误')));
        }*/
        if($ids == 0){
            if(!M('cart')->where(['user_id'=>USER_ID,'store_id'=>STORE_ID])->delete()){
                exit(json_encode(array('status' => -1, 'msg' => '删除失败')));
            }
        }elseif (!M('cart')->where(['id' => ['in', $ids],'user_id'=>USER_ID,'store_id'=>STORE_ID])->delete()) {
            exit(json_encode(array('status' => -1, 'msg' => '删除失败')));
        }
        exit(json_encode(array('status' => 1, 'msg' => '成功删除')));
    }

    //商品数量变更
    public function ajax_goods_num()
    {
        $id = I('id/d');
        $goods_num = I('num');
        if (empty($id) && empty($goods_num)) {
            exit(json_encode(array('status' => -1, 'msg' => '非法操作！')));
        }
        if ($goods_num == '0') {
            if (!M('cart')->where(['id' => $id])->delete()) {
                exit(json_encode(array('status' => -1, 'msg' => '失败')));
            }
        }elseif(!M('cart')->where(['id' => $id])->setField('goods_num', $goods_num)){
            exit(json_encode(array('status' => -1, 'msg' => '数据错误')));
        }
        exit(json_encode(array('status' => 1, 'msg' => '成功')));
    }

    /**
     * 获取规格列表，并标注是否可选
     */
    public function ajax_spec_list()
    {
        $goods_id = I('id/d');
        $key = I('key');
        $key_arr = explode('_', $key);
        if (!$goods_id) {
            exit(json_encode(array('status' => -1, 'msg' => '非法操作！')));
        }
        $field = 'b.key,a.goods_id,a.goods_name,a.goods_sn,b.key_name,a.goods_unit,a.brand_id,a.original_img,b.spec_img';
        $goods_data = M('goods')->alias('a')->join('__SPEC_GOODS_PRICE__ b', 'a.goods_id = b.goods_id', 'LEFT')->where(['a.goods_id' => $goods_id])->getField($field);
        $goodsLogic = new GoodsLogica();
        $filter_spec = $goodsLogic->get_spec($goods_id);//获取商品规格
        $price_logic = new PriceLogic();
        $price_logic->setUser(USER_ID, STORE_ID);
        foreach ($goods_data as $k => $v) {//获取商品报价
            $result = $price_logic->getGoodsPrice($v['goods_id'], $v['key']);
            $goods_data[$k]['offer_price'] = $result['result'];
        }
        foreach ($filter_spec as $k => $v) {
            $select_id = '';
            foreach ($v as $i => $val) {
                if (in_array($val['item_id'], $key_arr)) {
                    $select_id = $val['item_id'];
                }
            }
            foreach ($v as $i => $val) {
                if (in_array($val['item_id'], $key_arr)) {
                    $filter_spec[$k][$i]['is_selected'] = 1;
                } else {
                    $filter_spec[$k][$i]['is_selected'] = 0;
                }
                $new_key = str_replace($select_id, $val['item_id'], $key);
                $filter_spec[$k][$i]['can_select'] = $goods_data[$new_key]['offer_price'] === "" ? 0 : 1;//判断该规格是否可选
            }
        }
        $data = [
            'goods' => $goods_data[$key],
            'spec_list' => $filter_spec
        ];
        $this->ajaxReturn($data);
    }
    /**
     * 改变规格
     */
    function change_spec(){
        $goods_id = I('goods_id/d');
        $cart_id = I('cart_id/d');
        $spec_key = I('spec_key');
        if(!$goods_id || !$cart_id || !$spec_key){
            $this->ajaxReturn(['status'=>0,'msg'=>'参数错误']);
        }
        $where = ['a.goods_id'=>$goods_id,'a.is_on_sale'=>1];
        $goods = M('goods')->alias('a')->join('__SPEC_GOODS_PRICE__ b','a.goods_id = b.goods_id and b.key = "'.$spec_key.'"')->where($where)->find();
        if(!$goods){
            $this->ajaxReturn(['status'=>0,'msg'=>'商品不存在或已下架']);
        }
        if(!M('cart')->where(['id'=>$cart_id])->update(['spec_key'=>$spec_key,'spec_key_name'=>$goods['key_name']])){
            $this->ajaxReturn(['status'=>0,'msg'=>'操作失败']);
        }else{
            $price_logic = new PriceLogic();
            $price_logic->setUser(USER_ID, STORE_ID);
            $result = $price_logic->getGoodsPrice($goods_id, $spec_key);
            if(!$result['result']){
                $this->ajaxReturn(['status'=>0,'msg'=>'非法操作']);
            }
            $goods['offer_price'] = $result['result'];
            $this->ajaxReturn(['status'=>1,'msg'=>'操作成功','result'=>$goods]);
        }
    }

    /**
     * 更新购物车，并返回计算结果
     */
    public function AsyncUpdateCart()
    {
        $cart = input('cart/a', []);
        $cartLogic = new CartLogic();
        $cartLogic->setUserId(USER_ID);
        $cartLogic->setStoreId(STORE_ID);
        $result = $cartLogic->AsyncUpdateCart($cart);
        $this->ajaxReturn($result);
    }

    /**
     * 确认订单
     */
    function confirm_order()
    {
        $address_id = input("address_id/d");
        $cartLogic = new CartLogic();
        $cartLogic->setUserId(USER_ID);
        $cartLogic->setStoreId(STORE_ID);
        if ($cartLogic->getUserCartOrderCount() == 0) {
            $this->error('你的购物车没有选中商品', 'Cart/index');
        }
        try {
            $cartList = $cartLogic->getCartList(1); // 获取用户选中的购物车商品
        } catch (TpshopException $e) {
            $err_res = $e->getErrorArr();
            $this->error($err_res['msg']);
        }
        $address_where = ['user_id' => USER_ID];
        if ($address_id) {
            $address_where['a.address_id'] = $address_id;
        }

        $address = M('user_address')->alias('a')
            ->join('__USER_ADDRESS_FREIGHT__ b', 'a.address_id = b.address_id and b.store_id = ' . STORE_ID, 'LEFT')
            ->where($address_where)
            ->field('a.*,b.uaf_freight')
            ->order('is_default desc')->find();
        $area_id[] = $address['province'];
        $area_id[] = $address['city'];
        $area_id[] = $address['district'];
        $regionList = Db::name('region')->where("id", "in", $area_id)->getField('id,name');
        $this->assign('regionList', $regionList);
        $invoice = M('user_extend')->where(['user_id' => USER_ID])->find();
        $this->assign('invoice', $invoice);
        $store_name = M('store')->where(['store_id' => STORE_ID])->getField('store_name');
        $this->assign('cartList', $cartList);
        $this->assign('store_name', $store_name);
        $this->assign('address', $address);//地址
        return $this->fetch();
    }

    /**
     * 计算订单金额和订单提交
     */
    public function order_handle()
    {
        $address_id = input("address_id/d"); //  收货地址id
        $pay_type = input("pay_type/d"); //  支付方式
        $shipping_code = input("shipping_code"); //  支付方式
        $user_note = input('user_note'); // 给卖家留言
        $invoice_desc = input('invoice_desc'); // 发票
        $invoice_title = input('invoice_title'); // 发票
        $taxpayer = input('taxpayer'); // 纳税人识别号
        $is_use_balance = input("is_use_balance/d", 0); //  是否使用余额
        $pay_pwd = input('pwd');
        $data = input('request.');
        $cart_validate = Loader::validate('Cart');
        if (!$cart_validate->check($data)) {
            $error = $cart_validate->getError();
            $this->ajaxReturn(['status' => 0, 'msg' => $error, 'result' => '']);
        }
        $address = M('user_address')->alias('a')
            ->join('__USER_ADDRESS_FREIGHT__ b', 'a.address_id = b.address_id and b.store_id = ' . STORE_ID, 'LEFT')
            ->where(['a.address_id' => $address_id])
            ->field('a.*,b.uaf_freight')->find();
        $cartLogic = new CartLogic();
        $pay = new Pay();
        try {
            $cartLogic->setUserId(USER_ID);
            $cartLogic->setStoreId(STORE_ID);
            $cart_list = $cartLogic->getCartList(1);
            $pay->payCart($cart_list);
            $pay->setUserId(USER_ID);
            $pay->delivery($shipping_code, $address['uaf_freight']);
            if (!$pay_type) {//非账期支付才会用到余额
                $pay->useUserMoney($is_use_balance);
            }
            if ($_REQUEST['act'] == 'submit_order') {
                $placeOrder = new PlaceOrder($pay);
                $placeOrder->setUserAddress($address);
                $placeOrder->setInvoiceDesc($invoice_desc);
                $placeOrder->setInvoiceTitle($invoice_title);
                $placeOrder->setUserNote($user_note);
                $placeOrder->setTaxpayer($taxpayer);
                $placeOrder->setPayPsw($pay_pwd);
                $placeOrder->setPromType($pay_type);
                $placeOrder->setShippingCode($shipping_code);
                $placeOrder->addNormalOrder();
                $cartLogic->clear();
                $master_order_sn = $placeOrder->getMasterOrderSn();
                $this->ajaxReturn(['status' => 2, 'msg' => '提交订单成功', 'result' => $master_order_sn]);
            }
            $result = $pay->toArray();
            $return_arr = array('status' => 1, 'msg' => '计算成功', 'result' => $result); // 返回结果状态
            $this->ajaxReturn($return_arr);
        } catch (TpshopException $t) {
            $error = $t->getErrorArr();
            $this->ajaxReturn($error);
        }

    }

    /**
     * 订单收银台
     */
    function order_pay()
    {
        $order_sn = I('order_sn');
        $order = M('order')->where(['order_sn' => $order_sn,'user_id'=>USER_ID,'store_id'=>STORE_ID])->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        if ($order['prom_type'] == 1) {
            //如果是账期订单  直接显示提交成功
            $this->redirect(U('pay_success', ['order_sn' => $order_sn]));
        } elseif ($order['pay_status'] == 0) {
            $this->assign('order', $order);
            return $this->fetch();
        } elseif ($order['pay_status'] == 1) {
            //已支付订单直接跳转到支付成功页面
            $this->redirect(U('pay_success', ['order_sn' => $order_sn]));
        } elseif ($order['pay_status'] > 1) {
            $this->error('非法操作');
        }

    }

    /**
     * 支付成功
     */
    function pay_success()
    {
        $order_sn = I('order_sn');
        $order = M('order')->where(['order_sn' => $order_sn,'user_id'=>USER_ID,'store_id'=>STORE_ID])->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        $this->assign('order', $order);
        return $this->fetch();
    }
}