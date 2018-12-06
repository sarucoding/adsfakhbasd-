<?php
namespace app\common\logic;
use think\Model;
use think\Db;
/**
 *
 * Class UserRelationStoreLogic
 * @package common\Logic
 */
class UserRelationStoreLogic extends Model
{
    /**
     * 添加店铺关联
     */
    public function add_relation($data)
    {
        $user_count = M('user_relation_store')->where(['user_id'=>$data['user_id'],'store_id'=>$data['store_id']])->count();
        if ($user_count > 0) {
            return array('status' => -1, 'msg' => '该账号已关联过该店铺');
        }
        $data['add_time'] = time();
        $res = M('user_relation_store')->add($data);
        if($res){
            return ['status'=>1,'msg'=>'添加成功'];
        }else{
            return ['status'=>0,'msg'=>'添加失败'];
        }
    }

}