<?php
namespace app\mobile_admin\controller;

use think\Validate;

class Admin extends Base
{
    public function login()
    {
        if (session('?seller_id') && session('seller_id') > 0) {
            $this->error("您已登录", U('Index/index'));
        }
        if (request()->isPost()) {
            $loginData = I('post.');
            $rules = [
                'seller_name|用户名' => ['require', 'token', 'regex' => '/^1(3\d|4[57]|5[0-37-9]|66|7[367]|8[0235-9]|99)\d{8}$/'],
                'password|密码' => ['require', 'regex' => '/^\w{6,30}$/']
            ];
            $validate = new Validate($rules);
            if ($validate->check($loginData)) {
                $seller = M('seller')->where(array('seller_name' => $loginData['seller_name']))->find();
                if ($seller) {
                    $store = M('store')->where(array('store_id'=>$seller['store_id'],'store_state'=>1))->find();
                    if(!$store) exit(json_encode(array('status' => 0, 'msg' => '店铺已关闭，请联系平台客服')));
                    if($store['store_end_time']>0 && $store['store_end_time']<time()){
                        M('store')->where(array('store_id'=>$seller['store_id']))->save(array('store_state'=>0));
                        M('goods')->where(array('store_id'=>$seller['store_id']))->save(array('is_on_sale'=>0));
                        exit(json_encode(array('status' => 0, 'msg' => '店铺已到期自动关闭，请联系平台客服')));
                    }

                    $user_where = array('user_id' => $seller['user_id'],'password' => encrypt($loginData['password']));
                    $user = M('users')->where($user_where)->find();
                    if ($user) {
                        if ($seller['is_admin'] == 0 && $seller['enabled'] == 1) {
                            $this->msg(0,'该账户还没启用激活');
                        }
                        if ($seller['group_id'] > 0) {
                            $group = M('seller_group')->where(array('group_id' => $seller['group_id']))->find();
                            $seller['act_limits'] = $group['act_limits'];
                            $seller['smt_limits'] = $group['smt_limits'];
                        } else {
                            $seller['act_limits'] = 'all';
                            $seller['smt_limits'] = 'all';
                        }
                        session('seller', $seller);
                        session('seller_id', $seller['seller_id']);
                        session('store_id', $seller['store_id']);
                        M('seller')->where(array('seller_id' => $seller['seller_id']))->save(array('last_login_time' => time()));
                        sellerLog('商家管理中心登录');
                        $url = session('from_url') ? session('from_url') : U('Index/index');
                        $this->msg(1,$url);
                    } else {
                        $this->msg(0,'账号密码不正确');
                    }
                } else {
                    $this->msg(0,'账号不存在');
                }
            } else {
                //错误信息处理
                $this->msg(0,$validate->getError());
            }
        }
        return $this->fetch();
    }

}