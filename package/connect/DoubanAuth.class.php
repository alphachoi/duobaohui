<?php
namespace Snake\Package\Connect;

/**
 * @package connect 豆瓣互联授权及获取用户信息
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
 * @version 2.0
 */
class DoubanAuth extends ConnectLib {
	private static $instance = NULL;
	
	/** 
     * @return Object
     */
    public static function getInstance(){
        if (empty(self::$instance)) {
            self::$instance = new self(); 
        }   
        return self::$instance;
    }
   
	/**
     * @author yishuliu@meilishuo.com
	 * @param $refer $_SERVER['HTTP_REFERER']
	 * @param $frm isset($_GET['frm']) ? $_GET['frm'] : '';
	 * @param $type = douban
     * @param array $params 包括httpRequest信息，包括oauth_code,santorini_mm等信息  
	 */
	public function doubanAuth($type, $params = array()) {
        $base_uri = isset($params['request']->GET['baseUrl']) ? $params['request']->GET['baseUrl'] : $params['state'];

        $urlArray = array();
        $urlArray = parse_url($base_uri);
        $host = 'http://' . $urlArray['path'] . '/';

		$callback = $host . 'connect/auth/douban';
        $destUrl = 'connect/connect/douban';
		$state = $urlArray['path'];
		
		$result = array();
		$result = UserConnect::doubanAuth2($callback, array(DOUBAN_AKEY_NEW, DOUBAN_SKEY_NEW), $params['code'], $params['state']);
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
     * @author yishuliu@meilishuo.com
	 * @param $user_id int 
     * @param array $params 包括httpRequest信息，包括oauth_code,santorini_mm等信息  
	 */
	public function doubanLogin($userId, $params = array()) {
		$cacheObj = Memcache::instance();
        $cacheKey = 'douban:' . $params['santorini_mm'];
		$result = $cacheObj->get($cacheKey);
		
		$doubanTokens = array();
		$access_token = $result['douban_access_keys'];
		$key = $result['douban_access_keys']['access_token'];

        $doubanTokens['auth'] = $auth = $result['douban_access_keys']['douban_user_id'];
		$doubanTokens['ttl'] = $ttl = $result['douban_access_keys']['expires_in'];
		$doubanTokens['access_token'] = $result['douban_access_keys']['access_token']; //implode(',', $access_token);
	
		$doubanInfo = UserConnect::getDoubanInfo(array(DOUBAN_AKEY_NEW, DOUBAN_SKEY_NEW), $access_token);
		if (empty($doubanInfo)) {
            $failInfo = array();
            $failInfo['error'] = '获取豆瓣信息失败';
            return $failInfo;
		}

		$meiliUser = RedisUserOauth::getUserIdFromAuth('douban', $auth);
		if (empty($meiliUser)) {
			$param = array(
				'user_type' => 10,
				'auth' => $auth
			);
			$meiliUserExist = UserConnect::getInstance()->getUserConnectFromDB($param, "user_id, access");
			if (isset($meiliUserExist[0]['user_id']) && $meiliUserExist[0]['user_id'] != 0) {
				//用户非第一次进入，跳转到home页
				$user_id = $meiliUserExist[0]['user_id'];

				$result = $this->_updateRedis($user_id, 'douban', $doubanTokens, $params);
				return $result;
			}
		}
		else {
			$user_id = $meiliUser;
            $result = $this->_updateRedis($user_id, 'douban', $doubanTokens, $params);
			return $result;
		}
		$info = $this->_setDoubanInfoCache($doubanInfo, $auth, $key, $ttl);
        $memKey = 'Connect:Info:' . $params['santorini_mm'];
        $cacheObj->set($memKey, $info, 3600);

		//用户第一次来，引导用户注册
		$result = array();
		$result['reg_from'] = 10;
		$result['new_comer'] = 1;
		$result['destUrl'] = 'register/register_actionconnect';
		return $result;
	}

    private function _setDoubanInfoCache($userInfo, $auth, $access_token, $ttl) { 
		$rand = mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);
        $doubanInfo = array();
        $doubanInfo['avatar'] = $userInfo['image'];
        $doubanInfo['auth'] = $auth;
        $doubanInfo['email'] = '#' . $userInfo['id'] . '@t.douban.com';
        $doubanInfo['realname'] = ''; 

        //去掉屏蔽词
        $maskWords = new \Snake\Package\Spam\MaskWords($userInfo['title'], 'DFA_register');
        $maskResult = $maskWords->getMaskWords();
        $nickname = $maskResult['maskedContent'];

        $doubanInfo['nickname'] = mb_substr($nickname, 0, 6, 'UTF-8') . '_db' . $rand;
        $doubanInfo['gender'] = isset($userInfo['gender']) ? $userInfo['gender'] : '女';
        $doubanInfo['province'] = ''; 
        $doubanInfo['city'] = $userInfo['location'][0]; 
        $doubanInfo['openType'] = 10;
        $doubanInfo['type'] = 'bangdouban';
        $doubanInfo['access_token'] = $access_token;
        $doubanInfo['ttl'] = $ttl;
        return $doubanInfo;
    }   

	private function _updateRedis($user_id, $type, $doubanTokens, $params) {
		if ($type == 'douban') {
			$flag = 10;
		}
		//access_token包括access_token
		$access_token = $doubanTokens['access_token'];
		$wbid = $doubanTokens['auth'];
		$ttl = $doubanTokens['ttl'];

        UserConnect::getInstance()->updateUserConnectAuth($user_id, $flag, $wbid, 1, $access_token);
        RedisUserConnectHelper::updateUserToken($type, $user_id, $ttl, $access_token);
        RedisUserConnectHelper::updateUserAuth($type, $user_id, $wbid);
        RedisUserOauth::updateUserIdWithAuth($type, $wbid, $user_id);

        $firstVisit = FALSE;
        $result = $this->userLogin($user_id, $flag, $firstVisit, $params);
		return $result;
    }   

    /**
     * 更新UserConnect和UserOauth
     * @param $userId int 用户编号
     * @param $token string access token
     * @param $auth string 
     * @param $ttl 过期时间
     */
	public function doubanUpdateToken($userId, $token, $auth, $ttl) {
	    RedisUserConnectHelper::updateUserToken('douban', $userId, TRUE, $token);
		RedisUserConnectHelper::updateUserAuth('douban', $userId, $auth);
		RedisUserOauth::updateUserIdWithAuth('douban', $auth, $userId);		
	}
}
