<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * 采用最新Thinkphp5助手函数特性实现单字母函数M D U等简写方式
 * ============================================================================
 * Author: IT宇宙人
 * Date: 2015-09-09
 */

namespace app\mobile\logic;

use app\common\model\Cart;
use app\common\model\Goods;
use app\common\util\TpshopException;
use app\common\model\SpecGoodsPrice;
use app\common\logic\PriceLogic;
use think\Model;
use think\Db;

/**
 * 购物车 逻辑定义
 * Class CatsLogic
 * @package common\Logic
 */
class CartLogic extends Model
{
    protected $goods;//商品模型
    protected $specGoodsPrice;//商品规格模型
    protected $goodsBuyNum;//购买的商品数量
    protected $session_id;//session_id
    protected $user_id = 0;//user_id
    protected $store_id = 0;//user_id
    protected $shop_id = 0;//shop_id 门店ID
    protected $sgs_id = 0;//sgs_id 门店商品表ID
    protected $userGoodsTypeCount = 0;//用户购物车的全部商品种类
    protected $userStoreCouponNumArr; //用户符合购物车店铺可用优惠券数量
    protected $promType; //立即购买才会用到。
    protected $form; //标识商品详情加入购物车。

    public function __construct()
    {
        parent::__construct();
        $this->session_id = session_id();
    }

    public function setFrom($form)
    {
        $this->form = $form;
    }

    public function setPromType($promType)
    {
        $this->promType = $promType;
    }

    /**
     * 将session_id改成unique_id
     * @param $uniqueId |api唯一id 类似于 pc端的session id
     */
    public function setUniqueId($uniqueId)
    {
        $this->session_id = $uniqueId;
    }

    /**
     * 包含一个商品模型
     * @param $goods_id
     */
    public function setGoodsModel($goods_id)
    {
        if ($goods_id) {
            $goodsModel = new Goods();
            $this->goods = $goodsModel::get($goods_id, '', 10);
        }
    }

    /**
     * 包含一个商品规格模型
     * @param $item_id
     */
    public function setSpecGoodsPriceModel($item_id)
    {
        if ($item_id) {
            $specGoodsPriceModel = new SpecGoodsPrice();
            $this->specGoodsPrice = $specGoodsPriceModel::get($item_id, '', 10);
        }
    }

    /**
     * 设置用户ID
     * @param $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * 设置店铺ID
     * @param $user_id
     */
    public function setStoreId($store_id)
    {
        $this->store_id = $store_id;
    }

    /**
     * 门店ID
     * @param $shop_id
     */
    public function setShopId($shop_id)
    {
        $this->shop_id = $shop_id;
    }

    /**
     * 门店商品表ID
     * @param $sgs_id
     */
    public function setSgsId($sgs_id)
    {
        $this->sgs_id = $sgs_id;
    }

    /**
     * 设置购买的商品数量
     * @param $goodsBuyNum
     */
    public function setGoodsBuyNum($goodsBuyNum)
    {
        $this->goodsBuyNum = $goodsBuyNum;
    }

    /**
     * 加入购物车
     * @throws TpshopException
     */
    public function addGoodsToCart()
    {
        if (empty($this->goods)) {
            throw new TpshopException("加入购物车", 0, ['status' => 0, 'msg' => '购买商品不存在', 'result' => '']);
        }
        if ($this->goods['exchange_integral'] > 0) {
            throw new TpshopException("加入购物车", 0, ['status' => 0, 'msg' => '积分商品跳转', 'result' => ['url' => U('Goods/goodsInfo', ['id' => $this->goods['goods_id'], 'item_id' => $this->specGoodsPrice['item_id']], '')]]);
        }
        $userCartCount = Db::name('cart')->where(['user_id' => $this->user_id, 'session_id' => $this->session_id])->count();//获取用户购物车的商品有多少种
        if ($userCartCount >= 20) {
            throw new TpshopException("加入购物车", 0, ['status' => 0, 'msg' => '购物车最多只能放20种商品', 'result' => '']);
        }
        $specGoodsPriceCount = Db::name('SpecGoodsPrice')->where("goods_id", $this->goods['goods_id'])->count('item_id');
        if (empty($this->specGoodsPrice) && !empty($specGoodsPriceCount)) {
            throw new TpshopException("加入购物车", 0, ['status' => 0, 'msg' => '必须传递商品规格', 'result' => ['url' => U('Goods/goodsInfo', ['id' => $this->goods['goods_id']], '')]]);
        }
        $this->addNormalCart();
    }

