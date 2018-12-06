<?php
namespace app\admin\validate;
use think\Validate;
class Spec extends Validate
{       
    // 验证规则
    protected $rule = [
        ['name','require|unique:spec,name^cat_id1','规格名称必须填写|规格名称不能重复'],
        //['name','require','规格名称必须填写'],
        ['cat_id1', 'require|gt:0', '所属分类必须选择'],
        //['cat_id3', 'require|gt:0', '所属分类必须选择|所属分类必须选择第三级'], //改为二级
        ['order','number','排序必须为数字'],
    ];
    protected $scene = [
        'edit'  =>  ['name','cat_id1','order'],
    ];
      
}