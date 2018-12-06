<?php
namespace app\mobile\controller;

use think\AjaxPage;
use think\Loader;
use think\Db;
use think\Page;
use app\admin\logic\UsersLogic;
use app\common\logic\UserRelationStoreLogic;
use app\mobile\logic\GoodsLogic;


class Misc extends Base
{

    function _initialize()
    {
        parent::_initialize();

    }


 public function index()
 {
 	$this->assign('goods_hot',$goods_hot);
	return $this->fetch();
 }

 public function service()
 {
 	$this->assign('goods_hot',$goods_hot);
	return $this->fetch();
 }

 public function problem()
 {
 	$this->assign('goods_hot',$goods_hot);
	return $this->fetch();
 }

 public function article()
 {
 	$this->assign('goods_hot',$goods_hot);
	return $this->fetch();
 }

public function ajax_article()
{
	$limit = I("limit/d");
	$page = I("page/d");
	$article_list = M("article")->select();
	$this->ajaxReturn($article_list);
}

public function article_info()
{
	$id = I("id/d");
	$article_data = M("article")->where(['article_id'=>$id])->find();
	// dump($article_data);
	$this->assign('article',$article_data);
	return $this->fetch();
}




}