<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * Author: IT宇宙人
 * Date: 2015-09-09
 */
namespace app\common\model;

use think\Model;
use think\Db;

class Shop extends Model
{
    /**
     * 获取门店可领优惠券
     * @return $this
     */
    public function coupon(){
       return  $this->hasMany('coupon','store_id','store_id')->where(['type' => 2,'status'=>1,'send_start_time'=>['elt', time()],'send_end_time'=>['egt', time()]]);
    }

    /**
     * 获取门店地址
     * @param $value
     * @param $data
     * @return string
     */
    public function getAddressRegionAttr($value, $data){
        $regions = Db::name('region')->where('id', 'IN', [$data['province_id'], $data['city_id'], $data['district_id']])->order('level desc')->select();
        $address = '';
        if($regions){
            foreach($regions as $regionKey=>$regionVal){
                $address = $regionVal['name'] . ','.$address;
            }
        }
        return $address.$data['shop_address'];
    }

    /**
     * 获取门店订单活动，商品活动
     * @param $value
     * @param $data
     * @return array
     */
    public function getShopPromAttr($value, $data){
        $common_where = [
            'store_id'=>$data['store_id'],
            'status'=>1,
            'start_time'=>['lt',time()],
            'end_time'=>['gt',time()]
        ];
        $prom_order = Db::name('prom_order')->where($common_where)->select();
        $prom_goods = Db::name('prom_goods')->where($common_where)->where(['is_end'=>0])->select();
        $promList = array_merge($prom_order,$prom_goods);
        return $promList;
    }

    /**
     * 获取门店优惠券
     * @param $value
     * @param $data
     * @return array
     */
    public function getCouponListAttr($value, $data){
        $common_where = [
            'store_id'=>$data['store_id'],
            'status'=>1,
            'start_time'=>['lt',time()],
            'end_time'=>['gt',time()]
        ];
        $prom_order = Db::name('prom_order')->where($common_where)->select();
        $prom_goods = Db::name('prom_goods')->where($common_where)->where(['is_end'=>0])->select();
        $promList = array_merge($prom_order,$prom_goods);
        return $promList;
    }
}
