<?php
namespace app\mobile\controller;

use think\AjaxPage;
use think\Loader;
use think\Db;
use think\Page;
use app\admin\logic\UsersLogic;
use app\common\logic\UserRelationStoreLogic;
use app\mobile\logic\GoodsLogic;
use app\mobile\controller\Api;

class User extends Base
{

    function _initialize()
    {
        parent::_initialize();

    }



   //首页
   public function index()
   {
    $user = M('users')->where(['user_id'=>USER_ID])->find();
    $stoer = M('user_relation_store')->where(['user_id'=>USER_ID,'store_id'=>STORE_ID])->find();
    $this->assign('footer_show', 4);
    $this->assign('stoer',$stoer);
    $this->assign('user', $user);
    return $this->fetch();

   }

   //用户设置
   public function user_setting()
   {
    $user = M('users')->where(['user_id'=>USER_ID])->find();
    $this->assign('user', $user);
    return $this->fetch();
   }

   //编辑个人资料
   public function edit_userdata()
   {
      $user = M('users')->where(['user_id'=>USER_ID])->find();
      if(IS_POST){
        $nick_name = I('realname');
        if($nick_name = M('users')->where(['user_id'=>USER_ID])->find()){

        }
      }
      $this->assign('user', $user);
      return $this->fetch();
   }

   //账号余额
   public function account()
   {
      $stoer = M('user_relation_store')->where(['user_id'=>USER_ID,'store_id'=>STORE_ID])->find();
      $this->assign('stoer', $stoer);
      return $this->fetch();
   }

   //余额变动明细
   public function withhd_data()
   {
      $data = I("");
      $page = $data['page'];
      $limit = $data['limit'];
      $stoer = M('user_relation_store')->where(['user_id'=>USER_ID,'store_id'=>STORE_ID])->find();
      $start = ($page - 1) * $limit;
      $limit = $start . ',' . $limit;
      $result = M('store_account_log')->where(['store_id'=>STORE_ID,'re_id'=>$stoer['re_id']])->limit($limit)->order('log_id desc')->select();
      foreach ($result as $k => $v) {
          $result[$k]['log_add_time'] = date("Y-m-d H:i:s",$v['log_add_time']);
          if($v['log_money']>0){
             $result[$k]['log_money'] = '+'.$v['log_money'];
          }
      };
      $this->ajaxReturn($result);
   }


   //账号余额
   public function detailed()
   {
      $stoer = M('user_relation_store')->where(['user_id'=>USER_ID,'store_id'=>STORE_ID])->find();
      $this->assign('stoer', $stoer);
      return $this->fetch();
   }


   //提现明细
   public function withhd_detailed()
   {
      $data = I("");
      $page = $data['page'];
      $limit = $data['limit'];
      $start = ($page - 1) * $limit;
      $limit = $start . ',' . $limit;
      $result = M('withdrawals')->where(['user_id'=>USER_ID,'store_id'=>STORE_ID])->limit($limit)->order('id desc')->select();
      foreach ($result as $k => $v) {
        if($v['type']=='Alipay'){
          $result[$k]['type'] ='支付宝';
        }else{
          $result[$k]['type'] =$v['bank_name'];
        }
        if($v['status'] == 0){
          $result[$k]['status'] ='等待审核';
        }elseif ($v['status'] = 1) {
          $result[$k]['status'] ='审核通过';
        }elseif ($v['status'] = 2) {
          $result[$k]['status'] ='审核未通过';
        }
        $result[$k]['create_time'] = date("Y-m-d H:i:s",$v['create_time']);
      }
      $this->ajaxReturn($result);
   }


