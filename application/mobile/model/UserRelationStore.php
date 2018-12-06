<?php
namespace app\seller\model;
use think\Model;
class UserRelationStore extends Model {
    /**
     * 通过re_id获取用户详细信息
     */
    public function get_user_info($re_id) {
        $info = $this->alias('a')->join('__USERS__ b','a.user_id = b.user_id')->where(['store_id'=>STORE_ID,'re_id'=>$re_id])->find();
        return $info;
    }
    /**
     * 通过user_id获取用户详细信息
     */
    public function get_user_info1($user_id) {
        $info = $this->alias('a')->join('__USERS__ b','a.user_id = b.user_id and b.user_id = '.$user_id)->where(['store_id'=>STORE_ID])->find();
        return $info;
    }
}
