<?php
namespace app\mobile_admin\controller;
class Index extends Base{
    /**
     * 手机后台首页
     */

    public function index(){
        $seller = session('seller');
        $menu_list = getMenuList($seller['act_limits']);
        $count['waitsend_order'] = M('order')->where("store_id = " . STORE_ID . C('WAITSEND'))->count();//待发货订单




        return $this->fetch();
    }
}