<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 采有最新thinkphp5助手函数特性实现函数简写方式M D U等,也可db::name方式,可双向兼容
 * ============================================================================
 * Author: dyr
 * Date: 2017-12-04
 */

namespace app\common\logic;

use app\common\logic\team\Team;
use app\common\model\Order;
use app\common\model\PreSell;
use app\common\model\team\TeamActivity;
use app\common\model\Users;
use app\common\util\TpshopException;
use think\Cache;
use think\Hook;
use think\Model;
use think\Db;

/**
 * 提交下单类
 * Class CatsLogic
 * @package Home\Logic
 */
class PlaceOrder
{
    private $invoiceTitle;
    private $invoiceDesc;
    private $userNote;
    private $taxpayer;
    private $pay;
    private $order;
    private $userAddress;
    private $payPsw;
    private $promType;
    private $shipping_code;
    private $promId;
    private $mobile;

    private $orderList;
    private $masterOrderSn;//主订单号

    private $preSell;

    public function __construct(Pay $pay)
    {
        $this->pay = $pay;
        $this->order = new Order();
    }

    public function setMobile($mobile)
    {
        $payList = $this->pay->getPayList();
        if ($payList[0]['is_virtual']) {
            if ($mobile) {
                if (check_mobile($mobile)) {
                    $this->mobile = $mobile;
                } else {
                    throw new TpshopException("提交订单", 0, ['status' => -1, 'msg' => '手机号码格式错误', 'result' => ['']]);
                }
            } else {
                throw new TpshopException("提交订单", 0, ['status' => -1, 'msg' => '请填写手机号码', 'result' => ['']]);
            }
        }
    }

    /**
     * 支付密码
     * @param string $payPsw |密码为md5加密
     */
    public function setPayPsw($payPsw)
    {
        $this->payPsw = $payPsw;
    }

    public function setInvoiceTitle($invoiceTitle)
    {
        $this->invoiceTitle = $invoiceTitle;
    }

    public function setinvoiceDesc($invoiceDesc)
    {
        $this->invoiceDesc = $invoiceDesc;
    }

    public function setUserNote($userNote)
    {
        $this->userNote = $userNote;
    }

    public function setTaxpayer($taxpayer)
    {
        $this->taxpayer = $taxpayer;
    }

    public function setUserAddress($userAddress)
    {
        $this->userAddress = $userAddress;
    }

    public function setPromType($prom_type)
    {
        $this->promType = $prom_type;
    }
    public function setShippingCode($shipping_code)
    {
        $this->shipping_code = $shipping_code;
    }

    private function setPromId($prom_id)
    {
        $this->promId = $prom_id;
    }

    /**
     * 普通订单下单
     * @throws TpshopException
     */
    public function addNormalOrder()
    {
        $this->check();
        $this->queueInc();
        //$this->doOrderGoodsFlashSale();
        $this->addOrder();
        $this->addOrderGoods();
        //$reduce = tpCache('shopping.reduce');
        Hook::listen('user_add_order', $this->orderList);//下单行为
        /*if ($reduce == 1 || empty($reduce)) {
            minus_stock($orderVal);//下单减库存
        }*/
        // 如果应付金额为0  可能是余额支付 + 积分 + 优惠券 这里订单支付状态直接变成已支付
        if ($this->orderList['order_amount'] == 0) {
            update_pay_status($this->orderList['order_sn']);
        }
        $this->deductionCoupon();//扣除优惠券
        $this->changUserPointMoney();//扣除用户积分余额
        $this->queueDec();
    }

