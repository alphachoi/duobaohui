<?php
namespace Snake\Package\Session;

use \Snake\Package\User\User AS User;
use \Snake\Package\User\UserConnect AS UserConnect;
use \Snake\libs\Cache\Memcache AS Memcache;
Use \Snake\Package\User\Helper\RedisUserConnectHelper;
Use \Snake\Package\Mall\Mall;
Use \Snake\Package\Medal\MedalLib;
Use \Snake\Package\Shareoutside\ShareHelper;

/**
 * DataBase Store Session Class
 * @author ChenHailong
 * updated 2011-11-08
 */

class UserSession {

	private $request;
	private $globalCookieStr;
	private static $session_id;
	private static $session;
	private static $expired = 7200;
	private static $memcache;
	public static $online_status_prefix = 'user:login_status:';
	private $timestamp;

	/**
	 * initial function
	 */
	function __construct($sessionData = FALSE, $request) {
		if (\Snake\Libs\Base\Utilities::isSearchEngine() === TRUE) {
			return FALSE;
		}
		//$sessionData = $_SESSION['userInfo'];
		self::$memcache = Memcache::instance();
		$this->request = $request;
		$this->timestamp = $_SERVER['REQUEST_TIME'];
		//从cookie里面获得session_id
		$cookie_key = DEFAULT_SESSION_NAME;
		if (isset($this->request->COOKIE[$cookie_key])) {
			self::$session_id = $this->request->COOKIE[$cookie_key];
		}
		else {
			self::$session_id = '';
		}
		if (!empty($sessionData)) {
			self::$session = $sessionData;
		}

		
		$this->_generateSession();
		self::$session['avatar_c'] = empty(self::$session['avatar_c']) ? \Snake\Libs\Base\Utilities::convertPicture("/css/images/0.gif") : \Snake\Libs\Base\Utilities::convertPicture(self::$session['avatar_c']);
		self::$session['avatar_d'] = empty(self::$session['avatar_d']) ? \Snake\Libs\Base\Utilities::convertPicture("/css/images/0.gif") : \Snake\Libs\Base\Utilities::convertPicture(self::$session['avatar_d']);

		if (!empty(self::$session['user_id']) && strpos(self::$session['avatar_c'], "/css/images/0.gif") !== FALSE) {
			$cacheKey = "users_temp_avatar_" . self::$session['user_id'];
			$tempAvatar = self::$memcache->get($cacheKey);
			if (!empty($tempAvatar)) {
				self::$session['avatar_d'] = $tempAvatar;
				self::$session['avatar_c'] = $tempAvatar;
			}
		}
        if (!empty(self::$session['user_id']) && !empty(self::$session['nickname'])) {
            if (mb_strpos(self::$session['nickname'], '#', 0, 'utf-8') > 0) {
                $nick = explode('#', self::$session['nickname']);
                self::$session['nickname'] = $nick[0];
            }
			if (mb_strlen(self::$session['nickname'], 'utf-8') > 9) {
				self::$session['nickname'] = mb_substr(self::$session['nickname'], 0, 8, 'utf-8');
				self::$session['nickname'] = self::$session['nickname'] . '..';
			}
			elseif (mb_strlen(self::$session['nickname'], 'utf-8') == 9) {
				self::$session['nickname'] = mb_substr(self::$session['nickname'], 0, 9, 'utf-8');
			}
        }
		global $GLOBAL_COOKIE_STRING;
		$GLOBAL_COOKIE_STRING = $this->globalCookieStr;

		//TODO
		global $_ZSESSION;
	}

	function __destruct() {

	}