   //提现
   public function withdrawals()
   {
      $data = I("");
      $type = $data['paytype'];
      if(!$type){
        exit(json_encode(array('status' => -1, 'msg' => '参数错误!')));
      }
      if(!$data['bank_card'] || !$data['money']){
        exit(json_encode(array('status' => -1, 'msg' => '提现账号或金额不能为空')));
      }
      if($type==="Bank"){
        if(!$data['bank_name'] || !$data['realname']){
          exit(json_encode(array('status' => -1, 'msg' => '开户行或开户姓名不能为空')));
        }
      }
      $with = [
        'user_id' => USER_ID,
        'store_id' => STORE_ID,
        'money' => $data['money'],
        'create_time' => time(),
        'bank_name' => $data['bank_name'],
        'bank_card' => $data['bank_card'],
        'realname' => $data['realname'],
        'type' => $type,
      ];
      $store = M('user_relation_store')->where(['user_id'=>USER_ID,'store_id'=>STORE_ID])->find();
      if($data['money'] > $store['store_money']){
        exit(json_encode(array('status' => -1, 'msg' => '提现金额超出余额')));
      }
      if(!M('withdrawals')->add($with)){
        exit(json_encode(array('status' => -1, 'msg' => '提交失败请重试')));
      };
      M('user_relation_store')->where(['user_id'=>USER_ID,'store_id'=>STORE_ID])->setDec('store_money',$data['money']);
      $log = [
        're_id' => $store['re_id'],
        'store_id' => STORE_ID,
        'log_money' => '-'.$data['money'],
        'log_add_time' => time(),
        'log_info' => '余额提现',
      ];
      M('store_account_log')->add($log);
      exit(json_encode(array('status' => 1, 'msg' => '提交成功等待审核')));
   }



   //编辑支付密码
   public function edit_paypwa()
   {
      $paypwd = I('paypwd');
      $verify = I('verify');
      $api = new Api();
      $result = $api->validate_code($verify);
      if($result['status'] !== 1 ){
        exit(json_encode(array('status' => -1, 'msg' => '验证码错误!')));
      }
      if(!preg_match('/^\d{6}$/',$paypwd)){
        exit(json_encode(array('status' => -1, 'msg' => '密码参数错误!')));
      }
      if(!M('users')->where(['user_id'=>USER_ID])->update(['paypwd'=>encrypt($paypwd)])){
        exit(json_encode(array('status' => -1, 'msg' => '编辑失败!')));
      }
      exit(json_encode(array('status' => 1, 'msg' => '正确!')));
   }

   /*编辑地址*/
   public function add_address()
   {
      if(IS_POST){
        $data = I("");
        $address_id = $data['address_id'];
        $shop_name = $data['hotelname'];
        $consignee = $data['name'];
        $address = $data['address'];
        $mobile = $data['tel'];
        $zone = $data['zone'];
        $zonenum = $data['zonenum'];
        $is_default = $data['is_default']?$data['is_default']:0;
        if(!$consignee){
          exit(json_encode(array('status' => -1, 'msg' => '收货人不能为空!')));
        }
        if(!$address){
          exit(json_encode(array('status' => -1, 'msg' => '收货地址不能为空!')));
        }
        if(!$mobile){
          exit(json_encode(array('status' => -1, 'msg' => '联系方式不能为空!')));
        }
        if(!$zone){
          exit(json_encode(array('status' => -1, 'msg' => '地区不能为空!')));
        }
        if(!$zonenum){
          exit(json_encode(array('status' => -1, 'msg' => '信息错误!')));
        }
        $zonenum = explode(" ", $zonenum);
        if($is_default){
          if(M('user_address')->where(['user_id'=>USER_ID,'is_default'=>1])->find()){
            if(!M('user_address')->where(['user_id'=>USER_ID,'is_default'=>1])->setField('is_default',0)){
              exit(json_encode(array('status' => -1, 'msg' => '设置默认失败!')));
            }
          }
        }
        $addressadata = [
          'user_id' => USER_ID,
          'shop_name' => $shop_name,
          'consignee' => $consignee,
          'province' => $zonenum[0],
          'city'=> $zonenum[1],
          'district' => $zonenum[2],
          'address' => $address,
          'mobile' => $mobile,
          'is_default' => $is_default,
        ];
        if(empty($address_id)){
          if(!$select = M('user_address')->add($addressadata)){
            exit(json_encode(array('status' => -1, 'msg' => '添加失败!')));
          }
        }else{
          if(!$select = M('user_address')->where(['address_id'=>$address_id])->update($addressadata)){
            exit(json_encode(array('status' => -1, 'msg' => '更新失败!')));
          }
        }
        $addre = M('user_address')->where(['user_id'=>USER_ID])->find();
        exit(json_encode(array('status' => 1, 'msg' => '成功!','id'=>$addre['address_id'])));
      }
      $id = I('address_id/d','');
      $act = I('act');
      $goodslogic = new GoodsLogic();
      $address = M('user_address')->where(['address_id'=>$id])->find();
      $address['pr'] = $goodslogic->area($address['province']);
      $address['ci'] = $goodslogic->area($address['city']);
      $address['di'] = $goodslogic->area($address['district']);
      if($act == 'order'){
        $this->assign('act', $act);
      }
      $this->assign('address', $address);
      return $this->fetch();

   }