    /**
     * @param Team $team
     * 拼团订单下单
     * @throws TpshopException
     */
    public function addTeamOrder(Team $team)
    {
        $this->setPromType(6);
        $teamActivity = $team->getTeamActivity();
        $teamFoundId = $team->getFoundId();
        if ($teamFoundId) {
            $team_found_queue = Cache::get('team_found_queue');
            if ($team_found_queue[$teamFoundId] <= 0) {
                throw new TpshopException('提交订单', 0, ['status' => -1, 'msg' => '当前人数过多请耐心排队!', 'result' => '']);
            }
            $team_found_queue[$teamFoundId] = $team_found_queue[$teamFoundId] - 1;
            Cache::set('team_found_queue', $team_found_queue);
        }

        $this->setPromId($teamActivity['team_id']);
        $this->check();
        $this->queueInc();
        $this->addOrder();
        $this->addOrderGoods();
        $reduce = tpCache('shopping.reduce');
        foreach ($this->orderList as $orderKey => $orderVal) {
            $team->log($orderVal);
            Hook::listen('user_add_order', $orderVal);//下单行为
            if ($teamActivity['team_type'] != 2) {
                if ($reduce == 1 || empty($reduce)) {
                    minus_stock($orderVal);//下单减库存
                }
            }
            // 如果应付金额为0  可能是余额支付 + 积分 + 优惠券 这里订单支付状态直接变成已支付
            if ($orderVal['order_amount'] == 0) {
                update_pay_status($orderVal['order_sn']);
            }
        }
        $this->deductionCoupon();//扣除优惠券
        $this->changUserPointMoney();//扣除用户积分余额
        $this->queueDec();
    }

    /**
     * 预售订单下单
     * @param PreSell $preSell
     */
    public function addPreSellOrder(PreSell $preSell)
    {
        $this->preSell = $preSell;
        $this->setPromType(4);
        $this->setPromId($preSell['pre_sell_id']);
        $this->check();
        $this->queueInc();
        $this->addOrder();
        $this->addOrderGoods();
        $reduce = tpCache('shopping.reduce');
        foreach ($this->orderList as $orderKey => $orderVal) {
            Hook::listen('user_add_order', $orderVal);//下单行为
            if ($reduce == 1 || empty($reduce)) {
                minus_stock($orderVal);//下单减库存
            }
            //预售暂不至此积分余额优惠券支付
            // 如果应付金额为0  可能是余额支付 + 积分 + 优惠券 这里订单支付状态直接变成已支付
//            if ($orderVal['order_amount'] == 0) {
//                update_pay_status($orderVal['order_sn']);
//            }
        }
//        $this->changUserPointMoney();//扣除用户积分余额
        $this->queueDec();
    }


    /**
     * 获取订单表数据
     * @return Order
     */
    public function getOrderList()
    {
        return $this->orderList;
    }

    /**
     * 提交订单前检查
     * @throws TpshopException
     */
    public function check()
    {
        $pay_points = $this->pay->getPayPoints();
        $user_money = $this->pay->getUserMoney();
        if ($pay_points || $user_money) {
            $user = $this->pay->getUser();
            if ($user['is_lock'] == 1) {
                throw new TpshopException('提交订单', 0, ['status' => -5, 'msg' => "账号异常已被锁定，不能使用余额支付！", 'result' => '']);
            }
            if (empty($user['paypwd'])) {
                throw new TpshopException('提交订单', 0, ['status' => -6, 'msg' => "请先设置支付密码", 'result' => '']);
            }
            if (empty($this->payPsw)) {
                throw new TpshopException('提交订单', 0, ['status' => -7, 'msg' => "请输入支付密码", 'result' => '']);
            }
            if ($this->payPsw !== $user['paypwd'] && encrypt($this->payPsw) !== $user['paypwd']) {
                throw new TpshopException('提交订单', 0, ['status' => -8, 'msg' => '支付密码错误', 'result' => '']);
            }
        }

    }

    private function queueInc()
    {
        $queue = Cache::get('queue');
        if ($queue >= 100) {
            throw new TpshopException('提交订单', 0, ['status' => -99, 'msg' => "当前人数过多请耐心排队!" . $queue, 'result' => '']);
        }
        Cache::inc('queue');
    }

    /**
     * 订单提交结束
     */
    private function queueDec()
    {
        Cache::dec('queue');
    }

