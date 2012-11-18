<?php
namespace Snake\Package\Connect;

/**
 * @package connect 网易互联授权及获取用户信息
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

/**
 * @author yishuliu@meilishuo.com
 * @since 2012-06-20
 * @version 1.0
 */

class WangyiAuth extends ConnectLib {
	private static $instance = NULL;
	
    public static function getInstance(){
        if (empty(self::$instance)) {
            self::$instance = new self(); 
        }   
        return self::$instance;
    }
   
    /**
     * 用户授权流程
     * @author yishuliu@meilishuo.com
     * @param $refer $_SERVER['HTTP_REFERER']
     * @param $frm isset($_GET['frm']) ? $_GET['frm'] : '';
     * @param $type = wangyi
     * @param array $params 包括httpRequest信息，包括oauth_code,santorini_mm等信息  
	 */
	public function wangyiAuth($type, $params = array()) {
        $base_uri = isset($params['request']->GET['baseUrl']) ? $params['request']->GET['baseUrl'] : $params['state'];

        $urlArray = array();
        $urlArray = parse_url($base_uri);
        $host = 'http://' . $urlArray['path'] . '/';

        $callback = $host . 'connect/auth/wangyi';
        $destUrl = 'connect/connect/wangyi';
        $state = $urlArray['path'];
    
        $result = array();
        $result = UserConnect::wangyiAuth2($callback, array(WY_AKEY_NEW, WY_SKEY_NEW), $params['code'], $state);
        if (TRUE == $result['result']) {
            $cacheObj = Memcache::instance();
            $cacheKey = $type . ':' . $params['santorini_mm'];
            $cacheObj->set($cacheKey, $result, 3600);

            $result['destUrl'] = $destUrl;
            return $result;
        }    
        elseif ($result['result'] != TRUE) {
            return $result;
        }   
    }

	private function _checkRedirect($type, $refer, $frm) {
    }   

    /** 
     * 用户授权成功，获取用户信息
     * @author yishuliu@meilishuo.com
     * @param $user_id int 
     * @param array $params 包括httpRequest信息，包括oauth_code,santorini_mm等信息  
     */
	public function wangyiLogin($userId, $params = array()) {
		//print_r($params);
		$cacheObj = Memcache::instance();
        $cacheKey = 'wangyi:' . $params['santorini_mm'];
		$result = $cacheObj->get($cacheKey);

		$wangyiTokens = array();
		$tokens = $result['163_access_keys'];
		if (!empty($tokens)) {
			$wangyiTokens['access_token'] = implode(',', $tokens);
		}
		$access_token = $result['163_access_keys']['access_token'];
		$refresh_token = $result['163_access_keys']['refresh_token'];
		$wangyiTokens['ttl'] = $ttl = $result['163_access_keys']['expires_in'];

		$userInfo = UserConnect::getWangyiInfo(array(WY_AKEY_NEW, WY_SKEY_NEW), $access_token);
		if (empty($userInfo)) {
            $failInfo = array();
            $failInfo['error'] = '获取163信息失败';
            return $failInfo;
		}
		$wangyiTokens['auth'] = $auth = $userInfo['id'];

        $meiliUser = RedisUserOauth::getUserIdFromAuth('wangyi', $auth);
        if (!empty($meiliUser)) {
            //如果得到用户信息不为空，则代表为非第一次来，跳到用户的home页
            $user_id = $meiliUser;
            $result = $this->_updateRedis($user_id, 'wangyi', $wangyiTokens, $params);
            return $result;
        }   
        if (empty($meiliUser)) {
            $param = array('user_type' => 7, 'auth' => $auth);
            $meiliUserExist = UserConnect::getInstance()->getUserConnectFromDB($param, "user_id, access");
            if (isset($meiliUserExist[0]['user_id']) && $meiliUserExist[0]['user_id'] != 0) {
                //如果得到用户信息不为空，则代表为非第一次来，跳到用户的home页
                $user_id = $meiliUserExist[0]['user_id'];
                $result = $this->_updateRedis($user_id, 'wangyi', $wangyiTokens, $params);
                return $result;
            }   
        }   

		$wangyiInfo = $this->_setWangyiInfoCache($userInfo, $auth, $access_token, $ttl);
        $memKey = 'Connect:Info:' . $params['santorini_mm'];
        $cacheObj->set($memKey, $wangyiInfo, 3600);

		if (!empty($userInfo) && $userInfo['gender'] == 1) {
			$result = array();
			$result['destUrl'] = 'connect/fail/wangyi';
			return $result;
		}
		else {
			$result = array();
			$result['reg_from'] = 7;
			$result['new_comer'] = 1;
			$result['destUrl'] = 'register/register_actionconnect';
			return $result;
		}
	}