   //地址列表
   public function address_list()
   {

      $going = I("act");
      $address_id = I("address_id");
      $goodslogic = new GoodsLogic();
      $address = $goodslogic->address();
      $this->assign('address_id', $address_id);
      $this->assign('going', $going);
      $this->assign('address', $address);
      return $this->fetch();
   }


   //设置默认地址
   public function ajax_default()
   {
      $id = I("id/d");
      if(!$id){
        exit(json_encode(array('status' => -1, 'msg' => '参数错误!')));
      }
      M('user_address')->where(['user_id'=>USER_ID,'is_default'=>1])->update(['is_default'=>0]);
      if(!M('user_address')->where(['user_id'=>USER_ID,'address_id'=>$id])->update(['is_default'=>1])){
        exit(json_encode(array('status' => -1, 'msg' => '设置失败!')));
      }
      exit(json_encode(array('status' => 1, 'msg' => '设置成功!')));
   }

   //删除地址
   public function ajax_del()
   {
      $id = I("id/d");
      if (!$id) {
        exit(json_encode(array('status' => -1, 'msg' => '参数错误!')));
      }
      if(!M('user_address')->where(['user_id'=>USER_ID,'address_id'=>$id])->delete()){
        exit(json_encode(array('status' => -1, 'msg' => '删除失败!')));
      }
      exit(json_encode(array('status' => 1, 'msg' => '删除成功!')));
   }

   //我的报价
   public function  bill()
   {
      $goodslogic = new GoodsLogic();
      $where = [
        'user_id' => USER_ID,
        'store_id' => STORE_ID,
      ];
      $limit = 10;
      $page = 1;
      $result = $goodslogic->bill_list();
      $count = count($result);
      $this->assign('count',$count);
      $this->assign('goods', array_values($result));
      return $this->fetch();
   }

   //查看历史报价
   public function bill_history()
   {
      $data = I("");
      $data['key'] = $data['key'] ? $data['key'] : '';
      $field = 'a.goods_id,c.goods_name,a.offer_price,b.key_name,b.key,a.spec_key,a.offer_add_time';
      $result = M('offer')->alias('a')->join('__GOODS__ c','a.goods_id = c.goods_id')->join('__SPEC_GOODS_PRICE__ b','a.goods_id = b.goods_id and b.key = a.spec_key','LEFT')->where(['a.user_id'=>USER_ID,'a.store_id'=>STORE_ID,'a.goods_id'=>$data['id'],'a.spec_key'=>$data['key']])->order("offer_id DESC")->field($field)->select();
      $count = count($result);
      $this->assign('count',$count);
      $this->assign('goods',$result);
      return $this->fetch();
   }

   //我的供应商
   public function store_list()
   {
      $store = M('user_relation_store')->where(['user_id'=>USER_ID])->select();
      $id = [];
      foreach($store as $k => $v){
        $id[] = $v['store_id'];
      }
      $id  =  implode(',',$id);
      $store_list = M('store')->where(['store_id'=>['in',$id]])->select();
      $select_store = [];
      $not_select_store = [];
      foreach ($store_list as $key => $value) {
        if($value['store_id'] == STORE_ID){
          $select_store[] = $value;
        }else{
          $not_select_store[] = $value;
        }
      }
      $store_list = array_merge($select_store,$not_select_store);
      $this->assign('store',$store_list);
      return $this->fetch();
   }

   //查找供应商
   public function lookupshop()
   {
     $invite_code = I('code');
     if(empty($invite_code)){
      exit(json_encode(array('status' => -1, 'msg' => '邀请码不能为空!')));
     }
     $store = M('store')->where(['invite_code'=>$invite_code])->find();
     $store_list = M('user_relation_store')->where(['user_id'=>USER_ID,])->select();
     foreach($store_list as $k => $v){
          if($store['store_id'] == $v['store_id']){
            exit(json_encode(array('status' => -1, 'msg' => '此供应商已添加')));
          }
     }
     if(!$store){
      exit(json_encode(array('status' => -1, 'msg' => '没有找到此码对应的供应商!')));
     }
     exit(json_encode(array('status' => 1,'result' => $store)));
   }

