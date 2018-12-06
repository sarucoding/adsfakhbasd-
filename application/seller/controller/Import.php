<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 商业用途务必到官方购买正版授权, 使用盗版将严厉追究您的法律责任。
 * 采用最新Thinkphp5助手函数特性实现单字母函数M D U等简写方式
 * ============================================================================
 * Author: 聂晓克     
 * Date: 2017-11-15
 */

namespace app\seller\controller;

use app\seller\logic\GoodsCategoryLogic;
use PHPExcel_IOFactory;
use PHPExcel_Cell;
use think\Db;
use think\Page;

//淘宝CSV导入功能
class Import extends Base{
	public function index(){
            header("Content-type: text/html; charset=utf-8");
exit("请联系TPshop官网客服购买高级版支持此功能");      
	}

	/*
	ajax返回平台商品分类 
	*/
	public function return_goods_category(){
		$parent_id = I('get.parent_id/d', '0'); //商品分类父id
        empty($parent_id) && exit('');

        $GoodsCategoryLogic = new GoodsCategoryLogic();
        $GoodsCategoryLogic->setStore($this->storeInfo);
        $list = $GoodsCategoryLogic->getStoreGoodsCategory($parent_id);

        foreach ($list as $k => $v) {
            $html .= "<option value='{$v['id']}' rel='{$v['commission']}'>{$v['name']}</option>";
        }
        exit($html);
	}

	//上传的csv文件及图片文件 返回数组结果
	public function upload_data(){
            header("Content-type: text/html; charset=utf-8");
exit("请联系TPshop官网客服购买高级版支持此功能");
	}

	//csv导入处理
	public function add_data(){
        header("Content-type: text/html; charset=utf-8");
exit("请联系TPshop官网客服购买高级版支持此功能");
	}

	/**
	 * csv文件转码为utf8
	 * @param  string 文件路径
	 * @return resource  打开文件后的资源类型
	 */
	private function fopen_utf8($filename){  
        $encoding='';  
        $handle = fopen($filename, 'r');  
        $bom = fread($handle, 2);   
        rewind($handle);  
       
        if($bom === chr(0xff).chr(0xfe)  || $bom === chr(0xfe).chr(0xff)){   
            $encoding = 'UTF-16';  
        } else {  
            $file_sample = fread($handle, 1000) + 'e';    
            rewind($handle);  
            $encoding = mb_detect_encoding($file_sample , 'UTF-8, UTF-7, ASCII, EUC-JP,SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP');  
        }  
        if ($encoding){  
            stream_filter_append($handle, 'convert.iconv.'.$encoding.'/UTF-8');  
        }  
        return ($handle);  
    } 

    //csv文件读取为数组形式返回
	private function str_getcsv($string, $delimiter=',', $enclosure='"'){ 
        $fp = fopen('php://temp/', 'r+');
        fputs($fp, $string);
        rewind($fp);
        while($t = fgetcsv($fp, strlen($string), $delimiter, $enclosure)) {
            $r[] = $t;
        }
        if(count($r) == 1) 
            return current($r);
        return $r;
    }

    //excel导入/导出首页
    public function excel_index(){
    	$res=M('store_goods_class')->where('store_id= '.STORE_ID.' and parent_id=0 and is_show=1')->select();

		$GoodsCategoryLogic = new GoodsCategoryLogic();
        $GoodsCategoryLogic->setStore($this->storeInfo);
        $goodsCategoryLevelOne = $GoodsCategoryLogic->getStoreGoodsCategory();

        $this->assign('goodsCategoryLevelOne',$goodsCategoryLevelOne);//平台商品分类
		$this->assign('store_goods_class_list', $res);//店铺商品分类
		$this->assign('store_id',STORE_ID);
    	return $this->fetch();
    }

