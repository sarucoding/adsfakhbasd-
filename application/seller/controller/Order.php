<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 商业用途务必到官方购买正版授权, 使用盗版将严厉追究您的法律责任。
 * 采用最新Thinkphp5助手函数特性实现单字母函数M D U等简写方式
 * ============================================================================
 * Author: 当燃
 * Date: 2015-09-09
 */
namespace app\seller\controller;

use app\common\logic\Pay;
use app\common\model\DeliveryDoc;
use app\common\model\Order as OrderModel;
use app\common\model\OrderAction;
use app\common\model\OrderGoods;
use app\common\model\StoreAddress;
use app\common\model\team\TeamFollow;
use app\common\model\team\TeamFound;
use app\common\util\TpshopException;
use app\seller\logic\OrderLogic;
use app\seller\logic\SellerOrder;
use app\seller\logic\GoodsLogic;
use think\AjaxPage;
use think\Db;
use think\Page;

class Order extends Base
{
    public $order_status;
    public $shipping_status;
    public $pay_status;

    /*
     * 初始化操作
     */
    public function _initialize()
    {
        parent::_initialize();
        C('TOKEN_ON', false); // 关闭表单令牌验证
        // 订单 支付 发货状态
        $this->order_status = C('ORDER_STATUS');
        $this->pay_status = C('PAY_STATUS');
        $this->shipping_status = C('SHIPPING_STATUS');
        $this->assign('order_status', $this->order_status);
        $this->assign('pay_status', $this->pay_status);
        $this->assign('shipping_status', $this->shipping_status);
    }

    /*
     *订单首页
     */
    public function index()
    {
        return $this->fetch();
    }

    /*
     *Ajax首页
     */
    public function ajaxindex()
    {
        $select_year = $this->select_year;
        $begin = $this->begin;
        $end = $this->end;

        // 搜索条件 STORE_ID
        $condition = array('store_id' => STORE_ID, 'deleted' => 0); // 商家id
        I('consignee') ? $condition['consignee'] = trim(I('consignee')) : false;
        if ($begin && $end) {
            $condition['add_time'] = array('between', "$begin,$end");
        }
        I('order_sn') ? $condition['order_sn'] = trim(I('order_sn')) : false;
        I('mobile') ? $condition['mobile'] = trim(I('mobile')) : false;
        I('order_status') != '' ? $order_status = I('order_status/d') : $order_status = -1;
        I('prom_type') != '' ? $condition['prom_type'] = I('prom_type/d') : false;
        ///I('pay_status') != '' ? $condition['pay_status'] = I('pay_status/d') : false;
        I('pay_code') != '' ? $condition['pay_code'] = I('pay_code') : false;
        //I('shipping_status') != '' ? $condition['shipping_status'] = I('shipping_status/d') : false;
        I('order_statis_id/d') != '' ? $condition['order_statis_id'] = I('order_statis_id/d') : false; // 结算统计的订单
        if ($order_status != -1)
            switch ($order_status) {
                case 0://待确认
                    $condition['order_status'] = 0;
                    break;
                case 1://待发货
                    $condition['order_status'] = 1;
                    $condition['shipping_status'] = 0;
                    break;
                case 2://待确认实发数
                    $condition['order_status'] = 1;
                    $condition['shipping_status'] = 1;
                    break;
                case 3://待付尾款
                    $condition['order_status'] = 2;
                    $condition['shipping_status'] = 1;
                    $condition['pay_status'] = 1;
                    break;
                case 4://待收货
                    $condition['order_status'] = 2;
                    $condition['shipping_status'] = 1;
                    $condition['pay_status'] = ['neq', 1];//支付状态为0和2都可以是待收货
                    break;
                case 5://已收货
                    $condition['order_status'] = 3;
                    $condition['shipping_status'] = 1;
                    break;
                case 6://已取消
                    $condition['order_status'] = 4;
                    break;
                case 7://已作废
                    $condition['order_status'] = 6;
                    break;
            }
        $sort_order = I('order_by', 'DESC') . ' ' . I('sort');
        //$condition['prom_type'] = array('lt',5);
        $count = Db::name('order' . $select_year)->where($condition)->count();
        $Page = new AjaxPage($count, 10);
        $show = $Page->show();
        //获取订单列表
        //$orderList = $orderLogic->getOrderList($condition, $sort_order, $Page->firstRow, $Page->listRows);
        $orderList = Db::name('order' . $select_year)->where($condition)->limit("{$Page->firstRow},{$Page->listRows}")->order($sort_order)->select();
        //获取每个订单的商品列表
        $order_id_arr = get_arr_column($orderList, 'order_id');
        if (!empty($order_id_arr)) ;
        if ($order_id_arr) {
            $goods_list = Db::name('order_goods' . $select_year)->where("order_id", "in", implode(',', $order_id_arr))->select();
            $goods_arr = array();
            foreach ($goods_list as $v) {
                $goods_arr[$v['order_id']][] = $v;
            }
            $this->assign('goodsArr', $goods_arr);
        }
        $this->assign('orderList', $orderList);
        $this->assign('page', $show);// 赋值分页输出
        $this->assign('pager', $Page);
        return $this->fetch();
    }


    public function virtual_info()
    {
        $order_id = I('order_id/d');
        // 获取操作表
        $select_year = getTabByOrderId($order_id);
        $order = Db::name('order' . $select_year)->where(array('order_id' => $order_id, 'store_id' => STORE_ID))->find();
        !$order && $this->error('非法操作！！');
        if ($order['pay_status'] == 1) {
            $vrorder = Db::name('vr_order_code')->where(array('order_id' => $order_id))->select();
            if (!empty($vrorder)) {
                foreach ($vrorder as $k => $vrorderVal) {
                    if ($vrorderVal['vr_indate'] < time() && $vrorder['vr_state'] == 0) {
                        $vrorder[$k]['vr_state'] = 2;//看看有没有过期
                        Db::name('vr_order_code')->where(array('order_id' => $order_id))->update(['vr_state' => 2]);
                        break;
                    }
                }
            }
            $this->assign('vrorder', $vrorder);
        }
        $order_goods = Db::name('order_goods' . $select_year)->where(array('order_id' => $order_id, 'store_id' => STORE_ID))->find();
        $order_goods['virtual_indate'] = Db::name('goods')->where(array('goods_id' => $order_goods['goods_id'], 'store_id' => STORE_ID))->getField('virtual_indate');
        $this->assign('order', $order);
        $this->assign('order_goods', $order_goods);
        return $this->fetch();
    }

