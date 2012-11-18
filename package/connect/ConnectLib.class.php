<?php
namespace Snake\Package\Connect;

Use \Snake\Package\User\UserConnect;
Use \Snake\Package\User\User;
Use \Snake\Package\Session\UserSession;
Use \Snake\Package\User\LogOn;
Use \Snake\Package\Medal\MedalLib;
Use \Snake\Package\User\Helper\RedisUserOauth;
Use \Snake\Package\User\Helper\RedisUserConnectHelper;

class ConnectLib {

    protected $profileParams = array('user_id', 'nickname', 'email', 'ctime', 'password', 'active_code', 'cookie', 'is_actived', 'invite_code', 'last_logindate', 'status', 'realname', 'istested', 'reg_from', 'last_email_time', 'level', 'isPrompt', 'isBusiness', 'login_times', 'is_mall', 'mall_url');
	protected $outSites = array('renren' => 1, 'weibo' => 3, 'qzone' => 4, 'baidu' => 5, 'taobao' => 6, 'wangyi' => 7, 'txweibo' => 8, 'douban' => 10, 'qplus' => 11);

	/**
     * 互联用户登录设置 <br/>
     * @params $userId int <br/>
     * @params $type int <br/>
     *     1: renren, 3: weibo, 4: qzone, 5: baidu, 6: taobao, 7: wangyi,
     *     8: txweibo, 10: douban <br/>
     * @return BOOL <br/> 
     */
    public function userLogin($userId, $type, $firstVisit, $params) {
        $user = new User();

        //设置用户global key
        $globalKey = $user->checkGlobalKey($userId);
		$gKeyDB = !empty($globalKey) ? $globalKey[0]['global_key'] : '';
		$gKey = $params['request']->COOKIE['MEILISHUO_GLOBAL_KEY'];

		//$this->setGlobalKey($userId, $gKeyDB, $gKey, $params['request']->seashell);

        $baseInfo = $user->getUserBaseInfo($userId, $this->profileParams);
        if (empty($baseInfo)) {
            $failInfo = array();
            $failInfo['error'] = '获取个人用户信息失败';
            return $failInfo;
        }
		//根据is_actived数值判断是否跳转，－1或者－2跳转至welcome页
		if ($baseInfo['is_actived'] < 0) {
			$redirectUrl['destUrl'] = 'welcome';		
			return $redirectUrl;
		}
		$passwordE = md5(md5($baseInfo['email']) + $baseInfo['password']);
		$baseInfo['base64'] = base64_encode('userid=' . $baseInfo['user_id'] . '&username=' . $baseInfo['nickname'] . '&password=' . $passwordE);

        $setStatus = LogOn::logonSetSession ($baseInfo, $params['request']);
        LogOn::setUserCookie($userId, $baseInfo['nickname'], $baseInfo['password']);

		//TODO 发勋章
        //$medalLibHelper = new MedalLib($userId);
        //$medalLibHelper->handleMedalForWebapp();

		//修复用户settings相关选项
		$this->refreshUserSettings($userId, $type);

		$cpc_landing = $params['request']->COOKIE['MEILISHUO_BOB_LANDING'];
		$origion_refer = $params['request']->COOKIE['ORIGION_REFER'];
        $logHandle = new \Snake\Libs\Base\SnakeLog('weiboRedirect', 'normal');
        $logHandle->w_log(print_r($origion_refer . '***' , true));

		//控制跳转
        if (!empty($cpc_landing) && isset($origion_refer)) {
			$redirectUrl['destUrl'] = $origion_refer;		
			$redirectUrl['new_comer'] = 0;
            setcookie('ORIGION_REFER', FALSE, time() - 3600, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
            return $redirectUrl;
        }

        if($firstVisit === FALSE) {
			$frmFor360 = $params['frm'];
			if ($frmFor360 == '360') {
				$redirectUrl['destUrl'] = 'app/360dev/success';		
				$redirectUrl['new_comer'] = 0;
                return $redirectUrl;
			}
			$meiliRefer = $params['request']->COOKIE['MEILISHUO_ORIGION'];
			$redirectUrl = array();
            if (isset($origion_refer) && $origion_refer != 'home') {
				$redirectUrl['destUrl'] = $this->parseUrl($origion_refer);		
				$redirectUrl['new_comer'] = 0;
                setcookie('ORIGION_REFER', FALSE, time() - 3600, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
                return $redirectUrl;
            }
            elseif ($meiliRefer) {
				$redirectUrl['destUrl'] = $this->parseUrl($meiliRefer);		
				//$log = new \Snake\Libs\Base\SnakeLog('meilirefer', 'normal');
				//$log->w_log(print_r($meiliRefer, true));
				$redirectUrl['new_comer'] = 0;
                setcookie('MEILISHUO_ORIGION', FALSE, time() - 3600, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
                return $redirectUrl;
            }
            else {
				//$logHandle = new \Snake\Libs\Base\SnakeLog('connectRedirect', 'normal');
				$redirectUrl['destUrl'] = 'home'; //$this->parseUrl($params['request']->refer);		
				$redirectUrl['new_comer'] = 0;
                return $redirectUrl;
            }
            return FALSE;
        } 
        return TRUE;
    }

	private function setGlobalKey($userId, $gKeyDB, $gKey, $seashell) {
        $user = new User();
        if (empty($gKeyDB)) {
            //如果t_dolphin_user_gkey表里面没有Global_KEY，把当前COOKIE[GLOBAL_KEY]存入数据库中
            if (isset($gKey) && $gKey != 'Array') { 
                $globalKey = $gKey;
            }    
            else {
                $globalKey = \Snake\Libs\Base\Utilities::getGlobalKey($seashell);
            }    
            $user->setGlobalKey($globalKey, $userId, $_SERVER['REQUEST_TIME']);
            setcookie('MEILISHUO_GLOBAL_KEY', $globalKey, $_SERVER['REQUEST_TIME'] + 3600 * 24 * 365, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
        }    
        elseif ($gKeyDB == 'Array') {
			$globalKey = \Snake\Libs\Base\Utilities::getGlobalKey($seashell);
            $user->updateGlobalKey($globalKey, $userId);
            setcookie('MEILISHUO_GLOBAL_KEY', $globalKey, $_SERVER['REQUEST_TIME'] + 3600 * 24 * 365, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
        }    
        else {
            setcookie('MEILISHUO_GLOBAL_KEY', $gKeyDB, $_SERVER['REQUEST_TIME'] + 3600 * 24 * 365, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
        } 
	}

	private function parseUrl($url) {
		//去掉http头
		if (strpos($url, 'http://') !== FALSE) {        
			$urlArray = array();        
			$urlArray = parse_url($url);        
			$header = $urlArray['host'];
            $path = substr($url, strlen('http://') + strlen($header));
            $log = new \Snake\Libs\Base\SnakeLog('parse_url', 'normal');
            $log->w_log(print_r($path, true));
		}
		else {
			$path = $url;
		}
		//拾宝器互联登录回调链接，picurl是抓图的，mlsurl是抓宝贝的
		if (strpos('prefix' . $path, 'meilishuo_goods?picurl') || strpos('prefix' . $path, 'share/share?url=') || strpos('prefix' . $path, 'meilishuo_goods?mlsurl')) {
			$path = htmlspecialchars_decode($path);
		}
		if (substr($path, 0, 1) == '/') {
			$path = substr($path, 1);
		}
		return $path;
	}
	
	/**
	 * @params $type 互联类型 int
	 *
	 */
	private function refreshUserSettings($userId, $type) {
        $retq = json_decode(RedisUserConnectHelper::getUserSetting('qzone', $userId), TRUE);
        if ($type == 4 && empty($retq)) {
             //qzone设置部分
            $settingq = array();
            $settingq['sync_goods'] = 1; //qzone分享
            $settingq['sync_medal'] = 0; //
            $settingq['sync_collect'] = 0;  //
            $settingq['sync_like'] = 0;  //
            $settingq['sync_ask'] = 0;  //
            $settingq['sync_answer'] = 0;  //

            RedisUserConnectHelper::setUserSetting('qzone', $userId, json_encode($settingq));
        }

        $ret = json_decode(RedisUserConnectHelper::getUserSetting('qplus', $userId), TRUE);
		if ($type == 4 && empty($ret)) {
             //qplus设置部分
            $settingqp = array();
            $settingqp['sync_goods'] = 1; //
            $settingqp['sync_medal'] = 1; //分享宝贝
            $settingqp['sync_collect'] = 1;  //收集宝贝
            $settingqp['sync_like'] = 1;  //喜欢宝贝
            $settingqp['sync_ask'] = 1;  //创建杂志社
            $settingqp['sync_answer'] = 1;  //关注杂志社

            RedisUserConnectHelper::setUserSetting('qplus', $userId, json_encode($settingqp));
        }

		//微博初始化
        $retw = json_decode(RedisUserConnectHelper::getUserSetting('weibo', $userId), TRUE);
        if ($type == 3 && empty($retw)) {
             //weibo设置部分
            $settingw = array();
            $settingw['sync_goods'] = 1; //weibo分享
            $settingw['sync_medal'] = 0; //
            $settingw['sync_collect'] = 0;  //
            $settingw['sync_like'] = 0;  //
            $settingw['sync_ask'] = 0;  //
            $settingw['sync_answer'] = 0;  //
         
            RedisUserConnectHelper::setUserSetting('weibo', $userId, json_encode($settingw));  
        }    
	}
}
