<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * Author: IT宇宙人
 * Date: 2015-09-09
 */
namespace app\common\model;

use think\Model;

class Settlement extends Model
{
    /**
     * 获取关联订单记录
     * @return \think\model\relation\HasMany
     */
    public function Order()
    {
        return $this->hasMany('Order', 'is_ligation', 'settle_id')->order('order_id desc');
    }

    /**
     * 结算单状态中文描述
     */
    public function getSettleStatusDetailAttr($value, $data)
    {
        switch ($data['settle_status']) {
            case 0:
                return '待支付';
                break;
            case 1:
                return '已支付';
                break;
            case 3:
                return '部分支付';
                break;
            default:
                return '待支付';
                break;
        }
    }

}