    //excel导入处理
    public function excel_import(){	
    	$file=request()->file('excel');
    	$images=request()->file('images');
    	$form=I('post.');//表单数据

    	$path = 'public/upload/excel/';
		if (!file_exists($path)){
            mkdir($path);
        }

    	$result = $this->validate(	//验证excel文件
            ['file' => $file], 
            ['file'=>'fileSize:1500000|fileExt:xls,xlsx'],
            ['file.fileSize' => '上传excel文件过大','file.fileExt'=>'仅能上传excel文件']                    
        );

        $result2 = $this->validate(	//验证图片
            ['file' => $images], 
            ['file'=>'fileSize:600000|fileExt:jpg,png,jpeg'],
            ['file.fileSize' => '上传图片过大','file.fileExt'=>'仅能上传图片文件']                    
        );

        if (true !== $result ) {            
            $this->error($result, U('Seller/import/excel_index'));
        }
        if (true !== $result2 ) {            
            $this->error($result2, U('Seller/import/excel_index'));
        }

        if($file){
	        $info = $file->move($path);
	        if($info){
	        	//上传成功
		        $excel=$info->getSaveName();
	        }else{
	            //上传失败
	            $this->error($file->getError(), U('Seller/import/excel_index'));
	        }
	    }else{
	    	$this->error("导入excel文件失败", U('Seller/import/excel_index'));
	    }

	    $arrimg=array();
	    if($images){
		    foreach ($images as $k => $v){
				$res=$v->move($path,'');
				$arrimg[$k]=$res->getSaveName();
			}
	    }

	    //导入的excel数据处理开始
    	$excel=$path.$excel;
    	$arr=$this->importExcel($excel);//

    	//excel模板头数组
        $excel_model=array('编号','商品名','库存','市场价','本店价','成本价','简单描述','详细描述');
    	$excel_title=$arr[1];//excel头部标题部分
    	if($excel_title!==$excel_model){
    		$this->error('excel数据格式错误,请下载并参照excel模板',U('Seller/import/excel_index'));
    	}
    	unset($arr[1]);

    	$goods_sn=array();
    	foreach ($arr as $k => $v) {
    		if(!$v[3] || !(is_numeric(trim($v[3])))){	//判断市场价
    			$this->error('市场价不能为空且仅能设为数字',U('Seller/import/excel_index'));
    			break;
    		}
    		if(!$v[4] || !(is_numeric(trim($v[4])))){	//判断本店价
    			$this->error('本店价不能为空且仅能设为数字',U('Seller/import/excel_index'));
    			break;
    		}
    		if($v[2] && !(is_numeric(trim($v[2])))){
    			$this->error('库存仅能设为数字',U('Seller/import/excel_index'));
    			break;
    		}
    		if($v[0]){
    			$goods_sn[]=$v[0];	//记录填写了的货号
    		}
    	}

    	//判断导入的货号是否有重复
		if (count($goods_sn) != count(array_unique($goods_sn))) {   
		   $this->error("商品货号不能重复", U('Seller/import/excel_index'));
		}

		$store_goods_sn=Db::name('goods')->where('goods_sn !=""')->getField('goods_sn',true);

		$same=array_intersect($store_goods_sn,$goods_sn);
		if($same){
			$this->error('商品货号与商城已有商品存在重复',U('Seller/import/excel_index'));
		}

		foreach ($arrimg as $k => $v) {
			$tmp=substr(strrev($v),(strpos(strrev($v),".")+1),strlen($v));
			foreach ($arr as $k2 => $v2) {
				$tmp2=strrev($v2[0]);
				if(($tmp2) && ($tmp2==$tmp)){
					$arr[$k2][8]=$path.$v;
					continue;
				}
			}		
		}	


		//excel数据转换
		$param=array();
		foreach ($arr as $k => $v) {
			//tpshop数据字段 = excel数据字段
			$param[$k]['goods_sn']=$v[0];		//商品货号
			$param[$k]['goods_name']=$v[1];		//商品名称
			$param[$k]['cat_id1']=$form['goods_class_id1'];			//商品一级分类
			$param[$k]['cat_id2']=$form['goods_class_id2'];			//商品二级分类
			$param[$k]['cat_id3']=$form['goods_class_id3'];			//商品三级分类  
			$param[$k]['store_cat_id1']=$form['store_cat_id1'];		//本店一级分类    
			$param[$k]['store_cat_id2']=$form['store_cat_id2'];		//本店二级分类 
			$param[$k]['store_count']=$v[2];	//商品库存  
			$param[$k]['on_time']=time();		//商品上架时间
			$param[$k]['market_price']=$v[3];	//市场价
			$param[$k]['shop_price']=$v[4];		//本店价
			if($v[5]){
				$param[$k]['cost_price']=$v[5];	//成本价
			}
			$param[$k]['goods_remark']=$v[6];	//商品简单描述
			$param[$k]['goods_content']=$v[7];	//商品详细描述
			$param[$k]['store_id']=STORE_ID;	//店铺id
			$param[$k]['images']='/'.$v[8];                       
		} 

        //数据插入
        $add=0;
        foreach ($param as $k => $v) {
            if($v['images']){
                $v['original_img']=$v['images'];//没有主图时默认设为主图
            }
            $goods_id=M('goods')->add($v);//goods表插入主体数据
            if($goods_id){
                if($v['images']){
                    $res=M('goods_images')->add(array('goods_id'=>$goods_id,'image_url'=>$v['images']));//goods_image表插入商品图片
                }
            }else{
                $add+=1;//统计插入失败次数
                continue;//某次循环数据插入失败时跳出当前循环执行下一个
            }
        } 

        if($add==count($param)){
            $this->error("商品excel导入失败", U('Seller/import/index'));
        }else{
            $this->success("商品excel导入成功", U('Seller/Goods/goodsList'));
        }
    }

    public function importExcel($file){
	    require_once './vendor/PHPExcel/PHPExcel.php';
	    require_once './vendor/PHPExcel/PHPExcel/IOFactory.php';
	    require_once './vendor/PHPExcel/PHPExcel/Reader/Excel5.php';

	    $objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2007 for 2007 format
	    $objPHPExcel = $objReader->load($file);

	    $sheet = $objPHPExcel->getSheet(0);
	    $highestRow = $sheet->getHighestRow(); // 取得总行数
	    $highestColumn = $sheet->getHighestColumn(); // 取得总列数
	    $objWorksheet = $objPHPExcel->getActiveSheet();
	 
	    $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
	    $excelData = array();
	    for ($row = 1; $row <= $highestRow; $row++) {
	        for ($col = 0; $col < $highestColumnIndex; $col++) {
	            $excelData[$row][] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
	        }
	    }
	    return $excelData;
	}
}