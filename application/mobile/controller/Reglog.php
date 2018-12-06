<?php

namespace app\mobile\controller;

use think\Controller;
use think\Db;
use think\Loader;
use think\Page;
use app\seller\logic\OfferLogic;
use app\mobile\controller\Api;

class Reglog extends Base
{
	
	function _initialize()
	{
        
    
   	}

	public function index()
	{
		$data = M('user_relation_store ')->where("user_id='1'")->getField('store_id');
		// $user = M('users')->where("mobile='18581245605'")->find();
	}

 	//登录
    public function login()
    {   
        return $this->fetch();
    }


    //登录方法
	public function do_login()
	{
        $username = I('post.username','','trim');
        $password = I('post.password','','trim');
        if (!$username || !$password){
            return array('status' => 0, 'msg' => '请填写账号或密码');
        }
        $user = M('users')->where("mobile='{$username}'")->find();


        if (!$user){
        	exit(json_encode(array('status' => -1, 'msg' => '账号不存在!')));
        } elseif (encrypt($password) != $user['password']) {
        	exit(json_encode(array('status' => -2, 'msg' => '密码错误!')));
        } elseif ($user['is_lock'] == 1) {
            exit(json_encode(array('status' => -3, 'msg' => '账号异常已被锁定！！！')));
        } else{
        	if(!$user['last_login_store']) {
	    		$relation = M('user_relation_store')->where("user_id='{$user['user_id']}'")->find();
	    		if(!$relation){
	    			exit(json_encode(array('status' => -4, 'msg' => '异常账号！！！')));
	    		}else{
		        	$data['last_login_store'] = $relation['store_id'];
		        	M('users')->where("user_id='{$user['user_id']}'")->save($data);
	    		}
	        }else{
	        	$where = [
	        		'user_id' => $user['user_id'],
	        		'store_id' => $user['last_login_store'],
	        	];
	        	$relation = M('user_relation_store')->where($where)->find();
	        	if(!$relation){
	    			exit(json_encode(array('status' => -4, 'msg' => '异常账号！！！')));
	    		}
	        }
	         $last_login['last_login'] = time();
	         M('users')->where("user_id='{$user['user_id']}'")->save($last_login);
        	$res = array('status' => 1, 'msg' => '登陆成功');
        }
        $user = array_merge($user,$relation);
        session('user', $user);
        exit(json_encode($res));
    }


    //注册
 	public function reg()
 	{
        
 		return $this->fetch();
 	}

 	//找回密码
 	public function retrieve()
 	{ 

 		return $this->fetch();
 	}

    public function ajax_retrieve()
    {
        $mobile = I('mobile');
        $code = I('code');
        $password = I('password');
        if(!$mobile || !$code)exit(json_encode(array('status' => -1, 'msg' => '手机或验证码不能为空!')));
        $api = new Api();
        $result = $api->validate_code($code);
        if($result['status'] == -1)exit(json_encode(array('status' => -1, 'msg' => $result['msg'])));
        $rcode = M('sms_log')->where(['mobile'=>$mobile,'status'=>1,'scene'=>1,'code'=>$code])->where('add_time','GT',time()-3000)->find();
         // if(!$rcode)exit(json_encode(array('status' => -1, 'msg' => '验证码错误')));
        if(!M('users')->where(['mobile'=>$mobile])->find())
        exit(json_encode(array('status' => -1, 'msg' => '账号对应的号码不存在')));
        $up =  M('users')->where(['mobile'=>$mobile])->update(['password'=>encrypt($password)]);
        if(!$up){
            exit(json_encode(array('status' => -1, 'msg' => '失败')));
        }
        $user = M('users')->where(['mobile'=>$mobile])->find();
        $relation = M('user_relation_store')->where(['user_id'=>$user['user_id'],'store_id'=>$user['last_login_store']])->find();
        $use = array_merge($user,$relation);
        session('user', $use);
        exit(json_encode(array('status' => 1, 'msg' => '成功')));

    }


    //安全退出
    public function out_login(){
        session("user",null);
        exit(json_encode(array('status' => 1, 'msg' => '成功退出!')));
    }

    //注册方法
    public function do_reg()
    {
        $data = I("");
        $username = $data['phoneNum'];
        $password = $data['upwd'];
        $invicode = $data['inviCode'];
        $idenCode = $data['idenCode'];
        if(!$username)exit(json_encode(array('status' => -1, 'msg' => '手机号码异常')));
        if(!$password)exit(json_encode(array('status' => -1, 'msg' => '密码异常')));
        if(!$invicode)exit(json_encode(array('status' => -1, 'msg' => '邀请码异常')));
        if(!$idenCode)exit(json_encode(array('status' => -1, 'msg' => '验证异常')));
        $store_id = M('store')->where(['invite_code'=>$invicode])->find();
        if(!$store_id)exit(json_encode(array('status' => -1, 'msg' => '不存在的此邀请码店铺')));
        $rcode = M('sms_log')->where(['mobile'=>$username,'status'=>1,'scene'=>1,'code'=>$idenCode])->where('add_time','GT',time()-3000)->find();
        // if(!$rcode)exit(json_encode(array('status' => -1, 'msg' => '验证码错误')));
        if(M('users')->where(['mobile'=>$username])->find())exit(json_encode(array('status' => -1, 'msg' => '手机号码已存在')));
        $user = [
            'mobile' => $username,
            'reg_time'=> time(),
            'password' => encrypt($password),
            'last_login_store'=> $store_id['store_id'],
        ];
        if(!M('users')->add($user))exit(json_encode(array('status' => -1, 'msg' => '注册失败')));
        $users = M('users')->where(['mobile'=>$username])->find();
        $store = [
            'user_id' => $users['user_id'],
            'store_id' => $store_id['store_id'],
            'add_time' => time(),
        ];
        M('user_relation_store')->add($store);
        $relation = M('user_relation_store')->where(['user_id'=>$users['user_id'],'store_id'=>$store_id['store_id']])->find();
        $use = array_merge($users,$relation);
        session('user', $use);
       exit(json_encode(array('status' => 1, 'msg' => '注册成功')));
    }



}