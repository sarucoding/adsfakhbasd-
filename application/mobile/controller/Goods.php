<?php
namespace app\mobile\controller;

use think\AjaxPage;
use think\Loader;
use think\Db;
use think\Page;
use app\admin\logic\UsersLogic;
use app\common\logic\UserRelationStoreLogic;
use app\mobile\logic\GoodsLogic;
use app\common\logic\GoodsLogic as GoodsLogica;
use app\common\logic\PriceLogic;


class Goods extends Base
{

    function _initialize()
    {
        parent::_initialize();

    }


    //商品首页
    public function index()
    {
        $where = [
            'store_id' => STORE_ID,
            'parent_id' => '0'
        ];
        $goods_class = M("store_goods_class")->where($where)->select();
        $where = [
            'store_id' => STORE_ID,
            'parent_id' => $goods_class['0']['cat_id'],
        ];
        $cat = M("store_goods_class")->where($where)->select();
        $goods_list = [];
        if($cat){
            $where = "store_id = " . STORE_ID . " and store_cat_id2 = " . $cat[0]['cat_id'];
            $order = "a.goods_id DESC";
            $goods_logic = new GoodsLogic();
            $goods_list = $goods_logic->getGoodsList($where, 10, $order);
        }
        $brand = M('brand')->where(['store_id'=>['in',STORE_ID.',0'],'status'=>0])->select();
        $tel = M("store")->where(['store_id'=>STORE_ID])->field('service_phone')->find();
        $this->assign('tel', $tel['service_phone']);
        $this->assign('footer_show', 1);
        $this->assign('brand', $brand);
        $this->assign('goods_list', $goods_list);
        $this->assign('cat_class', $cat);
        $this->assign('store', $goods_class);
        return $this->fetch();
    }


    //ajax加载商品分类
    public function ajax_calss()
    {
        $cat_id = I("cid/d");
        $where = [
            'store_id' => STORE_ID,
            'parent_id' => $cat_id,
        ];
        $return = M("store_goods_class")->where($where)->select();
        $this->ajaxReturn($return);
    }


    //ajax加载商品列表
    public function ajax_goods_list()
    {
        $cat_id = I("cat_id/d");
        $page = I('page/d', '1');
        $limit = I('limit/d', '10');
        $brand_id = I('brand');
        // dump($brand_id);die;
        $order = I('order/d');
        if ($order == 1) {
            $order = "a.sales_sum DESC";
        } elseif ($order == 2) {
            $order = "a.sales_sum ASC";
        } else {
            $order = "a.goods_id ASC";
        };
        $where = "store_id = " . STORE_ID;
        if ($cat_id) {
            $where .= " and store_cat_id2 = " . $cat_id;
        }else{
            $this->ajaxReturn([]);
        }
        if ($brand_id) {
            $where .= ' and brand_id in ('.$brand_id.')';
        }
        $start = ($page - 1) * $limit;
        $limit = $start . ',' . $limit;
        $goods_logic = new GoodsLogic();
        $result = $goods_logic->getGoodsList($where, $limit, $order);
        $this->ajaxReturn($result);
    }

    //商品搜索
    public function searchlist()
    {
        $keywords = I('keywords');
        if(IS_AJAX){
            $page = I('page/d', '1');
            $limit = I('limit/d', '10');
            require(ROOT_PATH . "sphinxapi.php");
            $cl = new \SphinxClient ();
            $cl->SetServer('120.79.141.75', 9312);
            $cl->SetConnectTimeout(3);
            $cl->SetArrayResult(true);
            $cl->SetMatchMode(SPH_MATCH_ANY);
            $res = $cl->Query($keywords, "goods");
            $ids = [];
            if (!$res['matches']) {
                $this->ajaxReturn(array());
            }
            foreach ($res['matches'] as $k => $v) {
                $ids[] = $v['id'];
            }
            $ids = implode(',', $ids);
            //print_r($res);
            //高亮关键词字体设置颜色
            $opt = array(
                'before_match' => '<strong style="color:#faa001">',
                'after_match' => '</strong>',
            );
            $where = "store_id = " . STORE_ID;
            if($keywords){
                $where .= ' and goods_id in ('.$ids.')';
            }

            $start = ($page - 1) * $limit;
            $limit = $start . ',' . $limit;
            $goods_logic = new GoodsLogic();
            $list = $goods_logic->getGoodsList($where, $limit, 'goods_id DESC');
            foreach ($list as $k => $v) {
                $highlightTextList = $cl->buildExcerpts(
                    array(
                        $v['goods_name'],
                    ),
                    'goods',
                    $keywords,
                    $opt
                );
                $list[$k]['goods_name'] = $highlightTextList[0];
            }
            $this->ajaxReturn($list);
         
        }
        $this->assign('keywords', $keywords);
        return $this->fetch();
    }