	/**
	 * 如果没有session，生成新的session，其中customer_id,email应该为空
	 **/
	private function _generateSession() {
		$ip = $this->request->ip;
		$useragent = $this->request->agent;
		$timestamp = $this->timestamp;
		
		//(!empty($this->request->COOKIE['MEILISHUO_GLOBAL_KEY']) && $this->globalCookieStr = $this->request->COOKIE['MEILISHUO_GLOBAL_KEY']) || $this->setGlobalKey();

		if (empty($this->request->COOKIE['MEILISHUO_GLOBAL_KEY'])) {
			$this->setGlobalKey();
		}
		else {
			$this->globalCookieStr = $this->request->COOKIE['MEILISHUO_GLOBAL_KEY'];
		}

		$cookie_key = DEFAULT_SESSION_NAME;
		if (empty($this->request->COOKIE[$cookie_key]) || $this->request->COOKIE[$cookie_key] == 'deleted') {
			self::$session_id = \Snake\Libs\Base\Utilities::getUniqueId();
			setcookie($cookie_key, self::$session_id, 0, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
			$this->request->COOKIE[$cookie_key] = self::$session_id;
			self::$memcache->delete(self::$session_id);

			global $_ZSESSION;
			$_ZSESSION[$cookie_key] = self::$session_id;
		}
		else {
			self::$session_id = $this->request->COOKIE[$cookie_key];
		}
		// the cookie will be available within the entire domain,will expire at the end of the session (when the browser closes).
		$saveData = array ();
		$saveData['session_id'] = self::$session_id;
		$saveData['keyid'] = isset(self::$session['user_id']) ? self::$session['user_id'] : 0;
		$saveData['logon_ip'] = $ip;
		if (empty(self::$session)) {
			self::$session['user_id'] = 0;
			self::$session['reg_from'] = !empty($this->request->COOKIE[CHANNEL]) ? $this->request->COOKIE[CHANNEL] : 0;
		}
		$saveData['session_data'] = self::$session;

		//设置用户session到memcache	
		$this->setUserSession($saveData);

		//if not login but has cookie MEILISHUO_MM, then login automatically
        if (empty(self::$session['user_id'])) {
            $this->autoLogon($saveData, $this->request->COOKIE, $this->request->path_args);
        }
		//得到用户微博信息
		//判断用户是否是qzone互联用户，是否弹出qzone加粉弹窗
		//$this->_check_qzone_fans();

		//添加meilishuo total_fans_num 数据
		//$this->_fetch_total_fans();
		//$this->_initOpenTokens();
	}

	/**
	 * API Function: get the session values
	 */
	function get_session() {
		if (!empty(self::$session['user_id']) && !empty(self::$session['nickname'])) {
			if (mb_strpos(self::$session['nickname'], '#', 0, 'utf-8') > 0) {
				$nick = explode('#', self::$session['nickname']);
				self::$session['nickname'] = $nick[0];
			} 
		}
		if (!empty(self::$session['user_id'])) {
			$cacheKey = "QOP:" . self::$session['user_id'];
			if (empty(self::$memcache)) {
				self::$memcache = Memcache::instance();
			}
			$params = array(
				self::$session['user_id'] => $this->request->COOKIE['santorini_mm']
				);
			self::$memcache->set($cacheKey, $params, 86400);
		}
		return self::$session;
	}

	static function updateSessionData($sessionData, $COOKIE) {
		if (empty($sessionData)) {
			return FALSE;
		}
		$cookie_key = DEFAULT_SESSION_NAME;
		if (empty(self::$memcache)) {
			self::$memcache = Memcache::instance(); 
		}
		if (empty(self::$session_id)) {
			self::$session_id = $COOKIE[$cookie_key];
		}
		$sessionId = self::$session_id;
		$bigSession = self::$memcache->get($sessionId);
		$bigSession['session_data'] = $sessionData;
		if (!empty($sessionData['user_id'])) {
			$bigSession['keyid'] = $sessionData['user_id'];
		}
		self::$memcache->set($sessionId, $bigSession, self::$expired);
		self::$session = $sessionData;
		if ($bigSession['keyid'] > 0) {
			self::$memcache->set(self::$online_status_prefix . $bigSession['keyid'], TRUE, 2700);
		}
		return TRUE;
	}

	public function setGlobalKey() {
		$seashell = !empty($this->request->seashell) ? $this->request->seashell : '';
		$this->globalCookieStr = \Snake\Libs\Base\Utilities::getGlobalKey($seashell);
		setcookie('MEILISHUO_GLOBAL_KEY', $this->globalCookieStr, $this->timestamp + 3600 * 24 * 365 * 500, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
	}

	public function setUserSession($saveData) {
		$result = self::$memcache->get(self::$session_id);
		$reportUserHelper = new \Snake\Package\Spam\ReportUser();
		if (empty($result)) {
			$saveData['last_active_time'] = $this->timestamp;
			if (!empty($saveData['keyid'])) {
				//$saveData['session_data'] = self::setAvatar($saveData['keyid'], $saveData['session_data']);
				$saveData['session_data'] = self::_initOpenTokens($saveData['keyid'], $saveData['session_data']);
				$saveData['session_data']['isjb'] = $reportUserHelper->allowReport($saveData['keyid']); 
				if(empty($saveData['session_data']['gkey'])) {
					$saveData['session_data']['gkey'] = self::refreshGlobalKey($saveData['keyid']);
				}
			}
			//设置session到memcache
			self::_setSession(self::$session_id, $saveData);
		}
		else {
			//判断是否需要更新, 更新条件：或者距离上一次活动时间大于5分钟
            if ($saveData['keyid'] > 0 || ($result['keyid'] > 0 && $this->timestamp - $result['last_active_time'] > 300)) {
                if (empty($saveData['keyid'])) {
                    $saveData = $result; 
                }   
                $saveData['last_active_time'] = $this->timestamp;
				//$saveData['session_data'] = self::setAvatar($saveData['keyid'], $saveData['session_data']);
				$saveData['session_data'] = self::_initOpenTokens($saveData['keyid'], $saveData['session_data']);
				$saveData['session_data']['isjb'] = $reportUserHelper->allowReport($saveData['keyid']); 
				if(empty($saveData['session_data']['gkey'])) {
					$saveData['session_data']['gkey'] = self::refreshGlobalKey($saveData['keyid']);
				}
                self::_setSession(self::$session_id, $saveData);
            }
			else {
				if (!empty($saveData['keyid'])) {
					//$result['session_data'] = self::setAvatar($result['keyid'], $result['session_data']);
					$result['session_data'] = self::_initOpenTokens($result['keyid'], $result['session_data']);
					$result['session_data']['isjb'] = $reportUserHelper->allowReport($result['keyid']);
					if(empty($result['session_data']['gkey'])) {
						$result['session_data']['gkey'] = self::refreshGlobalKey($result['keyid']);
					}
				}
				self::$session = $result['session_data'];
			}
		}
	}

	static public function autoLogon($saveData, $COOKIE, $args) {
		if ((empty(self::$session['user_id']) || !isset(self::$session['weibo'])) && !empty($COOKIE['MEILISHUO_MM'])) { 
			$userData = array('cookie' => $COOKIE['MEILISHUO_MM']);
			$user = new User();
			$user->getUserFromCookie($userData, array('*', 'avatar_c', 'avatar_d'), TRUE);
			//print_r($user->getUser());die('**');
			$user_id = $user->user_id;
			if (!empty($user_id) && $user->is_actived >= 0) {
				$user->updateLogonInfo($user_id);
				$saveData['session_data'] = $user->getUser();
				$saveData['session_data']['is_mall'] = 0;
				$helper = new Mall();
				$result = $helper->getMallInfoById($user_id);
				if (!empty($result)) {
					$saveData['session_data']['is_mall'] = 1;
				}
				$saveData['keyid'] = $user->user_id;
				//$saveData['session_data'] = self::setAvatar($saveData['keyid'], $saveData['session_data']);
				$saveData['session_data'] = self::_initOpenTokens($saveData['keyid'], $saveData['session_data']);
				//是否举报
				$reportUserHelper = new \Snake\Package\Spam\ReportUser();
				$saveData['session_data']['isjb'] = $reportUserHelper->allowReport($user_id); 

				//set globalkey
				$globalKey = self::refreshGlobalKey($user_id);
				$saveData['session_data']['gkey'] = $globalKey;

				//七夕活动自动登录授予勋章
				$endTime = mktime(23, 59, 0, 8, 24, 2012);
				$currentTime = time();
				if ($currentTime < $endTime) {
					$medalId = 71;
					$medalLibHelper = new MedalLib();
					$medalLibHelper->medalLib($user->user_id);
					$medalLibHelper->addMedalForQixi($medalId, $saveData['session_data']);
				}
				self::_setSession(self::$session_id, $saveData);
			}
		}
	}

	static private function refreshGlobalKey ($userId) {
		//set globalkey
		$userHelper = new User();
		$globalKey = $userHelper->checkGlobalKey($userId);
		$globalKey = $globalKey[0]['global_key'];
		if (!empty($globalKey)) {
			setcookie ('MEILISHUO_GLOBAL_KEY', $globalKey, time() + 3600 * 24 * 365, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
		}
		return $globalKey;
	}


	static private function setAvatar($userId, $saveData) {
		$avatarKey = 'users_temp_avatar_' . self::$session;	
		$avatar = self::$memcache->get($avatarKey);
		$saveData['avatar_c'] = $avatar;
		$saveData['avatar_d'] = $avatar;
		return $saveData;
	}

	/**
     * 　１ 表示已经互联并同步，０表示没有互联，２表示互联不同步。 
	 **/
    static private function _initOpenTokens($userId, $saveData) {
            $saveData['weibo'] = 0;
            $saveData['qzone'] = 0;

		return $saveData;
		if (empty($userId)) {
			return FALSE;
		}
        $weiboSettings = RedisUserConnectHelper::getUserSetting('weibo', $userId);
        $weiboSettings = json_decode($weiboSettings, TRUE);
        $weiboToken = RedisUserConnectHelper::getUserToken('weibo', $userId);
        if (!empty($weiboSettings)) {
            $weibo = $weiboSettings['sync_goods'];
        }   
        else {
            $weibo = 1;
        }   
        if ($weibo == 0 && !empty($weiboToken)) {
            $saveData['weibo'] = 2;
        }   
        elseif ($weibo == 1 && !empty($weiboToken)) {
            $saveData['weibo'] = 1;
        }   
        else {
            $saveData['weibo'] = 0;
			$weiboAuth = RedisUserConnectHelper::getUserAuth('weibo', $userId);
			if (!empty($weiboAuth)) {
				$saveData['weibo'] = 3;
			}
        }   
        $qzoneSettings = RedisUserConnectHelper::getUserSetting('qzone', $userId);
        $qzoneSettings = json_decode($qzoneSettings, TRUE);
        $qzoneToken = RedisUserConnectHelper::getUserToken('qzone', $userId);
        if (!empty($qzoneSettings)) {
            $qzone = $qzoneSettings['sync_goods'];
        }   
        else {
            $qzone = 1;
        }   
        if ($qzone == 1 && !empty($qzoneToken)) {
            $saveData['qzone'] = 1;
        }   
        elseif (!empty($qzoneToken)) {
            $saveData['qzone'] = 2;    
        }   
        else {
            $saveData['qzone'] = 0;
			$qzoneAuth = RedisUserConnectHelper::getUserAuth('qzone', $userId);
			if (!empty($qzoneAuth)) {
				$saveData['qzone'] = 3;
			}
        }   
		return $saveData;
    } 

	private function _check_qzone_fans() {
		if (empty(self::$session['user_id'])) {
			return FALSE;
		}
		if (isset(self::$session['qzone_notfans'])) {
			return;
		}
		else {
			//$userConnect = new UserConnect();
			self::$session['qzone_notfans'] = UserConnect::getInstance()->checkQzoneIsFans(self::$session['user_id']);
			UserSession::updateSessionData(self::$session, $this->request->COOKIE);
		}
	}

	private function _fetch_total_fans() {
		if (isset(self::$session['total_fans_num'])) {
			return;
		}
		else {
			self::$session['total_fans_num'] = UserConnect::getInstance()->getTotalFansNum();
			UserSession::updateSessionData(self::$session, $this->request->COOKIE);
		}
	}

	/**
	 * API Function: destroy session
	 */
	static public function destroy_session($sessionId, $uid = 0) {
		//$sessionId = isset($this->request->COOKIE[DEFAULT_SESSION_NAME]) ? $this->request->COOKIE[DEFAULT_SESSION_NAME] : '';
		if (empty($sessionId)) {
			return TRUE;
		}
		if (empty(self::$memcache)) {
			self::$memcache = Memcache::instance(); 
		}
		self::$memcache->delete($sessionId);
		if ($uid > 0) {
			self::$memcache->delete(self::$online_status_prefix . $uid);
		}
		$cookie_key = DEFAULT_SESSION_NAME;
		//@setcookie($cookie_key, FALSE, $_SERVER['REQUEST_TIME'] - 3600, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
		setcookie('MEILISHUO_MM', FALSE, time() - 3600, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
		//@setcookie($cookie_key, FALSE, $_SERVER['REQUEST_TIME'] - 3600, DEFAULT_COOKIEPATH, 'www.meilishuo.com');
		setcookie('MEILISHUO_RZ', FALSE, time() - 3600, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
	}

	/**
	 * set session and user login status
	 * @param unknown_type $sessionId
	 * @param unknown_type $sessionData
	 * @param unknown_type $expired
	 */
	static function _setSession($sessionId, $sessionData, $expired = 0) {
		if (!is_array($sessionData) || empty($sessionData)) {
			return ;
		}
        if (empty($expired)) {
            $expired = self::$expired;
        }
        if (!empty($sessionData['session_data']) && $sessionData['session_data']['user_id'] > 0) {
            setcookie('MEILISHUO_RZ', $sessionData['session_data']['user_id'] * 3 + 7, 0, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
		}
		self::$memcache->set($sessionId, $sessionData, $expired);
		self::$session = $sessionData['session_data'];
		if ($sessionData['keyid'] > 0) {
			self::$memcache->set(self::$online_status_prefix . $sessionData['keyid'], TRUE, 2700);
		}
	}

	static function getOnlineStatusByUids($uids) {
		self::$memcache = Memcache::instance();
		$status = array();
		foreach ($uids as $uid) {
			$status[$uid] = (bool) self::$memcache->get(self::$online_status_prefix . $uid);
		}
		return $status;
	}
}
