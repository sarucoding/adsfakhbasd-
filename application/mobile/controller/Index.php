<?php
namespace app\mobile\controller;

use think\AjaxPage;
use think\Loader;
use think\Db;
use think\Page;
use app\admin\logic\UsersLogic;
use app\common\logic\UserRelationStoreLogic;
use app\mobile\logic\MiscLogic;

class Index extends Base
{

   function _initialize()
   {
       parent::_initialize();

    
   }




   //首页
   public function index()
   {
	   	$goods_new = $this->goods_data($where = array('is_new' => 1),3);
	 	$goos_recommend = $this->goods_data($where = array('is_recommend' => 1),7);
	 	$goods_hot = $this->goods_data($where = array('is_hot' => 1),6);
	 	$sort_data = M('store')->where(['store_id'=>STORE_ID])->find();
	 	$slide_img = explode(",",$sort_data['store_slide']);
	 	$slide_url = explode(",",$sort_data['store_slide_url']);
	 	$slide = [];
	 	foreach ($slide_img as $k => $v) {
	 		if(!empty($v)){
		 		$slide[$k]['img'] = $v;
		 		$slide[$k]['url'] = $slide_url[$k];
	 		}
	 	};
	 	if(!$slide){
	 		$slide["0"]["img"] = "/public/mobile/img/banner1.png";
	 		$slide["0"]["url"] = "###";
	 	}
	 	$misc = new MiscLogic();
	 	$misc_list = $misc->report(5);
	 	$this->assign('misc_list',$misc_list);
	 	$this->assign('slide',$slide);
	 	$this->assign('goods_new',$goods_new);
	 	$this->assign('goos_recommend',$goos_recommend);
	 	$this->assign('goods_hot',$goods_hot);
	    return $this->fetch();
 
   }


   public function goods_data($where,$limit)
   {
   		$where['store_id'] = STORE_ID;
   		$goods = M('goods')->where($where)->order('sort ASC')->limit($limit)->select();
   		return $goods;
   }


}