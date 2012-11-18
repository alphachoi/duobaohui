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
Use \Snake\Package\Oauth\RenrenClient;

/**
 * @author yishuliu@meilishuo.com
 * @since 2012-06-20
 * @version 1.0
 */
class RenrenAuth extends ConnectLib {

    /**
	 * 用户互联授权
     * @author yishuliu@meilishuo.com
     * @param $refer $_SERVER['HTTP_REFERER']
     * @param $frm isset($_GET['frm']) ? $_GET['frm'] : '';
     * @param $type = renren
     * @param array $params 包括httpRequest信息，包括oauth_code,santorini_mm等信息 
     */
	 public function renrenAuth($type, $params = array()) {
        $base_uri = isset($params['request']->GET['baseUrl']) ? $params['request']->GET['baseUrl'] : $params['state'];

        $urlArray = array();
        $urlArray = parse_url($base_uri);
        $host = 'http://' . $urlArray['path'] . '/';

		$callback = $host . 'connect/auth/renren';
        $destUrl = 'connect/connect/renren';
        if (isset($params['code'])){
			$result = array();
            $result = UserConnect::renrenAccess($callback, array(RENREN_API_KEY, RENREN_SECRET), $params['code'], $params['ip']);
            if (!empty($result['renren_access_keys'])){
                $cacheObj = Memcache::instance();
                $cacheKey = $type . ':' . $params['santorini_mm'];
                $cacheObj->set($cacheKey, $result, 3600);

                $result['destUrl'] = $destUrl;
                return $result;
            }    
            return FALSE;
        }    
        else {
			$this->_checkRedirect($type, $params);
			$param = array();
            $param['state'] = md5(uniqid(rand(), TRUE));
            $result = UserConnect::renrenAuth($callback, $param['state'], array(RENREN_API_KEY, RENREN_SECRET), NULL, $params['ip']);
			return $result;
        }    
	}
	