   //添加供应商
   public function addstor()
   {
      $id = I("sid/d","");
      $store = M('store')->where(['store_id'=>$id])->find();
      if(!$store){
        exit(json_encode(array('status' => -1, 'msg' => '店铺不存在')));
      }
      if(M('user_relation_store')->where(['user_id'=>USER_ID,'store_id'=>$id])->find()){
        exit(json_encode(array('status' => -1, 'msg' => '不能重复添加同一个供应商！')));
      }
      $data = [
          'store_id' =>$store["store_id"],
          'user_id' =>USER_ID,
          'add_time' => time(),
      ];
      if(!M('user_relation_store')->add($data)){
        exit(json_encode(array('status' => -1, 'msg' => '添加失败')));
      }
      exit(json_encode(array('status' => 1,'msg' => '添加成功')));
   }

   //切换店铺
   public function switch_shop()
   {
    $id = I("shop_id/d","");
    if(empty($id)){
    exit(json_encode(array('status' => -1,'msg' => '参数错误')));
    }
    if(!M('users')->where(['user_id'=>USER_ID])->setField('last_login_store',$id)){
      exit(json_encode(array('status' => -1,'msg' => '设置失败')));
    }
    session('user.store_id',$id);
     exit(json_encode(array('status' => 1,'msg' => '成功切换')));
    }

   //支付设置
   public function  pay_setting()
   {
    $user = M('users')->where(['user_id'=>USER_ID])->find();
    $this->assign('user', $user);
    return $this->fetch();
   }

   //询价单
   public function inquiry()
   {
      $field = 'a.goods_id,b.goods_name,b.original_img,b.goods_remark';
      $inquiry_list = M('inquiry_goods')->alias('a')
      ->join('__GOODS__ b','a.goods_id = b.goods_id')
      ->where(['a.user_id'=>USER_ID,'a.store_id'=>STORE_ID])->field($field)->select();
      foreach ($inquiry_list as $k => $v) {
        if($v['key_name']){
            $name = explode(" ",$v['key_name']);
            $inquiry_list[$k]['key_name'] = $this->name_ltrin($name);
        }
      }
      $num = (count($inquiry_list));
      $store = M('store')->where(['store_id'=>STORE_ID])->find();
      $this->assign('num', $num);
      $this->assign('store', $store);
      $this->assign('inquiry_list', $inquiry_list);
      return $this->fetch();
   }

    public function name_ltrin($name)
    {
        foreach ($name as $key => $value) {
            $n = explode(':',$value);
            $res .= ' '.$n[1];
        }
        return $res;
    }


    //提交询价单
    public function add_inquiry()
    {
      $goods_id_arr = I('goods_id/a');
      if(!$goods_id_arr){
        exit(json_encode(array('status' => -1,'msg' => '提交数据为空')));
      }
      $goods_id = implode("_",array_unique($goods_id_arr));
      $goods_ids = '_'.$goods_id.'_';
      $data = [
        'goods_ids' => $goods_ids,
        'user_id' => USER_ID,
        'store_id' => STORE_ID,
        'inquiry_add_time' => time()
      ];
      if(!M('inquiry')->add($data)){
        exit(json_encode(array('status' => -1,'msg' => '提交失败')));
      }
      $where = [
        'goods_id'=> ['in',$goods_id_arr],
        'user_id' => USER_ID,
        'store_id' => STORE_ID,
      ];
      M('inquiry_goods')->where($where)->delete();
      exit(json_encode(array('status' => 1,'msg' => '提交成功')));
    }

    //删除询价商品
    public function del_inquiry()
    {
      $goods_id = I('goods_id/d');
      if(!$goods_id){
        exit(json_encode(array('status' => -1,'msg' => '数据异常')));
      }
      $where = [
        'goods_id' => $goods_id,
        'user_id' => USER_ID,
        'store_id' => STORE_ID,
      ];
      if(!M('inquiry_goods')->where($where)->delete()){
      exit(json_encode(array('status' => -1,'msg' => '失败')));
      };
      exit(json_encode(array('status' => 1,'msg' => '删除成功')));
    }

     public function inquiry_success()
     {
      return $this->fetch();
     }

}
    





















