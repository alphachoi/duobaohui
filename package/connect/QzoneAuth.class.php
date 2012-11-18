<?php
namespace Snake\Package\Connect;

/**
 * @package connect 百度互联授权及获取用户信息
 * @author yishuliu@meilishuo.com
 * 采用OAuth2.0授权
 */

Use \Snake\Package\User\UserConnect;
Use \Snake\Package\Session\UserSession;
Use \Snake\Package\Medal\MedalLib;
Use \Snake\Libs\Base\Utilities;
Use \Snake\Package\User\Helper\RedisUserOauth;
Use \Snake\Package\User\Helper\RedisUserConnectHelper;
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\Shareoutside\ShareHelper;
Use \Snake\Package\User\UserSetting;
Use \Snake\Package\Oauth\QzoneClient;

/**
 * @author yishuliu@meilishuo.com
 * @since 2012-06-20
 * @version 1.0
 */
class QzoneAuth extends ConnectLib {
	private static $instance = NULL;
	
    /**
     * 用户授权流程
     * @author yishuliu@meilishuo.com
     * @param $type = qzone
     * @param array $params 包括httpRequest信息，包括oauth_code,santorini_mm等信息 
	 */
	public function qzoneAuth($type, $params = array()) {
		$base_uri = isset($params['request']->GET['baseUrl']) ? $params['request']->GET['baseUrl'] : '';
        $urlArray = array();
        $urlArray = parse_url($base_uri);
        $host = 'http://' . $urlArray['path'] . '/';

		$callback = $host . 'connect/auth/qzone?baseUrl=' . $urlArray['path'];

        $destUrl = 'connect/connect/qzone';
		if (!empty($params['frm'])) {
			$callback .= "&r=" . $params['frm'];
			$destUrl .= "?r=" . $params['frm'];
		}
        if (!empty($params['code'])) { 
			$result = array();
            $result = UserConnect::qzoneAccess($callback, array(QZONE_ID, QZONE_KEY), $params['code'], $params['ip']);
			if (isset($result['qzone_access_keys']['access_token']) && isset($result['qzone_openid']['openid'])) {
				$cacheObj = Memcache::instance();
        		$cacheKey = 'QzoneAuth:qzone:' . $params['santorini_mm'];
				$cacheObj->set($cacheKey, $result, 3600);

				$result['destUrl'] = $destUrl;
                return $result;
			}
			$logHandle = new \Snake\Libs\Base\SnakeLog('qzone_auth_token', 'normal');
			$logHandle->w_log(print_r($result, true));
            return FALSE;
        }
        else {
            $queryStr = isset($params['queryCookie']) ? $params['queryCookie'] : "";
            setcookie('MEILISHUO_QUERY', FALSE, $_SERVER['REQUEST_TIME']-3600, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
            if(!empty($queryStr)) {
                $queryStatus = strpos($queryStr, 'qz_') && !strpos($queryStr, 'qz_guanggao') && !strpos($queryStr, 'app=tuiguang&style=');
            }
			$this->_checkRedirect($type, $params, $urlArray['path'], $queryStatus);

            $state = md5(uniqid(rand(), TRUE));
            $result = UserConnect::qzoneAuth($callback, $state, array(QZONE_ID, QZONE_KEY), 'default', $params['ip']);
            return $result;
        }
    }

	private function _checkRedirect($type, $params, $host, $queryStatus) {
		$refer = isset($params['request']->refer) ? $params['request']->refer : '';
		$frm = isset($params['request']->GET['frm']) ? $params['request']->GET['frm'] : '';
		$frm360 = isset($params['frm']) ? $params['frm'] : '';

        $callback = 'http://' . $host . 'connect/auth/' . $type . '?baseUrl=' . $host;
        $destUrl = 'connect/connect/' . $type;
		//frm 以fk_作为前缀的都是从浮层点击的，跳向首页
		//frm 以tk_作为前缀的都是弹点击的。
		if (strpos('prefix' . $frm, 'fk_') || (strpos("pop" . $frm, 'tk_') && strpos("pop" . $frm, 'gad'))) {
			setcookie("ORIGION_REFER", 'home', 0, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
		}
		elseif(strpos("pop" . $frm, 'tk_') && $queryStatus) {
			setcookie("MEILISHUO_ORIGION", $params['request']->refer, 0, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
		}
        elseif (strpos('prefix' . $frm360, 'share/share?url=')) {
            setcookie("ORIGION_REFER", $params['frm'], 0, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
        }  
		elseif (!strpos($params['request']->refer, 'logon') && !strpos($params['request']->refer, 'register')) {
			setcookie("ORIGION_REFER", $params['request']->refer,  0, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
		}
        return TRUE;
    }   

	//TODO
    /** 
     * 用户授权成功，获取用户信息
     * @author yishuliu@meilishuo.com
     * @param $user_id int 
     * @param array $params 包括httpRequest信息，包括oauth_code,santorini_mm等信息  
     */
	public function qzoneLogin($userId, $params = array()) {
		$cacheObj = Memcache::instance();
        $cacheKey = 'QzoneAuth:qzone:' . $params['santorini_mm'];
		$result = $cacheObj->get($cacheKey);

		$access_token = $result['qzone_access_keys']['access_token'];
		$ttl = $result['qzone_access_keys']['expires_in'];
		$auth = $result['qzone_openid']['openid'];
		
		$logHandle = new \Snake\Libs\Base\SnakeLog('connect_getQzoneInfo', 'normal');
		$start = microtime(true);
		$userInfo = UserConnect::getQzoneInfo($access_token, $auth);
		$spend = microtime(true) - $start;
		$str = "Get userInfo spend time is:" . $spend . "\n";
		$logHandle->w_log(print_r($str, true));

		//add by huazhulin - start 
		$qc = new QzoneClient(QZONE_ID, QZONE_KEY, $access_token, $auth);
		$qc->add_idol('meilishuo');
		// - end 

		//var_dump($auth, $userInfo);
		if (empty($auth) || empty($userInfo)) {
			$failInfo = array();
            $failInfo['error'] = '获取Qzone信息失败';
            return $failInfo;
		}

		$meiliUser = RedisUserOauth::getUserIdFromAuth('qzone', $auth);
		$qzoneTokens = array();
		$qzoneTokens['auth'] = $auth;
		$qzoneTokens['ttl'] = !empty($ttl) ? $ttl : TRUE;
		$qzoneTokens['access_token'] = $access_token;//implode(',', $result['qzone_access_keys']);
		if (empty($meiliUser)) {
			$param = array('user_type' => 4, 'auth' => $auth);
			$meiliUserExist = UserConnect::getInstance()->getUserConnectFromDB($param, "user_id, access");
			if (isset($meiliUserExist[0]['user_id']) && $meiliUserExist[0]['user_id'] != 0) {
				//如果得到用户信息不为空，则代表为非第一次来，跳到用户的home页
				$user_id = $meiliUserExist[0]['user_id'];
				$result = $this->_updateRedis($user_id, 'qzone', $qzoneTokens, $params);
				return $result;
			}
		}
        //$logHelper = new \Snake\Libs\Base\SnakeLog('qzone_success', 'normal');
        //$logHelper->w_log(print_r(array($meiliUser, $params),TRUE));
		if (!empty($meiliUser)) {
			//如果得到用户信息不为空，则代表为非第一次来，跳到用户的home页
			$result = $this->_updateRedis($meiliUser, 'qzone', $qzoneTokens, $params);
			//原送Q币活动
			//connectHelper::shareQbi(4, TRUE);
			return $result;
		}
		else {
			//将qzoneInfo存入memcache
			$qzoneInfo = $this->_setQzoneInfoCache($userInfo, $auth, $access_token, $ttl);
			$memKey = 'Connect:Info:' . $params['santorini_mm'];
			$cacheObj->set($memKey, $qzoneInfo, 3600);

			$frmFor360 = $params['frm'];
			$result = $this->_checkUserInfo($userInfo, $frmFor360);
			return $result;
		}
	}

	private function _checkUserInfo($userInfo, $frmFor360) {
		if (!empty($userInfo) && !empty($userInfo['gender'])) {
			if (empty($userInfo['nickname'])) {
				$userInfo['nickname'] = 'qzone';
			}
			if (0 && $userInfo['gender'] != '女') {
				if ($frmFor360 == '360') {
					$result = array();
					$result['destUrl'] = '/app/360dev/boyfail';
					return $result;
				}
				$result = array();
				$result['destUrl'] = 'connect/fail/qzone';
				return $result;
			}
			else {
				$result = array();
				$result['reg_from'] = 4;
				$result['new_comer'] = 1;
				$result['destUrl'] = 'register/register_actionconnect';
				return $result;
			}
		}
	}

	private function _setQzoneInfoCache($userInfo, $auth, $access_token, $ttl) { 
		$rand = mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);
		$qzoneInfo = array();
		$qzoneInfo['avatar'] = !empty($userInfo['figureurl_2']) ? $userInfo['figureurl_2'] : \Snake\Libs\Base\Utilities::convertPicture('/css/images/0.gif');
		$qzoneInfo['auth'] = $auth;
		$temp = substr($auth, 0, 9);
		$qzoneInfo['email'] = '#' . $temp . time() . '@t.qzone.com';
		$qzoneInfo['realname'] = '';

		//去掉屏蔽词
		$maskWords = new \Snake\Package\Spam\MaskWords($userInfo['nickname'], 'DFA_register');
		$maskResult = $maskWords->getMaskWords();
		$nickname = $maskResult['maskedContent'];

		$qzoneInfo['nickname'] = !empty($nickname) ? mb_substr($nickname, 0, 6, 'UTF-8') . '_qq' . $rand : substr($auth, 0, 6) . '_qq' . $rand;
		$qzoneInfo['gender'] = $userInfo['gender'];
		$qzoneInfo['province'] = '';
		$qzoneInfo['city'] = '';
		$qzoneInfo['openType'] = 4;
		$qzoneInfo['type'] = 'bangqzone';
		$qzoneInfo['access_token'] = $access_token;
		$qzoneInfo['ttl'] = $ttl;
		return $qzoneInfo;
	}

	private function _updateRedis($user_id, $type, $qzoneTokens, $params) {
		if ($type == 'qzone') {
			$flag = 4;
		}
		$auth = $qzoneTokens['auth'];
		$ttl = $qzoneTokens['ttl'];
		//access_token是由access_token
		$access_token = $qzoneTokens['access_token'];
        UserConnect::getInstance()->updateUserConnectAuth($user_id, $flag, $auth, 1, $access_token);
        RedisUserConnectHelper::updateUserToken($type, $user_id, $ttl, $access_token);
        RedisUserConnectHelper::updateUserAuth($type, $user_id, $auth);
        RedisUserOauth::updateUserIdWithAuth($type, $auth, $user_id);
        $firstVisit = FALSE;
        $result = $this->userLogin($user_id, $flag, $firstVisit, $params);
		return $result;
    }   

    /**
     * qzone互联用户是男性操作
     * @author yishuliu@meilishuo.com
     * @param array $params 包括httpRequest信息，包括santorini_mm等信息  
     * @return array $userInfo
     */
	public function qzoneFail($params = array()) {
		//print_r($params);
		$cacheObj = Memcache::instance();
		$memKey = 'Connect:Info:' . $params['santorini_mm'];
		$userInfo = $cacheObj->get($memKey);

        $userInfo['gender'] = '男';
        $userInfo['school'] = ''; 
        $userInfo['workplace'] = ''; 
		$cacheObj->delete($memKey);
        return $userInfo;
	}

	/**
	 * 更新UserConnect和UserOauth
	 * @param $userId int 用户编号
	 * @param $token string access token
	 * @param $auth string 
	 * @param $ttl 过期时间
	 */
	public function qzoneUpdateToken($userId, $token, $auth, $ttl) {
		RedisUserConnectHelper::updateUserToken('qzone', $userId, $ttl, $token);   
		RedisUserConnectHelper::updateUserAuth('qzone', $userId, $auth);
	    RedisUserOauth::updateUserIdWithAuth('qzone', $auth, $userId);

        $settings['sync_goods'] = 1;
        $settings['sync_medal'] = 1;
        $settings['sync_collect'] = 1;
        $settings['sync_like'] = 1;
        $settings['sync_ask'] = 1;
        $settings['sync_answer'] = 1;

		RedisUserConnectHelper::setUserSetting('qzone', $userId, json_encode($settings));
		RedisUserConnectHelper::setUserSetting('qplus', $userId, json_encode($settings));

		UserConnect::getInstance()->updateSettings($userId, 4, $settings);
	}
}