    /**
     * 购物车添加普通商品
     * @throws TpshopException
     */
    private function addNormalCart()
    {
        if (empty($this->specGoodsPrice)) {
            $price = $this->goods['shop_price'];
            $store_count = $this->goods['store_count'];
        } else {
            //如果有规格价格，就使用规格价格，否则使用本店价。
            $price = $this->specGoodsPrice['price'];
            $store_count = $this->specGoodsPrice['store_count'];
        }

        // 查询购物车是否已经存在这商品
        if (!$this->user_id) {
            $userCartGoods = Cart::get(['user_id' => $this->user_id, 'session_id' => $this->session_id, 'goods_id' => $this->goods['goods_id'], 'spec_key' => ($this->specGoodsPrice['key'] ?: '')]);
        } else {
            $userCartGoods = Cart::get(['user_id' => $this->user_id, 'goods_id' => $this->goods['goods_id'], 'spec_key' => ($this->specGoodsPrice['key'] ?: '')]);
        }
        // 如果该商品已经存在购物车
        if ($userCartGoods) {
            $userCartGoodsNum = empty($userCartGoods['goods_num']) ? 0 : $userCartGoods['goods_num'];
            $userWantGoodsNum = $this->goodsBuyNum + $userCartGoods['goods_num'];//本次要购买的数量加上购物车的本身存在的数量
            if ($userWantGoodsNum > 200) {
                $userWantGoodsNum = 200;
            }
            if ($userWantGoodsNum > $store_count) {
                throw new TpshopException("加入购物车", 0, ['status' => 0, 'msg' => '商品库存不足，剩余' . $store_count . ',当前购物车已有' . $userCartGoodsNum . '件', 'result' => '']);
            }
            $cartResult = $userCartGoods->save(['goods_num' => $userWantGoodsNum, 'goods_price' => $price, 'member_goods_price' => $price]);
        } else {
            //如果该商品没有存在购物车
            if ($this->goodsBuyNum > $store_count) {
                throw new TpshopException("加入购物车", 0, ['status' => -4, 'msg' => '商品库存不足，剩余' . $this->goods['store_count'], 'result' => '']);
            }
            $cartAddData = array(
                'user_id' => $this->user_id,   // 用户id
                'session_id' => $this->session_id,   // sessionid
                'goods_id' => $this->goods['goods_id'],   // 商品id
                'goods_sn' => $this->goods['goods_sn'],   // 商品货号
                'goods_name' => $this->goods['goods_name'],   // 商品名称
                'market_price' => $this->goods['market_price'],   // 市场价
                'goods_price' => $price,  // 原价
                'member_goods_price' => $price,  // 会员折扣价 默认为 购买价
                'goods_num' => $this->goodsBuyNum, // 购买数量
                'add_time' => time(), // 加入购物车时间
                'prom_type' => 0,   // 0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠
                'prom_id' => 0,   // 活动id
                'store_id' => $this->goods['store_id'],   // 店铺id
                'shop_id' => $this->shop_id,   // 门店id
                'sgs_id' => $this->sgs_id,
            );
            if ($this->specGoodsPrice) {
                $cartAddData['item_id'] = $this->specGoodsPrice['item_id'];
                $cartAddData['spec_key'] = $this->specGoodsPrice['key'];
                $cartAddData['spec_key_name'] = $this->specGoodsPrice['key_name']; // 规格 key_name
                $cartAddData['sku'] = $this->specGoodsPrice['sku']; //商品条形码
            }
            $cartResult = Db::name('Cart')->insertGetId($cartAddData);
        }
        if ($cartResult === false) {
            throw new TpshopException("加入购物车", 0, ['status' => 0, 'msg' => '加入购物车失败', 'result' => '']);
        }
    }

    /**
     * 获取用户购物车商品总数
     * @return float|int
     */
    public function getUserCartGoodsNum()
    {
        $goods_num = Db::name('cart')->where(['user_id' => $this->user_id])->sum('goods_num');
        $goods_num = empty($goods_num) ? 0 : $goods_num;
        setcookie('cn', $goods_num, null, '/');
        return $goods_num;
    }

    /**
     * 获取用户购物车商品种类数
     * @return float|int
     */
    public function getUserCartGoodsTypeNum()
    {
        $goods_num = Db::name('cart')->where(['user_id' => $this->user_id])->count();
        return empty($goods_num) ? 0 : $goods_num;
    }

