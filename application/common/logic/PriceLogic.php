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

namespace app\common\logic;

use think\db;
use app\common\util\TpshopException;

/**
 * 一客一价
 * Class PriceLogic
 * @package common\Logic
 */
class PriceLogic
{
    private $user;
    public function setUser($user_id,$store_id){
        $this->user = M('users')->alias('a')
            ->join('__USER_RELATION_STORE__ b','a.user_id = b.user_id and b.store_id = '.$store_id)
            ->where('a.user_id',$user_id)
            ->find();
        if (empty($this->user)) {
            throw new TpshopException("设置用户", 0, ['status' => 0, 'msg' => '未找到用户', 'result' => '']);
        }
    }
    /**
     * 获取某客户对应某单品的价格
     */
    public function getGoodsPrice($goods_id, $spec_key = '')
    {
        $goods = M('goods')->where('goods_id',$goods_id)->find();
        if(!$spec_key){
            $spec_where = ['goods_id'=>$goods_id];
        }else{
            $spec_where = ['goods_id'=>$goods_id,'key'=>$spec_key];
        }
        $spec = M('spec_goods_price')->where($spec_where)->find();
        if(!$goods || ($spec_key == '' && $spec) || ($spec_key && !$spec)){
            throw new TpshopException("商品不存在", 0, ['status' => 0, 'msg' => '商品不存在', 'result' => '']);
        }

        $goods_price = $goods['price'] ? $goods['price'] : $goods['shop_price'];//商城价格
        $where = 'goods_id = '.$goods_id.' and user_id = '.$this->user['user_id'].' and spec_key = "'.$spec_key.'"';
        //先查看是否有报价记录
        $offer = M('offer')->where($where)->order('offer_id desc')->find();
        if(!$offer){//如果没有报价则看看是否可以查看所有商品价格
            if($this->user['is_look']){
                return ['status'=>1,'msg'=>'成功','result'=>$goods_price];
            }else{
                //如果没得价格的话，去查询询价单，看是否已经添加了询价，已经添加了询价的产品只需要显示等待报价
                $res = M('inquiry')->where(['goods_ids'=>['like','%_'.$goods_id.'_%'],'user_id'=>$this->user['user_id'],'inquiry_status'=>0,'store_id'=>$this->user['store_id']])->find();
                if(!$res){
                    $res = M('inquiry_goods')->where(['goods_id'=>$goods_id,'user_id'=>$this->user['user_id'],'store_id'=>$this->user['store_id']])->find();
                }
                $inquiry_status = $res ? 1 : 0;
                return ['status'=>1,'msg'=>'成功','result'=>'','inquiry_status'=>$inquiry_status];
            }
        }else{
            $price = ($offer['offer_type'] == 1 ? $goods_price : 0) + $offer['offer_price'];//计算报价
            return ['status'=>1,'msg'=>'成功','result'=>$price];
        }
    }
}