<?php
namespace app\seller\validate;

use think\Validate;
use think\Db;

class User extends Validate
{
    // 验证规则
    protected $rule = [
        'realname' => 'require',
        'mobile' => ['require','regex'=>'/1[345789]\d{9}$/'],
        'password' => 'require|length:6,16',
        'is_look' => 'require|in:0,1',
        'is_contract' => 'require|in:0,1',
        'settle_cycle' => 'in:0,1,2',
        'settle_interval' => 'integer',
    ];
    protected $scene = [
        'edit'  =>  ['is_look','is_contract','settle_cycle','settle_interval'],
    ];
    //错误信息
    protected $message = [
        'realname.require' => '客户姓名必填',
        'mobile.require' => '手机号必填',
        'mobile.regex' => '手机号格式错误',
        'password.require' => '登录密码必填',
        'password.length' => '登录密码为6-16位字母数字符号组合',
        'is_look.require' => '非法操作',
        'is_look.in' => '非法操作',
        'is_contract.require' => '非法操作',
        'is_contract.in' => '非法操作',
        'settle_cycle.in' => '非法操作',
        'settle_interval.integer' => '账期必须为正整数',
    ];


}