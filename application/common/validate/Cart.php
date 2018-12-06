<?php
namespace app\common\validate;
use think\Validate;
use think\Db;
class Cart extends Validate
{
    // 验证规则
    protected $rule = [
        'shipping_code' => 'in:self,seller',
        'user_note'                 =>'checkUserNote',
    ];
    //错误信息
    protected $message  = [
    ];

    /**
     * 检查用户备注
     * @param $value|验证数据
     * @param $rule|验证规则
     * @param $data|全部数据
     * @return bool|string
     */
    protected function checkUserNote($value, $rule ,$data)
    {
        foreach($value as $k => $val){
            $note_len = strlen($val);
            if ($note_len > 50) {
                return '留言长度最多为50个字符！';
            }
        }
        return true;
    }

}