    private function _setWangyiInfoCache($userInfo, $auth, $access, $ttl) {
		$rand = mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);
        $wangyiInfo = array();
        $wangyiInfo['avatar'] = str_replace('=48', '=80', $userInfo['profile_image_url']);
        $wangyiInfo['auth'] = $auth;
        $wangyiInfo['email'] = $userInfo['email'];
        $wangyiInfo['realname'] = $userInfo['realName'];

        //去掉屏蔽词
        $maskWords = new \Snake\Package\Spam\MaskWords($userInfo['name'], 'DFA_register');
        $maskResult = $maskWords->getMaskWords();
        $nickname = $maskResult['maskedContent'];

        $wangyiInfo['nickname'] = mb_substr($nickname, 0, 6, 'UTF-8') . '_wy' . $rand;
        $wangyiInfo['gender'] = $userInfo['gender'];
        $wangyiInfo['province'] = '';
        $wangyiInfo['city'] = '';
        $wangyiInfo['openType'] = 7;
        $wangyiInfo['type'] = 'bangwangyi';
        $wangyiInfo['access_token'] = $access;
        $wangyiInfo['ttl'] = $ttl;
        return $wangyiInfo;
    }

	private function _updateRedis($user_id, $type, $wangyiTokens, $params) {
		if ($type == 'wangyi') {
			$flag = 7;
		}
		//access_token包括oauth_token，oauth_token_secret
		$access_token = $wangyiTokens['access_token'];
		$ttl = $wangyiTokens['ttl'];
		$wbid = $wangyiTokens['auth'];
        UserConnect::getInstance()->updateUserConnectAuth($user_id, $flag, $wbid, 1, $access_token);
        RedisUserConnectHelper::updateUserToken($type, $user_id, $ttl, $access_token);
        RedisUserConnectHelper::updateUserAuth($type, $user_id, $wbid);
        RedisUserOauth::updateUserIdWithAuth($type, $wbid, $user_id);
        $firstVisit = FALSE;
        $result = $this->userLogin($user_id, $flag, $firstVisit, $params);
		return $result;
    }   

    /**
     * 网易互联用户是男性操作
     * @author yishuliu@meilishuo.com
     * @param array $params 包括httpRequest信息，包括santorini_mm等信息  
     * @return array $userInfo
     */
	public function wangyiFail($params = array()) {
		$cacheObj = Memcache::instance();
        $cacheKey = 'Connect:Info:' . $params['santorini_mm'];
		$userInfo = $cacheObj->get($cacheKey);

		if (empty($userInfo)) {
			die('获取163信息失败');
		}
        $userInfo['gender'] = '男';
        $userInfo['school'] = '';
        $userInfo['workplace'] = '';
		$cacheObj->delete($cacheKey);
        return $userInfo;
	}

    /** 
     * 更新UserConnect和UserOauth
     * @param $userId int 用户编号
     * @param $token string access token
     * @param $auth string 
     * @param $ttl 过期时间
     */
	public function wangyiUpdateToken($userId, $token, $auth, $ttl) {
		$type = 'wangyi';	
		RedisUserConnectHelper::updateUserToken($type, $userId, $ttl, $token);
        RedisUserConnectHelper::updateUserAuth($type, $userId, $auth);
        RedisUserOauth::updateUserIdWithAuth($type, $auth, $userId);		
	}
}
