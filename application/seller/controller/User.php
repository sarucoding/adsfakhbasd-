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

use think\AjaxPage;
use think\Loader;
use think\Db;
use think\Page;
use app\admin\logic\UsersLogic;
use app\common\logic\UserRelationStoreLogic;

class User extends Base
{
    /**
     * 客户列表
     */
    function index(){
        $relation = M('user_relation_store');
        $keywords = I('keywords','');
        $on = 'a.user_id = b.user_id';
        if($keywords){
            $on .= ' and (realname like "%'.$keywords.'%" or mobile like "%'.$keywords.'%")';
        }
        $count = $relation->alias('a')->join('__USERS__ b',$on)->where('store_id', STORE_ID)->count();
        $page = new Page($count, 10);
        $list = $relation->alias('a')->join('__USERS__ b',$on)->where('store_id',STORE_ID)->order('re_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->assign('keywords', $keywords);
        return $this->fetch();
    }
    /**
     * 添加客户
     */
    function add(){
        if(IS_POST){
            $data = input('post.');
            $userValidate = Loader::validate('User');
            if (!$userValidate->batch()->check($data)) {
                $this->ajaxReturn(['status' => 0, 'msg' => '操作失败', 'result' => $userValidate->getError()]);
            }
            if($data['is_contract'] == 1){
                if($data['settle_interval'] == ''){
                    $err['settle_interval'] = '账期不能为空';
                }
                if($data['settle_cycle'] > 0 && !$data['settle_day']){
                    $err['settle_day'] = '支付日期必须选择';
                }
            }
            if($err)
                $this->ajaxReturn(['status' => 0, 'msg' => '操作失败', 'result' => $err]);
            $data['recommend_store_id'] = STORE_ID;//哪个店铺添加的用户，推荐店铺就是哪个
            $data['nickname'] = mobile_hide($data['mobile']);//默认昵称为隐藏的手机号
            $user_obj = new UsersLogic();
            $res = $user_obj->addUser($data);
            if($res['status'] == 1){
                $data['user_id'] = $res['user_id'];
                $data['store_id'] = STORE_ID;
                $relation_logic = new UserRelationStoreLogic();
                $res = $relation_logic->add_relation($data);
                $res['result'] = '';
                $this->ajaxReturn($res);
            }else{
                $this->ajaxReturn(['status' => 0, 'msg' => '添加客户失败，'.$res['msg'], 'result' => '']);
            }

        }
        $service = M('seller')->where(['store_id'=>STORE_ID,'enabled'=>0])->select();
        $this->assign('service',$service);
        return $this->fetch();
    }
    /**
     * 编辑客户
     */
    function edit(){
        if(IS_POST){
            $data = input('post.');
            $userValidate = Loader::validate('User');
            if (!$userValidate->batch()->scene('edit')->check($data)) {
                $this->ajaxReturn(['status' => 0, 'msg' => '操作失败', 'result' => $userValidate->getError()]);
            }
            if($data['is_contract'] == 1){
                if($data['settle_interval'] == ''){
                    $err['settle_interval'] = '账期不能为空';
                }
                if($data['settle_cycle'] > 0 && !$data['settle_day']){
                    $err['settle_day'] = '支付日期必须选择';
                }
            }
            if($err)
                $this->ajaxReturn(['status' => 0, 'msg' => '操作失败', 'result' => $err]);
            $res = M('user_relation_store')->where(['store_id'=>STORE_ID,'re_id'=>$data['re_id']])->save($data);
            if($res){
                $this->ajaxReturn(['status' => 1, 'msg' => '编辑成功', 'result' => '']);
            }else{
                $this->ajaxReturn(['status' => 0, 'msg' => '编辑失败', 'result' => '']);
            }

        }
        $re_id = I('re_id/d');
        $user = M('user_relation_store')->alias('a')->join('__USERS__ b','a.user_id = b.user_id')->where(['store_id'=>STORE_ID,'re_id'=>$re_id])->find();
        if(!$user){
            $this->error('该客户不存在');
        }
        $option = '<option value="">请选择支付日期</option>';//支付日期选择框标签代码
        if($user['is_contract'] == 1 && $user['settle_cycle'] == 1){
            for($i = 1; $i <= 7; $i++){
                $selected = $user["settle_day"] == $i?"selected":"";
                switch($i){
                    case 1:
                        $day = '周一';
                        break;
                    case 2:
                        $day = '周二';
                        break;
                    case 3:
                        $day = '周三';
                        break;
                    case 4:
                        $day = '周四';
                        break;
                    case 5:
                        $day = '周五';
                        break;
                    case 6:
                        $day = '周六';
                        break;
                    case 7:
                        $day = '周日';
                        break;
                }
                $option .= '<option value="'.$i.'" '.$selected.'>'.$day.'</option>';
            }
        }elseif($user['is_contract'] == 1 && $user['settle_cycle'] == 2){
            for($i = 1; $i <= 28; $i++){
                $selected = $user["settle_day"] == $i?"selected":"";
                $option .= '<option value="'.$i.'" '.$selected.'>'.$i.'号</option>';
            }
        }
        $service = M('seller')->where(['store_id'=>STORE_ID,'enabled'=>0])->select();
        $this->assign('service',$service);
        $this->assign('info',$user);
        $this->assign('option',$option);
        return $this->fetch();
    }
    /**
     * 余额明细
     */
    function account_log(){
        $re_id = I('re_id/d');
        $user = D('user_relation_store')->get_user_info($re_id);
        if(!$user){
            $this->error('该客户不存在');
        }
        $count = M('store_account_log')->where(['store_id'=>STORE_ID,'re_id'=>$re_id])->count();
        $page = new Page($count, 10);
        $list = M('store_account_log')->where(['store_id'=>STORE_ID,'re_id'=>$re_id])->order('log_id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->assign('user', $user);
        return $this->fetch();
    }
    /**
     * 资金手动调节
     */
    function edit_account(){
        if(IS_POST){
            $data = input('post.');
            $validate = $this->validate($data, [
                're_id' => 'integer|require',
                'log_money' => 'number|require',
                'log_info' => 'require',
            ]);
            if ($validate !== true)
                $this->ajaxReturn(['status' => 0, 'msg' => '参数错误']);
            if($data['log_money'] == 0){
                $this->ajaxReturn(['status' => 0, 'msg' => '金额不能为0']);
            }
            $user = D('user_relation_store')->get_user_info($data['re_id']);
            if(!$user){
                $this->ajaxReturn(['status' => 0, 'msg' => '该客户不存在']);
            }
            if($user['store_money'] + $data['log_money'] < 0){
                $this->ajaxReturn(['status' => 0, 'msg' => '客户余额不足']);
            }
            $res = userStoreAccountLog($data['re_id'],STORE_ID,$data['log_money'],$data['log_info']);
            if($res)
                $this->ajaxReturn(['status' => 1, 'msg' => '操作成功', 'result' => $data]);
            else
                $this->ajaxReturn(['status' => 0, 'msg' => '操作失败']);
        }
        $re_id = I('re_id/d');
        $user = D('user_relation_store')->get_user_info($re_id);
        if(!$user){
            $this->error('该客户不存在');
        }
        $this->assign('user', $user);
        return $this->fetch();
    }
    /**
     * ajax获取客户列表，进行客户选择
     */
    public function ajax_user_lists(){
        if(IS_AJAX){
            $page = input('page/d');
            $limit = input('limit/d');
            $realname = input('realname');
            $where = "a.store_id = ".STORE_ID;
            $on = 'a.user_id = b.user_id';
            if($realname){
                $on .= ' and b.realname like "%'.$realname.'%"';
            }
            $count = M('user_relation_store')->alias('a')->join('__USERS__ b',$on,'LEFT')->where($where)->count();
            $start = ($page-1) * $limit;
            $list = M('user_relation_store')->alias('a')->join('__USERS__ b',$on,'LEFT')->where($where)->limit($start . ',' . $limit)->select();
            foreach($list as $k=>$v){
                $list[$k]['radio'] = "<input name='user_id' type='radio' value='$v[user_id]'>";
                $list[$k]['type'] = $v['is_contract'] == 1 ? "账期客户" : '非账期客户';
            }
            $this->ajaxReturn(['code'=>0,'msg'=>'','data'=>$list,'count'=>$count]);
        }
        return $this->fetch();
    }
    /**
     * 地址列表
     */
    function address(){
        $user_id = I('user_id/d');
        $count = M('user_address')->alias('a')->join('__USER_ADDRESS_FREIGHT__ b','a.address_id = b.address_id and b.store_id = '.STORE_ID,'LEFT')->where(['a.user_id'=>$user_id])->count();
        $page = new Page($count, 10);
        $field = 'a.*,b.uaf_freight';
        $list = M('user_address')->alias('a')
            ->join('__USER_ADDRESS_FREIGHT__ b','a.address_id = b.address_id and b.store_id = '.STORE_ID,'LEFT')
            ->where(['a.user_id'=>$user_id])
            ->order('a.address_id desc')
            ->field($field)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->select();
        $region_list = Db::name('region')->cache(true)->getField('id,name');
        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->assign('region_list', $region_list);
        return $this->fetch();
    }
    /**
     * 运费设置
     */
    function edit_freight(){
        if(IS_POST){
            $data = input('post.');
            $validate = $this->validate($data, [
                'address_id' => 'integer|require',
                'uaf_freight' => 'number|require',
            ]);
            if ($validate !== true)
                $this->ajaxReturn(['status' => 0, 'msg' => '参数错误']);
            if($data['uaf_freight'] < 0){
                $this->ajaxReturn(['status' => 0, 'msg' => '金额必须大于等于0']);
            }
            $address_freight = M('user_address_freight')->where(['store_id'=>STORE_ID,'address_id'=>$data['address_id']])->find();
            if(!$address_freight){//如果原本不存在运费数据则新增，否则直接更新
                $res = M('user_address_freight')->add(['address_id'=>$data['address_id'],'store_id'=>STORE_ID,'uaf_freight'=>$data['uaf_freight']]);
            }else{
                $res = M('user_address_freight')->where(['store_id'=>STORE_ID,'address_id'=>$data['address_id']])->update(['uaf_freight'=>$data['uaf_freight']]);
            }
            if($res)
                $this->ajaxReturn(['status' => 1, 'msg' => '操作成功', 'result' => $data]);
            else
                $this->ajaxReturn(['status' => 0, 'msg' => '操作失败']);
        }
        $address_id = I('address_id/d');
        $this->assign('address_id', $address_id);
        return $this->fetch();
    }
    /**
     * 账户资金调节
     */
    public function return_goods()
    {
        $desc = I('post.desc');
        $return_goods_id = I('return_goods_id/d');
        $return_goods = M('return_goods')->where(['id' => $return_goods_id, 'store_id' => STORE_ID])->find();
        empty($return_goods) && $this->error("参数有误");

        $user_id = $return_goods['user_id'];
        $order_goods = M('order_goods')->where(['order_id' => $return_goods['order_id'], 'goods_id' => $return_goods['goods_id'], 'spec_key' => $return_goods['spec_key']])->find();
        if ($order_goods['is_send'] != 1) {
            $is_send = array(0 => '未发货', 1 => '已发货', 2 => '已换货', 3 => '已退货');
            $this->error("商品状态为: {$is_send[$order_goods['is_send']]} 状态不能退款操作");
        }
        /*
                $order = M('order')->where("order_id = {$return_goods['order_id']}")->find();


                // 计算退回积分公式
                //  退款商品占总商品价比例 =  (退款商品价 * 退款商品数量)  / 订单商品总价      // 这里是算出 退款的商品价格占总订单的商品价格的比例 是多少
                //  退款积分 = 退款比例  * 订单使用积分

                // 退款价格的比例
                $return_price_ratio = ($order_goods['member_goods_price'] * $order_goods['goods_num']) / $order['goods_price'];
                // 退还积分 = 退款价格的比例 *
                $return_integral = ceil($return_price_ratio * $order['integral']);

                 // 退还金额 = (订单商品总价 - 优惠券 - 优惠活动) * 退款价格的比例 - (退还积分 + 当前商品送出去的积分) / 积分换算比例
                 // 因为积分已经退过了, 所以退金额时应该把积分对应金额推掉 其次购买当前商品时送出的积分也要退回来,以免被刷积分情况

                $return_goods_price = ($order['goods_price'] - $order['coupon_price'] - $order['order_prom_amount']) * $return_price_ratio - ($return_integral + $order_goods['give_integral']) /  tpCache('shopping.point_rate');
                $return_goods_price = round($return_goods_price,2); // 保留两位小数
         */

        $refund = order_settlement($return_goods['order_id'], $order_goods['rec_id']);  // 查看退款金额
        //  print_r($refund);
        $return_goods_price = $refund['refund_settlement'] ? $refund['refund_settlement'] : 0; // 这个商品的退款金额
        //$refund_integral = $refund['refund_integral'] ? ($refund['refund_integral'] * -1) : 0; // 这个商品的退积分
        $refund_integral = $refund['refund_integral'] - $refund['give_integral'];


        if (IS_POST) {
            if (!$desc)
                $this->error("请填写操作说明");
            if (!$user_id > 0)
                $this->error("参数有误");

//            $pending_money = M('store')->where(" store_id = ".STORE_ID)->getField('pending_money'); // 商家在未结算资金 
//            if($pending_money < $return_goods_price)
//                $this->error("你的未结算资金不足 ￥{$return_goods_price}");

            //     M('store')->where(" store_id = ".STORE_ID)->setDec('pending_money',$user_money); // 从商家的 未结算自己里面扣除金额
            $result = storeAccountLog(STORE_ID, 0, $return_goods_price * -1, $desc, $return_goods['order_id'], $return_goods['order_sn']);
            if ($result) {
                accountLog($user_id, $return_goods_price, $refund_integral, '订单退货', 0, $return_goods['order_id'], $return_goods['order_sn']);
            } else {
                $this->error("操作失败");
            }
            M('order_goods')->where("rec_id", $order_goods['rec_id'])->save(array('is_send' => 3));//更改商品状态
            // 如果一笔订单中 有退货情况, 整个分销也取消                      
            M('rebate_log')->where("order_id", $return_goods['order_id'])->save(array('status' => 4, 'remark' => '订单有退货取消分成'));

            $this->success("操作成功", U("Order/return_list"));
            exit;
        }

        $this->assign('return_goods_price', $return_goods_price);
        $this->assign('return_integral', $refund_integral);
        $this->assign('order_goods', $order_goods);
        $this->assign('user_id', $user_id);
        return $this->fetch();
    }
    /**
     *
     * @time 2017/03/23
     * @author dyr
     * 商家发送站内信
     */
    public function sendMessage()
    {
        $user_id_array = I('get.user_id_array');
        $users = array();
        if (!empty($user_id_array)) {
            $users = M('users')->field('user_id,nickname')->where(array('user_id' => array('IN', $user_id_array)))->select();
        }
        $this->assign('users', $users);
        return $this->fetch();
    }
    /**
     * 商家发送活动消息
     */
    public function doSendMessage()
    {
        $call_back = I('call_back');//回调方法
        $type = I('post.type', 0);//个体or全体
        $seller_id = session('seller_id');
        $users = I('post.user/a');//个体id
        $category = I('post.category/d', 0); //0系统消息，1物流通知，2优惠促销，3商品提醒，4我的资产，5商城好店

        $raw_data = [
            'title'       => I('post.title', ''),
            'order_id'    => I('post.order_id', 0),
            'discription' => I('post.text', ''), //内容
            'goods_id'    => I('post.goods_id', 0),
            'change_type' => I('post.change_type/d', 0),
            'money'       => I('post.money/d', 0),
            'cover'       => I('post.cover', '')
        ];

        $msg_data = [
            'seller_id' => $seller_id,
            'category' => $category,
            'type' => $type
        ];

        $msglogic = new \app\common\logic\MessageLogic;
        $msglogic->sendMessage($msg_data, $raw_data, $users);
        
        exit("<script>parent.{$call_back}(1);</script>");
    }
}