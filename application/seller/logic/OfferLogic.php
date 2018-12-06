<?php


namespace app\seller\logic;

use think\Model;
use think\Db;

class OfferLogic extends Model
{
    /**
     * @param $bill_id
     * @param $bill_status
     * 获取报价子商品列表并渲染标签
     */
    public function get_offer_goods($goods_ids,$user_id){
        if(strpos($goods_ids,'_') !== false){
            $goods_ids = str_replace('_',',',$goods_ids);
            $goods_ids = substr($goods_ids,0,strlen($goods_ids)-1);
            $goods_ids = substr($goods_ids,1,strlen($goods_ids));
        }
        $goods_lists = M('goods')->alias('a')->join([
            ['__SPEC_GOODS_PRICE__ b','a.goods_id = b.goods_id','LEFT'],
        ])->where(['a.goods_id'=>['in',$goods_ids]])->field('a.*,b.key_name,b.key,b.price')->select();
        $arr = [];
        foreach($goods_lists as $k=>$v){
            $arr[$v['goods_id']]['goods_id'] = $v['goods_id'];
            $arr[$v['goods_id']]['goods_name'] = $v['goods_name'];
            if(!$v['price']){//如果没有规格则使用商品显示价格
                $v['price'] = $v['shop_price'];
            }
            $arr[$v['goods_id']]['child'][] = $v;
        }
        $goods_html = '';
        foreach($arr as $key=>$val){
            $rowspan = count($val['child']);
            foreach($val['child'] as $k=>$v){
                $goods_html .= '<tr class="goods'.$v['goods_id'].'">';
                if($k == 0)
                    $goods_html .= '<td rowspan="'.$rowspan.'">'.$val['goods_name'].'</td>';
                $where = ['goods_id'=>$v['goods_id'],'user_id'=>$user_id,'store_id'=>STORE_ID];
                if($v['key']){
                    $where['spec_key'] = $v['key'];
                }
                if(!$v['price']){//如果没有规格则使用商品显示价格
                    $v['price'] = $v['shop_price'];
                }
                $offer = M('offer')->where($where)->order('offer_id desc')->find();
                $offer_price = $offer['offer_price'] ? ($offer['offer_type'] == 1 ? '浮动:' : '固定:').$offer['offer_price'] : '暂无';
                $goods_html .= '<td>'.$v['key_name'].'</td>';
                $goods_html .= '<td>'.$v['price'].'<input type="hidden" class="shop_price" value="'.$v['price'].'"></td>';
                $goods_html .= '<td align="center" valign="middle">'.$offer_price.'</td>';
                $goods_html .= '<td align="center" valign="middle" style="line-height: 30px;">' .
                    '<input style="width: 50px" type="text" name="offer_price[]" onkeyup="this.value=this.value.replace(/[^-\\d.]/g,\'\')" onpaste="this.value=this.value.replace(/[^-\\d.]/g,\'\')">' .
                    '<input value="'.$v['goods_id'].'" type="hidden" name="goods_id[]">' .
                    '<input value="'.$v['goods_name'].'" type="hidden" name="goods_name[]">' .
                    '<input value="'.$v['key_name'].'" type="hidden" name="spec_key_name[]">' .
                    '<input value="'.$v['key'].'" type="hidden" name="spec_key[]">' .
                    '</td>';
                if($k == 0)
                    $goods_html .= '<td rowspan="'.$rowspan.'" align="center"><i class="icon-trash" data-id="'.$v['goods_id'].'" onclick="del_goods(this)" style="cursor: pointer"></i></td>';
                $goods_html .= '</tr>';
            }

        }
        return ['status' => 1, 'msg' => '成功', 'result' => $goods_html];
    }

}