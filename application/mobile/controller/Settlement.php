<?php
namespace app\mobile\controller;

use think\Db;
use app\common\model\Settlement as SettlementModel;
class Settlement extends Base
{

    function _initialize()
    {
        parent::_initialize();

    }


    /**
     * 账单列表
     * @return mixed
     */
    public function index()
    {
        if(IS_AJAX){
            $settle_status = I('settle_status','0','intval');
            if(!in_array($settle_status,[0,1])){
                $this->ajaxReturn(['status'=>0,'msg'=>'参数错误']);
            }
            $settle_status = $settle_status ? $settle_status : ['in','0,2'];
            $SettlementModel = new SettlementModel();
            $lists = $SettlementModel->relation('order')->where(['user_id'=>USER_ID,'STORE_ID'=>STORE_ID,'settle_status'=>$settle_status])->select();
            foreach($lists as $k=>$v){
                $lists[$k]['settle_status_new'] = $v['settle_status_detail'];
                $lists[$k]['overdue_days'] = '暂无逾期';
                $lists[$k]['clear_time'] = '';
                if($v['settle_status'] == 1){//如果是已还款
                    $lists[$k]['clear_time'] = '<li>结清时间：<span style="color:#4e4e4e;">'.date("Y-m-d H:i:s",$v['settle_time']).'</span></li>';
                    $days = round(($v['settle_time'] - $v['settle_end_time'])/3600/24);//四舍五入计算逾期天数
                }else{
                    $days = round((time() - $v['settle_end_time'])/3600/24);//如果还未结清账单，那么就按照当前时间计算逾期
                }
                if($days > 0){
                    $lists[$k]['overdue_days'] = '<span style="color:#fc1b1b;">已逾期'.$days . '天</span>';
                }
                $lists[$k]['settle_add_time'] = date("Y-m-d H:i:s",$v['settle_add_time']);
                $lists[$k]['settle_end_time'] = date("Y-m-d H:i:s",$v['settle_end_time']);
                $v['has_pay'] = $v['settle_total'] - $v['settle_amount'] - $v['settle_discount'];//已支付金额
                $v['settle_discount'] = $v['settle_discount'] > 0 ? '<li>折让金额：<span style="color:#4e4e4e;">￥'.$v['settle_discount'].'</span></li>' : '';
            }
            $this->ajaxReturn($lists);
        }
        return $this->fetch();
    }

    /**
     * 账单支付
     */
    public function settle_pay(){
        $settle_id = I('settle_id/d');
        $settle = M('settlement')->where(['settle_id' => $settle_id,'settle_status'=>['in','0,2'],'settle_amount'=>['gt',0],'user_id'=>USER_ID,'store_id'=>STORE_ID])->find();
        if (!$settle) {
            $this->error('账单不存在或已支付');
        }
        if($settle['settle_amount'] > $this->user['store_money']){
            $user_money = $this->user['store_money'];
        }else{
            $user_money = $settle['settle_amount'];
        }
        $this->assign('settle', $settle);
        $this->assign('user_money', $user_money);
        return $this->fetch();
    }
    /**
     * 余额支付
     */
    public function balance_pay(){
        $settle_id = I('settle_id/d');
        $password = I('password/d');
        $settle = M('settlement')->where(['settle_id' => $settle_id,'settle_status'=>['in','0,2'],'settle_amount'=>['gt',0],'user_id'=>USER_ID,'store_id'=>STORE_ID])->find();
        if (!$settle) {
            $this->ajaxReturn(['status'=>0,'msg'=>'账单不存在或已支付']);
        }
        if($settle['settle_amount'] > $this->user['store_money']){
            $user_money = $this->user['store_money'];
        }else{
            $user_money = $settle['settle_amount'];
        }
        if(!$user_money){
            $this->ajaxReturn(['status'=>0,'msg'=>'不好意思，您的可用余额为0']);
        }
        if(encrypt($password) != $this->user['paypwd']){
            $this->ajaxReturn(['status'=>0,'msg'=>'余额支付密码错误']);
        }
        if(!userStoreAccountLog($this->user['re_id'],STORE_ID,$user_money,'支付账单:'.$settle['settle_sn'])){
            $this->ajaxReturn(['status'=>0,'msg'=>'支付失败']);
        }
        if(!settlementPayLog($settle,$user_money,0,'用户余额抵扣账单金额')){
            $this->ajaxReturn(['status'=>0,'msg'=>'支付失败']);
        }
        $settle_amount = M('settlement')->where(['settle_id' => $settle_id])->getField('settle_amount');
        if($settle_amount > 0){
            $this->ajaxReturn(['status'=>1,'msg'=>'成功']);
        }else{
            $this->ajaxReturn(['status'=>2,'msg'=>'成功']);
        }
    }
    /**
     * 账单支付成功
     */
    public function pay_success(){
        $settle_id = I('settle_id');
        $settle = M('settlement')->where(['settle_id' => $settle_id,'settle_status'=>1,'settle_amount'=>0,'user_id'=>USER_ID,'store_id'=>STORE_ID])->find();
        if (!$settle) {
            $this->error('账单不存在');
        }
        $this->assign('settle', $settle);
        return $this->fetch();
    }
}