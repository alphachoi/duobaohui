<?php
namespace Snake\Package\Connect;

/**
 * @package connect 腾讯微博互联授权及获取用户信息
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

class TxweiboAuth extends ConnectLib {
   
    /**
     * 用户授权流程
     * @author yishuliu@meilishuo.com
     * @param $refer $_SERVER['HTTP_REFERER']
     * @param $frm isset($_GET['frm']) ? $_GET['frm'] : '';
     * @param $type = txweibo
     * @param array $params 包括httpRequest信息，包括oauth_code,santorini_mm等信息
	 * @return $result array
 	 * <code>
		$result['tx_access_keys']
		{ access_token: "de943a22a5fc3ab34467fc134aee1182",
 		  expire_in: "1209600",
          code: "fda3fe1af6059bdf9a67d24ba884ce51",
          openid: "DD8C3A27A48A0F8DE851DF9E87B3B54A",
  		  openkey: "0442CB5C64BAE6FB1E8B550BD131A0B9",
		}
	 * </code>
	 */
	public function txweiboAuth($type, $params = array()) {
		$base_uri = isset($params['request']->GET['baseUrl']) ? $params['request']->GET['baseUrl'] : '';

        $urlArray = array();
        $urlArray = parse_url($base_uri);
        $host = 'http://' . $urlArray['path'] . '/';

        $callback = $host . 'connect/auth/txweibo?baseUrl=' . $urlArray['path'];
        $destUrl = 'connect/connect/txweibo';
		$result = array();
		$result = UserConnect::txweiboAuth2($callback, $keyInfo = array(TX_AKEY, TX_SKEY), $params);
        if (TRUE == $result['result']) {
			$cacheObj = Memcache::instance();
			$cacheKey = 'TxweiboAuth:txweibo:' . $params['santorini_mm'];

			$cacheObj->set($cacheKey, $result, 3600);

			$result['destUrl'] = $destUrl;
			return $result;
        }    
        elseif ($result['result'] != TRUE) {
            return $result;
        }
    }

	//腾讯微博互联目前无相关策略
	private function _checkRedirect($type, $refer, $frm) {
    }   

    /** 
     * 用户授权成功，获取用户信息
     * @author yishuliu@meilishuo.com
     * @param $user_id int 
     * @param array $params 包括httpRequest信息，包括oauth_code,santorini_mm等信息  
     */
	public function txweiboLogin($userId, $params = array()) {
		$cacheObj = Memcache::instance();
        $cacheKey = 'TxweiboAuth:txweibo:' . $params['santorini_mm'];
		$result = $cacheObj->get($cacheKey);

		$txTokens = array();
		$access_token = $result['tx_access_keys']['access_token'];
		$txTokens['ttl'] = $ttl = $result['tx_access_keys']['expire_in'];
		$open_id = $result['tx_access_keys']['openid'];

		if ($userId !=  0) {
			//has logged in , give the medal
			$dest = 'home';
            $result['destUrl'] = $dest;
            return $result['destUrl'];
		}
		$userInfo = UserConnect::getTxweiboInfo(array(TX_AKEY, TX_SKEY), $access_token, $open_id);
		if (empty($userInfo)) {
			$failInfo = array();
            $failInfo['error'] = '获取腾讯微博信息失败';
            return $failInfo;
		}
		$data = (array) $userInfo['data'];
		$wbid = $userInfo['data']->name;

		$txTokens['access_token'] = $access_token . ',' . $open_id . ',' . $ttl . ',' . $wbid;
		$txTokens['auth'] = $wbid;
		$param = array("user_type" => 8, "auth" => $wbid);

		$meiliUser = UserConnect::getInstance()->getUserConnectFromDB($param, "user_id, access");
		if (isset($meiliUser[0]['user_id']) && $meiliUser[0]['user_id'] != 0) {
			//如果得到用户信息不为空，则代表为非第一次来，跳到用户的home页
			$user_id = $meiliUser[0]['user_id'];
			$result = $this->_updateRedis($user_id, 'txweibo', $txTokens, $params);
			return $result;
		}
		//如果信息为空，表示第一次来，引导注册
		else {
			$txInfo = $this->_setTxInfoCache($data, $wbid, $access_token, $ttl);
			$memKey = 'Connect:Info:' . $params['santorini_mm'];
			$cacheObj->set($memKey, $txInfo, 3600);

			$sex = $userInfo['data']->sex;
			if (!empty($sex) && $sex != 2) {
				$result = array();
				$result['destUrl'] = 'connect/fail/txweibo';
				return $result;
			}
			else {
                $result = array();
                $result['reg_from'] = 8;
				$result['new_comer'] = 1;
                $result['destUrl'] = 'register/register_actionconnect';
                return $result;
			}
		}
	}

    private function _setTxInfoCache($userInfo, $auth, $access_token, $ttl) {
		$rand = mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);
        $txInfo = array();

		$logHandle = new \Snake\Libs\Base\SnakeLog('tx_weibo_avatar', 'normal');
		$logHandle->w_log(print_r($userInfo['head'], TRUE));

        $txInfo['avatar'] = !empty($userInfo['head']) ? $userInfo['head'].'/180' : \Snake\Libs\Base\Utilities::convertPicture('css/images/0.gif');
        $txInfo['auth'] = $userInfo['name'];
        $id = substr($userInfo['name'], -5, 5); 
        $timestmp = time();
        $txInfo['email'] = '#'.$id .$timestmp.'@t.txwb.com';
        $txInfo['realname'] = '';

        //去掉屏蔽词
        $maskWords = new \Snake\Package\Spam\MaskWords($userInfo['nick'], 'DFA_register');
        $maskResult = $maskWords->getMaskWords();
        $nickname = $maskResult['maskedContent'];

        $txInfo['nickname'] = mb_substr($nickname, 0, 6, 'UTF-8') . '_tq' . $rand;
        $txInfo['gender'] = $userInfo['sex'];
        if (!empty($userInfo['location'])) {
            $location = explode(' ', $userInfo['location']);
            $txInfo['province'] = $location[0];
            $txInfo['city'] = isset($location[1]) ? $location[1] : '';
        }
        $txInfo['openType'] = 8;
        $txInfo['type'] = 'bangtxweibo';
        $txInfo['access_token'] = $access_token;
        $txInfo['ttl'] = $ttl;
        return $txInfo;
    }

	private function _updateRedis($user_id, $type, $txTokens, $params) {
		if ($type == 'txweibo') {
			$flag = 8;
		}
		$wbid = $txTokens['auth'];
		$ttl = $txTokens['ttl'];
		//access_token由access_token, open_id, expire_time, nick拼接成
		$access_token = $txTokens['access_token'];

        UserConnect::getInstance()->updateUserConnectAuth($user_id, $flag, $wbid, 1, $access_token);
        RedisUserConnectHelper::updateUserToken($type, $user_id, $ttl, $access_token);
        RedisUserConnectHelper::updateUserAuth($type, $user_id, $wbid);
        RedisUserOauth::updateUserIdWithAuth($type, $wbid, $user_id);
        $firstVisit = FALSE;
        $result = $this->userLogin($user_id, $flag, $firstVisit, $params);
		return $result;
    }   

    /**
     * 腾讯微博互联用户是男性操作
     * @author yishuliu@meilishuo.com
     * @param array $params 包括httpRequest信息，包括oauth_code,santorini_mm等信息  
     * @return array $userInfo
     */
	public function txweiboFail($params = array()) {
		$cacheObj = Memcache::instance();
        $cacheKey = 'Connect:Info:' . $params['santorini_mm'];
		$userInfo = $cacheObj->get($cacheKey);
		if (empty($userInfo)) {
			die('获取腾讯微博失败');
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
	public function txweiboUpdateToken($userId, $token, $auth, $ttl) {
	    RedisUserConnectHelper::updateUserToken('txweibo', $userId, TRUE, $token);
		RedisUserConnectHelper::updateUserAuth('txweibo', $userId, $auth);
		RedisUserOauth::updateUserIdWithAuth('txweibo', $auth, $userId);	
	}
}