    public function virtual_cancel()
    {
        $order_id = I('order_id/d');
        if (IS_POST) {
            $admin_note = I('admin_note');
            $order = Db::name('order')->where(array('order_id' => $order_id, 'store_id' => STORE_ID))->find();
            if ($order) {
                $r = Db::name('order')->where(array('order_id' => $order_id, 'store_id' => STORE_ID))->save(array('order_status' => 3, 'admin_note' => $admin_note));
                if ($r) {
                    $orderLogic = new OrderLogic();
                    $seller_id = session('seller_id');
                    $orderLogic->orderActionLog($order, '取消订单', $admin_note, $seller_id, 1);
                    $this->ajaxReturn(array('status' => 1, 'msg' => '操作成功'));
                } else {
                    $this->ajaxReturn(array('status' => -1, 'msg' => '操作失败'));
                }
            } else {
                $this->ajaxReturn(array('status' => -1, 'msg' => '订单不存在'));
            }
        }
        $order = Db::name('order')->where(array('order_id' => $order_id, 'store_id' => STORE_ID))->find();
        $this->assign('order', $order);
        return $this->fetch();
    }

    public function verify_code()
    {
        if (IS_POST) {
            $vr_code = trim(I('vr_code'));
            if (!preg_match('/^[a-zA-Z0-9]{15,18}$/', $vr_code)) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '兑换码格式错误，请重新输入'));
            }
            $vr_code_info = Db::name('vr_order_code')->where(array('vr_code' => $vr_code))->find();
            $order = Db::name('order')->where(['order_id' => $vr_code_info['order_id']])->field('order_status,order_sn,user_note')->find();
            if ($order['order_status'] < 1) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '兑换码对应订单状态不符合要求'));
            }
            if (empty($vr_code_info) || $vr_code_info['store_id'] != STORE_ID) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '该兑换码不存在'));
            }
            if (empty($vr_code_info) || $vr_code_info['store_id'] != STORE_ID) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '该兑换码不存在'));
            }
            if ($vr_code_info['vr_state'] == '1') {
                $this->ajaxReturn(array('status' => -1, 'msg' => '该兑换码已被使用'));
            }
            if ($vr_code_info['vr_indate'] < time()) {
                $this->ajaxReturn(array('status' => -1, 'msg' => '该兑换码已过期，使用截止日期为： ' . date('Y-m-d H:i:s', $vr_code_info['vr_indate'])));
            }
            if ($vr_code_info['refund_lock'] > 0) {//退款锁定状态:0为正常,1为锁定(待审核),2为同意
                $this->ajaxReturn(array('status' => -1, 'msg' => '该兑换码已申请退款，不能使用'));
            }
            $update['vr_state'] = 1;
            $update['vr_usetime'] = time();
            Db::name('vr_order_code')->where(array('vr_code' => $vr_code))->save($update);
            //检查订单是否完成
            $condition = array();
            $condition['vr_state'] = 0;
            $condition['refund_lock'] = array('in', array(0, 1));
            $condition['order_id'] = $vr_code_info['order_id'];
            $condition['vr_indate'] = array('gt', time());
            $vr_order = Db::name('vr_order_code')->where($condition)->select();
            if (empty($vr_order)) {
                $data['order_status'] = 2;  //此处不能直接为4，不然前台不能评论
                $data['confirm_time'] = time();
                Db::name('order')->where(['order_id' => $vr_code_info['order_id']])->save($data);
                Db::name('order_goods')->where(['order_id' => $vr_code_info['order_id']])->save(['is_send' => 1]);  //把订单状态改为已收货
            }
            $order_goods = Db::name('order_goods')->where(['order_id' => $vr_code_info['order_id']])->find();
            if ($order_goods) {
                $result = [
                    'vr_code' => $vr_code,
                    'order_goods' => $order_goods,
                    'order' => $order,
                    'goods_image' => goods_thum_images($order_goods['goods_id'], 240, 240),
                ];
                $this->ajaxReturn(['status' => 1, 'msg' => '兑换成功', 'result' => $result]);
            } else {
                $this->ajaxReturn(['status' => -1, 'msg' => '虚拟订单商品不存在']);
            }
        }
        return $this->fetch();
    }

    /*
     * ajax 发货订单列表
    */
    public function ajaxdelivery()
    {
        $begin = $this->begin;
        $end = $this->end;
        $select_year = $this->select_year; // 表后缀
        $condition = array('store_id' => STORE_ID, 'deleted' => 0);
        if ($begin && $end) {
            $condition['add_time'] = array('between', "$begin,$end");
        }
        I('consignee') ? $condition['consignee'] = trim(I('consignee')) : false;
        I('order_sn') != '' ? $condition['order_sn'] = trim(I('order_sn')) : false;
        $shipping_status = I('shipping_status');
        $condition['shipping_status'] = empty($shipping_status) ? 0 : $shipping_status;
        $condition['order_status'] = array('in', '1,2,4');
        $condition['prom_type'] = array('in', '0,1,2,3,4,6');
        $count = Db::name('order' . $select_year)->where($condition)->count();
        $Page = new AjaxPage($count, 10);
        $show = $Page->show();
        $orderList = Db::name('order' . $select_year)->where($condition)->limit($Page->firstRow . ',' . $Page->listRows)->order('add_time DESC')->select();
        //@new 新UI 需要 {
        //获取每个订单的商品列表
        $order_id_arr = get_arr_column($orderList, 'order_id');
        //查询所有订单的所有商品
        if (count($order_id_arr) > 0) {
            $goods_list = Db::name('order_goods' . $select_year)->where("order_id", "in", implode(',', $order_id_arr))->select();
            $goods_arr = array();
            foreach ($goods_list as $v) {
                $goods_arr[$v['order_id']][] = $v;
            }
            $this->assign('goodsArr', $goods_arr);
        }
        //查询所有订单的用户昵称
        $user_id_arr = get_arr_column($orderList, 'user_id');
        if (count($user_id_arr) > 0) {
            $users = Db::name('users')->where("user_id", "in", implode(',', $user_id_arr))->getField("user_id,nickname");
            $this->assign('users', $users);
        }
        //}
        $this->assign('orderList', $orderList);
        $this->assign('page', $show);// 赋值分页输出
        return $this->fetch();
    }

    /**
     * 订单详情
     */
    public function detail()
    {
        $order_id = input('order_id');
        $Order = new OrderModel();
        $OrderGoods = new OrderGoods();
        $OrderAction = new OrderAction();
        $select_year = getTabByOrderId($order_id);
        $order = $Order->name('order' . $select_year)->where(['order_id' => $order_id])->find();
        if (!$order) {
            $this->error('该订单不存在或没有权利查看', U('Seller/Order/index'));
        }
        $order_goods = $OrderGoods->name('order_goods' . $select_year)->where(['order_id' => $order_id])->select();
        // 获取操作记录
        $action_log = $OrderAction->name('order_action' . $select_year)->where(['order_id' => $order_id])->order('log_time desc')->select();
        $express = Db::name('delivery_doc' . $select_year)->where("order_id", $order_id)->select();  //发货信息（可能多个）
        if ($order['is_comment'] == 1) {
            $comment_time = Db::name('comment')->where('order_id', $order['order_id'])->order('comment_id desc')->value('add_time');
            $this->assign('comment_time', $comment_time); //查询评论时间
        }
        $this->assign('order', $order);
        $this->assign('action_log', $action_log);
        $this->assign('order_goods', $order_goods);
        $this->assign('express', $express);
        return $this->fetch();
    }

    /**
     * 订单编辑
     */
    public function edit_order()
    {
        $order_id = I('order_id/d');
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        if ($order['store_id'] != STORE_ID) {
            $this->error('该订单不存在', U('Seller/Order/index'));
        }
        if ($order['order_status'] >= 2) {
            $this->error('已确认实发数订单不允许编辑');
            exit;
        }

        $orderGoods = $orderLogic->getOrderGoods($order_id);
        $shipping_list = config('shipping_lists');
        $payment_list = config('payment_lists');

        if (IS_POST) {
            $order['consignee'] = I('consignee');// 收货人
            $order['province'] = I('province'); // 省份
            $order['city'] = I('city'); // 城市
            $order['district'] = I('district'); // 县
            $order['address'] = I('address'); // 收货地址
            $order['mobile'] = I('mobile'); // 手机           
            $order['invoice_title'] = I('invoice_title');// 发票
            $order['admin_note'] = I('admin_note'); // 管理员备注
            $order['admin_note'] = I('admin_note'); //
            $order['shipping_code'] = I('shipping');// 物流方式
            $order['shipping_name'] = $shipping_list[$order['shipping_code']];
            $order['pay_code'] = I('payment');// 支付方式
            $order['pay_name'] = I('payname');
            $goods_id_arr = I("goods_id/a");
            $new_goods = $old_goods_arr = array();
            //################################订单添加商品
            if ($goods_id_arr) {
                $new_goods = $orderLogic->get_spec_goods($goods_id_arr);
                foreach ($new_goods as $key => $val) {
                    $val['order_id'] = $order_id;
                    $val['store_id'] = STORE_ID;
                    $rec_id = Db::name('order_goods')->add($val);//订单添加商品
                    if (!$rec_id)
                        $this->error('添加失败');
                }
            }

            //if ($order['pay_status'] == 0) {   //订单未支付才修改商品，订单费用
                //################################订单修改删除商品
                $old_goods = I('old_goods/a');
                $old_final_price = I('old_final_price/a');
                foreach ($orderGoods as $val) {
                    if (empty($old_goods[$val['rec_id']])) {
                        Db::name('order_goods')->where("rec_id", $val['rec_id'])->delete();//删除商品
                    } else {
                        //修改商品数量
                        if ($old_goods[$val['rec_id']] != $val['goods_num'] || $old_final_price[$val['rec_id']] != $val['final_price']) {
                            $val['goods_num'] = $old_goods[$val['rec_id']];
                            $val['final_price'] = $old_final_price[$val['rec_id']];
                            Db::name('order_goods')->where("rec_id", $val['rec_id'])->save(array('goods_num' => $val['goods_num'],'final_price'=>$val['final_price']));
                        }
                        $old_goods_arr[] = $val;
                    }
                }

                $goodsArr = array_merge($old_goods_arr, $new_goods);
                $pay = new Pay();
                try {
                    $pay->setUserId($order['user_id']);
                    $pay->payOrder($goodsArr);
                    //$pay->delivery($order['district']);
                } catch (TpshopException $t) {
                    $error = $t->getErrorArr();
                    $this->error($error['msg']);
                }

                //################################修改订单费用
                $order['goods_price'] = $pay->getGoodsPrice(); // 商品总价
                //$order['shipping_price'] = $pay->getShippingPrice();//物流费
                $total_amount = $pay->getGoodsPrice()+$order['shipping_price']+$order['discount']; // 订单总价
                $order['order_amount'] = $order['order_amount']+$total_amount-$order['total_amount']; // 应付金额
                $order['total_amount'] = $total_amount;
                $o = Db::name('order')->where(['order_id' => $order_id, 'store_id' => STORE_ID])->save($order);
            //} else {  //已支付订单只能修改下配送方式，配送地址
             //   $o = Db::name('order')->where(['order_id' => $order_id, 'store_id' => STORE_ID])->save($order);
            //}


            $seller_id = session('seller_id');
            $l = $orderLogic->orderActionLog($order, '编辑订单', '修改订单', $seller_id, 1);//操作日志
            if ($o && $l) {
                $this->success('修改成功', U('Order/detail', array('order_id' => $order_id)));
            } else {
                $this->success('修改失败', U('Order/detail', array('order_id' => $order_id)));
            }
            exit;
        }
        // 获取省份
        $province = Db::name('region')->where(array('parent_id' => 0, 'level' => 1))->select();
        //获取订单城市
        $city = Db::name('region')->where(array('parent_id' => $order['province'], 'level' => 2))->select();
        //获取订单地区
        $area = Db::name('region')->where(array('parent_id' => $order['city'], 'level' => 3))->select();
        //获取支付方式
        //$payment_list = Db::name('plugin')->where(array('status' => 1, 'type' => 'payment'))->select();
        //获取配送方式
        /*$shipping_list = Db::name('store_shipping')->field('s.shipping_name,s.shipping_code')
            ->alias('ss')->join('__SHIPPING__ s', 'ss.shipping_id = s.shipping_id')->where('ss.store_id', STORE_ID)->select();*/
        $this->assign('order', $order);
        $this->assign('province', $province);
        $this->assign('city', $city);
        $this->assign('area', $area);
        $this->assign('orderGoods', $orderGoods);
        $this->assign('shipping_list', $shipping_list);
        $this->assign('payment_list', $payment_list);
        return $this->fetch();
    }

    /*
     * 拆分订单
     */
    /*public function split_order()
    {
        $order_id = I('order_id/d');
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        if ($order['store_id'] != STORE_ID) {
            $this->error('该订单不存在', U('Seller/Order/index'));
        }
        if ($order['pay_status'] != 1) {
            $this->error('未支付订单不允许编辑');
            exit;
        }
        if ($order['shipping_status'] != 0) {
            $this->error('已发货订单不允许编辑');
            exit;
        }
        $orderGoods = $orderLogic->getOrderGoods($order_id);
        if (IS_POST) {
            //################################先处理原单剩余商品和原订单信息
            $old_goods = I('old_goods/a');
            $oldGoodsPrice = 0;
            foreach ($orderGoods as $val) {
                if (empty($old_goods[$val['rec_id']])) {
                    Db::name('order_goods')->where("rec_id", $val['rec_id'])->delete();//删除商品
                } else {
                    //修改商品数量
                    if ($old_goods[$val['rec_id']] != $val['goods_num']) {
                        $val['goods_num'] = $old_goods[$val['rec_id']];
                        Db::name('order_goods')->where("rec_id", $val['rec_id'])->save(array('goods_num' => $val['goods_num']));
                    }
                    $oldArr[] = $val;//剩余商品
                    $oldGoodsPrice += $val['goods_price'];
                }
                $all_goods[$val['rec_id']] = $val;//所有商品信息
            }
            $pay = new Pay();
            try {
                $pay->setUserId($order['user_id']);
                $pay->payOrder($oldArr);
                $pay->delivery($order['district']);
            } catch (TpshopException $t) {
                $error = $t->getErrorArr();
                $this->error($error['msg']);
            }
            //修改订单费用
            $res['goods_price'] = $pay->getGoodsPrice(); // 商品总价
            $res['order_amount'] = $pay->getOrderAmount(); // 应付金额
            $res['status'] = $order['status']; // 订单状态
            $res['pay_status'] = $order['pay_status']; // 支付状态
            $res['total_amount'] = $pay->getTotalAmount(); // 订单总价
            if ($order['user_money'] > 0 || $order['integral'] > 0) {
                $res['user_money'] = round($oldGoodsPrice / $order['goods_price'] * $order['user_money'], 2);
                $res['integral'] = floor($oldGoodsPrice / $order['goods_price'] * $order['integral']);
            }
            Db::name('order')->where(['order_id' => $order_id, 'store_id' => STORE_ID])->save($res);
            //################################原单处理结束

            //################################新单处理
            for ($i = 1; $i < 20; $i++) {
                if (!empty($_POST[$i . '_old_goods'])) {
                    $split_goods[] = $_POST[$i . '_old_goods'];//新订单商品
                }
            }
            foreach ($split_goods as $key => $vrr) {
                foreach ($vrr as $k => $v) {
                    $all_goods[$k]['goods_num'] = $v;
                    $brr[$key][] = $all_goods[$k];
                }
            }

            foreach ($brr as $goods) {
                $pay = new Pay();
                try {
                    $pay->setUserId($order['user_id']);
                    $pay->payOrder($goods);
                    $pay->delivery($order['district']);
                } catch (TpshopException $t) {
                    $error = $t->getErrorArr();
                    $this->error($error['msg']);
                }
                $new_order = $order;
                $new_order['order_sn'] = date('YmdHis') . mt_rand(1000, 9999);
                $new_order['parent_sn'] = $order['order_sn'];
                $new_order['user_money'] = 0;
                $new_order['integral'] = 0;
                if ($order['user_money'] > 0 || $order['integral'] > 0) {
                    $new_order['user_money'] = round($pay->getGoodsPrice() / $order['goods_price'] * $order['user_money'], 2);
                    $new_order['integral'] = floor($pay->getGoodsPrice() / $order['goods_price'] * $order['integral']);
                    $new_order['integral_money'] = $new_order['integral'] / ((int)tpCache('shopping.point_rate'));
                }
                //修改订单费用
                $new_order['goods_price'] = $pay->getGoodsPrice(); // 商品总价
                $new_order['order_amount'] = $pay->getGoodsPrice() - $new_order['user_money'] - $new_order['integral_money']; // 应付金额
                $new_order['total_amount'] = $pay->getGoodsPrice(); // 订单总价
                $new_order['add_time'] = time();

                $new_order['shipping_price'] = 0;//商家主动拆单物流费用忽略
                $new_order['pay_status'] = $order['pay_status'];
                $new_order['shipping_status'] = $order['shipping_status'];
                $new_order['order_status'] = $order['order_status'];
                unset($new_order['order_id']);
                $new_order_id = Db::name('order')->add($new_order);//插入订单表
                foreach ($goods as $vv) {
                    $vv['order_id'] = $new_order_id;//新订单order_id
                    unset($vv['rec_id']);
                    $vv['store_id'] = STORE_ID;
                    $nid = Db::name('order_goods')->add($vv);//插入订单商品表
                }
            }
            $orderLogic->orderActionLog($order, 'split', '拆分订单', session('seller_id'));
            //################################新单处理结束
            $this->success('操作成功', U('Order/detail', array('order_id' => $order_id)));
            exit;
        }

        foreach ($orderGoods as $val) {
            $brr[$val['rec_id']] = array('goods_num' => $val['goods_num'], 'goods_name' => getSubstr($val['goods_name'], 0, 35) . $val['spec_key_name']);
        }
        $this->assign('order', $order);
        $this->assign('goods_num_arr', json_encode($brr));
        $this->assign('orderGoods', $orderGoods);
        return $this->fetch();
    }*/

    /*
     * 价钱修改
     */
    public function editprice($order_id)
    {
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        if ($order['store_id'] != STORE_ID) {
            $this->error('该订单不存在', U('Seller/Order/index'));
        }
        if ($order['order_status'] >= 2) {
            $this->error('已确认实发数订单不允许编辑');
            exit;
        }
        $this->assign('order', $order);
        return $this->fetch();
    }

    /**
     * 修改订单价格
     */
    function editOrderPrice()
    {
        try {
            $post_data = I('post.', 0);
            $orderLogic = new SellerOrder($post_data['order_id']);
            $orderLogic->updataOrderPrice($post_data);
            $this->ajaxReturn(['status' => 1, 'msg' => '提交订单成功']);
        } catch (TpshopException $t) {
            $error = $t->getErrorArr();
            $this->ajaxReturn($error);
        }
    }

    /**
     * 订单删除
     */
    public function delete_order()
    {
        $orderLogic = new OrderLogic();
        $order_id = input('order_id/d');
        if (empty($order_id)) {
            $this->error('参数错误');
        }
        $order = Db::name('order')->where('order_id', $order_id)->find();
        if (empty($order)) {
            $this->error('订单记录不存在');
        }
        if ($order['deleted'] == 1) {
            $this->error('订单记录已经删除');
        }
        if ($order['order_status'] != 5) {
            $this->error('只有作废的订单才能删除');
        }
        $del = $orderLogic->delOrder($order_id, STORE_ID);
        if ($del !== false) {
            $this->success('删除订单成功');
        } else {
            $this->error('订单删除失败');
        }
    }

    /**
     * 订单取消付款
     */
    public function pay_cancel($order_id)
    {
        if (I('remark')) {
            $data = I('post.');
            $orderLogic = new OrderLogic();
            $order = $orderLogic->getOrderInfo($data['order_id']);
            if ($order['store_id'] != STORE_ID) {
                $this->error('该订单不存在', U('Seller/Order/index'));
            }
            $note = array('退款到用户余额', '已通过其他方式退款', '不处理，误操作项');
            if ($data['refundType'] == 0 && $data['amount'] > 0) {
                accountLog($data['user_id'], $data['amount'], 0, '退款到用户余额');
            }
            $orderLogic->orderProcessHandle($data['order_id'], 'pay_cancel', STORE_ID);
            $seller_id = session('seller_id');
            $d = $orderLogic->orderActionLog($order, '取消付款', $data['remark'] . ':' . $note[$data['refundType']], $seller_id, 1);
            if ($d) {
                exit("<script>window.parent.pay_callback(1);</script>");
            } else {
                exit("<script>window.parent.pay_callback(0);</script>");
            }
        } else {
            $order = Db::name('order')->where("order_id", $order_id)->find();
            $this->assign('order', $order);
            return $this->fetch();
        }
    }

    /**
     * 订单打印
     * @return mixed
     */
    public function order_print()
    {
        $ids = I('get.ids');
        $ids = trim($ids, ',');
        $ids = explode(',', $ids);
        if (!is_numeric($ids[0])) {
            unset($ids[0]);
        }

        $orderLogic = new OrderLogic();

        $orders = array();
        foreach ($ids as $k => $v) {
            $orders[] = $orderLogic->getOrderInfo($v);
        }

        foreach ($orders as $k => $v) {
            if ($v['store_id'] != STORE_ID) {
                unset($orders[$k]);//删掉不存在订单
            }
        }

        if (!$orders) {
            $this->error('所选订单不存在', U('Seller/Order/index'));
        }

        foreach ($orders as $k => $v) {
            $orders[$k]['province'] = getRegionName($v['province']);
            $orders[$k]['city'] = getRegionName($v['city']);
            $orders[$k]['district'] = getRegionName($v['district']);
            $orders[$k]['full_address'] = $orders[$k]['province'] . ' ' . $orders[$k]['city'] . ' ' . $orders[$k]['district'] . ' ' . $orders[$k]['address'];
        }

        foreach ($orders as $k => $v) {
            $orders[$k]['orderGoods'] = $orderLogic->getOrderGoods($v['order_id']);
        }

        foreach ($orders as $k => $v) {
            $orders[$k]['order_num_arr'] = get_arr_column($v['orderGoods'], 'goods_num');
        }


        foreach ($orders as $k => $v) {
            if ($v['order_num_arr']) {
                $orders[$k]['goodsCount'] = array_sum($v['order_num_arr']);
            }
        }
        $DeliveryDoc = new DeliveryDoc();
        $deliveryDoc = $DeliveryDoc->where('order_id', 'in', $ids)->select();
        $shop = tpCache('shop_info');
        $this->assign('orders', $orders);
        $this->assign('shop', $shop);
        $this->assign('deliveryDoc', $deliveryDoc);
        $template = I('template', 'picking');
        return $this->fetch($template);
    }


    /**
     * 快递单打印
     */
    public function shipping_print($id = '')
    {
        $order_id = $id ? $id : I('get.order_id/d');

        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        if ($order['store_id'] != STORE_ID) {
            $this->error('该订单不存在', U('seller/Order/index'));
        }
        //查询是否存在订单及物流
        $shipping = Db::name('shipping')->where(['shipping_code' => $order['shipping_code']])->find();
        if (!$shipping) {
            $this->error('物流插件不存在');
        }
        if (empty($shipping['template_html'])) {
            $this->error('请联系平台管理员设置' . $shipping['shipping_name'] . '打印模板');
        }
        $shop = Db::name('store')->field('store_name')->where(['store_id' => STORE_ID])->find();
        $delivery_doc = Db::name('delivery_doc')->where(['order_id' => $order_id])->find();
        $delivery_doc['province'] = empty($delivery_doc['store_address_province_id']) ? '' : getRegionName($delivery_doc['store_address_province_id']);
        $delivery_doc['city'] = empty($delivery_doc['store_address_city_id']) ? '' : getRegionName($delivery_doc['store_address_city_id']);
        $delivery_doc['district'] = empty($delivery_doc['store_address_district_id']) ? '' : getRegionName($delivery_doc['store_address_district_id']);

        $order['province'] = getRegionName($order['province']);
        $order['city'] = getRegionName($order['city']);
        $order['district'] = getRegionName($order['district']);
        $template_var = array("发货点-名称", "发货点-联系人", "发货点-电话", "发货点-省份", "发货点-城市",
            "发货点-区县", "发货点-手机", "发货点-详细地址", "收件人-姓名", "收件人-手机", "收件人-电话",
            "收件人-省份", "收件人-城市", "收件人-区县", "收件人-邮编", "收件人-详细地址", "时间-年", "时间-月",
            "时间-日", "时间-当前日期", "订单-订单号", "订单-备注", "订单-配送费用");
        $content_var = array($shop['store_name'], $delivery_doc['store_address_consignee'], $delivery_doc['store_address_mobile'], $delivery_doc['province'], $delivery_doc['city'],
            $delivery_doc['district'], $delivery_doc['store_address_mobile'], $delivery_doc['store_address'], $order['consignee'], $order['mobile'], $order['phone'],
            $order['province'], $order['city'], $order['district'], $order['zipcode'], $order['address'], date('Y'), date('M'),
            date('d'), date('Y-m-d'), $order['order_sn'], $order['admin_note'], $order['shipping_price'],
        );
        $shipping['template_html_replace'] = str_replace($template_var, $content_var, $shipping['template_html']);
        if ($id) {
            return $shipping;
        } else {
            $shippings[] = $shipping;
            $this->assign('shipping', $shippings);
            return $this->fetch("print_express");
        }
    }

    /*
    *批量打印快递单 
    */
    public function shipping_print_batch()
    {
        $ids = I('get.ids');
        $ids = substr($ids, 0, -1);
        $ids = explode(',', $ids);
        if (!is_numeric($ids[0])) {
            unset($ids[0]);
        }

        $shippings = array();
        foreach ($ids as $k => $v) {
            $shippings[$k] = $this->shipping_print($v);
        }
        $this->assign('shipping', $shippings);
        return $this->fetch("print_express");
    }

    /**
     * 生成发货单
     */
    public function deliveryHandle()
    {
        $orderLogic = new OrderLogic();
        $data = I('post.');
        $res = $orderLogic->deliveryHandle($data, STORE_ID);
        if ($res) {
            $this->ajaxReturn(['status' => 1, 'msg' => '操作成功', 'url' => U('Seller/Order/index')]);
        } else {
            $this->ajaxReturn(['status' => 1, 'msg' => '操作失败', 'url' => U('Order/delivery_info', array('order_id' => $data['order_id']))]);
        }
    }


    public function delivery_info()
    {
        //商家发货
        $order_id = I('order_id/d');
        $orderLogic = new OrderLogic();
        $order = $orderLogic->getOrderInfo($order_id);
        if ($order['store_id'] != STORE_ID) {
            $this->error('该订单不存在', U('Seller/Order/index'));
        }
        $return_goods = Db::name('return_goods')->where(array('order_id' => $order_id))->getField('rec_id,goods_num');
        $orderGoods = $orderLogic->getOrderGoods($order_id);
        $DeliveryDoc = new DeliveryDoc();
        $delivery_record = $DeliveryDoc->where(['order_id' => $order_id])->select();
        if ($delivery_record) {
            $order['invoice_no'] = $delivery_record[count($delivery_record) - 1]['invoice_no'];
        }
        $order['unsend'] = $return_goods ? 1 : 0;
        foreach ($orderGoods as $k => $v) {
            if (!empty($return_goods[$v['rec_id']])) {
                $orderGoods[$k]['unsend'] = 1;
            } else {
                $orderGoods[$k]['unsend'] = 0;
            }
        }
        /*$shipping_list = Db::name('store_shipping')->field('shipping_name,shipping_code')
            ->alias('ss')->join('__SHIPPING__ s', 's.shipping_id = ss.shipping_id', 'left')->where('ss.store_id', STORE_ID)->select();*/
        $shipping_list = config('shipping_lists');
        $StoreAddress = new StoreAddress();
        $deliver_address = $StoreAddress->where(['store_id' => STORE_ID, 'type' => 0])->order('is_default desc')->select();
        $order_extend = Db::name('order_extend')->where(['store_id' => STORE_ID, 'order_id' => $order_id])->find();
        $this->assign('shipping_list', $shipping_list);
        $this->assign('deliver_address', $deliver_address);//发货地址
        $this->assign('order', $order);
        $this->assign('order_extend', $order_extend);
        $this->assign('orderGoods', $orderGoods);
        $this->assign('delivery_record', $delivery_record);//发货记录
        return $this->fetch();
    }

    /*
    *批量发货处理
    */
    public function send_batch()
    {
        $data = I('post.');
        $param = $data['order'];
        //参数拼装
        foreach ($param as $k => $v) {
            $param[$k]['shipping_name'] = $data['shipping_name'];
            $param[$k]['shipping_code'] = $data['shipping_code'];
            $param[$k]['note'] = '';//好像是必填项 没有此字段会数据库报错 先赋个空值
        }

        $orderLogic = new OrderLogic();
        foreach ($param as $k => $v) {
            $res = $orderLogic->deliveryHandle($v, STORE_ID);
            if ($res === false) {
                $this->ajaxReturn(['status' => 0, 'msg' => '操作失败']);
            }
        }
        $this->ajaxReturn(['status' => 1, 'msg' => '操作成功']);
    }

    /**
     * 发货单列表
     */
    public function delivery_list()
    {
        $this->assign('begin', date('Y-m-d', strtotime("-3 month") + 86400));
        $this->assign('end', date('Y-m-d', strtotime('+1 days')));
        return $this->fetch();
    }

    /**
     * 订单操作
     */
    public function order_action()
    {
        $orderLogic = new OrderLogic();
        $type = I('get.type');
        $order_id = I('get.order_id/d');
        if ($type && $order_id) {
            $order = $orderLogic->getOrderInfo($order_id);
            $button = $orderLogic->getOrderButton($order);
            if ($order) {
                $a = $orderLogic->orderProcessHandle($order_id, $type, STORE_ID);
                if($a !== true){
                    exit(json_encode(array('status' => 0, 'msg' => $a['msg'])));
                }
                $seller_id = session('seller_id');
                $action = '';
                if (in_array($type, array_keys($button))) {
                    $action = $button[$type];
                }
                $res = $orderLogic->orderActionLog($order, $action, I('note'), $seller_id, 1);
                if ($res && $a) {
                    exit(json_encode(array('status' => 1, 'msg' => '操作成功')));
                } else {
                    exit(json_encode(array('status' => 0, 'msg' => '操作失败')));
                }
            } else {
                exit(json_encode(array('status' => 0, 'msg' => '非法操作')));
            }
        } else {
            $this->error('参数错误', U('Seller/Order/detail', array('order_id' => $order_id)));
        }
    }

    public function order_log()
    {
        $condition = array();
        $log = Db::name('order_action');
        $admin_id = I('admin_id/d');
        if ($admin_id > 0) {
            $condition['action_user'] = $admin_id;
        }
        $condition['store_id'] = STORE_ID;
        $count = $log->where($condition)->count();
        $Page = new Page($count, 20);
        foreach ($condition as $key => $val) {
            $Page->parameter[$key] = urlencode($val);
        }
        $show = $Page->show();
        $list = $log->where($condition)->order('action_id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $seller = Db::name('seller')->getField('seller_id,seller_name');
        $this->assign('admin', $seller);
        return $this->fetch();
    }

    public function export_order()
    {
        //搜索条件
        $where['store_id'] = STORE_ID;
        $consignee = I('consignee');
        if ($consignee) {
            $where['consignee'] = ['like', '%' . $consignee . '%'];
        }
        $order_sn = I('order_sn');
        if ($order_sn) {
            $where['order_sn'] = $order_sn;
        }
        $order_status = I('order_status');
        if ($order_status) {
            $where['order_status'] = $order_status;
        }

        $timegap = I('timegap');
        if ($timegap) {
            $gap = explode('-', $timegap);
            $begin = strtotime($gap[0]);
            $end = strtotime($gap[1]);
            $where['add_time'] = ['between', [$begin, $end]];
        }
        $region = Db::name('region')->cache(true)->getField('id,name');
        $orderList = Db::name('order')->field("*,FROM_UNIXTIME(add_time,'%Y-%m-%d') as create_time")->where($where)->order('order_id')->select();
        $strTable = '<table width="500" border="1">';
        $strTable .= '<tr>';
        $strTable .= '<td style="text-align:center;font-size:12px;width:120px;">订单编号</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="100">日期</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">收货人</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">收货地址</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">电话</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">订单金额</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">实际支付</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付方式</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付状态</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">发货状态</td>';
        $strTable .= '<td style="text-align:center;font-size:12px;" width="*">商品信息</td>';
        $strTable .= '</tr>';

        foreach ($orderList as $k => $val) {
            $strTable .= '<tr>';
            $strTable .= '<td style="text-align:center;font-size:12px;">&nbsp;' . $val['order_sn'] . '</td>';
            $strTable .= '<td style="text-align:left;font-size:12px;">' . $val['create_time'] . ' </td>';
            $strTable .= '<td style="text-align:left;font-size:12px;">' . "{$region[$val['province']]},{$region[$val['city']]},{$region[$val['district']]},{$region[$val['twon']]}{$val['consignee']}" . ' </td>';
            $strTable .= '<td style="text-align:left;font-size:12px;">' . $val['address'] . '</td>';
            $strTable .= '<td style="text-align:left;font-size:12px;">' . $val['mobile'] . '</td>';
            $strTable .= '<td style="text-align:left;font-size:12px;">' . $val['goods_price'] . '</td>';
            $strTable .= '<td style="text-align:left;font-size:12px;">' . $val['order_amount'] . '</td>';
            if ($val['pay_name'] == '') {
                $strTable .= '<td style="text-align:left;font-size:12px;">在线支付</td>';
            } else {
                $strTable .= '<td style="text-align:left;font-size:12px;">' . $val['pay_name'] . '</td>';
            }
            $strTable .= '<td style="text-align:left;font-size:12px;">' . $this->pay_status[$val['pay_status']] . '</td>';
            $strTable .= '<td style="text-align:left;font-size:12px;">' . $this->shipping_status[$val['shipping_status']] . '</td>';
            $orderGoods = D('order_goods')->where('order_id', $val['order_id'])->select();
            $strGoods = "";
            foreach ($orderGoods as $goods) {
                $strGoods .= "商品编号：" . $goods['goods_sn'] . " 商品名称：" . $goods['goods_name'];
                if ($goods['spec_key_name'] != '') $strGoods .= " 规格：" . $goods['spec_key_name'];
                $strGoods .= "<br />";
            }
            unset($orderGoods);
            $strTable .= '<td style="text-align:left;font-size:12px;">' . $strGoods . ' </td>';
            $strTable .= '</tr>';
        }
        $strTable .= '</table>';
        unset($orderList);
        downloadExcel($strTable, 'order');
        exit();
    }

    /**
     * 添加一笔订单
     */
    public function add_order()
    {
        $order = array('store_id' => STORE_ID);
        //  获取省份
        $province = Db::name('region')->where(array('parent_id' => 0, 'level' => 1))->select();
        //  获取订单城市
        $city = Db::name('region')->where(array('parent_id' => $order['province'], 'level' => 2))->select();
        //  获取订单地区
        $area = Db::name('region')->where(array('parent_id' => $order['city'], 'level' => 3))->select();
        //  获取配送方式
        /*$shipping_list = Db::name('store_shipping')->field('s.shipping_name,s.shipping_code')
            ->alias('ss')->join('__SHIPPING__ s', 'ss.shipping_id = s.shipping_id')->where('ss.store_id', STORE_ID)->select();*/
        //  获取支付方式
        //$payment_list = Db::name('plugin')->where(array('status' => 1, 'type' => 'payment'))->select();
        $shipping_list = config('shipping_lists');
        $payment_list = config('payment_lists');
        if (IS_POST) {
            $order['user_id'] = I('user_id/d');// 用户id 可以为空
            $order['consignee'] = I('consignee');// 收货人
            $order['province'] = I('province'); // 省份
            $order['city'] = I('city'); // 城市
            $order['district'] = I('district'); // 县
            $order['address'] = I('address'); // 收货地址
            $order['mobile'] = I('mobile'); // 手机           
            $order['invoice_title'] = I('invoice_title');// 发票
            $order['admin_note'] = I('admin_note'); // 管理员备注            
            $order['order_sn'] = date('YmdHis') . mt_rand(1000, 9999); // 订单编号;
            $order['admin_note'] = I('admin_note'); // 
            $order['add_time'] = time(); //                    
            $order['shipping_code'] = I('shipping');// 物流方式
            $order['shipping_price'] = I('shipping_price');// 物流费
            $order['shipping_name'] = $shipping_list[$order['shipping_code']];
            $order['pay_code'] = I('payment');// 支付方式
            //$order['pay_name'] = Db::name('plugin')->where(array('status' => 1, 'type' => 'payment', 'code' => I('payment')))->getField('name');
            $order['pay_name'] = $payment_list[$order['pay_code']];
            if($order['pay_code'] == 'contract'){//如果是账期支付订单类型也为账期订单
                $order['prom_type'] = 1;
            }
            $goods_id_arr = I("goods_id/a");
            $orderLogic = new OrderLogic();
            $order_goods = $orderLogic->get_spec_goods($goods_id_arr);
            $pay = new Pay();
            try {
                $pay->setUserId($order['user_id']);
                $pay->payGoodsList($order_goods);
                $pay->delivery($order['shipping_code'],$order['shipping_price']);
            } catch (TpshopException $t) {
                $error = $t->getErrorArr();
                $this->error($error['msg']);
            }
            $order['goods_price'] = $pay->getGoodsPrice(); // 商品总价
            $order['shipping_price'] = $pay->getShippingPrice() > 0 ? $pay->getShippingPrice() : 0; //物流费
            $order['order_amount'] = $pay->getOrderAmount(); // 应付金额
            $order['total_amount'] = $pay->getTotalAmount(); // 订单总价

            // 添加订单
            $order_id = Db::name('order')->add($order);
            if ($order_id) {
                foreach ($order_goods as $key => $val) {
                    $val['order_id'] = $order_id;
                    $val['store_id'] = STORE_ID;
                    $rec_id = Db::name('order_goods')->add($val);
                    if (!$rec_id)
                        $this->error('添加失败');
                }
                $this->success('添加商品成功', U("Order/detail", array('order_id' => $order_id)));
                exit();
            } else {
                $this->error('添加失败');
            }
        }
        $this->assign('shipping_list', $shipping_list);
        $this->assign('payment_list', $payment_list);
        $this->assign('province', $province);
        $this->assign('city', $city);
        $this->assign('area', $area);
        return $this->fetch();
    }

    /**
     * 选择搜索商品
     */
    public function search_goods()
    {
        $GoodsLogic = new GoodsLogic;
        $brandList = $GoodsLogic->getSortBrands();
        $categoryList = $GoodsLogic->getSortCategory();
        $where = array('store_id' => STORE_ID, 'is_on_sale' => 1);
        I('intro') && $where[I('intro')] = 1;
        $brand_id = I('brand_id/d');
        $cat_id = I('cat_id/d');
        $keywords = I('keywords');
        $where['is_virtual'] = I('is_virtual/d', 0); //默认不查虚拟商品
        if ($cat_id) {
            $goods_category = Db::name('goods_category')->where("id", $cat_id)->find();
            $where['cat_id' . $goods_category['level']] = $cat_id; // 初始化搜索条件
            $this->assign('cat_id', $cat_id);
        }
        if ($brand_id) {
            $this->assign('brand_id', $brand_id);
            $where['brand_id'] = $brand_id;
        }
        if ($keywords) {
            $this->assign('keywords', $keywords);
            $where['goods_name|keywords'] = array('like', '%' . $keywords . '%');
        }
        $count = Db::name('goods')->where($where)->count();
        $Page = new Page($count, 10);
        $goodsList = Db::name('goods')->where($where)->order('goods_id DESC')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($goodsList as $key => $val) {
            $spec_goods = Db::name('spec_goods_price')->where("goods_id", $val['goods_id'])->select();
            $goodsList[$key]['spec_goods'] = $spec_goods;
        }
        $store_bind_class = Db::name('store_bind_class')->where("store_id", STORE_ID)->select();
        $cat_id1 = get_arr_column($store_bind_class, 'class_1');
        $cat_id2 = get_arr_column($store_bind_class, 'class_2');
        $cat_id3 = get_arr_column($store_bind_class, 'class_3');
        $cat_id0 = array_merge($cat_id1, $cat_id2, $cat_id3);

        $this->assign('categoryList', $categoryList);
        $this->assign('brandList', $brandList);
        $this->assign('page', $Page->show());//赋值分页输出
        $this->assign('cat_id0', $cat_id0);
        $this->assign('goodsList', $goodsList);
        return $this->fetch();
    }

    public function ajaxOrderNotice()
    {
        $order_amount = Db::name('order')->where(array('order_status' => 0))->count();
        echo $order_amount;
    }

    //虚拟订单临时支付方法，以后要删除
    public function generateVirtualCode()
    {
        $order_id = I('order_id/d');
        // 获取操作表
        $select_year = getTabByOrderId($order_id);
        $order = Db::name('order' . $select_year)->where(array('order_id' => $order_id, 'store_id' => STORE_ID))->find();
        $res = update_pay_status($order['order_sn'], 1);
        $this->success('成功生成兑换码', U('Order/virtual_info', ['order_id' => $order_id]), 1);
    }
    /**
     * ajax获取客户的列表
     */
    public function ajax_address_lists(){
        $user_id = input('user_id/d');
        if(IS_AJAX){
            $page = input('page/d');
            $limit = input('limit/d');
            $where = ['user_id'=>$user_id];
            $count = M('user_address')->where($where)->count();
            $start = ($page-1) * $limit;
            $list = M('user_address')->where($where)->limit($start . ',' . $limit)->select();
            foreach($list as $k=>$v){
                $list[$k]['radio'] .= "<input name='address_id' type='radio' value='$v[address_id]'>";
                $list[$k]['radio'] .= "<input name='province' type='hidden' value='$v[province]'>";
                $list[$k]['radio'] .= "<input name='city' type='hidden' value='$v[city]'>";
                $list[$k]['radio'] .= "<input name='district' type='hidden' value='$v[district]'>";
                $list[$k]['radio'] .= "<input name='mobile' type='hidden' value='$v[mobile]'>";
                $list[$k]['radio'] .= "<input name='consignee' type='hidden' value='$v[consignee]'>";
            }
            $this->ajaxReturn(['code'=>0,'msg'=>'','data'=>$list,'count'=>$count]);
        }
        $this->assign('user_id',$user_id);
        return $this->fetch();
    }
}
