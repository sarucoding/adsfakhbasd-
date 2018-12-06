<?php

namespace app\seller\controller;

use app\seller\logic\StockLogic;
use think\Db;
use think\Loader;
use think\Page;

class Stock extends Base
{

    public function index()
    {
        $stock_bill = M('stock_bill');
        $where = 'store_id = '.STORE_ID;
        $keywords = I('keywords','');
        if($keywords){
            $where .= ' and (bill_sn like "%'.$keywords.'%" or suppliers_name like "%'.$keywords.'%")';
        }
        $count = $stock_bill->where($where)->count();
        $Page = new Page($count, 10);
        $list = $stock_bill->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('bill_id desc')->select();
        foreach($list as $k=>$v){
            $list[$k]['bill_img'] = explode(',',$v['bill_img']);
        }
        $this->assign('page', $Page);
        $this->assign('list', $list);
        $this->assign('keywords', $keywords);
        return $this->fetch();
    }

    /**
     * 进货入库
     */
    public function add()
    {
        if(IS_POST){
            $suppliers_id = input('suppliers_id/d');
            $bill_img = input('bill_img/a');
            $goods_id = input('goods_id/a');
            $goods_name = input('goods_name/a');
            $spec_key_name = input('spec_key_name/a');
            $stock_price = input('stock_price/a');
            $stock_in_num = input('stock_in_num/a');
            $key = input('key/a');
            $suppliers = M('suppliers')->where(['store_id'=>STORE_ID,'suppliers_id'=>$suppliers_id])->find();
            if(!$suppliers){
                $err['suppliers_id'] = '供应商必须选择';
            }
            if($err){
                $this->ajaxReturn(['status' => 0, 'msg' => '提交失败', 'result' => $err]);
            }
            $goods_count = 0;
            $goods_amount = 0;
            foreach($stock_price as $k=>$val){
                $goods_count++;//计算商品数
                $goods_amount += $val * $stock_in_num[$k];//计算商品总价值
            }
            $bill_data['goods_count'] = $goods_count;
            $bill_data['goods_amount'] = $goods_amount;
            $bill_data['store_id'] = STORE_ID;
            $bill_data['suppliers_id'] = $suppliers['suppliers_id'];
            $bill_data['suppliers_name'] = $suppliers['suppliers_name'];
            $bill_data['bill_add_time'] = time();
            $bill_data['bill_remark'] = I('bill_remark');
            $bill_data['bill_person'] = session('seller.real_name');
            array_pop($bill_img);
            $bill_data['bill_img'] = implode(',',$bill_img);
            do{
                $bill_data['bill_sn'] = build_serial_number();
                $count = M('stock_bill')->where(['bill_sn'=>$bill_data['bill_sn']])->count();
            }while($count>0);//保证入库单编号唯一
            $bill_id = M('stock_bill')->add($bill_data);
            if(!$bill_id){
                $this->ajaxReturn(['status' => 0, 'msg' => '提交失败', 'result' => '']);
            }
            $stock_data = [];
            foreach($stock_price as $k=>$val){
                $stock_data[$k]['goods_id'] = $goods_id[$k];
                $stock_data[$k]['bill_id'] = $bill_id;
                $stock_data[$k]['bill_sn'] = $bill_data['bill_sn'];
                $stock_data[$k]['store_id'] = STORE_ID;
                $stock_data[$k]['suppliers_id'] = $suppliers['suppliers_id'];
                $stock_data[$k]['suppliers_name'] = $suppliers['suppliers_name'];
                $stock_data[$k]['goods_name'] = $goods_name[$k];
                $stock_data[$k]['spec_key_name'] = $spec_key_name[$k];
                $stock_data[$k]['spec_key'] = $key[$k];
                $stock_data[$k]['stock_in_num'] = $stock_in_num[$k];
                $stock_data[$k]['stock_surplus_num'] = $stock_in_num[$k];
                $stock_data[$k]['stock_price'] = $stock_price[$k];
                $stock_data[$k]['stock_add_time'] = time();
            }
            $res = M('stock_in')->insertAll($stock_data);
            if(!$res){
                $this->ajaxReturn(['status' => 0, 'msg' => '提交失败', 'result' => '']);
            }else{
                $this->ajaxReturn(['status' => 1, 'msg' => '入库成功', 'result' => '']);
            }
        };
        $suppliers = M('suppliers')->where(['store_id'=>STORE_ID])->select();
        $this->assign('suppliers',$suppliers);
        return $this->fetch();
    }

