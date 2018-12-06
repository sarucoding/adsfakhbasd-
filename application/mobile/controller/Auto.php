<?php
namespace app\mobile\controller;

use think\Db;
use think\Controller;

class Auto extends Controller
{
    /**
     * 自动生成结算单，由Linux的crontab定时任务每天执行一次
     */
    public function settlement()
    {
        if(!$this->checkIP()){//请求者必须是服务器自己
            exit('非法操作');
        }
        $settle_cycle = ['0'];
        if (date('w') == 1) {
            $settle_cycle[] = '1';
        }
        if (date('d') == 1) {
            $settle_cycle[] = '2';
        }
        $where = ['is_contract' => 1, 'settle_cycle' => ['in', $settle_cycle]];
        $user = M('user_relation_store')->where($where)->select();
        $time = strtotime(date('Y-m-d'));//时间必须小于计算当天凌晨0点，也就是取当天以前的订单
        foreach ($user as $k => $v) {
            $order_where = [
                'prom_type' => 1,
                'is_ligation' => 0,
                'add_time' => ['lt', $time],
                'pay_status' => 0,
                'order_status' => ['in', '2,3,5'],
                'user_id' => $v['user_id'],
                'store_id' => $v['store_id'],
            ];
            $lists = M('order')->where($order_where)->getField('order_id,order_amount');
            $settle_amount = array_sum($lists);//计算结算单金额
            if (count($lists) > 0) {
                $end_time = '';
                switch ($v['settle_cycle']) {
                    case 0:
                        $end_time = strtotime(date('Y-m-d', strtotime('+' . $v['settle_interval'] . ' day'))) - 1;
                        break;
                    case 1:
                        $end_time = strtotime(date('Y-m-d', strtotime('+' . $v['settle_interval'] . ' week'))) + $v['settle_day'] * 24 * 3600 - 1;
                        break;
                    case 2:
                        $end_time = strtotime(date('Y-m-d', strtotime('+' . $v['settle_interval'] . ' month'))) + $v['settle_day'] * 24 * 3600 - 1;
                }
                $order_ids = implode(',', array_keys($lists));
                $data = [
                    'settle_sn' => build_serial_number(),
                    'order_ids' => $order_ids,
                    'user_id' => $v['user_id'],
                    'store_id' => $v['store_id'],
                    'order_count' => count($lists),
                    'settle_amount' => $settle_amount,
                    'settle_total' => $settle_amount,
                    'settle_add_time' => time(),
                    'settle_end_time' => $end_time,
                ];
                $id = Db::name('settlement')->insertGetId($data);//添加结算单
                M('order')->where($order_where)->update(['is_ligation' => $id]);//更新订单
            }
        }
    }

    /**
     * 验证来访者ip和服务器ip是否相同
     * @return bool
     */
    function checkIP()
    {
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else $ip = "Unknow";
        $ch = curl_init('http://tool.huixiang360.com/zhanzhang/ipaddress.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $a  = curl_exec($ch);
        preg_match('/\[(.*)\]/', $a, $server_ip);
        return $ip == $server_ip[1];
    }
}