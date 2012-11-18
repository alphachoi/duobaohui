<?php
namespace Snake\Package\Connect;

/**
 * @package connect 新浪微博互联授权及获取用户信息
 * @author yishuliu@meilishuo.com
 * 采用OAuth2.0授权
 */

Use \Snake\Package\User\UserConnect;
Use \Snake\Package\Session\UserSession;
Use \Snake\Package\User\UserLib;
Use \Snake\Package\Medal\MedalLib;
Use \Snake\Package\User\Helper\RedisUserOauth;
Use \Snake\Package\User\Helper\RedisUserConnectHelper;
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\User\UserSetting;
Use \Snake\Package\Medal\Medal;




/**
 * @author yishuliu@meilishuo.com
 * @since 2012-06-20
 * @version 1.0
 */

class WeiboAuth extends ConnectLib {
	
	function __construct(){

    } 

    /**
	 * 用户授权流程
     * @author yishuliu@meilishuo.com
     * @param $refer $_SERVER['HTTP_REFERER']
     * @param $frm isset($_GET['frm']) ? $_GET['frm'] : '';
     * @param $type = sina
     * @param array $params 包括httpRequest信息，包括oauth_code,santorini_mm等信息 
	 */
 	public function weiboAuth($type, $params = array()) {
		$base_uri = isset($params['request']->GET['baseUrl']) ? $params['request']->GET['baseUrl'] : $params['state'];

		//添加weibo refer记录log
		$logHandle = new \Snake\Libs\Base\SnakeLog('weibo_refer', 'normal');
		$logHandle->w_log(print_r($params['request']->refer, TRUE));

		$urlArray = array();
		$urlArray = parse_url($base_uri);
		$host = 'http://' . $urlArray['path'] . '/';

        $callback = $host . 'connect/auth/' . $type;
        $destUrl = 'connect/connect/' . $type;
		if (!empty($params['frm'])) {
			$callback .= "?r=" . $params['frm'];
			//$destUrl .= "?r=" . $params['frm'];
		}

        $result = array();
		$result = UserConnect::weiboAuth($callback, array(WB_AKEY, WB_SKEY), $params['state'], $params['code']);
        if ($result['result'] == TRUE) {
            $cacheObj = Memcache::instance();
            $cacheKey = 'WeiboAuth:weibo:' . $params['santorini_mm'];
            $cacheObj->set($cacheKey, $result, 3600);

            if ($params['state'] === 'settings') {
                $destUrl = 'settings/syncBind/weibo';
                $result['destUrl'] = $destUrl;
                return $result;
            }
            elseif ($params['state'] === 'bind') {
                $destUrl = 'settings/bind/weibo?state=bind';
                $result['destUrl'] = $destUrl;
                return $result;
            }
            else {
                $result['destUrl'] = $destUrl;
                return $result;
            }
            return;
		}
		elseif ($result['result'] != TRUE) {
			self::_checkRedirect($type, $params);
        	return $result;
		}
	}

