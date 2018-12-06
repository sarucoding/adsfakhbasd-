<?php

namespace app\mobile\logic;

use think\Model;
use think\Db;
use app\common\logic\PriceLogic;
use app\common\util\TpshopException;
/**
 * 商品逻辑
 * Class CatsLogic
 * @package Home\Logic
 */
class GoodsLogic extends Model
{
    public function getGoodsList($where,$limit,$order){
        $goods_ids = M('goods')->where($where)->field('goods_id')->limit($limit)->select();
        foreach ($goods_ids as $key => $value) {
            $arr[] = $value['goods_id'];
        }
        if(!$arr){
            return [];
        }
        $goods_ids = implode(',',$arr);
        $field = 'a.goods_id,a.goods_name,b.key_name,b.key,a.goods_unit,a.brand_id,a.original_img';
        $list = M('goods')->alias('a')->join('__SPEC_GOODS_PRICE__ b','a.goods_id = b.goods_id','LEFT')->where(['a.goods_id'=>['in',$goods_ids],'is_on_sale'=>1])->order($order)->field($field)->select();
        $price_logic = new PriceLogic();
        $price_logic->setUser(USER_ID,STORE_ID);
        foreach($list as $k=>$v){
            $result = $price_logic->getGoodsPrice($v['goods_id'],$v['key']);
            $list[$k]['offer_price'] = $result['result'];
            $list[$k]['inquiry_status'] = $result['inquiry_status'];
        }
        $arr = [];
        foreach ($list as $k => $v) {
            $arr[$v['goods_id']]['goods_id'] = $v['goods_id'];
            $name = explode(" ",$v['key_name']);
            $v['spec_key_name'] = $this->name_ltrin($name);
            $where = 'goods_id = '.$v['goods_id'].' and store_id = '.STORE_ID.' and user_id = '.USER_ID;
            if($v['key']){
                $where .= ' and spec_key = "'.$v['key'].'"';
            }
            $cart_goods = M('cart')->where($where)->find();
            $v['cart_goods_num'] = $cart_goods['goods_num']?$cart_goods['goods_num']:0;
            $arr[$v['goods_id']]['key'] = $v['key'];
            $arr[$v['goods_id']]['goods_name'] = $v['goods_name'];
            $arr[$v['goods_id']]['spec_key_name'] = $v['spec_key_name'];
            $arr[$v['goods_id']]['goods_unit'] = $v['goods_unit'];
            $arr[$v['goods_id']]['inquiry_status'] = $v['inquiry_status'];
            $arr[$v['goods_id']]['cart_goods_num'] = $v['cart_goods_num'];
            if(!isset($arr[$v['goods_id']]['offer_price']) || empty($arr[$v['goods_id']]['offer_price']) || (!empty($v['offer_price']) && $arr[$v['goods_id']]['offer_price'] > $v['offer_price'])){
                $arr[$v['goods_id']]['offer_price'] = $v['offer_price'];
            }
            if(!isset($arr[$v['goods_id']]['min_offer_price']) || $arr[$v['goods_id']]['min_offer_price'] > $v['offer_price']){
                $arr[$v['goods_id']]['min_offer_price'] = $v['offer_price'];
            }
            if(!isset($arr[$v['goods_id']]['max_offer_price']) || $arr[$v['goods_id']]['max_offer_price'] < $v['offer_price']){
                $arr[$v['goods_id']]['max_offer_price'] = $v['offer_price'];
            }
            if($arr[$v['goods_id']]['max_offer_price'] != '' && $arr[$v['goods_id']]['min_offer_price'] != ''){
                $arr[$v['goods_id']]['type'] = 1;
            }elseif($arr[$v['goods_id']]['max_offer_price'] != '' && $arr[$v['goods_id']]['min_offer_price'] == ''){
                $arr[$v['goods_id']]['type'] = 2;
            }else{
                $arr[$v['goods_id']]['type'] = 3;
            }
            $arr[$v['goods_id']]['original_img'] = $v['original_img'];
            if($v['offer_price']){
                $v['offer_price'] = '￥'.$v['offer_price'].' /'.$v['goods_unit'];
            }
            unset($v['original_img']);
            $arr[$v['goods_id']]['child'][] = $v;
           
        }
        $arr = array_values($arr);
        return $arr;
    }


    public function name_ltrin($name)
    {
        foreach ($name as $key => $value) {
            $n = explode(':',$value);
            $res .= ' '.$n[1];
        }
        return $res;
    }


     /*
    *获取购物信息
     */
    public function cart_goods($where)
    {
        $cat_goods = M('cart')->where($where)->select();
        $cart_goods_data = [];
        foreach ($cat_goods as $k => $v) {
            $field = 'a.goods_unit,a.brand_id,a.original_img';
            $goods_data = M('goods')->alias('a')->join('__SPEC_GOODS_PRICE__ b','a.goods_id = b.goods_id and b.key = "'.$cat_goods['spec_key'].'"','LEFT')->where(['a.store_id'=>STORE_ID,'a.goods_id'=>$v['goods_id']])->field($field)->find();
            $cart_goods_data[$k] = $v;
            $cart_goods_data[$k]['add_ons'] = $goods_data;
        }
        return $cart_goods_data;
    }

    /*
    *获取地址
     */
    public function address()
    {
        $address_list = M('user_address')->where(['user_id'=>USER_ID])->order("is_default DESC")->select();
        foreach ($address_list as $k => $v) {
            $address_list[$k]['country'] = $this->area($v['country']);
            $address_list[$k]['province'] = $this->area($v['province']);
            $address_list[$k]['city'] = $this->area($v['city']);
            $address_list[$k]['district'] = $this->area($v['district']);
            $address_list[$k]['twon'] = $this->area($v['twon']);
            $address_list[$k]['realname'] = $v['shop_name'];
        }
        return $address_list;

    }

    public function area($id)
    {
        $area = M('region')->where(['id'=>$id])->find();
        return $area['name'];
    }


    /*
    *报价产品列表获取
     */
    public function bill_list()
    {
        $field = 'a.offer_id,a.goods_id,c.goods_name,a.offer_price,a.spec_key_name,b.key,a.spec_key';
        $result = M('offer')->alias('a')->join('__GOODS__ c','a.goods_id = c.goods_id')->join('__SPEC_GOODS_PRICE__ b','a.goods_id = b.goods_id and a.spec_key = b.key','LEFT')->where(['a.user_id'=>USER_ID,'a.store_id'=>STORE_ID])->group('a.goods_id,a.spec_key')->order("offer_id DESC")->field($field)->select();
        $PriceLogic = new PriceLogic();
        $PriceLogic->setUser(USER_ID,STORE_ID);
        $arr = [];
        foreach ($result as $k => $v) {
            try {
                $res = $PriceLogic->getGoodsPrice($v['goods_id'],$v['spec_key']);
            } catch (TpshopException $t) {
                M('offer')->where(['offer_id'=>$v['offer_id']])->delete();
                continue;
                //$error = $t->getErrorArr();
                //exit(json_encode(array('status' => -1, 'msg' => $error['msg'])));
            }
            $arr[$v['goods_id']]['goods_id'] = $v['goods_id'];
            $arr[$v['goods_id']]['goods_name'] = $v['goods_name'];
            $v['offer_price'] = $res['result'];
            $name = explode(" ",$v['spec_key_name']);
            $v['spec_key_name'] = $this->name_ltrin($name);
            $arr[$v['goods_id']]['child'][] = $v;
        }
        return $arr;
    }


}