    //搜索
    public function search()
    {

        $hot = M('store')->where(['store_id'=>STORE_ID])->find();
        $words = $hot['hot_keywords'];
        $words = explode('|',$words);
        $this->assign('words', $words);
        return $this->fetch();
    }   

    //商品详情
    public function goods_info()
    {
        $goods_id = I('goods_id/d');
        $key = I('key');
        $goods = M('goods')->where(['goods_id'=>$goods_id,'is_on_sale'=>1])->find();
        $goods_img = M('goods_images')->where(['goods_id'=>$goods_id])->select();
        if(!$goods){
            $this->error('商品不存在或已下架');
        }
        if($key){
            $spec_goods_price = M('spec_goods_price')->where(['goods_id'=>$goods_id,'key'=>$key])->find();
            if(!$spec_goods_price){
                $key = '';
            }
        }
        if(!$key){
            $goods_Logic = new GoodsLogica();
            $filter_spec = $goods_Logic->get_spec($goods_id);
            if($filter_spec){
                $key_arr = [];
                foreach($filter_spec as $k=>$v){
                    $key_arr[] = $v[0]['item_id'];
                }
                $key = implode('_',$key_arr);
            }
        }
        if(M('goods_collect')->where(['goods_id'=>$goods_id,'user_id'=>USER_ID,'store_id'=>STORE_ID])->find()){
            $goods['status'] = 1;
        }else{
            $goods['status'] = 2;
        };
        $goods['images'] = $goods_img;
        $goods['key'] = $key;
        $this->assign('goods', $goods);
        return $this->fetch();

    }


    //加载商品规格数据
    public function ajax_spec()
    {
        $goods_id = I("goods_id/d");
        $key = I("key");
        $key_arr = explode('_', $key);
        $goods = M('goods')->where(['goods_id'=>$goods_id,'is_on_sale'=>1])->find();
        $goodsLogic = new GoodsLogica();
        $filter_spec = $goodsLogic->get_spec($goods_id);//获取商品规格
        $price_logic = new PriceLogic();
        $price_logic->setUser(USER_ID, STORE_ID);
        $result = $price_logic->getGoodsPrice($goods_id, $key);
        $goods['offer_price'] = $result['result'];
        $goods['inquiry_status'] = $result['inquiry_status'];
        $where = 'goods_id = '.$goods_id.' and store_id = '.STORE_ID.' and user_id = '.USER_ID;
        if($key){
            $where .= ' and spec_key = "'.$key.'"';
        }
        $cart_goods = M('cart')->where($where)->find();
        $goods['cart_goods_num'] = $cart_goods['goods_num']?$cart_goods['goods_num']:0;
        foreach ($filter_spec as $k => $v) {
            $select_id = '';
            foreach ($v as $i => $val) {
                if (in_array($val['item_id'], $key_arr)) {
                    $select_id = $val['item_id'];
                }
            }
            foreach ($v as $i => $val) {
                if (in_array($val['item_id'], $key_arr)) {
                    $filter_spec[$k][$i]['is_selected'] = 1;
                } else {
                    $filter_spec[$k][$i]['is_selected'] = 0;
                }
            }
        }
        $inquiry_num = M('inquiry_goods')->where(['user_id'=>USER_ID,'store_id'=>STORE_ID])->count();
        $tel = M("store")->where(['store_id'=>STORE_ID])->field('service_phone')->find();
         $data = [
            'goods' => $goods,
            'spec_list' => $filter_spec,
            'inquiry_num' => $inquiry_num ? $inquiry_num : 0,
            'tel' =>$tel['service_phone'],
        ];
        $this->ajaxReturn($data);
    }

    //加入询价
    public function ajax_inquiry()
    {
        $goods_id = I('goods_id/d');
        if(!$goods_id){
            exit(json_encode(array('status' => -1, 'msg' => '商品为空')));
        }
        $data = [
            'goods_id' => $goods_id,
            'user_id' => USER_ID,
            'store_id' => STORE_ID,
            'ig_add_time' => time(),
        ];
        if (M('inquiry_goods')->where(['goods_id'=>$goods_id,'user_id'=>USER_ID,'store_id'=>STORE_ID])->find()) {
             exit(json_encode(array('status' => -1, 'msg' => '商品已经添加')));
        }
        if(!M('inquiry_goods')->add($data)){
            exit(json_encode(array('status' => -1, 'msg' => '添加失败')));
        }
        exit(json_encode(array('status' => 1, 'msg' => '成功')));
    }




}