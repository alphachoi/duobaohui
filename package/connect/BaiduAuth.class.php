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

/**
 * @author yishuliu@meilishuo.com
 * @since 2012-06-20
 * @version 1.0
 */

class BaiduAuth extends ConnectLib {
	
	/**
     * 用户授权流程
	 * @author yishuliu@meilishuo.com
	 * @param $refer $_SERVER['HTTP_REFERER']
	 * @param $frm isset($_GET['frm']) ? $_GET['frm'] : '';
     * @param $type = baidu
     * @param array $params 包括httpRequest信息，包括oauth_code,santorini_mm等信息  
	 * Array(		
			[baiduInfo] => Array
				(
					[userid] => 52126306
					[username] => 卡尼吉亚0001
					[sex] => 0
					[birthday] => 0000-00-00
					[access_token] => 3.4d5725deb1f1113badd3befd747c1293.2592000.1342865080.52126306-125130
				)

			[result] => 1
			[destUrl] => http://ls.meilishuo.com/connect/loginSuccess/baidu
		) 
	 */
	public function baiduAuth($type, $params = array()) {
        //$base_uri = isset($params['request']->GET['baseUrl']) ? $params['request']->GET['baseUrl'] : '';

        //$urlArray = array();
        //$urlArray = parse_url($base_uri);
        //$host = 'http://' . $urlArray['path'] . '/';

		$callback = 'http://www.meilishuo.com/connect/auth/baidu';
        $destUrl = 'connect/connect/baidu';

		$result = array();
        $result = UserConnect::baiduAuth($callback, array(BAIDU_AKEY, BAIDU_SKEY), $params);
        if ($result['result'] == TRUE) {
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
	public function baiduLogin($userId, $params = array()) {
		$cacheObj = Memcache::instance();
        $cacheKey = 'baidu:' . $params['santorini_mm'];
		$result = $cacheObj->get($cacheKey);

		$baiduTokens = array();
		$baiduTokens['access_token'] = $result['baiduInfo']['access_token'];
		$baiduTokens['ttl'] = TRUE;
		$baiduTokens['auth'] = $auth = $result['baiduInfo']['userid'];

		$keyInfo = array(BAIDU_AKEY, BAIDU_SKEY);
		$aKey = $keyInfo[0];
		$sKey = $keyInfo[1];
		if (empty($auth)) {
            $failInfo = array();
            $failInfo['error'] = '获取百度用户信息失败!';
            return $failInfo;
		}

		$meiliUser = '';
		//get user_id from redis where auth = auth
		$meiliUser = RedisUserOauth::getUserIdFromAuth('baidu', $auth);
		//print_r($meiliUser);
		if (empty($meiliUser)) {
			$param = array('user_type' => 5, 'auth' => $auth);
			$meiliUserExist = UserConnect::getInstance()->getUserConnectFromDB($param, "user_id, access");
			if (isset($meiliUserExist[0]['user_id']) && $meiliUserExist[0]['user_id'] != 0) {
				//如果得到用户信息不为空，则代表为非第一次来，跳到用户的home页
				$user_id = $meiliUserExist[0]['user_id'];
				$result = $this->_updateRedis($user_id, 'baidu', $baiduTokens, $params);
				return $result;
			}
		}
		if (!empty($meiliUser)) {
			//如果得到用户信息不为空，则代表为非第一次来，跳到用户的home页
			$user_id = $meiliUser;
			$result = $this->_updateRedis($user_id, 'baidu', $baiduTokens, $params);
			return $result;
		}
		else {
			$baiduInfo = $this->_setBaiduInfoCache($result['baiduInfo'], $auth, $baiduTokens['access_token']);
			$memKey = 'Connect:Info:' . $params['santorini_mm'];
			$cacheObj->set($memKey, $baiduInfo, 3600);

			$sex = $result['baiduInfo']['sex'];
			if (!empty($sex) && $sex == '男') {
				$result = array();
				$result['destUrl'] = 'connect/fail/baidu';
				return $result;
			}
			else {
				$result = array();
				$result['reg_from'] = 5;
				$result['new_comer'] = 1;
				$result['destUrl'] = 'register/register_actionconnect';
				return $result;
			}
		}
	}

    private function _setBaiduInfoCache($userInfo, $auth, $access_token) { 
		$rand = mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);
        $baiduInfo = array();
        $baiduInfo['avatar'] = !empty($userInfo['portrait']) ? "http://himg.bdimg.com/sys/portrait/item/{$userInfo['portrait']}.jpg" : \Snake\Libs\Base\Utilities::convertPicture('/css/images/0.gif');
        $baiduInfo['auth'] = $auth;
        $baiduInfo['email'] = '#' . $userInfo['userid'] . '@t.baidu.com';
        $baiduInfo['realname'] = $userInfo['realname']; 

        //去掉屏蔽词
        $maskWords = new \Snake\Package\Spam\MaskWords($userInfo['username'], 'DFA_register');
        $maskResult = $maskWords->getMaskWords();
        $nickname = $maskResult['maskedContent'];

        $baiduInfo['nickname'] = mb_substr($nickname, 0, 6, 'UTF-8') . '_bd' . $rand;
        $baiduInfo['gender'] = $userInfo['sex'];
        $baiduInfo['province'] = ''; 
        $baiduInfo['city'] = ''; 
        $baiduInfo['openType'] = 5;
        $baiduInfo['type'] = 'bangbaidu';
        $baiduInfo['access_token'] = $access_token;
        $baiduInfo['ttl'] = TRUE;
        return $baiduInfo;
    }   

	private function _updateRedis($user_id, $type, $baiduTokens, $params) {
		if ($type == 'baidu') {
			$flag = 5;
		}
		//access_token只包含access_token
		$access_token = $baiduTokens['access_token'];
		$ttl = $baiduTokens['ttl'];
		$wbid = $baiduTokens['auth'];
        UserConnect::getInstance()->updateUserConnectAuth($user_id, $flag, $wbid, 1, $access_token);
        RedisUserConnectHelper::updateUserToken($type, $user_id, $ttl, $access_token);
        RedisUserConnectHelper::updateUserAuth($type, $user_id, $wbid);
        RedisUserOauth::updateUserIdWithAuth($type, $wbid, $user_id);
        $firstVisit = FALSE;
        $result = $this->userLogin($user_id, $flag, $firstVisit, $params);
		return $result;
    }   

    /**
     * 百度互联用户是男性操作
     * @author yishuliu@meilishuo.com
     * @param array $params 包括httpRequest信息，包括santorini_mm等信息  
     * @return array $userInfo
     */
	public function baiduFail($params = array()) {
		$cacheObj = Memcache::instance();
        $cacheKey = 'Connect:Info:' . $params['santorini_mm'];
		$userInfo = $cacheObj->get($cacheKey);

		if (empty($userInfo)) {
			die('获取百度信息失败');
		}
        $userInfo['gender'] = '男';
        $userInfo['school'] = '';
        $userInfo['workplace'] = '';
		$cacheObj->delete($cacheKey);
        return $userInfo;
	}

	public function baiduUpdateToken($userId, $token, $auth, $ttl) {
		$type = 'baidu';
        RedisUserConnectHelper::updateUserToken($type, $userId, $ttl, $token);
        RedisUserConnectHelper::updateUserAuth($type, $userId, $auth);
        RedisUserOauth::updateUserIdWithAuth($type, $auth, $userId);
	}
}