    /**
     * ajax审核入库单
     */
    public function check_bill(){
        if(IS_AJAX){
            $bill_id = input('bill_id/d');
            $bill_status = input('bill_status/d');
            $bill_note = input('bill_note');
            if(!in_array($bill_status,[1,2]) || $bill_id <= 0){
                $this->ajaxReturn(['status' => 0, 'msg' => '参数错误', 'result' => '']);
            }
            $stockLogic = new StockLogic();
            $res = $stockLogic->check_bill($bill_id,$bill_status,$bill_note);
            $this->ajaxReturn($res);
        }
    }
    /**
     * 入库明细
     */
    public function in_lists(){
        $bill_id = input('bill_id/d');
        $where = ['bill_id'=>$bill_id];
        $count = M('stock_in')->where($where)->count();
        $Page = new Page($count, 10);
        $list = M('stock_in')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('stock_id desc')->select();
        $this->assign('page', $Page);
        $this->assign('list', $list);
        return $this->fetch();
    }
    /**
     * 库存列表
     */
    public function goods_stock_lists()
    {
        $where = 'a.store_id = '.STORE_ID;
        $keywords = I('keywords','');
        if($keywords){
            $where .= ' and goods_name like "%'.$keywords.'%"';
        }
        $count = M('goods')->alias('a')->join('__SPEC_GOODS_PRICE__ b','a.goods_id = b.goods_id','LEFT')->where($where)->count();
        $Page = new Page($count, 10);
        $field = 'a.goods_id,a.goods_name,b.key_name,b.key,a.goods_unit,a.store_count as stock_amount,b.store_count as stock_count';
        $list = M('goods')->alias('a')->join('__SPEC_GOODS_PRICE__ b','a.goods_id = b.goods_id','LEFT')->where($where)->field($field)->limit($Page->firstRow . ',' . $Page->listRows)->order('a.goods_id desc')->select();
        foreach($list as $k=>$v){
            $list[$k]['stock'] = $v['stock_count'] === null ? $v['stock_amount'] : $v['stock_count'];
        }
        $this->assign('page', $Page);
        $this->assign('list', $list);
        $this->assign('keywords', $keywords);
        return $this->fetch();
    }
    /**
     * 库存日志
     */
    public function stock_log()
    {
        $goods_id = input('goods_id/d');
        $key = input('key/s','null');
        $where = 'store_id = '.STORE_ID.' and goods_id = '.$goods_id.' and spec_key = "'.$key.'"';
        $count = M('stock_log')->where($where)->count();
        $Page = new Page($count, 10);
        $list = M('stock_log')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('id desc')->select();
        $this->assign('page', $Page);
        $this->assign('list', $list);
        return $this->fetch();
    }
    /**
     * 全部库存日志
     */
    public function stock_all_log()
    {
        $change_type = input('change_type/d');
        $keywords = input('keywords/s','');
        $where = ['store_id'=>STORE_ID];
        if($change_type){
            $where['change_type'] = $change_type;
        }
        if($keywords){
            $where['goods_name'] = ['like','%'.$keywords.'%'];
        }
        $where1 = "id > 0";
        if(I('start_time')){
            $where1 .= " and ctime >= ".$this->begin;
        }
        if(I('end_time')){
            $where1 .= " and ctime <= ".$this->end;
        }
        $count = M('stock_log')->where($where)->where($where1)->count();
        $Page = new Page($count, 10);
        $list = M('stock_log')->where($where)->where($where1)->limit($Page->firstRow . ',' . $Page->listRows)->order('id desc')->select();
        $this->assign('page', $Page);
        $this->assign('list', $list);
        $this->assign('keywords', $keywords);
        return $this->fetch();
    }
    /**
     * 入库记录
     */
    public function in_stock_log()
    {
        $goods_id = input('goods_id/d');
        $key = input('key/s','null');
        $where = 'store_id = '.STORE_ID.' and goods_id = '.$goods_id.' and spec_key = "'.$key.'"';
        $count = M('stock_in')->where($where)->count();
        $Page = new Page($count, 10);
        $list = M('stock_in')->where($where)->limit($Page->firstRow . ',' . $Page->listRows)->order('stock_id desc')->select();
        $this->assign('page', $Page);
        $this->assign('list', $list);
        return $this->fetch();
    }
    /**
     * 异常处理
     */
    public function edit_stock(){
        if(IS_POST){
            $stock_id = input('stock_id/d');
            $stock = input('stock/d');
            $change_type = input('change_type/d');
            $stock_in = M('stock_in')->where(['store_id'=>STORE_ID,'stock_id'=>$stock_id])->find();
            if(!$stock_in){
                $err['stock_id'] = '库存批次必须选择';
            }
            if($stock <= 0){
                $err['stock'] = '变更数量有误';
            }
            if($change_type == 2){//报损出库
                $stock = -$stock;
                $desc = '报损出库，库存批次'.$stock_in['bill_sn'];
            }elseif($change_type == 3){
                $desc = '报溢入库，库存批次'.$stock_in['bill_sn'];
            }elseif($change_type == 5){
                $stock = -$stock;
                $desc = '退货出库，库存批次'.$stock_in['bill_sn'];
            }else{
                $err['change_type'] = '变更类型有误';
            }
            if($err){
                $this->ajaxReturn(['status' => 0, 'msg' => '提交失败', 'result' => $err]);
            }
            $res = update_stock_log($stock,$stock_id,$change_type,$desc);
            if($res){
                $this->ajaxReturn(['status' => 1, 'msg' => '操作成功', 'result' => '']);
            }else{
                $this->ajaxReturn(['status' => 0, 'msg' => '操作失败', 'result' => '']);
            }
        }
        $goods_id = input('goods_id/d');
        $key = input('key/s','null');
        $where = 'store_id = '.STORE_ID.' and goods_id = '.$goods_id.' and spec_key = "'.$key.'"';
        $goods = M('goods')->alias('a')->join('__SPEC_GOODS_PRICE__ b','a.goods_id = b.goods_id and b.key = "'.$key.'"','LEFT')->where(['a.goods_id'=>$goods_id])->find();
        $stock_list = M('stock_in')->where($where)->order('stock_id desc')->select();
        $this->assign('goods', $goods);
        $this->assign('stock_list', $stock_list);
        return $this->fetch();
    }

