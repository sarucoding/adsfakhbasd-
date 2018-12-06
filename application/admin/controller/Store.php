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
 * Author: 当燃      
 * Date: 2016-05-27
 */

namespace app\admin\controller;

use app\admin\logic\StoreLogic;
use app\common\logic\ModuleLogic;
use think\Page;
use think\Loader;
use think\Db;

class Store extends Base{
	
	//店铺等级
	public function store_grade(){
		$model =  M('store_grade');
		$count = $model->count();
		$Page = new Page($count,10);
		$list = $model->order('sg_id')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$show = $Page->show();
		$this->assign('pager',$Page);
		$this->assign('page',$show);
		return $this->fetch();
	}

	public function grade_info()
	{
		$sg_id = input('sg_id/d');
		if ($sg_id) {
			$info = Db::name('store_grade')->where('sg_id',$sg_id)->find();
			if(empty($info)){
				$this->error('没有找到相关的记录');
			}
            if($info['sg_act_limits'] != 'all') {
                $info['sg_act_limits'] = explode(',', $info['sg_act_limits']);
            }
			$this->assign('info', $info);
		}
		$right = Db::name('system_menu')->where(array('type' => 1))->order('id')->select();
        if(is_array($right)) {
            foreach ($right as $k => $val) {
                if (!empty($info) && $info['sg_act_limits'] != 'all') {
                    $val['enable'] = in_array($val['id'], $info['sg_act_limits']);
                }
                $modules[$val['group']][] = $val;
            }
        }else{
            $this->error('数据格式错误！！');
        }
		//权限组
        $moduleLogic = new ModuleLogic;
        $group = $moduleLogic->getPrivilege(1);
		$this->assign('group', $group);
		$this->assign('modules', $modules);
		return $this->fetch();
	}

	public function grade_info_save(){
		$data = I('post.');
		$data['sg_act_limits'] = $data['sg_allow_full'] == 1? 'all' : implode(',', $data['right']);
        if($data['act'] == 'del'){  //删除
            if(M('store')->where(array('grade_id'=>$data['del_id']))->count()>0){
                $this->ajaxReturn(['status'=>0,'msg'=>'该等级下有开通店铺，不得删除']);
            }else{
                $r = M('store_grade')->where("sg_id=".$data['del_id'])->delete();
                $this->ajaxReturn(['status'=>1,'msg'=>'删除成功']);
            }
        }else{
            //添加，编辑
            $res = M('store_grade')->where(['sg_name'=>$data['sg_name'],'sg_id'=>['neq',$data['sg_id']]])->count();
            $res>0 && $this->error('店铺等级名称已存在！！');
            if($data['sg_id'] > 0){
                $r = M('store_grade')->where("sg_id=".$data['sg_id'])->save($data);
            }else{
                $r = M('store_grade')->add($data);
            }

        }
		if($r){
			$this->success('编辑成功',U('Store/store_grade'));
		}else{
			$this->error('提交失败');
		}
	}
	