    /**
     * @param int $selected |是否被用户勾选中的 0 为全部 1为选中  一般没有查询不选中的商品情况
     * 获取用户的购物车列表
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getCartList($selected = 0)
    {
        $cart = new Cart();
        $cartWhere['a.user_id'] = $this->user_id;
        $cartWhere['a.store_id'] = $this->store_id;
        if ($selected != 0) {
            $cartWhere['selected'] = 1;
        }
        $cartList = $cart->alias('a')->join([
            ['__GOODS__ b', 'a.goods_id = b.goods_id'],
            ['__SPEC_GOODS_PRICE__ c', 'a.goods_id = c.goods_id and a.spec_key = c.key', 'LEFT']
        ])
            ->where($cartWhere)
            ->field('a.*,b.*,c.key,c.spec_img')
            ->select();  // 获取购物车商品
        //$cartList = $cart->with('goods')->where($cartWhere)->select();  // 获取购物车商品
        $cartCheckAfterList = $this->checkCartList($cartList);
        $price_logic = new PriceLogic();
        $price_logic->setUser(USER_ID, STORE_ID);
        foreach ($cartCheckAfterList as $k => $v) {
            try{
                $result = $price_logic->getGoodsPrice($v['goods_id'], $v['spec_key']);
            } catch (TpshopException $t){
                //获取到购物车内异常商品，直接删除购物车
                M('cart')->where(['id'=>$v['id']])->delete();
                unset($cartCheckAfterList[$k]);
                continue;
            }
            if(!$result['result']){
                throw new TpshopException("价格异常", 0, ['status' => 0, 'msg' => '商品价格异常', 'result' => '']);
            }
            $cartCheckAfterList[$k]['offer_price'] = $result['result'];
        }
//      $cartCheckAfterList = $cartList;
        $cartGoodsTotalNum = array_sum(array_map(function ($val) {
            return $val['goods_num'];
        }, $cartCheckAfterList));//购物车购买的商品总数
        setcookie('cn', $cartGoodsTotalNum, null, '/');
        return $cartCheckAfterList;
    }

    /**
     * 过滤掉无效的购物车商品
     * @param $cartList
     */
    public function checkCartList($cartList)
    {
        foreach ($cartList as $cartKey => $cart) {
            //商品不存在或者已经下架或则商品数量为零的
            if ($cart['is_on_sale'] != 1 || $cart['goods_num'] == 0) {
                $cart->delete();
                unset($cartList[$cartKey]);
                continue;
            }
            if($cart['spec_key'] && !$cart['key']){//过滤掉购物车无效商品
                M('cart')->where('id',$cart['id'])->delete();
                continue;
            }
        }
        return $cartList;
    }

    /**
     *  modify ：cart_count
     *  获取用户购物车欲购买的商品有多少种
     * @return int|string
     */
    public function getUserCartOrderCount()
    {
        $count = Db::name('Cart')->where(['user_id' => $this->user_id, 'selected' => 1])->count();
        return $count;
    }

    /**
     * 更改购物车的商品数量
     * @param $cart_id |购物车id
     * @param $goods_num |商品数量
     * @return array
     */
    public function changeNum($cart_id, $goods_num)
    {
        $Cart = new Cart();
        $cart = $Cart::get($cart_id);

        if ($goods_num > $cart->limit_num) {
            return ['status' => 0, 'msg' => '商品数量不能大于' . $cart->limit_num, 'result' => ['limit_num' => $cart->limit_num]];
        }
        if ($goods_num > 200) {
            $goods_num = 200;
        }
        $cart->goods_num = $goods_num;
        $cart->save();
        return ['status' => 1, 'msg' => '修改商品数量成功', 'result' => ''];
    }

    /**
     * 删除购物车商品
     * @param array $cart_ids
     * @return int
     * @throws \think\Exception
     */
    public function delete($cart_ids = array())
    {
        if ($this->user_id) {
            $cartWhere['user_id'] = $this->user_id;
        } else {
            $cartWhere['session_id'] = $this->session_id;
            $user['user_id'] = 0;
        }
        $delete = Db::name('cart')->where($cartWhere)->where('id', 'IN', $cart_ids)->delete();
        return $delete;
    }