    /**
     * 插入订单表
     * @throws TpshopException
     */
    private function addOrder()
    {
        $OrderLogic = new OrderLogic();
        $user = $this->pay->getUser();
        //$store_list_pay_info = $this->pay->getStoreListPayInfo();
        $this->masterOrderSn = $OrderLogic->get_order_sn();//先生成订单号
        $orderAllData = [];
        //$payList = $this->pay->getPayList();
        //foreach ($store_list_pay_info as $payInfoKey => $payInfoVal) {
            $orderData = [
                'order_sn' => $this->masterOrderSn, // 订单编号
                //'master_order_sn' => $this->masterOrderSn, // 主订单编号
                'user_id' => $user['user_id'], // 用户id
                'email' => $user['email'], // 用户id
                'goods_price' => $this->pay->getGoodsPrice(),//'商品价格',
                'total_amount' => $this->pay->getTotalAmount(),// 订单总额
                'order_amount' => $this->pay->getOrderAmount(),//'应付款金额',
                'add_time' => time(), // 下单时间
                'store_id' => STORE_ID,
            ];
            //运费
            if ($this->pay->getShippingPrice() > 0) {
                $orderData['shipping_price'] = $this->pay->getShippingPrice();
            } else {
                $orderData['shipping_price'] = 0;
            }
            //使用余额
            if ($this->pay->getUserMoney() > 0) {
                $orderData['user_money'] = $this->pay->getUserMoney();
            } else {
                $orderData['user_money'] = 0;
            }
            //使用积分
            /*if ($this->pay->getPayPoints() > 0) {
                $orderData['integral'] = $this->pay->getPayPoints();
                $orderData['integral_money'] = $payInfoVal['integral_money'];
            } else {
                $orderData['integral'] = 0;
                $orderData['integral_money'] = 0;
            }*/
            //用户备注
            if (!empty($this->userNote)) {
                $orderData['user_note'] = $this->userNote;
            }
            //用户地址
            if (!empty($this->userAddress)) {
                $orderData['consignee'] = $this->userAddress['consignee'];// 收货人
                $orderData['user_shop_name'] = $this->userAddress['shop_name'];// 收货店铺名称
                $orderData['province'] = $this->userAddress['province'];//'省份id',
                $orderData['city'] = $this->userAddress['city'];//'城市id',
                $orderData['district'] = $this->userAddress['district'];//'县',
                $orderData['twon'] = $this->userAddress['twon'];// '街道',
                $orderData['address'] = $this->userAddress['address'];//'详细地址'
                $orderData['mobile'] = $this->userAddress['mobile'];//'手机',
                $orderData['zipcode'] = $this->userAddress['zipcode'];//'邮编',
//                $orderData['email'] = $this->userAddress['email'];//'邮箱'
            } else {
                $orderData['consignee'] = $user['nickname'];// 收货人
                if ($this->mobile) {
                    $orderData['mobile'] = $this->mobile;//'手机',
                } else {
                    $orderData['mobile'] = $user['mobile'];//'手机',
                }
            }
            //发票
            if ($this->invoiceDesc == '明细' && !empty($this->invoiceTitle) && !empty($this->taxpayer)) {
                $orderData['invoice_desc'] = $invoice_data['invoice_desc'] = $this->invoiceDesc;
                $orderData['invoice_title'] = $invoice_data['invoice_title'] = $this->invoiceTitle;
                $orderData['taxpayer'] = $invoice_data['taxpayer'] = $this->taxpayer;
                $user_extend = M('user_extend')->where(['user_id' => $user['user_id']])->find();
                if ($user_extend)
                    M('user_extend')->where(['user_id' => $user['user_id']])->update($invoice_data);//更新发票信息
                else {
                    $invoice_data['user_id'] = $user['user_id'];
                    M('user_extend')->add($invoice_data);
                }
            }
            //支付方式，可能是余额支付或积分兑换，后面其他支付方式会替换
            if ($orderData['integral'] > 0 || $orderData['user_money'] > 0) {
                $orderData['pay_name'] = $orderData['user_money'] ? '余额支付' : '积分兑换';
            }
            if ($this->promType) {
                $orderData['prom_type'] = $this->promType;//订单类型
            }
            if ($this->shipping_code) {
                $orderData['shipping_code'] = $this->shipping_code;//配送方式
            }
        //}
        $order_id = $this->order->insertGetId($orderData);
        if ($order_id === false) {
            throw new TpshopException("订单入库", 0, ['status' => -8, 'msg' => '添加订单失败', 'result' => '']);
        }
        $this->orderList = $this->order->where(['order_id'=>$order_id])->find();
    }

