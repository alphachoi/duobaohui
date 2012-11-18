<?php
namespace Snake\Package\Connect;

/**
 * @package connect 淘宝互联授权及获取用户信息
 * @author yishuliu@meilishuo.com
 * 采用OAuth2.0授权
 */

Use \Snake\Package\User\User;
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
class TaobaoAuth extends ConnectLib {
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
	 * 用户授权流程
     * @author yishuliu@meilishuo.com
     * @param $refer $_SERVER['HTTP_REFERER']
     * @param $frm isset($_GET['frm']) ? $_GET['frm'] : '';
     * @param $type = taobao
     * @param array $params 包括httpRequest信息，包括oauth_code,santorini_mm等信息  
	 */
	public function taobaoAuth($type, $params = array()) {
        //$base_uri = isset($params['request']->GET['baseUrl']) ? $params['request']->GET['baseUrl'] : '';

        //$urlArray = array();
        //$urlArray = parse_url($base_uri);
        //$host = 'http://' . $urlArray['path'] . '/';

        //$callback = $host . 'connect/auth/taobao';
		$callback = 'http://www.meilishuo.com/connect/auth/taobao';
        $destUrl = 'connect/connect/taobao';

		$result = array();
        $result = UserConnect::taobaoAuth($callback, array(TAOBAO_APPKEY_AUTH, TAOBAO_APPSECRET_AUTH), $params);
        if ($result['result'] == TRUE) {
			$cacheObj = Memcache::instance();
            $cacheKey = 'TaobaoAuth:taobao:' . $params['santorini_mm'];
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
	public function taobaoLogin($userId, $params = array()) {
		$cacheObj = Memcache::instance();
        $cacheKey = 'TaobaoAuth:taobao:' . $params['santorini_mm'];
		$result = $cacheObj->get($cacheKey);

	    $userInfo = $result['taobaoInfo']['userInfo'];
		if (empty($userInfo)) {
			$failInfo = array();
            $failInfo['error'] = '获取淘宝信息失败';
            return $failInfo;
		}
		$taobaoTokens = array();
		$taobaoTokens['ttl'] = TRUE;
		$taobaoTokens['auth'] = $auth = $userInfo->user_id;
		if (!isset($userInfo->sex)) {
			$userInfo->sex = 'f';
		}

		$meiliUser = RedisUserOauth::getUserIdFromAuth('taobao', $auth);
		if (empty($meiliUser)) {
			$param = array('user_type' => 6, 'auth' => $auth);
			$meiliUserExist = UserConnect::getInstance()->getUserConnectFromDB($param, "user_id, access");
			$param_mtop = array('user_type' => 9, 'auth' => $auth);
			$meiliUserExist_mtop = UserConnect::getInstance()->getUserConnectFromDB($param_mtop, "user_id, access");

			if (isset($meiliUserExist[0]['user_id']) && $meiliUserExist[0]['user_id'] != 0) {
				$user_id  = $meiliUserExist[0]['user_id'];
				$taobaoTokens['access_token'] = $result['taobaoInfo']['access_token'] . ',' . $userInfo->nick;
			}
			elseif (isset($meiliUserExist_mtop[0]['user_id']) && $meiliUserExist_mtop[0]['user_id'] != 0) {
				//如果用户只在手机登录过，那么找到在手机注册过的用户id
				$user_id  = $meiliUserExist_mtop[0]['user_id'];
				$taobaoTokens['access_token'] = $result['taobaoInfo']['access_token'] . ',' . $userInfo->nick;
				//只有无线淘宝的认证信息，插入网站淘宝的认证信息
				UserConnect::getInstance()->insertUserConnectInfo($user_id, 6, $auth, $taobaoTokens['access_token']);
			}
			if (!empty($user_id)) {
				//如果得到用户信息不为空，则代表为非第一次来，跳到用户的home页
				$result = $this->_updateRedis($user_id, 'taobao', $taobaoTokens, $params);
				return $result;
			}
		}
		if (!empty($meiliUser)) {
			$taobaoTokens['access_token'] = $result['taobaoInfo']['access_token'] . ',' . $userInfo->nick;
			//如果得到用户信息不为空，则代表为非第一次来，跳到用户的home页
			$result = $this->_updateRedis($meiliUser, 'taobao', $taobaoTokens, $params);
			return $result;
		} 
		else {
			$taobaoInfo = $this->_setTaobaoInfoCache($userInfo, $auth, $result['taobaoInfo']['access_token'] . ',' . $userInfo->nick);
			$memKey = 'Connect:Info:' . $params['santorini_mm'];
			$cacheObj->set($memKey, $taobaoInfo, 3600);

			//如果信息为空，表示第一次来，引导注册
			$sex = $userInfo->sex;
			if (!empty($userInfo)) {
				if ($sex != 'f') {
                	$result = array();
                	$result['destUrl'] = 'connect/fail/taobao';
                	return $result;	
				} else {
					if (!empty($userInfo->email)) {
						$param = array('email' => $userInfo->email);
						$userHelper = new User();
						$meiliUser = $userHelper->getUserProfile($param, "user_id");
						if (isset($meiliUser[0]['user_id']) && $meiliUser[0]['user_id'] > 0) {
							$result['userInfo']->email = '#' . $userInfo->user_id . '@t.taobao.com';
							$logHandle = new \Snake\Libs\Base\SnakeLog("taobao_connect", "normal");
							$pStr = "[$auth]\t[{$meiliUser[0]['user_id']}]\t";
							$logHandle->w_log($pStr);
						}
					}
                    $result['reg_from'] = 6;
					$result['new_comer'] = 1;
                	$result['destUrl'] = 'register/register_actionconnect';
                	return $result;	
				}
			}
		}//end else
	}

    private function _setTaobaoInfoCache($userInfo, $auth, $access_token) {
		$rand = mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);
        $taobaoInfo = array();
        $taobaoInfo['avatar'] = !empty($userInfo->avatar) ? $userInfo->avatar : \Snake\Libs\Base\Utilities::convertPicture('/css/images/0.gif');
        $taobaoInfo['auth'] = $userInfo->user_id;
        $taobaoInfo['email'] = !empty($userInfo->email) ? $userInfo->email : '#' . $userInfo->user_id . '@t.taobao.com';
        $taobaoInfo['realname'] = '';

        //去掉屏蔽词
        $maskWords = new \Snake\Package\Spam\MaskWords($userInfo->nick, 'DFA_register');
        $maskResult = $maskWords->getMaskWords();
        $nickname = $maskResult['maskedContent'];

        $taobaoInfo['nickname'] = mb_substr($nickname, 0, 6, 'UTF-8') . '_tb' . $rand;
        $taobaoInfo['gender'] = $userInfo->sex;
        $taobaoInfo['province'] = !empty($userInfo->location->state) ? $userInfo->location->state : '';
        $taobaoInfo['city'] = !empty($userInfo->location->city) ? $userInfo->location->city : '';
        $taobaoInfo['openType'] = 6;
        $taobaoInfo['type'] = 'bangtaobao';
        $taobaoInfo['access_token'] = $access_token;
        $taobaoInfo['ttl'] = TRUE;
        return $taobaoInfo;
    }

	private function _updateRedis($user_id, $type, $taobaoTokens, $params) {
		if ($type == 'taobao') {
			$flag = 6;
		}
		$wbid = $taobaoTokens['auth'];
		$ttl = $taobaoTokens['ttl'];
		//access_token是由access_token和nick拼接
		$access_token = $taobaoTokens['access_token'];
        UserConnect::getInstance()->updateUserConnectAuth($user_id, $flag, $wbid, 1, $access_token);
        RedisUserConnectHelper::updateUserToken($type, $user_id, $ttl, $access_token);
        RedisUserConnectHelper::updateUserAuth($type, $user_id, $wbid);
        RedisUserOauth::updateUserIdWithAuth($type, $wbid, $user_id);
        $firstVisit = FALSE;
        $result = $this->userLogin($user_id, $flag, $firstVisit, $params);
		return $result;
    }   

    /**
     * 淘宝互联用户是男性操作
     * @author yishuliu@meilishuo.com
     * @param array $params 包括httpRequest信息，包括santorini_mm等信息  
     * @return array $userInfo
     */
	public function taobaoFail($params = array()) {
		$cacheObj = Memcache::instance();
        $cacheKey = 'Connect:Info:' . $params['santorini_mm'];
		$userInfo = $cacheObj->get($cacheKey);

		if (empty($userInfo)) {
			die('获取淘宝信息失败');
		}

        $userInfo['gender'] = '男';
        $userInfo['school'] = '';
        $userInfo['workplace'] = '';
		$cacheObj->delete($cacheKey);
        return $userInfo;
	}

	public function taobaoUpdateToken($userId, $token, $auth, $ttl) {
		RedisUserConnectHelper::updateUserToken('taobao', $userId, TRUE, $token);
		RedisUserConnectHelper::updateUserAuth('taobao', $userId, $auth);
		RedisUserOauth::updateUserIdWithAuth('taobao', $auth, $userId);
	}
}