    static function _checkRedirect($type, $params) {
        $frm = isset($params['request']->GET['frm']) ? $params['request']->GET['frm'] : ''; 
		$frm360 = isset($params['frm']) ? $params['frm'] : ''; 

        //frm 以fk_作为前缀的都是从浮层点击的，跳向首页
        if (strpos('prefix' . $frm, 'fk_') || (strpos("pop" . $frm, 'tk_') && strpos("pop" . $frm, 'gad'))) {
            setcookie('ORIGION_REFER', 'home', 0, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
        }
        elseif (strpos('prefix' . $frm360, 'share/share?url=')) {
            setcookie("ORIGION_REFER", $params['frm'], 0, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
        }
        elseif (!strpos($params['request']->refer, 'logon') && !strpos($params['request']->refer, 'register')) {
            setcookie('ORIGION_REFER', $params['request']->refer, 0, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
        }
    }

    /** 
	 * 用户授权成功，获取用户信息
     * @author yishuliu@meilishuo.com
     * @param $user_id int 
     * @param array $params 包括httpRequest信息，包括oauth_code,santorini_mm等信息  
     */
	public function weiboLogin($userId, $params = array()) {
        $cacheObj = Memcache::instance();
        $cacheKey = 'WeiboAuth:weibo:' . $params['santorini_mm'];
        $result = $cacheObj->get($cacheKey);

		if ($userId != 0) {
			//TODO 添加发送勋章
			//$medalLibHelper = new MedalLib($userId);
			//$medalLibHelper->handleMedalForWebapp();
			$result['destUrl'] = 'home';
			return $result;
		}

        $logHandle = new \Snake\Libs\Base\SnakeLog('connect_getWeiboInfo', 'normal');
        $start = microtime(true);
		$userInfo = UserConnect::getWeiboInfo(array(WB_AKEY, WB_SKEY), $result['weibo_access_keys']);

        $spend = microtime(true) - $start;
        $str = "Get weibo userInfo spend time is:" . $spend . "\n";
        $logHandle->w_log(print_r($str, true));

		if (empty($userInfo)) {
			$failInfo = array();
			$failInfo['error'] = '获取微博信息失败';
			return $failInfo;
		}
		$wbid = $userInfo['id'];

		if (empty($wbid)) {
			$logHandle = new \Snake\Libs\Base\SnakeLog('empty_auth', 'normal');
			$logHandle->w_log(print_r($userInfo, true));
			$redirect['destUrl'] = 'welcome';
			return $redirect;
		}

		$meiliUser = RedisUserOauth::getUserIdFromAuth('weibo', $wbid);
		if (empty($meiliUser)) {
			$param = array('user_type' => 3, 'auth' => $wbid);
			$meiliUserExist = UserConnect::getInstance()->getUserConnectFromDB($param, "user_id, access");
			if (isset($meiliUserExist[0]['user_id']) && $meiliUserExist[0]['user_id'] != 0) {
				//如果得到用户信息不为空，则代表为非第一次来，跳到用户的home页
				$weiboTokens = array();
				$user_id  = $meiliUserExist[0]['user_id'];
				$keys = $result['weibo_access_keys'];
				$weiboTokens['ttl'] = $result['weibo_access_keys']['expires_in'];
				$weiboTokens['access_token'] = implode(',', $keys);
				$weiboTokens['auth'] = $wbid;
				$redirect = $this->_updateRedis($user_id, 'weibo', $weiboTokens, $params);
				return $redirect;
			}
		}
		if (!empty($meiliUser)) {
			$weiboTokens = array();
			$keys = $result['weibo_access_keys'];
			$weiboTokens['ttl'] = $result['weibo_access_keys']['expires_in'];
			$weiboTokens['access_token'] = implode(',', $keys);
			$weiboTokens['auth'] = $wbid;
			$redirect = $this->_updateRedis($meiliUser, 'weibo', $weiboTokens, $params);
			return $redirect;
		}
		//如果信息为空，表示第一次来，引导注册
		else {
			if (!empty($userInfo)) {
				//将用户信息存在memcache
				$weiboInfo = $this->_setWeiboInfoCache($userInfo, $wbid, $result['weibo_access_keys']['access_token'], $result['weibo_access_keys']['expires_in']);
				$memKey = 'Connect:Info:' . $params['santorini_mm'];
				$cacheObj->set($memKey, $weiboInfo, 3600);

				if ($userInfo['gender'] != 'f') {
					$redirect['destUrl'] = 'connect/fail/weibo';
					if ($params['frm'] == '360') {
						$result['destUrl'] = '/app/360dev/boyfail';
					}
					return $redirect;
				}
				else {
					$redirect['reg_from'] = 3;
					$redirect['new_comer'] = 1;
					$redirect['destUrl'] = 'register/register_actionconnect';
					return $redirect;
				}
			}
		}
	}

    private function _setWeiboInfoCache($userInfo, $auth, $access_token, $ttl) { 
		$rand = mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);
        $weiboInfo = array();
        $weiboInfo['avatar'] = str_replace('/50/', '/180/', $userInfo['profile_image_url']);
        $weiboInfo['auth'] = $auth;
        $weiboInfo['email'] = '#' . $userInfo['id'] . '@t.sina.com';
        $weiboInfo['realname'] = ''; 

        //去掉屏蔽词
        $maskWords = new \Snake\Package\Spam\MaskWords($userInfo['name'], 'DFA_register');
        $maskResult = $maskWords->getMaskWords();
        $nickname = $maskResult['maskedContent'];

        $weiboInfo['nickname'] = !empty($nickname) ? mb_substr($nickname, 0 ,6, 'UTF-8') . '_wb' . $rand : substr($auth, 0 ,6, 'UTF-8') . '_wb' . $rand;
        $weiboInfo['gender'] = $userInfo['gender'];
		$locationArr = explode(' ', $userInfo['location']);
        $weiboInfo['province'] = $locationArr[0]; 
        $weiboInfo['city'] = isset($locationArr[1]) ? $locationArr[1] : '';
        $weiboInfo['openType'] = 3;
        $weiboInfo['type'] = 'bangweibo';
        $weiboInfo['access_token'] = $access_token;
        $weiboInfo['ttl'] = $ttl;
        return $weiboInfo;
    }  

	private function _updateRedis($user_id, $type, $weiboTokens, $params) {
		$wbid = $weiboTokens['auth'];
		$ttl = $weiboTokens['ttl'];
		//access_token是由access_token，remind_in，expires_in，uid和ctime拼接成
		$access_token = $weiboTokens['access_token'];

		UserConnect::getInstance()->updateUserConnectAuth($user_id, 3, $wbid, 1, $access_token);
		RedisUserConnectHelper::updateUserToken('weibo', $user_id, $ttl, $access_token);
		RedisUserConnectHelper::updateUserAuth('weibo', $user_id, $wbid);
		RedisUserOauth::updateUserIdWithAuth('weibo', $wbid, $user_id);
		$firstVisit = FALSE;
		$result = $this->userLogin($user_id, 3, $firstVisit, $params);
		return $result;
	}

	/**
     * 微博互联用户是男性操作
     * @author yishuliu@meilishuo.com
     * @param array $params 包括httpRequest信息，包括oauth_code,santorini_mm等信息  
     * @return array $userInfo
     */
	public function weiboFail($params = array()) {
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
	 * 男生勋章同步到新浪微博
	 */
	public function shareToWeibo($santor) {
        $cacheObj = Memcache::instance();
        $memKey = 'Connect:Info:' . $santor; //$params['santorini_mm'];
        $userInfo = $cacheObj->get($memKey);

		$result['access_token'] = $userInfo['access_token'];
		$medalHelper = new Medal();
		$medalId = 28;
		$medalInfo = $medalHelper->getMedalInfoByMids(array(28));
        $result['image'] =  'http://img.meilishuo.net/css/images/medal/icons/' . $medalInfo[$medalId]['medal_icon'];
        $result['content'] = '美丽说不让男的玩儿啊！！只能转给周围的女生们了，进去逛吧，喜欢啥哥给你们买！ http://www.meilishuo.com/welcome?frm=man';
		return $result;
	}

	/** 
     * 更新UserConnect,UserOauth
     * @param $userId int 用户编号
     * @param $token string access token
     * @param $auth string 
     * @param $ttl 过期时间
     */
	public function weiboUpdateToken($userId, $token, $auth, $ttl) {
		RedisUserConnectHelper::updateUserToken('weibo', $userId, $ttl, $token);
	    RedisUserConnectHelper::updateUserAuth('weibo', $userId, $auth);
		RedisUserOauth::updateUserIdWithAuth('weibo', $auth, $userId);	 

		$setting['sync_goods'] = 1; 
		$setting['sync_medal'] = 0; 
		$setting['sync_collect'] = 0; 
		$setting['sync_like'] = 0; 
		$setting['sync_ask'] = 0; 
		$setting['sync_answer'] = 0; 		
		RedisUserConnectHelper::setUserSetting('weibo', $userId, json_encode($setting));
		UserConnect::getInstance()->updateSettings($userId, 3, $setting);
	}
}