    /**
     * 插入订单商品表
     */
    private function addOrderGoods()
    {
        $payList = $this->pay->getPayList();
        $goods_ids = get_arr_column($payList, 'goods_id');
        $goodsArr = Db::name('goods')->where('goods_id', 'IN', $goods_ids)->getField('goods_id,cost_price,give_integral,distribut,cat_id3');
        //$cat_id_arr = get_arr_column($goodsArr, 'cat_id3');
        //$cat_ids = implode($cat_id_arr, ',');
        //$commission = Db::name('goods_category')->where('id', 'IN', $cat_ids)->getField('id,commission');  //分类对应的商家抽成比例
        $orderGoodsAllData = [];
        foreach ($payList as $payKey => $payItem) {
            $order = $this->getOrderList();//找到订单
            /*$totalPriceToRatio = $payItem['member_goods_price'] / $order['goods_price'];  //商品价格占总价的比例
            $orderDiscounts = $order['order_prom_amount'] + $order['coupon_price']; //订单优惠价钱
            $finalPrice = round($payItem['final_price'] - ($totalPriceToRatio * $orderDiscounts), 3);// 每件商品实际支付价格*/
            $orderGoodsData['order_id'] = $order['order_id']; // 订单id
            $orderGoodsData['goods_id'] = $payItem['goods_id']; // 商品id
            $orderGoodsData['goods_name'] = $payItem['goods_name']; // 商品名称
            $orderGoodsData['goods_sn'] = $payItem['goods_sn']; // 商品货号
            $orderGoodsData['goods_num'] = $payItem['goods_num']; // 购买数量
            $orderGoodsData['final_price'] = $payItem['final_price']; // 每件商品实际支付价格
            $orderGoodsData['goods_price'] = $payItem['goods_price']; // 商品价               为照顾新手开发者们能看懂代码，此处每个字段加于详细注释
            if (!empty($payItem['spec_key'])) {
                $orderGoodsData['spec_key'] = $payItem['spec_key']; // 商品规格
                $orderGoodsData['spec_key_name'] = $payItem['spec_key_name']; // 商品规格名称
            } else {
                $orderGoodsData['spec_key'] = ''; // 商品规格
                $orderGoodsData['spec_key_name'] = ''; // 商品规格名称
            }
            $orderGoodsData['sku'] = $payItem['sku']; // sku
            $orderGoodsData['member_goods_price'] = $payItem['member_goods_price']; // 会员折扣价
            $orderGoodsData['order_goods_img'] = $payItem['spec_img'] ? $payItem['spec_img'] : $payItem['original_img']; // 商品图片
            $orderGoodsData['cost_price'] = $goodsArr[$payItem['goods_id']]['cost_price']; // 成本价
            $orderGoodsData['store_id'] = $payItem['store_id']; // 店铺id
            /*$orderGoodsData['give_integral'] = $goodsArr[$payItem['goods_id']]['give_integral']; // 购买商品赠送积分
            $orderGoodsData['prom_type'] = $payItem['prom_type']; // 0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠
            $orderGoodsData['prom_id'] = $payItem['prom_id']; // 活动id
            $orderGoodsData['distribut'] = $goodsArr[$payItem['goods_id']]['distribut']; // 三级分销金额
            $orderGoodsData['commission'] = $commission[$payItem['cat_id3']]; // 商家抽成比例*/
            array_push($orderGoodsAllData, $orderGoodsData);
        }
        Db::name('order_goods')->insertAll($orderGoodsAllData);
    }

