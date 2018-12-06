<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 如果商业用途务必到官方购买正版授权, 以免引起不必要的法律纠纷.
 * 采用最新Thinkphp5助手函数特性实现单字母函数M D U等简写方式
 * ============================================================================
 */

namespace app\common\logic\wechat;

use app\common\util\TpshopException;

use think\Db;

/**
 * 小程序官方接口类
 */
class MiniAppUtil extends WxCommon
{
    private $config = []; //小程序配置

    public function __construct($config = null)
    {
        if ($config === null) {
            $wxPay = Db::name('plugin')->where(array('type'=>'payment','code'=>'miniAppPay'))->find();
            $config = unserialize($wxPay['config_value']);
        }
        $this->config = $config;
    }
    

    public function getWxUserInfo($code , $iv , $encryptedData , &$data){
            try{
                $appid = $this->config['appid'];
                $session = $this->getSessionInfo($code);
		        if ($session === false) {
                    throw new TpshopException("小程序登录失败", 0, ['status' => 0, 'msg' => $this->getError(), 'result' => '']);
                }
                $sessionKey = $session['session_key'];
                $pc = new WxBizDataCrypt($appid, $sessionKey);
                $errCode = $pc->decryptData($encryptedData, $iv, $data);
                return $errCode;
            }catch (TpshopException $t){
                throw $t;
            } 
    }

    /**
     * 获取小程序session信息
     * @param string $code 登录码
     */
    public function getSessionInfo($code)
    {
        $appId = $this->config['appid'];
        $appSecret = $this->config['appsecret'];
        if (!$appId || !$appSecret) {
            $this->setError('请检查后台是否配置appid和appsecret');
            return false;
        }
        
        $fields = [
            'appid' => $appId,
            'secret' => $appSecret,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ];
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $return = $this->requestAndCheck($url, 'GET', $fields);
        if ($return === false) {
            $this->setError('小程序登录失败, errcode : '.$return['errcode'].', errmsg : '.$return['errmsg']);
            return false;
        }
        return $return;
    }
    
}