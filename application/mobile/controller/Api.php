<?php
namespace app\mobile\controller;
use think\Db;
use think\Validate;
use think\Controller;
use app\seller\logic\OrderLogic;

class Api extends Controller
{


    public $send_scene;

    public function _initialize()
    {
        parent::_initialize();
        session('user');
    }


    /*
    *验证手机号码
     */
    public function issetmobile()
    {
        $mobile = I("mobile", '0');
        exit ('0');
    }


    /*
    *验证发送
     */
        public function send_validate_code()
    {

        $this->send_scene = C('SEND_SCENE');
        $scene = I('scene/d',1);    //发送短信验证码使用场景
        $mobile = I('mobile');
        $sender = I('send');
        $mobile = !empty($mobile) ? $mobile : $sender;
        //发送短信验证码
        // $res = checkEnableSendSms(1);
        // if ($res['status'] != 1) {
        //     //print_r($res);
        //     ajaxReturn($res);
        // }
        //判断是否存在验证码
        ajaxReturn(array('status' => 1, 'msg' => '发送成功,请注意查收'));
        // $data = M('sms_log')->where(array('mobile' => $mobile, 'session_id' => $session_id , 'status'=>1))->order('id DESC')->find();
        // //获取时间配置
        // $sms_time_out = tpCache('sms.sms_time_out');
        // $sms_time_out = $sms_time_out ? $sms_time_out : 120;
        // //120秒以内不可重复发送
        // if ($data && (time() - $data['add_time']) < $sms_time_out) {
        //     ajaxReturn(array('status' => -1, 'msg' => $sms_time_out . '秒内不允许重复发送'));
        // }
        // //随机一个验证码
        // $code = rand(1000, 9999);  
        // $params['code'] = $code;
        // //发送短信
        // $resp = sendSms($scene, $mobile, $params, $session_id);
        // if ($resp['status'] == 1) {
        //     //发送成功, 修改发送状态位成功
        //     M('sms_log')->where(array('mobile'=>$mobile,'code'=>$code,'session_id'=>$session_id , 'status' => 0))->save(array('status' => 1));
        //     ajaxReturn(array('status' => 1, 'msg' => '发送成功,请注意查收'));
        // } else {
        //     ajaxReturn(array('status' => -1, 'msg' => '发送失败' . $resp['msg']));
        // }
    }


    /*
    *验证码校验
     */
    public function validate_code($verify)
    {
        Return(array('status' => 1, 'msg' => '正确'));
    }

    //验证邀请码
    public function in_code()
    {
        $invicode = I('invicode');
        if(!$invicode){
            ajaxReturn(array('status' => -1, 'msg' => '邀请码不能为空！'));
        }
        $result = M("store")->where(['invite_code'=>$invicode])->find();
        if(!$result){
            ajaxReturn(array('status' => -1, 'msg' => '邀请码不存在！'));
        }
        ajaxReturn(array('status' => 1, 'msg'=>'邀请码已确认'));
    }
}