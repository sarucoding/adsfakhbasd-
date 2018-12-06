<?php
namespace app\mobile\controller;

use think\Db;
use app\mobile\logic\OrderLogic;
use app\common\logic\OrderLogic as ComOrderLogic;
class Order extends Base
{

    function _initialize()
    {
        parent::_initialize();

    }


    /**
     * 订单列表
     * @return mixed
     */
    public function index()
    {
        $order_type = I('order_type', '0', 'intval');
        $navIndex = I('navIndex', '0', 'intval');
        $order_logic = new OrderLogic();
        $has_contract = $order_logic->has_contract();//看是否需要显示账期订单选项
        if (!$has_contract) {
            $order_type = 0;
        }
        $orderNavArr = config('orderNavArr')[$order_type];//获取订单列表页导航
        $this->assign('order_type', $order_type);
        $this->assign('has_contract', $has_contract);
        $this->assign('navIndex', $navIndex);
        $this->assign('orderNavArr', $orderNavArr);
        return $this->fetch();
    }

    /**
     * ajax获取订单列表
     */
    function ajax_get_order()
    {
        $order_type = I('order_type', '0', 'intval');
        $navIndex = I('navIndex', '0', 'intval');
        $page = I('page/d', '1');
        $limit = I('limit/d', '10');
        $order_logic = new OrderLogic();
        $has_contract = $order_logic->has_contract();//看是否需要显示账期订单选项
        if (!$has_contract) {
            $order_type = 0;
        }
        $where = ['user_id'=>USER_ID,'store_id'=>STORE_ID,'prom_type'=>$order_type];
        if ($order_type) {
            switch($navIndex){
                case 1://待发货
                    $where['order_status'] = ['in','0,1'];
                    $where['shipping_status'] = 0;
                    break;
                case 2://待确认实发数
                    $where['order_status'] = 1;
                    $where['shipping_status'] = 1;
                    break;
                case 3://待收货
                    $where['order_status'] = 2;
                    $where['shipping_status'] = 1;
                    $where['is_ligation'] = 0;
                    break;
                case 4://待扎账
                    $where['order_status'] = 3;
                    $where['shipping_status'] = 1;
                    $where['is_ligation'] = 0;
                    break;
                case 5://待付款
                    $where['order_status'] = ['in','2,3'];
                    $where['shipping_status'] = 1;
                    $where['is_ligation'] = ['gt',0];
                    break;
                case 6://已完成
                    $where['order_status'] = ['in','2,3'];
                    $where['pay_status'] = 2;
                    $where['is_ligation'] = ['gt',0];
                    break;
            }
        } else {
            switch ($navIndex) {
                case 1://待付款
                    $where['order_status'] = 0;
                    $where['pay_status'] = 0;
                    break;
                case 2://待发货
                    $where['order_status'] = ['in','0,1'];
                    $where['shipping_status'] = 0;
                    $where['pay_status'] = 1;
                    break;
                case 3://待确认实发数
                    $where['order_status'] = 1;
                    $where['shipping_status'] = 1;
                    $where['pay_status'] = 1;
                    break;
                case 4://待付尾款
                    $where['order_status'] = 2;
                    $where['shipping_status'] = 1;
                    $where['pay_status'] = 1;
                    break;
                case 5://待收货
                    $where['order_status'] = 2;
                    $where['shipping_status'] = 1;
                    $where['pay_status'] = 2;
                    break;
                case 6://已完成
                    $where['order_status'] = 3;
                    $where['shipping_status'] = 1;
                    $where['pay_status'] = 2;
                    break;
            }
        }
        $start = ($page - 1) * $limit;
        $limit = $start . ',' . $limit;
        $data = $order_logic->get_order_lists($where,$limit);
        $this->ajaxReturn($data);
    }
    /**
     * 订单操作
     */
    public function order_handle(){
        $order_id = I('order_id/d');
        $act = I('act');
        $logic = new ComOrderLogic();
        $logic->setUserId(USER_ID);
        $logic->setStoreId(STORE_ID);
        if($act == 'cancel'){
            $data = $logic->cancel_order($order_id);
        }elseif($act == 'receive'){
            $data = $logic->receive_order($order_id);
        }
        $this->ajaxReturn($data);
    }
    /**
     * 确认收货成功
     */
    public function receive_success(){
        return $this->fetch();
    }
    /**
     * 订单详情
     */
    public function detail(){
        $order_id = I('order_id/d');
        $where = ['order_id'=>$order_id,'user_id'=>USER_ID,'store_id'=>STORE_ID];
        $logic = new OrderLogic();
        $order = $logic->get_order_detail($where);
        if(!$order){
            $this->error('订单不存在');
        }
        $area_id[] = $order['province'];
        $area_id[] = $order['city'];
        $area_id[] = $order['district'];
        $regionList = Db::name('region')->where("id", "in", $area_id)->getField('id,name');
        $this->assign('regionList', $regionList);
        $this->assign('order',$order);
        return $this->fetch();
    }
    /**
     * 订单追踪
     */
    public function order_follow(){
        $order_id = I('order_id/d');
        $where = ['order_id'=>$order_id,'user_id'=>USER_ID,'store_id'=>STORE_ID];
        $logic = new OrderLogic();
        $order = $logic->get_order_detail($where);
        $lists = M('order_action')->where(['order_id'=>$order_id])->order('log_time desc')->select();
        $store_logo = M('store')->where(['store_id' => STORE_ID])->getField('store_logo');
        $this->assign('order',$order);
        $this->assign('store_logo',$store_logo);
        $this->assign('lists',$lists);
        return $this->fetch();
    }
    /**
     * 支付尾款
     */
    function pay_tail()
    {
        $order_id = I('order_id/d');
        $order = M('order')->where(['order_id' => $order_id,'prom_type'=>0,'pay_status'=>1,'order_status'=>2,'user_id'=>USER_ID,'store_id'=>STORE_ID])->find();
        if (!$order) {
            $this->error('订单不存在或已支付');
        }
        $this->assign('order', $order);
        return $this->fetch();

    }
    /**
     * 支付尾款成功
     */
    function pay_tail_success()
    {
        $order_id = I('order_id');
        $order = M('order')->where(['order_id' => $order_id,'prom_type'=>0,'pay_status'=>2,'order_status'=>2,'user_id'=>USER_ID,'store_id'=>STORE_ID])->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        $this->assign('order', $order);
        return $this->fetch();
    }
}