    /**
     *  更新购物车，并返回计算结果
     * @param array $cart
     * @return array
     */
    public function AsyncUpdateCart($cart = [])
    {
        $cartSelectedId = $cartNoSelectedId = [];
        if (empty($cart)) {
            return ['status' => 0, 'msg' => '购物车没商品', 'result' => compact('total_fee', 'goods_fee', 'goods_num', 'storeCartList')];
        }
        foreach ($cart as $key => $val) {
            if ($cart[$key]['selected'] == 1) {
                $cartSelectedId[] = $cart[$key]['id'];
            } else {
                $cartNoSelectedId[] = $cart[$key]['id'];
            }
        }
        $Cart = new Cart();
        $cartWhere['user_id'] = $this->user_id;
        $cartWhere['store_id'] = $this->store_id;
        if (!empty($cartNoSelectedId)) {
            $Cart->where('id', 'IN', $cartNoSelectedId)->where($cartWhere)->update(['selected' => 0]);
        }
        if (empty($cartSelectedId)) {
            $cartPriceInfo = $this->getCartPriceInfo();
            return ['status' => 1, 'msg' => '购物车没选中商品', 'result' => $cartPriceInfo];
        }
        $price_logic = new PriceLogic();
        $price_logic->setUser($this->user_id, STORE_ID);
        $cartList = $Cart->where('id', 'IN', $cartSelectedId)->where($cartWhere)->select();
        $is_update = 0;
        foreach ($cartList as $cartKey => $cartVal) {
            if ($cartList[$cartKey]['selected'] == 0 && $is_update == 0) {
                $Cart->where('id', 'IN', $cartSelectedId)->where($cartWhere)->update(['selected' => 1]);
                $is_update = 1;
            }
            $result = $price_logic->getGoodsPrice($cartVal['goods_id'], $cartVal['spec_key']);
            $cartList[$cartKey]['member_goods_price'] = $result['result'];
        }
        if ($cartList) {
            $cartList = collection($cartList)->append(['total_fee', 'goods_fee'])->toArray();
            $cartPriceInfo = $this->getCartPriceInfo($cartList);
            return ['status' => 1, 'msg' => '计算成功', 'result' => $cartPriceInfo];
        } else {
            $cartPriceInfo = $this->getCartPriceInfo();
            return ['status' => 1, 'msg' => '购物车没选中商品', 'result' => $cartPriceInfo];
        }
    }


    /**
     * 获取购物车的价格详情
     * @param $cartList |购物车列表
     * @return array
     */
    public function getCartPriceInfo($cartList = null)
    {
        $total_fee = $goods_fee = $goods_num = 0;//初始化数据。商品总额/节约金额/商品总共数量
        if ($cartList) {
            foreach ($cartList as $cartKey => $cartItem) {
                $total_fee += $cartItem['goods_fee'];
                $goods_num += $cartItem['goods_num'];
            }
        }
        return ['total_fee' => $total_fee, 'goods_num' => $goods_num];
    }


    /**
     * @param $invoice_title
     * @param $taxpayer
     * @param $invoice_desc
     * @return array
     */
    public function save_invoice($invoice_title, $taxpayer, $invoice_desc)
    {
        if (empty($invoice_title)) return $result = ['status' => -1, 'msg' => '请填写发票抬头', 'result' => ''];
        if (empty($invoice_desc)) return $result = ['status' => -1, 'msg' => '请填写发票内容', 'result' => ''];
        //B.1校验用户是否有历史发票记录
        $map['user_id'] = $this->user_id;
        $info = M('user_extend')->where($map)->find();
        //B.2发票信息
        $data['invoice_title'] = $invoice_title;
        $data['taxpayer'] = $taxpayer;
        $data['invoice_desc'] = $invoice_desc;
        //B.3发票抬头
        if ($invoice_title == "个人") {
            $data['invoice_title'] = "个人";
            $data['taxpayer'] = "";
        } else {
            (empty($invoice_title) || empty($taxpayer)) && $result = ['status' => -2, 'msg' => '发票信息请填完整', 'result' => ''];
        }
        //是否存贮过发票信息
        if (empty($info)) {
            $data['user_id'] = $this->user_id;
            (M('user_extend')->add($data)) ? $status = 1 : $status = -1;
        } else {
            (M('user_extend')->where($map)->save($data)) ? $status = 1 : $status = -1;
        }
        $result = ['status' => $status, 'msg' => '保存成功', 'result' => ''];
        return $result;
    }

    /**
     * 清除用户购物车选中
     * @throws \think\Exception
     */
    public function clear()
    {
        Db::name('cart')->where(['user_id' => $this->user_id, 'selected' => 1])->delete();
    }
}