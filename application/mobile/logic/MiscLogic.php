<?php

namespace app\mobile\logic;

use think\Model;
use think\Db;
use app\common\logic\PriceLogic;

/**
 * 文字
 * Class MiscLogic
 * @package Mobile\Logic
 */
class MiscLogic extends Model
{

	public function report($limit)
	{
		$misc_list = M('article')->where(['store_id'=>STORE_ID])->limit($limit)->select();
		return $misc_list;
	}

}