	private function _checkRedirect($type, $params) {
        $refer = isset($params['request']->refer) ? $params['request']->refer : ''; 
        $frm = isset($params['request']->GET['frm']) ? $params['request']->GET['frm'] : ''; 
		//frm 以fk_作为前缀的都是从浮层点击的，跳向首页
		if (strpos('prefix' . $frm, 'fk_')) {
			setcookie("ORIGION_REFER", 'home', 0, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);    
		}    
		elseif (!strpos($params['request']->refer, 'logon') && !strpos($params['request']->refer, 'register')) {
			setcookie("ORIGION_REFER", $params['request']->refer,  0, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
		}    
        return TRUE;
    }

    /** 
     * 用户授权成功，获取用户信息
     * @author yishuliu@meilishuo.com
     * @param $user_id int 
     * @param array $params 包括httpRequest信息，包括oauth_code,santorini_mm等信息  
     */
	public function renrenLogin($userId, $params = array()) {
        $cacheObj = Memcache::instance();
        $cacheKey = 'renren:' . $params['santorini_mm'];
        $result = $cacheObj->get($cacheKey);

		$renrenTokens = array();

        $access_token = $result['renren_access_keys']['access_token'];
		$refresh_token = $result['renren_access_keys']['refresh_token'];
        $renrenTokens['ttl'] = $ttl = $result['renren_access_keys']['expires_in'];
        $renrenTokens['auth'] = $auth = $result['renren_access_keys']['user']['id'];
		$renrenTokens['access_token'] = $access_token;  // . ',' . $ttl . ',' . $auth;
		if (empty ($auth)) {
            $failInfo = array();
            $failInfo['error'] = '获得人人信息错误!';
            return $failInfo;
		}
		$meiliUser = '';
		//get user_id from redis where auth = auth
		$meiliUser = RedisUserOauth::getUserIdFromAuth('renren', $auth);

		$rbc = new RenrenClient(RENREN_API_KEY, RENREN_SECRET, $access_token, $refresh_token);
		$userInfo = $rbc->get_user_info();

		if (empty($meiliUser)) {
			$param = array('user_type' => 1, 'auth' => $auth);
			$meiliUserExist = UserConnect::getInstance()->getUserConnectFromDB($param, "user_id, access");
			if (isset($meiliUserExist[0]['user_id']) && $meiliUserExist[0]['user_id'] != 0) {
				//如果得到用户信息不为空，则代表为非第一次来，跳到用户的home页
				$user_id  = $meiliUserExist[0]['user_id'];
				$result = $this->_updateRedis($user_id, 'renren', $renrenTokens, $params);
				return $result;
			}
		}
		if (!empty($meiliUser)) {
			//如果得到用户信息不为空，则代表为非第一次来，跳到用户的home页
			$user_id  = $meiliUser;
			$result = $this->_updateRedis($user_id, 'renren', $renrenTokens, $params);
			return $result;
		}
		else {
			$renrenInfo = $this->_setRenrenInfoCache($userInfo, $auth, $access_token, $ttl);
			$memKey = 'Connect:Info:' . $params['santorini_mm'];
			$cacheObj->set($memKey, $renrenInfo, 3600);

			//新用户互联
			if (empty($userInfo)) {
				die('get user info from renren error');
			}
			if ($userInfo[0]['sex'] != 0) {
				$result = array();
                $result['destUrl'] = 'connect/fail/renren';
                return $result;
			}
			else {
				$result = array();
				$result['reg_from'] = 1;
				$result['new_comer'] = 1;
				$result['destUrl'] = 'register/register_actionconnect';
				return $result;
			}
		}
	}

    private function _setRenrenInfoCache($userInfo, $auth, $access_token, $ttl) {
		$rand = mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);
        $renrenInfo = array();
        $renrenInfo['avatar'] = $userInfo[0]['mainurl'];//['avatar'][2]['url'];
        $renrenInfo['auth'] = $auth;
        $renrenInfo['email'] = '#' . $auth . '@t.renren.com';
        $renrenInfo['realname'] = '';

        //去掉屏蔽词
        $maskWords = new \Snake\Package\Spam\MaskWords($userInfo[0]['name'], 'DFA_register');
        $maskResult = $maskWords->getMaskWords();
        $nickname = $maskResult['maskedContent'];

        $renrenInfo['nickname'] = mb_substr($nickname, 0, 6, 'UTF-8') . '_rr' . $rand;
        $renrenInfo['gender'] = $userInfo[0]['sex'];
        $renrenInfo['province'] = '';
        $renrenInfo['city'] = '';
        $renrenInfo['openType'] = 1;
        $renrenInfo['type'] = 'bangrenren';
        $renrenInfo['access_token'] = $access_token;
        $renrenInfo['ttl'] = $ttl;
        return $renrenInfo;
    }

	//TODO 将此方法移至父类中
	private function _updateRedis($user_id, $type, $renrenTokens, $params) {
        if ($type == 'renren') {
            $flag = 1;
        }   
		$wbid = $renrenTokens['auth'];
		$ttl = $renrenTokens['ttl'];
		//access_token由access_token
		$access_token = $renrenTokens['access_token'];
        UserConnect::getInstance()->updateUserConnectAuth($user_id, $flag, $wbid, 1, $access_token);
        RedisUserConnectHelper::updateUserToken($type, $user_id, $ttl, $access_token);
		RedisUserConnectHelper::updateUserrefToken('renren', $user_id, 5184000, $refresh_token);
        RedisUserConnectHelper::updateUserAuth($type, $user_id, $wbid);
        RedisUserOauth::updateUserIdWithAuth($type, $wbid, $user_id);
        $firstVisit = FALSE;
        $result = $this->userLogin($user_id, $flag, $firstVisit, $params);
        return $result;
    }   

    /**
     * 人人互联用户是男性操作
     * @author yishuliu@meilishuo.com
     * @param array $params 包括httpRequest信息，包括santorini_mm等信息  
     * @return array $userInfo
     */
	public function renrenFail($params = array()) {
        $cacheObj = Memcache::instance();
        $cacheKey = 'Connect:Info:' . $params['santorini_mm'];
		$userInfo = array();
        $userInfo = $cacheObj->get($cacheKey);

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
	public function renrenUpdateToken($userId, $token, $auth, $ttl) {
		RedisUserConnectHelper::updateUserToken('renren', $userId, $ttl, $token);
		RedisUserConnectHelper::updateUserAuth('renren', $userId, $auth);
		RedisUserOauth::updateUserIdWithAuth('renren', $auth, $userId);
	}	
}
