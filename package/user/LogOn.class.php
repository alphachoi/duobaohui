<?php
namespace Snake\Package\User;

Use \Snake\Package\User\Helper\RedisUserConnectHelper;
Use \Snake\Package\User\UserConnect;
Use \Snake\Package\User\Helper\RedisUserOauth;
Use \Snake\Package\Session\UserSession;
Use \Snake\Package\Medal\Medal;
Use \Snake\Package\Medal\MedalLib;
Use \Snake\libs\Cache\Memcache;
Use \Snake\Package\Mall\Mall;

class LogOn {

	private $userId = NULL;
	private static $request = NULL;
	private $isActived = NULL;
	

	public static function feee() {

	}

	public function userLogOn(array $postParams, $from = 'web') {
		if (empty($postParams)) {
			return FALSE;
		}
		$response = array();
		$emailAddress = trim($postParams['email_address']);
		$password = trim($postParams['password']);
		$setCookie = isset($postParams['save_state']) ? $postParams['save_state'] : TRUE;
		self::$request = $postParams['request'];
		$cookie = self::$request->COOKIE;
		if ($setCookie == 'false') {
			$setCookie = FALSE;
		}
		if (empty($_SERVER['HTTP_USER_AGENT']) || empty($cookie) || $_SERVER['HTTP_USER_AGENT'] == '-' || $_SERVER['HTTP_USER_AGENT'] == 'Mozilla/3.0 (compatible)' || $_SERVER['HTTP_USER_AGENT'] == 'meilishuo write is robot' || (empty($_SERVER['HTTP_REFERER']) && $from == 'web')) {
			$response  = array('status' => 1 , 'redirect' => MEILISHUO_URL);
			return $response;
		}
		$type = trim($postParams['type']);
		$wbid = trim($postParams['wbid']);
		//$tempUid = $userId;
		//这个时候还没有登录，不存在$userId
		$checkStatus = $this->logonActive($emailAddress, $password, $cookie, $setCookie, $from);
		
		if ($checkStatus['status'] == -1 || $checkStatus['status'] == -2 || $checkStatus['status'] == -3 || $checkStatus['status'] == 6) {
			$response = $checkStatus;
			return $response;
		}
		//已经登录了，有了userid
		//删除控制弹出机制的cookie
		setcookie('NOT_SHOW_LOGIN', FALSE, 0, '/');
		setcookie('SHOW_LOGIN_TIMES', FALSE, 0, '/');
		$userId = $checkStatus['user_id'];


		setcookie("SINA_LANDING_PAGE", 0, time()-3600, DEFAULT_COOKIEPATH , DEFAULT_COOKIEDOMAIN);
		
		$response = array();
		if (!empty($cookie['QBI_LANDING_PAGE'])) {
			$response['status'] = 11;
		}
		elseif (isset($cookie['TO_LOGIN']) && $cookie['TO_LOGIN'] == 'to_login') {
			$response['status'] = 12;
		}
		else {
			$response['status'] = 1;
			$blackList = array('users','welcome','logon','connect','u','capi','click','verification','apply','home','edm','url');
			if ($from == '360') {
				$response['base64'] = $checkStatus['base64'];
			}	
			if (isset($postParams['redirect'])) {
				$redirect = HTTP_HEADER_SAFE ? urldecode(htmlspecialchars_decode($postParams['redirect'])) : urldecode($postParams['redirect']);
				$urlInfo= parse_url($redirect);
				$path = trim($urlInfo['path'], '/');
				$tmpArray = explode(':', rtrim(str_replace('http://', '', BASE_URL), '/'), 2);
				if (isset($tmpArray[1])) {
					$port = $tmpArray[1];
				} else {
					$port = 80;
				}
				$host = $tmpArray[0];
				$pos = strpos($urlInfo['host'], '.meilishuo.com');
				if (!empty($pos) && !in_array($redirect, $blackList)) {
					$response['redirect'] = urldecode($redirect);
				}
			}
			if (empty($response['redirect']) && isset($cookie['LOGON_FROM'])) {
				$response['redirect'] = $cookie['LOGON_FROM'];
				setcookie('LOGON_FROM', FALSE, time() - 3600, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
			}
			isset($response['redirect']) || $response['redirect'] = MEILISHUO_URL . '/ihome';
			$positionWLC = strpos($response['redirect'], 'welcome');
			$positionRegister = strpos($response['redirect'], 'register');
			if (!empty($positionWLC) || !empty($positionRegister) ) {
				$response['redirect'] = MEILISHUO_URL . '/ihome';
			}
		}
		return $response;
		echo json_encode($response);

		//@左边表示是否激活：1，激活；0：没激活。＠右边表示是否做过测试：1：做过；0：没做过
		/*if ($checkStatus [0] ['istested'] == 0 && $checkStatus [0] ['is_actived'] == 1 && $checkStatus [0] ['user_id'] > 600) {
			echo $checkStatus [0] ['is_actived'] . "@" . $checkStatus [0] ['istested'];
		} else {
			echo $checkStatus [0] ['is_actived'];
		}*/
		return TRUE;
	}

	private function checkUserFrom($postParams) {
		$userId = $this->userId;
		$type = $postParams['type'];
		if ($type === 'weibo') {
			$keys = $postParams['weibo_access_keys'];
			if (!empty($postParams['weibo_access_keys'])) {
				$postParams['weibo_access_keys'] = explode(',', $key);
			}

			$openId = $postParams['weibo_access_keys']['uid'];
			$postParams['open_id'] = $openId;
			$postParams['user_type'] = 3;
			$postParams['expires_in'] = $postParams['weibo_access_keys']['expires_in'];
			$postParams['from_type'] = 'weibo';
			$stats = $this->logonSetConnectInfo($postParams);
			if (!empty($stats)) {
				return $stats;
			}

			$settings['sync_goods'] = 1;
			$settings['sync_medal'] = 1;
			$settings['sync_collect'] = 1;
			$settings['sync_like'] = 1;
			$settings['sync_ask'] = 1;
			$settings['sync_answer'] = 1;

			UserConnect::getInstance()->updateUserConnectSettings($userId, $weibo, $settings);
		}
		elseif ($type == 'bangdouban') {
			if (!empty($postParams['douban_access_keys'])) {
				$postParams['acess_key'] = implode(',', $postParams['douban_access_keys']); 
			}
			$postParams['open_id'] = $wbid; 
			$postParams['user_type'] = 10;
			$postParams['expires_in'] = TRUE;
			$postParams['from_type'] = 'douban';
			$this->logonSetConnectInfo($postParams);
		}
		elseif ($type == 'bangwangyi') {
			$postParams['acess_key'] = implode(',', $postParams['163_access_keys']); 
			$postParams['open_id'] = $wbid; 
			$postParams['user_type'] = 7;
			$postParams['expires_in'] = TRUE;
			$postParams['from_type'] = 'wangyi';
			$this->logonSetConnectInfo($postParams);
		}
		elseif ($type == 'bangtxwb') {
			$postParams['acess_key'] = implode(',', $postParams['qzone_access_keys']); 
			$postParams['open_id'] = $wbid; 
			$postParams['user_type'] = 8;
			$postParams['expires_in'] = TRUE;
			$postParams['from_type'] = 'txweibo';
			$this->logonSetConnectInfo($postParams);
		}
		elseif ($type == 'bangqzone') {
			$postParams['acess_key'] = $postParams['qzone_access_keys']['access_token']; 
			$postParams['open_id'] = $postParams['qzone_openid']['openid']; 
			$postParams['user_type'] = 4;
			$postParams['expires_in'] = $postParams['qzone_access_keys']['expires_in'];
			$postParams['from_type'] = 'qzone';
			$this->logonSetConnectInfo($postParams);
		}
		elseif ($type == 'bangrenren') {
			$postParams['acess_key'] = $postParams['renren_access_keys']['access_token']; 
			$postParams['open_id'] = $postParams['renren_user_info']['id']; 
			$postParams['user_type'] = 1;
			$postParams['expires_in'] = $postParams['renren_access_keys']['expires_in'];
			$postParams['from_type'] = 'renren';
			$this->logonSetConnectInfo($postParams);
			UserConnect::updateUserrefToken('renren', $userId, 5184000, $postParams['renren_access_keys']['refresh_token']);
		}
		elseif ($type == 'bangbaidu') {
			$qzone = 5;
			//TBC
			/*importer('corelib.Baidu');
			$baidu = new Baidu(BAIDU_AKEY, BAIDU_SKEY, new BaiduSessionStore(BAIDU_AKEY));
			$access_token = $baidu->getAccessToken();
			$param = array(
			  			"user_id"  => $userId,
						"user_type" => 5);
			$preAuth = userModel::getInstance()->getUserConnect($param, "auth");
			//$preAuth为空，代表没有关联微博信息
			if (empty($preAuth)) {
				userModel::getInstance()->insertUserConnectInfo($userId, 5, $wbid, $access_token);
			}
			else {
				//如果有关联，则update auth为新wbid，status改为0，0代表正常
				userModel::getInstance()->updateUserConnectAuth($userId, 5, $wbid, 0, $access_token);
			}*/
		}
		elseif ($type == 'bangtaobao') {
			$qzone = 6;
			$sInfo = $postParams['taobaoInfo']['userInfo'];
			if (empty($sInfo)) {
				die('获取淘宝信息失败');
			}
			$wbid = $sInfo->user_id;
			$access_token = $postParams['taobaoInfo']['access_token'] . ',' . $sInfo->nick;
			$postParams['acess_key'] = $access_token; 
			$postParams['open_id'] = $wbid; 
			$postParams['user_type'] = 6;
			$postParams['expires_in'] = TRUE;
			$postParams['from_type'] = 'taobao';
			$return = RedisUserConnectHelper::updateTaobaoInfo($userId, $sInfo);
		}
		elseif ($checkStatus[0]['is_actived'] == 2) {
			$response = array();
			$response['status'] = 1;
			$response['redirect'] = BASE_URL . 'users/activate_message';
			return $response;
			echo json_encode($response);
			return TRUE;
		}
		return $response;

	}

	private function logonSetConnectInfo($params = array(), $response = array()) {
		if (empty($params)) {
			return FALSE;
		}
		$type = $params['from_type'];
		$userId = $this->userId;
		//$userId = 7579131; 
		$openId = isset($params['open_id']) ? $params['open_id'] : 0;
		$userType = $params['user_type'];
		$ttl = $params['expires_in'];
		$access = $params['access_key'];

		$preAuth = UserConnect::getInstance()->getUserConnect($userId, $type);
		$userHasConnect = RedisUserConnectHelper::getUserAuth($type, $userId);
		$userAccessInfo = explode(',', $preAuth);
		if (!empty($userHasConnect) && $type == 'weibo') {
			$response['status'] = -5;
			return $response;
		}
		if (empty($preAuth)) {
			UserConnect::getInstance()->insertUserConnectInfo($userId, $userType, $openId, $access);
		}
		else {
			$preauth = $openId;
			RedisUserOauth::removeUserIdFromAuth($type, $preauth);
			UserConnect::getInstance()->updateUserConnectAuth($userId, $userType, $openId, 0, $preAuth);
		}
		RedisUserConnectHelper::updateUserToken($type, $userId, $ttl, $access);
		RedisUserConnectHelper::updateUserAuth($type, $userId, $openId);
		RedisUserOauth::updateUserIdWithAuth($type, $openId, $userId);

		return array();
	}

    public function logonActive($emailAddress, $password, $cookie, $set_cookie = TRUE, $from) {

		$userHelper = new User();
		$checkStatus = $this->checkLogOnFrom($emailAddress, $password, $from, $userHelper);
		$cacheHelper = Memcache::instance();
		
        if ($checkStatus === FALSE) {
            //$DEFAULT_COOKIE_LOGON = isset($cookie['DEFAULT_COOKIE_LOGON']) ? $cookie['DEFAULT_COOKIE_LOGON'] : 0; 
			$cacheKey = "LOGON_ERROR_TIMES" . $cookie[DEFAULT_SESSION_NAME];
			$logonTimes = $cacheHelper->get($cacheKey);
			if (!empty($logonTimes)) {
				$logonTimes ++;
			}
			else {
				$logonTimes = 1;
			}
			$cacheHelper->set($cacheKey, $logonTimes, 600);
            if ($logonTimes <= 3) { 
				$checkStatus['status'] = -1;
                return $checkStatus;
            } else {
				$checkStatus['status'] = 6;
                return $checkStatus;
            }    
        } else {
			$cacheKey = 'users_temp_avatar_' . $checkStatus['user_id'];
			$avatar = $cacheHelper->get($cacheKey);
			if (!empty($avatar)) {
				$checkStatus['avatar_c'] = $avatar;
				$checkStatus['avatar_a'] = $avatar;
				$checkStatus['avatar_d'] = $avatar;
			}
            if ($checkStatus['is_actived'] >= 0) { //如果用户已经激活或者未激活女性用户
                $userId = $checkStatus['user_id'];
				$this->userId = $userId;
                //得到用户的C头像，32×32
                $setStatus = self::logonSetSession($checkStatus, self::$request);

                /*yhl 如果用户有Global_KEY，不做操作，如果用户没有Global_KEY,，从COOKIE里取出存入表中  yhl*/
				$globalKey = $userHelper->checkGlobalKey($userId);
				$globalKey = $globalKey[0]['global_key'];
                if (empty ($globalKey)) { 
                    //如果t_dolphin_user_gkey表里面没有Global_KEY，把当前COOKIE[GLOBAL_KEY]存入数据库中
                    if (isset($cookie['MEILISHUO_GLOBAL_KEY'])) { 
                        $globalKey = $cookie['MEILISHUO_GLOBAL_KEY'];
                    }    
                    $time = time(); //读取当前日期
					$userHelper->setGlobalKey($globalKey, $userId, $time);
                }    
				setcookie ('MEILISHUO_GLOBAL_KEY', $globalKey, time() + 3600 * 24 * 365, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
                if ($checkStatus === false) {
                    return false;
                }    
                setcookie('DEFAULT_COOKIE_LOGON', '', time() + 600, '/');
                if (isset($set_cookie) && $set_cookie == true) {
                    self::setUserCookie($checkStatus['user_id'], $emailAddress, $password);
                } else {
                   $userHelper->updateUserCookie($userId);
                }
                //这个地方判断用户是第几次登录，如果第二次登录且还没有上传头像，则发送edm邮件
				$userInfo = $userHelper->getUserInfo($userId, array('is_uploaded', 'login_times'), TRUE);
                /*if( $userInfo['is_uploaded'] == 0 && $userInfo['login_times'] == 2){
					// TODO TBC
                    importer("corelib.edmMail");
                    $edmfollower = new edmMail(0,68);
                    $edmfollower->noticeEdmInsert('assure',NULL,$checkStatus['user_id'],'1');
                }*/
            } else if ($checkStatus['is_actived'] == -1) { //如果是男性没有激活用户
				$checkStatus['status'] = -3;	
                return $checkStatus;
            } 
			else if ($checkStatus['is_actived'] == -2) {
				$checkStatus['status'] = -4; 
				$checkStatus['redirect'] = MEILISHUO_URL . "welcome";
			}
            return $checkStatus;
        }
    }

    public static function logonSetSession($userInfo, $request) {
        if (empty ($userInfo)) {
            return false;
        }

		$userHelper = new User();
		if (is_array($userInfo)) {
			$userId = $userInfo['user_id'];
			$helper = new Mall();
			$result = $helper->getMallInfoById($userId);
			//判断是不是商家
			$is_mall = 0;
			if(!empty($result)) {
				$is_mall = 1;
			}
			$result = $userHelper->getUserInfo($userId);
			$userInfo['is_mall'] = $is_mall;
			$userInfo['avatar_d'] = \Snake\Libs\Base\Utilities::convertPicture($result['avatar_d']);
			$userInfo['avatar_c'] = \Snake\Libs\Base\Utilities::convertPicture($result['avatar_c']);
		}
		else {
			$userId = $userInfo;
			$result = $userHelper->getUserInfo($userId, array('*'));
			$userInfo = array();
			$userInfo = $result;
		}
		if (self::$request->REQUEST['frm'] == 360) {
			$userInfo['360_logon'] = 1;
			//$userInfo['password'] = md5(md5($userInfo['nickname']) + $userInfo['password']);
		}
        self::setUserSession($userInfo, $request);
        return TRUE;
    }

    public static function setUserCookie($userId, $emailAddress, $password, $time = "") {
        $cookieKey = "MEILISHUO_MM";
		$userHelper = new User();
		$userBasicInfo = $userHelper->getUserInfo($userId, array('cookie'));
        if (empty($userBasicInfo[0]['cookie'])) {
            $cookieValue = md5($emailAddress . $password);
        }    
		else {
			$cookieValue = $userBasicInfo[0]['cookie'];
		}
		if ($time == '') {
            $cookieExpire = $_SERVER['REQUEST_TIME'] + 3600 * 24 * 30;
        }    
		$userHelper->updateUserCookie($userId, $cookieValue);
        $cookiePath = DEFAULT_COOKIEPATH;
        $cookieDomain = DEFAULT_COOKIEDOMAIN;
        setcookie($cookieKey, $cookieValue, $cookieExpire, $cookiePath, $cookieDomain);
    } 

    /**
     * 处理用户登录后的session操作
     */
    public static function setUserSession($userInfo, $request) {
		/*
		$cacheHelper = Memcache::instance();
		$session = $userInfo;
		$ip = $request->ip;
		$useragent = $request->agent;
		$timestamp = $_SERVER['REQUEST_TIME'];


        $cookie_key = DEFAULT_SESSION_NAME;
		if (empty($request->COOKIE[$cookie_key]) || $request->COOKIE[$cookie_key] == 'deleted') {
			$session_id = \Snake\Libs\Base\Utilities::getUniqueId();
			setcookie($cookie_key, $session_id, 0, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
			$request->COOKIE[$cookie_key] = $session_id;
			$cacheHelper->delete($session_id);

			global $_ZSESSION;
			$_ZSESSION[$cookie_key] = $session_id;
		}   
		else {
			$session_id = $request->COOKIE[$cookie_key];
		}   
		// the cookie will be available within the entire domain,will expire at the end of the session (when the browser closes).
		$saveData = array (); 
		$saveData['session_id'] = $session_id;
		$saveData['keyid'] = isset($session['user_id']) ? $session['user_id'] : 0;
		$saveData['logon_ip'] = $ip;
		if (empty($session)) {
			$session['user_id'] = 0;
			$session['reg_from'] = !empty($this->request->COOKIE[CHANNEL]) ? $this->request->COOKIE[CHANNEL] : 0;
		}   
		$saveData['session_data'] = $session;
		$cacheHelper->set($session_id, $saveData, 7200);

		 */
		//$_SESSION['userInfo'] = $userInfo;



        $userSession = new UserSession($userInfo, $request);
		//UserSession::updateSessionData($userInfo, $request->COOKIE);
		return TRUE;
    }

	private function rebuildTimeline() {
		 if (UserHomePosterTimeline::exists($this->userId) == FALSE || UserHomePosterTimeline::getSize($this->userId) < 50) {
            $result = TimelineDB::getInstance()->getTimelineFromDB($this->userId, "/*Home-rebuild gc*/ last_tid, last_update_time, tids");
            $lastTid = 0;
            $dbTids = array();
            $lastUpdateTime = 0;
            if (!empty($result)) {
                $lastTid = $result[0]['last_tid'];
                $lastUpdateTime = strtotime($result[0]['last_update_time']);
                $dbTids = explode(',', $result[0]['tids']);
            }   
            Timeline::rebuildUserHomePosterTimelineNew($this->userId, $lastTid, $lastUpdateTime, $dbTids);
        }   
	}
	
	public function checkLogOnFrom($emailAddress, $password, $from, $userHelper) {
		switch($from) {
			case "web": 
				$password = md5($password);
				$checkStatus = $userHelper->getUserBaseInfoByUsernameAndPassword($emailAddress, $password);
				return $checkStatus;

			case "360":
				$logOnHelper = new LogOn360();
				$params = array('user_name' => $emailAddress, 'password' => $password);
				$checkStatus = $logOnHelper->userLogOn($params);
				return $checkStatus;

			case "register":
				$password = md5($password);
				$checkStatus = $userHelper->getUserBaseInfoByUsernameAndPassword($emailAddress, $password);
				return $checkStatus;
			
			case "activate":
				$checkStatus = $userHelper->getUserBaseInfoByUsernameAndPassword($emailAddress, $password);
				return $checkStatus;

			default :
				$checkStatus = $userHelper->getUserBaseInfoByUsernameAndPassword($emailAddress, $password);
				return $checkStatus;
				
		}
	}
}
