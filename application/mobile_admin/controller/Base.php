<?php
 namespace app\mobile_admin\controller;
 use think\Controller;
 class Base extends Controller{
  protected $beforeActionList=[
      'checklogin'=>['except'=>'login,logout']
  ];

  protected function checkLogin(){
   if(session('seller_id') <= 0){
      $this->redirect('Admin/login');
   }
  }
  protected function msg($status,$message){
   exit(json_encode(array('status' => $status, 'msg' => $message)));
  }
 }