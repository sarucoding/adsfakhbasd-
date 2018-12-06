<?php
namespace app\mobile\controller;

use think\AjaxPage;
use think\Loader;
use think\Db;
use think\Page;
use app\admin\logic\UsersLogic;
use app\common\logic\UserRelationStoreLogic;
use app\mobile\logic\GoodsLogic;
use app\mobile\logic\CartLogic;
use app\common\logic\PriceLogic;
use app\common\logic\Pay;
use app\common\util\TpshopException;
use app\common\logic\PlaceOrder;
use app\common\logic\GoodsLogic as GoodsLogica;


class Commonlist extends Base
{

	function _initialize()
    {
        parent::_initialize();

    }

   //常用清单列表
   	public function index()
   	{   
        $this->assign('footer_show', 2);
        return $this->fetch();
   	} 

    public function ajax_list()
    {
        $page = I('page/d', '1');
        $limit = I('limit/d', '10');
        $where = "store_id = " . STORE_ID ." and user_id = ".USER_ID;
        $start = ($page - 1) * $limit;
        $limit = $start . ',' . $limit;
        $ids = M('goods_collect')->where($where)->limit($limit)->order('collect_id desc')->getField('goods_id,goods_id');
        $order_buy = ','.implode(',',$ids).',';
        $goods_logic = new GoodsLogic();
        $list = $goods_logic->getGoodsList(['goods_id'=>['in',$ids]], $limit, "INSTR('".$order_buy."',CONCAT(',',a.goods_id,','))");
        $this->ajaxReturn($list);
    }


    //加入清单
    public function collect_action()
    {
        $goods_id = I('goods_id/d');
        $status = I('status/d');
        if(!$goods_id || !$status){
            exit(json_encode(array('status' => -1, 'msg' => '参数错误'))); 
        }
        $data = [
            'goods_id' => $goods_id,
            'user_id' => USER_ID,
            'store_id' => STORE_ID,
        ];
        $collect = M('goods_collect')->where($data)->find();
        if($status == 2 && !$collect){
            $data['add_time'] = time();
            $result = M('goods_collect')->add($data);
            exit(json_encode(array('status' => 1, 'msg' => '成功', 'a' => 1))); 

        }else if($status == 1 && $collect){
            $result = M('goods_collect')->where($data)->delete();
            exit(json_encode(array('status' => 1, 'msg' => '成功','a'=>2 ))); 
        }
        exit(json_encode(array('status' => -1, 'msg' => '失败'))); 
    }
}

