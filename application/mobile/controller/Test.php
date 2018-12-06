<?php
namespace app\mobile\controller;

use think\AjaxPage;
use think\Loader;
use think\Db;
use think\Page;
use app\admin\logic\UsersLogic;
use app\common\logic\UserRelationStoreLogic;
use app\mobile\logic\GoodsLogic;


class Test extends Base
{

    function _initialize()
    {
        parent::_initialize();

    }


    //商品首页
    public function search()
    {
        $keywords = I('keywords');
        require(ROOT_PATH."sphinxapi.php");
        $cl = new \SphinxClient ();
        $cl->SetServer('120.79.141.75', 9312);
        $cl->SetConnectTimeout(3);
        $cl->SetArrayResult(true);
        $cl->SetMatchMode(SPH_MATCH_ANY);
        $res = $cl->Query($keywords, "goods");
//dump($res['matches']);die;
//print_r($cl);
//$ids = array_keys($res['matches']);
        $ids = [];
        if(!$res['matches']){
            echo '无结果';die;
        }
        foreach ($res['matches'] as $k => $v) {
            $ids[] = $v['id'];
        }
        $ids = implode(',', $ids);
//print_r($res);
//高亮关键词字体设置颜色
        $opt = array(
            'before_match' => '<strong style="color:#faa001">',
            'after_match' => '</strong>',
        );

//连接mysql
        $list = M('goods')->where(['goods_id'=>['in',$ids]])->select();

        foreach($list as $k=>$v){
            $highlightTextList = $cl->buildExcerpts(
                array(
                    $v['goods_name'],
                    $v['goods_remark'],
                    $v['goods_sn'],
                ),
                'goods',
                $keywords,
                $opt
            );
            echo "id: {$v['goods_id']}<br />title: {$highlightTextList[0]}<br />content: {$highlightTextList[1]}<br />content: {$highlightTextList[2]}<br />";
            echo "<hr />";
        }
        //return $this->fetch();
    }


}