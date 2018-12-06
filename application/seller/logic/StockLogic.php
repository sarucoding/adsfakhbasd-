<?php


namespace app\seller\logic;

use think\Model;
use think\Db;

class StockLogic extends Model
{
    /**
     * @param $bill_id
     * @param $bill_status
     * 审核入库单
     */
    public function check_bill($bill_id,$bill_status,$bill_note){
        $bill = M('stock_bill')->where(['store_id'=>STORE_ID,'bill_id'=>$bill_id])->find();
        if(!$bill){
            return ['status' => 0, 'msg' => '入库单不存在', 'result' => ''];
        }
        $res = M('stock_bill')->where(['store_id'=>STORE_ID,'bill_id'=>$bill_id])->save(['bill_status'=>$bill_status,'bill_note'=>$bill_note]);//改变入库单状态
        if($res){
            $res = M('stock_in')->where(['store_id'=>STORE_ID,'bill_id'=>$bill_id])->save(['stock_status'=>$bill_status]);//改变入库明细状态
            if($res){
                if($bill_status == 1){
                    suppliersAccountLog($bill['suppliers_id'],STORE_ID,$bill['goods_amount'],'进货入库货款增加，入库单编号为'.$bill['bill_sn']);//增加供应商货款
                    $list = M('stock_in')->where(['store_id'=>STORE_ID,'bill_id'=>$bill_id])->select();
                    foreach($list as $k=>$v){//修改入库商品的库存
                        update_stock_log($v['stock_in_num'],$v['stock_id'],0,'进货入库，入库单编号为'.$v['bill_sn']);
                    }
                }
                return ['status' => 1, 'msg' => '审核成功', 'result' => ''];
            }else{
                return ['status' => 0, 'msg' => '审核失败', 'result' => ''];
            }
        }else{
            return ['status' => 0, 'msg' => '审核失败', 'result' => ''];
        }
    }

}