    /**
     * 扣除优惠券
     */
    public function deductionCoupon()
    {
        $userCoupons = $this->pay->getUserCoupon();
        if ($userCoupons) {
            $user = $this->pay->getUser();
            $couponListData['uid'] = $user['user_id'];
            $couponListData['use_time'] = time();
            $couponListData['status'] = 1;
            foreach ($userCoupons as $couponItemKey => $couponItemVal) {
                $order = $this->findStoreOrder($couponItemVal['store_id']);
                $couponListData['order_id'] = $order['order_id'];
                Db::name('coupon_list')->where('id', $couponItemVal['id'])->update($couponListData);
                Db::name('coupon')->where('id', $couponItemVal['cid'])->setInc('use_num');// 优惠券的使用数量加一
            }
        }
    }

    /**
     * 扣除用户积分余额
     */
    public function changUserPointMoney()
    {
        if ($this->pay->getPayPoints() > 0 || $this->pay->getUserMoney() > 0) {
            $user = $this->pay->getUser();
            /*$user = Users::get($user['user_id']);
            if ($this->pay->getPayPoints() > 0) {
                $user->pay_points = $user->pay_points - $this->pay->getPayPoints();// 消费积分
            }
            $user->save();*/
            if ($this->pay->getUserMoney() > 0) {
                userStoreAccountLog($user['re_id'],$user['store_id'],-$this->pay->getUserMoney(),'支付订单：'.$this->getOrderList()['order_sn']);
            }
            /*$storeListPayInfo = $this->pay->getStoreListPayInfo();
            $accountLogAllData = [];
            foreach ($storeListPayInfo as $payInfoKey => $payInfoVal) {
                $order = $this->findStoreOrder($payInfoKey);
                $accountLogData = [
                    'user_id' => $order['user_id'],
                    'user_money' => -$payInfoVal['user_money'],
                    'pay_points' => -$payInfoVal['integral'],
                    'change_time' => time(),
                    'desc' => '下单消费',
                    'order_sn' => $order['order_sn'],
                    'order_id' => $order['order_id'],
                ];
                array_push($accountLogAllData, $accountLogData);
            }
            Db::name('account_log')->insertAll($accountLogAllData);*/
        }
    }

    /**
     * 这方法特殊，只限拼团使用。
     * @param $order_list
     */
    public function setOrderList($order_list)
    {
        $this->orderList = $order_list;
    }

    /**
     * 获取主订单号ID
     */
    public function getMasterOrderSn()
    {
        return $this->masterOrderSn;
    }

    /**
     * 获取单个店铺订单
     * @param $store_id
     * @return null
     */
    private function findStoreOrder($store_id)
    {
        foreach ($this->orderList as $orderKey => $orderVal) {
            if ($orderVal['store_id'] == $store_id) {
                return $orderVal;
            }
        }
        return null;
    }

    /**
     * 检查订单商品是否有秒杀商品
     */
    private function doOrderGoodsFlashSale()
    {
        $payList = $this->pay->getPayList();
        foreach ($payList as $goodsKey => $goodsVal) {
            if ($goodsVal['prom_type'] == 1) {
                $flash_sale_queue = Cache::get('flash_sale_queue');
                if (array_key_exists($goodsVal['prom_id'], $flash_sale_queue)) {
                    if ($flash_sale_queue[$goodsVal['prom_id']] <= 0) {
                        throw new TpshopException('提交订单', 0, ['status' => 0, 'msg' => $goodsVal['goods_name'] . '--' . $goodsVal['spec_key_name'] . '当前抢购人数过多请耐心排队!', 'result' => '']);
                    }
                    $flash_sale_queue[$goodsVal['prom_id']] = $flash_sale_queue[$goodsVal['prom_id']] - 1;
                    Cache::set('flash_sale_queue', $flash_sale_queue);
                }
            }
        }
    }
}