	public function store_class(){
		$count = Db::name('store_class')->count();
		$Page = new Page($count,10);
		$list = Db::name('store_class')->order('sc_sort desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$show = $Page->show();
		$this->assign('pager',$Page);
		$this->assign('page',$show);
		return $this->fetch();
	}
	
	//店铺分类
	public function class_info(){
		$sc_id = I('sc_id');
		if($sc_id){
			$info = M('store_class')->where("sc_id=$sc_id")->find();
			$this->assign('info',$info);
		}
		return $this->fetch();
	}
	
	public function class_info_save(){
		$data = I('post.');
		$storeClassValidate = Loader::validate('StoreClass');
		if($data['sc_id'] > 0){
			if($data['act']== 'del'){
				$storeClassCount = Db::name('store')->where('sc_id',$data['sc_id'])->count();
				if($storeClassCount > 0){
					$this->ajaxReturn(['status' => 0, 'msg' => '该分类下有开通店铺，不得删除', 'result' => '']);
				}else{
					$r = Db::name('store_class')->where("sc_id", $data['sc_id'])->delete();
					if($r !== false){
						$this->ajaxReturn(['status' => 1, 'msg' => '删除成功', 'result' => '']);
					}else{
						$this->ajaxReturn(['status' => 0, 'msg' => '删除失败', 'result' => '']);
					}
				}
			}else{
				if (!$storeClassValidate->batch()->check($data)) {
					$this->ajaxReturn(['status' => 0, 'msg' => '编辑失败', 'result' => $storeClassValidate->getError()]);
				}else{
					$r = Db::name('store_class')->where("sc_id", $data['sc_id'])->save($data);
					if($r !== false){
						$this->ajaxReturn(['status' => 1, 'msg' => '编辑成功', 'result' => '']);
					}else{
						$this->ajaxReturn(['status' => 0, 'msg' => '编辑失败', 'result' => '']);
					}
				}
			}
		}else{
			if (!$storeClassValidate->batch()->check($data)) {
				$this->ajaxReturn(['status' => 0, 'msg' => '添加失败', 'result' => $storeClassValidate->getError()]);
			}else{
				$r = Db::name('store_class')->add($data);
				if($r !== false){
					$this->ajaxReturn(['status' => 1, 'msg' => '添加成功', 'result' => '']);
				}else{
					$this->ajaxReturn(['status' => 0, 'msg' => '添加失败', 'result' => '']);
				}
			}
		}
	}
	
	//普通店铺列表
	public function store_list(){
        $map['is_own_shop'] = 0 ;
        $map['deleted'] = 0 ;
		$grade_id = I("grade_id");
		if($grade_id>0) $map['grade_id'] = $grade_id;
		$sc_id =I('sc_id');
		if($sc_id>0) $map['sc_id'] = $sc_id;
		$store_state = I("store_state");
        switch ($store_state){
            case '': '';break;
            case 3:
                $map['store_end_time'] = ['between',[time(),strtotime('+1 month')]];
                break;  //即将过期（1个月）
            case 4:
                $map['store_end_time'] = ['between',1,time()];
                break;  //已过期
            case 5:
                $map['deleted'] = 1;
                break;  //已过期
            default : $map['store_state'] = $store_state;break;
        }
		$seller_name = I('seller_name');
		if($seller_name) $map['seller_name'] = array('like',"%$seller_name%");
		$store_name = I('store_name');
		if($store_name) $map['store_name'] = array('like',"%$store_name%");
		$count = Db::name('store')->where($map)->count();
		$Page = new Page($count,10);
		$list = Db::name('store')->where($map)->order('store_id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$store_ids = get_arr_column($list, 'store_id');
		$store_ids = implode(",",$store_ids);
		$sellers = M('seller')->where('store_id' , 'in' , $store_ids)->where('is_admin','1')->column('store_id,seller_name');
		$this->assign('list',$list);
		$this->assign('sellers',$sellers);
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('pager',$Page);
		$store_grade = M('store_grade')->getField('sg_id,sg_name');
		$this->assign('store_grade',$store_grade);
		$this->assign('store_class',M('store_class')->getField('sc_id,sc_name',true));
		return $this->fetch();
	}
	
	/*添加店铺*/
	public function store_add(){
        $is_own_shop = I('is_own_shop',1);
		if(IS_POST){
			$store_name = trim(I('store_name'));
			$user_name = trim(I('user_name'));
			$seller_name = trim(I('seller_name'));
            $password = trim(I('password'));
            if(strlen($password) < 6){
                $this->ajaxReturn(['status'=>0,'msg'=>'登陆密码不能为空且不能少于6个字符']);
            }
			if(M('store')->where("store_name='$store_name'")->count()>0){
				$this->ajaxReturn(['status'=>0,'msg'=>'店铺名称已存在']);
			}
			if(M('seller')->where("seller_name='$seller_name'")->count()>0){
                $this->ajaxReturn(['status'=>0,'msg'=>'此会员已被占用']);
			}
			$user_id = M('users')->where("email='$user_name' or mobile='$user_name'")->getField('user_id');
			if($user_id){
				if(M('store')->where(array('user_id'=>$user_id))->count()>0){
                    $this->ajaxReturn(['status'=>0,'msg'=>'该会员已经申请开通过店铺']);
				}
			}else{   //没有这个用户就创建一个
                if(check_email($user_name)){
                    $user_data['email'] = $user_name;
                }else{
                    $user_data['mobile'] = $user_name;
                };
                $user_data['password'] = $password;
                $user_obj = new \app\admin\logic\UsersLogic();
                $add_user_res = $user_obj->addUser($user_data);
                if($add_user_res['status'] !=1) $this->ajaxReturn($add_user_res);
                $user_id = $add_user_res['user_id'];
            }
			$store = array('store_name'=>$store_name,
				'user_id'       =>  $user_id,	
                'user_name'     =>  $user_name,
                'store_state'   =>  1,
				'seller_name'   =>  $seller_name,
                'password'      =>  I('password'),
				'store_time'    =>  time(),
                'is_own_shop'   =>  $is_own_shop
			);
			$storeLogic = new StoreLogic();
			if($storeLogic->addStore($store)){
				if($is_own_shop == 1){
                    $this->ajaxReturn(['status'=>1,'msg'=>'店铺添加成功','url'=>U('Store/store_own_list')]);
				}else{
                    $this->ajaxReturn(['status'=>1,'msg'=>'店铺添加成功','url'=>U('Store/store_list')]);
				}
				exit;
			}else{
                $this->ajaxReturn(['status'=>0,'msg'=>'店铺添加失败']);
			}
		}
		$this->assign('is_own_shop',$is_own_shop);
		return $this->fetch();
	}
	
	/*验证店铺名称，店铺登陆账号*/
	public function store_check(){
		$store_name = I('store_name');
		$seller_name = I('seller_name');
		$user_name = I('user_name');
		$res = array('status'=>1);
		if($store_name && M('store')->where("store_name='$store_name'")->count()>0){
			$res = array('status'=>-1,'msg'=>'店铺名称已存在');
		}
		
		if(!empty($user_name)){
			if(!check_email($user_name) && !check_mobile($user_name)){
				$res = array('status'=>-1,'msg'=>'店主账号格式有误');
			}
			if(M('users')->where("email='$user_name' or mobile='$user_name'")->count()>0){
				$res = array('status'=>-1,'msg'=>'会员名称已被占用');
			}
		}

		if($seller_name && M('seller')->where("seller_name='$seller_name'")->count()>0){
			$res = array('status'=>-1,'msg'=>'此账号名称已被占用');
		}
		respose($res);
	}
	
	/*编辑自营店铺*/
	public function store_edit(){
		if(IS_POST){
			$data = I('post.');
			if(M('store')->where("store_id=".$data['store_id'])->save($data)){
				$this->success('编辑店铺成功',U('Store/store_own_list'));
				exit;
			}else{
				$this->error('编辑失败');
			}
		}
		$store_id = I('store_id',0);
		$store = M('store')->where("store_id=$store_id")->find();
		$this->assign('store',$store);
		return $this->fetch();
	}
	
	//编辑外驻店铺
	public function store_info_edit(){
		if(IS_POST){
			$map = I('post.');
			$store = $map['store'];
            $store['deposit'] = $map['store']['deposit'];
			unset($map['store']);
			$store['province_id'] = $map['company_province']; //省
			$store['city_id'] = $map['company_city']; //市
			$store['district'] = $map['company_district'];  //区
			$store['company_name'] = $map['company_name'];
			$store['store_phone'] = $map['store_person_mobile'];
			$store['store_address'] = $map['company_address'];
			$store['store_address'] = $map['company_address'];
			$store['store_end_time'] = strtotime($map['store_end_time']);
			$store['store_qq'] = $map['store_person_qq'];
			$map['business_permanent'] == 'Y' && $map['business_date_end'] = '长期';
            if($store['city_id']<1 || $store['district']<1){
                $this->error('请填写完整的公司所在地');
            }
			$save_store = M('store')->where(array('store_id'=>$map['store_id']))->save($store);
            $select_apply = M('store_apply')->where(array('user_id'=>$store['user_id']))->find();
            if($select_apply){
                //fix: 管理员后台编辑店铺分类, 商家店铺分类没同时更新 @{
                $map['sc_id'] = $store['sc_id'];
                $store_class = Db::name('store_class')->where("sc_id", $map['sc_id'])->find();
                $map['sc_name'] = $store_class['sc_name']; 
                // @}
                $save_apply = M('store_apply')->where(array('user_id'=>$store['user_id']))->save($map);
            }else{
                $map['user_id']=$store['user_id'];
                $save_apply = M('store_apply')->add($map);
            }
			if($save_store===false && $save_apply===false){
                $this->error('编辑失败');
			}else{
                if($store['store_state'] == 0){
                    M('goods')->where(array('store_id'=>$map['store_id']))->save(array('is_on_sale'=>0));//关闭店铺，同时下架店铺所有商品
                }
                $backuUrl = I('get.is_own_shop') == 1 ? U('Store/store_own_list') : U('Store/store_list');  //自营店铺页
                    $this->success('编辑店铺成功',$backuUrl);
                exit;
			}
		}
		$store_id = I('store_id');
		if($store_id>0){
			$store = M('store')->where("store_id=$store_id")->find();
			$this->assign('store',$store);
			$apply = M('store_apply')->where('user_id='.$store['user_id'])->find();
			$bank_province_name = M('region')->where(array('id'=>$apply['bank_province']))->getField('name');
			$bank_city_name = M('region')->where(array('id'=>$apply['bank_city']))->getField('name');
			$this->assign('bank_province_name',$bank_province_name);
			$this->assign('bank_city_name',$bank_city_name);
			$this->assign('apply',$apply);
            $city = M('region')->where(array('parent_id'=>$apply['company_province'],'level'=>2))->select();
            $area = M('region')->where(array('parent_id'=>$apply['company_city'],'level'=>3))->select();
            $this->assign('city',$city);
            $this->assign('area',$area);
		}
		$company_type = config('company_type');
		$rate_list = config('rate_list');
		$this->assign('rate_list', $rate_list);
		$this->assign('company_type', $company_type);
		$this->assign('store_grade',M('store_grade')->getField('sg_id,sg_name'));
		$this->assign('store_class',M('store_class')->getField('sc_id,sc_name'));
		$province = M('region')->where(array('parent_id'=>0,'level'=>1))->select();
		$this->assign('province',$province);
		return $this->fetch();
	}
	
	/*删除店铺*/
	public function store_del(){
		$store_id = I('del_id');
		if($store_id > 1){
			$store = Db::name('store')->where("store_id", $store_id)->find();
			$storeGoodsCount = Db::name('goods')->where("store_id", $store_id)->count();
			if ($storeGoodsCount > 0) {
				$this->ajaxReturn(['status' => 0, 'msg' => '该店铺有发布商品，不得删除', 'result' => '']);
			}else{
				Db::name('store')->where("store_id", $store_id)->save(['deleted'=>1,'store_state'=>0]);
				Db::name('seller')->where("store_id", $store_id)->save(['enabled'=>1]);
//				Db::name('users')->where("user_id", $store['user_id'])->delete();
				Db::name('store_apply')->where("user_id", $store['user_id'])->save(['apply_state'=>2]);
				adminLog("删除店铺".$store['store_name']);
				$this->ajaxReturn(['status' => 1, 'msg' => '删除店铺'.$store['store_name'].'成功', 'result' => '']);
			}
		}else{
			$this->ajaxReturn(['status' => 0, 'msg' => '基础自营店，不得删除', 'result' => '']);
		}
	}
	
	//店铺信息
	public function store_info(){
		$store_id = I('store_id');
		$store = M('store')->where("store_id=".$store_id)->find();
		$this->assign('store',$store);
		$store_grade = M('store_grade')->where(array('sg_id'=>$store['grade_id']))->find();
		$this->assign('store_grade',$store_grade);
		$apply = M('store_apply')->where("user_id=".$store['user_id'])->find();
		$province_name = M('region')->where(array('id'=>$apply['company_province']))->getField('name');
		$city_name = M('region')->where(array('id'=>$apply['company_city']))->getField('name');
		$district_name = M('region')->where(array('id'=>$apply['company_district']))->getField('name');
		$bank_province_name = M('region')->where(array('id'=>$apply['bank_province']))->getField('name');
		$bank_city_name = M('region')->where(array('id'=>$apply['bank_city']))->getField('name');
		$this->assign('bank_province_name',$bank_province_name);
		$this->assign('bank_city_name',$bank_city_name);
		$this->assign('province_name',$province_name);
		$this->assign('city_name',$city_name);
		$this->assign('district_name',$district_name);
		$this->assign('apply',$apply);
		$bind_class_list = M('store_bind_class')->where("store_id=".$store_id)->select();
		$goods_class = M('goods_category')->getField('id,name');
		for($i = 0, $j = count($bind_class_list); $i < $j; $i++) {
			$bind_class_list[$i]['class_1_name'] = $goods_class[$bind_class_list[$i]['class_1']];
			$bind_class_list[$i]['class_2_name'] = $goods_class[$bind_class_list[$i]['class_2']];
			$bind_class_list[$i]['class_3_name'] = $goods_class[$bind_class_list[$i]['class_3']];
		}
		$this->assign('bind_class_list',$bind_class_list);
		return $this->fetch();
	}
	
	//自营店铺列表
	public function store_own_list(){

		$model =  M('store');
		$map['is_own_shop'] = 1 ;
		$grade_id = I("grade_id");
		if($grade_id>0) $map['grade_id'] = $grade_id;
		$sc_id =I('sc_id');
		if($sc_id>0) $map['sc_id'] = $sc_id;
		$store_state = I("store_state");
        switch ($store_state){
            case '': '';break;
            case 3:
                $map['store_end_time'] = ['between',[time(),strtotime('+1 month')]];
                break;  //即将过期（1个月）
            case 4:
                $map['store_end_time'] = ['between',1,time()];
                break;  //已过期
            default : $map['store_state'] = $store_state;break;
        }
		$seller_name = I('seller_name');
		if($seller_name) $map['seller_name'] = array('like',"%$seller_name%");
		$store_name = I('store_name');
		if($store_name) $map['store_name'] = array('like',"%$store_name%");
		$count = $model->where($map)->count();
		$Page = new Page($count,10);
		$list = $model->where($map)->order('store_id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);	
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('pager',$Page);
		$store_grade = M('store_grade')->getField('sg_id,sg_name');
		$this->assign('store_grade',$store_grade);
		$this->assign('store_class',M('store_class')->getField('sc_id,sc_name'));
		return $this->fetch();
	}
	
	//店铺申请列表
	public function apply_list(){
		$model =  M('store_apply');
		$map['apply_state'] = array('neq',1);
		$map['add_time'] = array('gt',0); //填写完成的才显示
		$sg_id = I("sg_id");
		if($sg_id>0) $map['sg_id'] = $sg_id;
		$sc_id =I('sc_id');
		if($sc_id>0) $map['sc_id'] = $sc_id;
		$seller_name = I('seller_name');
		$seller_name && $map['seller_name'] = array('like',"%$seller_name%");
		$store_name = I('store_name');
		$store_name && $map['store_name'] = array('like',"%$store_name%");
		$count = $model->where($map)->count();
		$Page = new Page($count,10);
		$list = $model->where($map)->order('add_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$show = $Page->show();
		$this->assign('pager',$Page);
		$this->assign('page',$show);
		$this->assign('store_grade',M('store_grade')->getField('sg_id,sg_name'));
		$this->assign('store_class',M('store_class')->getField('sc_id,sc_name'));
		return $this->fetch();
	}
	
	public function apply_del(){
		$id = I('del_id');
		if($id && M('store_apply')->where(array('id'=>$id))->delete()){
//			$this->success('操作成功',U('Store/apply_list'));
            $this->ajaxReturn(['status'=>1,'操作成功']);
		}else{
//			$this->error('操作失败');
            $this->ajaxReturn(['status'=>0,'操作失败']);
		}
	}
	//经营类目申请列表
	public function apply_class_list(){
		$state = I('state'); //审核状态
        $store_name = I('store_name');  ///店铺名称
        if($store_name) {
            $store_where['store_name'] = $store_name;  ///店铺名称
            $bind_class_where['store_id'] = Db::name('store')->where($store_where)->getField('store_id');
        }
        $state>=0 && $bind_class_where['state'] = $state;
        $count = Db::name('store_bind_class')->where($bind_class_where)->count();
        $Page = new Page($count,20);
        $bind_class = Db::name('store_bind_class')
            ->where($bind_class_where)
            ->order('bid desc')
            ->limit($Page->firstRow,$Page->listRows)
            ->select();
		$goods_class = Db::name('goods_category')->cache(true)->getField('id,name');
		for($i = 0, $j = count($bind_class); $i < $j; $i++) {
			$bind_class[$i]['class_1_name'] = $goods_class[$bind_class[$i]['class_1']];
			$bind_class[$i]['class_2_name'] = $goods_class[$bind_class[$i]['class_2']];
			$bind_class[$i]['class_3_name'] = $goods_class[$bind_class[$i]['class_3']];
			$store = Db::name('store')->where("store_id=".$bind_class[$i]['store_id'])->find();
			$bind_class[$i]['store_name'] = $store['store_name'];
			$bind_class[$i]['seller_name'] = $store['seller_name'];
		}
		$this->assign('bind_class',$bind_class);
		$this->assign('page',$Page);
		return $this->fetch();
	}
	
	//查看-添加店铺经营类目
	public function store_class_info(){
		$store_id = I('store_id');
		$store = M('store')->where(array('store_id'=>$store_id))->find();
		$this->assign('store',$store);
		if(IS_POST){
			$data = I('post.');
			$data['state'] = 1;
			$where = 'class_3 ='.$data['class_3'].' and store_id='.$data['store_id'];
			if(M('store_bind_class')->where($where)->count()>0){
				$this->ajaxReturn(['status'=>-1,'msg'=>'该店铺已申请过此类目']);
			}
            if($data['class_1']==0 || $data['class_3']==0 || $data['class_3']==0){
                $this->ajaxReturn(['status'=>-1,'msg'=>'必须选择第三级类目']);
            }
			$data['commis_rate'] = M('goods_category')->where(array('id'=>$data['class_3']))->getField('commission');
			if(M('store_bind_class')->add($data)){
				adminLog('添加店铺经营类目，类目编号:'.$data['class_3'].',店铺编号:'.$data['store_id']);
				$this->ajaxReturn(['status'=>1,'msg'=>'添加经营类目成功']);exit;
			}else{
				$this->ajaxReturn(['status'=>-1,'msg'=>'操作失败']);
			}
		}
		$bind_class_list = M('store_bind_class')->where('store_id='.$store_id)->select();
		$goods_class = M('goods_category')->getField('id,name');
		for($i = 0, $j = count($bind_class_list); $i < $j; $i++) {
			$bind_class_list[$i]['class_1_name'] = $goods_class[$bind_class_list[$i]['class_1']];
			$bind_class_list[$i]['class_2_name'] = $goods_class[$bind_class_list[$i]['class_2']];
			$bind_class_list[$i]['class_3_name'] = $goods_class[$bind_class_list[$i]['class_3']];
		}
		$this->assign('bind_class_list',$bind_class_list);
		$cat_list = M('goods_category')->where("parent_id = 0")->select();
		$this->assign('cat_list',$cat_list);
		return $this->fetch();
	}
	
	
	public function apply_class_save(){
		$data = I('post.');
		if($data['act']== 'del'){
			$r = M('store_bind_class')->where("bid=".$data['del_id'])->delete();
			respose(1);
		}else{
			$data = I('get.');
			$r = M('store_bind_class')->where("bid=".$data['bid'])->save(array('state'=>$data['state']));
		}
		if($r){
			$this->success('操作成功',U('Store/apply_class_list'));
		}else{
			$this->error('提交失败');
		}
	}
	
	//店铺申请信息详情
	public function apply_info(){
		$id = I('id');
		$apply = M('store_apply')->where("id=$id")->find();
		$province_name = M('region')->where(array('id'=>$apply['company_province']))->getField('name');
		$city_name = M('region')->where(array('id'=>$apply['company_city']))->getField('name');
		$district_name = M('region')->where(array('id'=>$apply['company_district']))->getField('name');
		$this->assign('province_name',$province_name);
		$this->assign('city_name',$city_name);
		$this->assign('district_name',$district_name);
		$bank_province_name = M('region')->where(array('id'=>$apply['bank_province']))->getField('name');
		$bank_city_name = M('region')->where(array('id'=>$apply['bank_city']))->getField('name');
		$this->assign('bank_province_name',$bank_province_name);
		$this->assign('bank_city_name',$bank_city_name);
		$goods_cates = M('goods_category')->getField('id,name,commission');
		if(!empty($apply['store_class_ids'])){
			$store_class_ids = unserialize($apply['store_class_ids']);
            if(!is_array($store_class_ids)){ $this->error('经营类目数据格式错误！！');};
			foreach ($store_class_ids as $val){
				$cat = explode(',', $val);
				$bind_class_list[] = array(
						'class_1'=>$goods_cates[$cat[0]]['name'],
						'class_2'=>$goods_cates[$cat[1]]['name'],
						'class_3'=>$goods_cates[$cat[2]]['name'],
						'commis_rate'=>$goods_cates[$cat[2]]['commission'],
						'value'=>$val,
				);
			}
			$this->assign('bind_class_list',$bind_class_list);
		}
		$company_type = config('company_type');
		$this->assign('company_type', $company_type);
		$this->assign('apply',$apply);

		$apply_log = M('admin_log')->where(array('log_type'=>1))->select();
		$this->assign('apply_log',$apply_log);
		$this->assign('store_grade',M('store_grade')->select());
		$this->assign('store_class',M('store_class')->select());
		return $this->fetch();
	}
	
	//审核店铺开通申请
	public function review(){
		$data = I('post.');
		if($data['id']){
			$apply = M('store_apply')->where(array('id'=>$data['id']))->find();
			if(empty($apply['store_name'])){
				$this->error('店铺名称不能为空.');
			}
			if($apply && M('store_apply')->where("id=".$data['id'])->save($data)){				
				if($data['apply_state'] == 1){ //同意开店 审核通过
					$users = M('users')->where(array('user_id'=>$apply['user_id']))->find();
					if(empty($users)) $this->error('申请会员不存在或已被删除，请检查');
					$time = time();$store_end_time = $time+24*3600*365*$data['store_end_time'];//开店时长
					$store = array('user_id'=>$apply['user_id'],'seller_name'=>$apply['seller_name'],
							'user_name'=>empty($users['email']) ? $users['mobile'] : $users['email'],
							'grade_id'=>empty($data['sg_id']) ? 1 : $data['sg_id'],'store_name'=>$apply['store_name'],'sc_id'=>$apply['sc_id'],
							'company_name'=>$apply['company_name'],'store_phone'=>$apply['store_person_mobile'],
							'store_address'=>empty($apply['store_address']) ? '待完善' : $apply['store_address'] ,
							'store_time'=>$time,'store_state'=>1,'store_qq'=>$apply['store_person_qq'],
							'store_end_time'=>$store_end_time,'province_id'=>$apply['company_province'],
							'city_id'=>$apply['company_city'],'district'=>$apply['company_district']							
					);
					$store_id = M('store')->add($store);//通过审核开通店铺
					if($store_id){
						$seller = array('seller_name'=>$apply['seller_name'],'user_id'=>$apply['user_id'],
							'group_id'=>0,'store_id'=>$store_id,'is_admin'=>1,'add_time'=>time()
						);
						M('seller')->add($seller);//点击店铺管理员
						//绑定商家申请类目
						if(!empty($apply['store_class_ids'])){
							$goods_cates = M('goods_category')->where(array('level'=>3))->getField('id,name,commission');
							$store_class_ids = unserialize($apply['store_class_ids']);
                            if(!is_array($store_class_ids)){ $this->error('经营类目数据格式错误！！');};
							foreach ($store_class_ids as $val){
								$cat = explode(',', $val);
								$bind_class = array('store_id'=>$store_id,'commis_rate'=>$goods_cates[$cat[2]]['commission'],
										'class_1'=>$cat[0],'class_2'=>$cat[1],'class_3'=>$cat[2],'state'=>1);
								M('store_bind_class')->add($bind_class);
							}
						}
					}
					adminLog($apply['store_name'].'开店申请审核通过',1);

					//审核通过  调用iM接口 添加店铺与企业管理人
					$kf_config['im_choose'] =  tpCache('basic.im_choose');
					$kf_config['im_website'] = tpCache('basic.im_website');
					if($kf_config['im_choose'] == 1 && !empty($kf_config['im_website'])) {
						$url = $kf_config['im_website'] . '/index/api/apiStoreAdd';
						$params = [
								'store' => [
										'store_id' => $store_id,
										'store_name' => $apply['store_name'],
										'avatar' => '',
										'web_id' => $apply['company_website'],
										'phone' => $apply['store_person_mobile'],
										'email' => $apply['store_person_email'],
										'city' => $apply['store_address'],
								],
								'boss' => [
										'username' => $apply['seller_name'],
										'password' => '123456',
								]
						];
						$params = http_build_query($params);
						$this->sendToImStore($url, $params);
					}

				}else if($data['apply_state'] == 2){  //审核不通过
					adminLog($apply['store_name'].'开店申请审核未通过，备注信息：'.$data['review_msg'],1);
				}
				$this->success('操作成功',U('Store/store_list'));
			}else{
				$this->error('提交失败',U('Store/apply_list'));
			}
		}
	}

	//向im 发送数据
	public function sendToImStore($url,$data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url); 			//定义表单提交地址
		curl_setopt($ch, CURLOPT_POST, true);   		//定义提交类型 1：POST ；0：GET
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_HEADER, 0); 			//定义是否显示状态头 1：显示 ； 0：不显示
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	//定义是否直接输出返回流
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 	//定义提交的数据，这里是XML文件
		$res = curl_exec($ch);
		curl_close($ch);
		return $res;
	}


    /**
     * 签约申请
     * @return mixed
     */
	public function reopen_list()
	{
        $reopen_where=[];
        if (IS_POST){
            $seller_name = input('seller_name'); //店主名称
            $store_name = input('store_name');  //店铺名称
            $sc_id = input('sc_id');       //店铺类别
            $grade_id = input('grade_id');       //店铺等级
            $seller_name ? $where['seller_name'] = ['like',"%$seller_name%"]: false; //店主名称
            $sc_id ? $where['sc_id'] = $sc_id : false;       //店铺类别
            $grade_id ? $reopen_where['re_grade_id'] = $grade_id : false;       //店铺等级
            $store_id_arr = Db::name('store')->where($where)->getField('store_id',true);  //查找店铺ID
            $store_ids = implode(',',$store_id_arr);
            $reopen_where['re_store_id'] = ['in',$store_ids];   //店铺id
            $reopen_where['re_store_name'] = ['like',"%$store_name%"];  //店铺名称
        }
        $StoreReopenModel =new  \app\common\model\StoreReopen();
		$count = $StoreReopenModel->where($reopen_where)->count();
        $page = new Page($count,20);
        $StoreReopenObj = $StoreReopenModel->where($reopen_where)->order('re_id desc')->limit($page->listRows,$page->firstRow)->select();
        if($StoreReopenObj){
            $list = collection($StoreReopenObj)->append(['reopen_state'])->toArray();
        }
        $store_grade = Db::name('store_grade')->getField('sg_id,sg_name');
        $store_class = Db::name('store_class')->getField('sc_id,sc_name');
		$this->assign('store_grade', $store_grade);
		$this->assign('store_class', $store_class);
		$this->assign('list', $list);
		$this->assign('page', $page);
		return $this->fetch();
	}

    /**
     * @return mixed
     */
    public function reopen_info(){
        $StoreReopenModel =new  \app\common\model\StoreReopen();
        $save_where = ['re_id'=>input('re_id/d')];
        $reopen = $StoreReopenModel::get($save_where);
        $data = $reopen->append(['reopen_state'])->toArray();
        $this->assign('reopen',$data);
        return $this->fetch();
    }

    /**
     * 审核签约申请
     */
    public function reopen_save(){
        $StoreReopenModel =new  \app\common\model\StoreReopen();
        $save_where = ['re_id'=>input('re_id/d')];
        $re_state = input('re_state/d');
        $admin_note = input('admin_note');
        $reopen = $StoreReopenModel::get($save_where);
        if ($reopen){
            if ($re_state == 2){
                Db::name('store')->where(['store_id'=>$reopen['re_store_id']])->update(['store_end_time'=>$reopen['re_end_time'],'grade_id'=>$reopen['re_grade_id']]);
            }
            $res = $StoreReopenModel->where($save_where)->save(['re_state'=>$re_state,'admin_note'=>$admin_note]);
            if ($res !== false){
                $this->ajaxReturn(['status'=>1,'msg'=>'操作成功！','url'=>U('Store/reopen_list')]);
            }
            $this->ajaxReturn(['status'=>-1,'msg'=>'操作失败！']);
        }else{
            $this->ajaxReturn(['status'=>0,'msg'=>'非法操作！']);
        }
    }

    /**
     * 删除签约申请
     */
    public  function  reopen_del(){
        $StoreReopenModel =new  \app\common\model\StoreReopen();
        $del_where = ['re_id'=>input('re_id/d',0)];
        $res = $StoreReopenModel->where($del_where)->delete();
        if ($res === false)$this->ajaxReturn(['status'=>-1,'msg'=>'删除失败']);
        $this->ajaxReturn(['status'=>1,'msg'=>'删除成功']);
    }

	public function domain_list(){
		$model =  M('store');
		$map['store_state'] = 1;
		$store_domian = I("store_domain");
		if($store_domian) {
			$map['store_domian'] = array('like',"%$store_domian%");
		}else{
			$map['store_domain'] = array('neq',"");
		}
		$seller_name = I('seller_name');
		if($seller_name) $map['seller_name'] = array('like',"%$seller_name%");
		$store_name = I('store_name');
		if($store_name) $map['store_name'] = array('like',"%$store_name%");
		$count = $model->where($map)->count();
		$Page = new Page($count,20);
		$list = $model->where($map)->order('store_id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		
		$show = $Page->show();
		$this->assign('page',$show);
		$this->assign('pager',$Page);
		return $this->fetch();
	}
	
        /**
        *  店家满意度 
        *  2017-9-22 14：45
        */
	public function satisfaction(){
            //A.查询条件
            ($user_name  = I('user_name'))  && $map['u.nickname']    = ['like',"%$user_name%"];            
            ($store_name = I('store_name')) && $map['s.store_name']  = ['like',"%$store_name%"];         
            $prefix      = C('prefix');
            $field       = [
                'oc.*',
                'u.user_id',
                'u.nickname',
                'o.order_id',
                'o.order_sn',
                's.store_id',
                's.store_name',
            ];
            // B. 开始查询
        $count = Db::name('order_comment oc')
                    ->field($field)
                    ->join($prefix . 'users u  ','u.user_id = oc.user_id','left')
                    ->join($prefix . 'store s  ','s.store_id = oc.store_id','left')
                    ->join($prefix . 'order o  ','o.order_id = oc.order_id','left')
                    ->where($map)
                    ->order('order_commemt_id DESC')
                    ->count();
            // B.2 开始分页
            $Page = new Page($count,20);
            $show = $Page->show();
            $list = Db::name('order_comment oc')
                ->field($field)
                ->join($prefix . 'users u  ','u.user_id = oc.user_id','left')
                ->join($prefix . 'store s  ','s.store_id = oc.store_id','left')
                ->join($prefix . 'order o  ','o.order_id = oc.order_id','left')
                ->where($map)
                ->order('order_commemt_id DESC')
                ->limit($Page->firstRow.','.$Page->listRows)->select();
            $this->assign('page',$show);
            $this->assign('pager',$Page);
            $this->assign('list',$list);        
            return $this->fetch();
	}
	
        /**
        *  店铺评分
        *  2017-9-22 15：05
        */
	public function score(){     
            $model =  M('order_comment oc');
            $store_name = I('store_name');
            if($store_name) $map['store_name'] = array('like',"%$store_name%");
             $field=[
                'store_id',
                'COUNT(*) AS number',
                'AVG(describe_score) AS describe_score',
                'AVG(seller_score) AS seller_score',
                'AVG(logistics_score) AS logistics_score', 
            ];
            $count = $model->field($field)->where($map)->group("store_id")->count();
            $Page = new Page($count,20);           
            $list = $model->field($field)
                        ->where($map)
                        ->order('order_commemt_id DESC')
                        ->limit($Page->firstRow.','.$Page->listRows)
                        ->group("store_id")
                        ->select();
            if($list && is_array($list)){
                foreach($list as $k => $v){                                  
                    $v['store_name'] = M('store')->cache(true)
                            ->where('store_id = '.$v['store_id'])
                            ->getfield('store_name');               
                    $order_comment_list[] = $v;
                }
            }             
            $show = $Page->show();
            $this->assign('page',$show);
            $this->assign('pager',$Page);
            $this->assign('list',$order_comment_list);
            return $this->fetch();
	}
}