    /**
     * 供应商列表
     * @return mixed
     */
    public function suppliers_list()
    {
        $map = array();
        $map['store_id'] = STORE_ID;
        $suppliers_name = trim(I('suppliers_name'));
        if ($suppliers_name) {
            $map['suppliers_name'] = array('like', "%$suppliers_name%");
        }
        $suppliers_list = M('suppliers')->where($map)->select();
        $this->assign('suppliers_list', $suppliers_list);
        return $this->fetch();
    }

    /**
     * 供应商详情
     * @return mixed
     * @throws \think\Exception
     */
    public function suppliers_info()
    {
        if (IS_POST) {
            $data = I('post.');
            $data['store_id'] = STORE_ID;
            if ($data['act'] == 'del') {
                Db::name('goods')->where(array('suppliers_id' => $data['suppliers_id']))->update(['suppliers_id'=>0]);
                $r = M('suppliers')->where(array('suppliers_id' => $data['suppliers_id']))->delete();
            } elseif ($data['suppliers_id'] > 0) {
                $r = M('suppliers')->where(array('suppliers_id' => $data['suppliers_id']))->save($data);
            } else {
                $r = M('suppliers')->add($data);
            }
            if ($r) {
                $this->ajaxReturn(1, 'json');
            } else {
                $this->ajaxReturn(0, 'json');
            }
        }
        $suppliers_id = I('suppliers_id/d');
        if ($suppliers_id) {
            $suppliers = M('suppliers')->where(array('suppliers_id' => $suppliers_id))->find();
            $this->assign('suppliers', $suppliers);
        }
        return $this->fetch();
    }
}
