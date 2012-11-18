<?php
namespace Snake\Package\User;

/**
 * 网站用户互联相关操作类
 * 主要包括各大平台用户互联登录美丽说授权 
 * @author yishuliu@meilishuo.com
 * @since 2012-05-14
 * @version 1.0
 */

Use \Snake\Package\User\Helper\RedisUserConnectHelper;
Use \Snake\Package\User\Helper\RedisUserStatisticHelper;
Use \Snake\Package\User\Helper\CacheUserHelper;
Use \Snake\Package\User\Helper\DBUserHelper;
Use \Snake\Libs\Cache\Memcache;
Use \Snake\Libs\Base\ZooClient;
Use \Snake\Libs\Base\SnakeLog;
Use \Snake\Libs\Base\HttpRequest;
Use \Snake\Libs\Base\Utilities;
Use \Snake\Package\Session\UserSession;
Use \Snake\Package\Oauth\QzoneOauth;
Use \Snake\Package\Oauth\QzoneClient;
Use \Snake\Package\Oauth\SaeTClient;
Use \Snake\Package\Oauth\SaeTOAuth;
Use \Snake\Package\Oauth\RenrenOauth;
Use \Snake\Package\Oauth\RenrenClient;
Use \Snake\Package\Oauth\WYOAuth;
Use \Snake\Package\Oauth\WYOAuth2;
Use \Snake\Package\Oauth\WYClient;
Use \Snake\Package\Oauth\TX_OAuth2;
Use \Snake\Package\Oauth\TX_WeiboClient;
Use \Snake\Package\Oauth\Taobao_sdk\TopSdk;
Use \Snake\Package\Oauth\Taobao_sdk\Top\TopClient;
Use \Snake\Package\Oauth\Baidu;
Use \Snake\Package\Oauth\BaiduSessionStore;
Use \Snake\Package\Oauth\Baidu_pcs\BaiduPCS;
Use \Snake\Package\Oauth\DouBanPublic;
Use \Snake\Package\Oauth\DouBanClient2;
Use \Snake\Package\Oauth\DouBanOAuth2;

/**
 * @since 2012-05-14
 * @version 1.0
 */
class UserConnect {

	private static $instance = NULL;
    
    /** 
     * @return userConnect Object
     */
    public static function getInstance(){
        if (empty(self::$instance)) {
            self::$instance = new UserConnect(); 
        }   
        return self::$instance;
    }
   
	private function __construct() {
        //$this->user = $user;
    }
	
	/**  
     * 得到站外用户的connectToken
	 * @author yishuliu@meilishuo.com
     * @param int $userId <br/>
	 * @param int $type <br/>
     *      1: renren, 3: weibo, 4: qzone, 5: baidu, 6: taobao, 7: wangyi,
     *      8: txweibo, 10: douban <br/>
	 * @return array <br/>
     */
	public function getUserConnect($userId, $type) {
        $result = RedisUserConnectHelper::getUserToken($type, $userId);
        $this->user[$type] = $result;
        return $this->user[$type];
    }

	public function getUserConnectFromDB($param = array(), $selectColumn = "*" , $master = FALSE, $indexKey = '') {
        $sqlData = array();
        $sqlComm = "SELECT $selectColumn FROM t_dolphin_user_profile_connect WHERE 1=1 ";
        if (isset($param['user_id'])) {
            $sqlComm .= "AND user_id=:_user_id";
            $sqlData['_user_id'] = $param['user_id'];
        }    
        if (isset($param['user_type'])) {
            $sqlComm .= " AND user_type=:_user_type";
            $sqlData['_user_type'] = $param['user_type'];
        }    
        if (isset($param['auth'])) {
            $sqlComm .= " AND auth=:auth";
            $sqlData['auth'] = $param['auth'];
        }    
        if (isset($param['from']) && isset($param['limit'])) {
            $sqlComm .= " limit :_from, :_limit";
            $sqlData['_from'] = $param['from'];
            $sqlData['_limit'] = $param['limit'];
        }    
        $result = array();
		$result = DBUserHelper::getConn()->read($sqlComm, $sqlData, FALSE, $indexKey);
        return $result;
    }    

	/**  
     * 更新站外用户的认证ID
     * @param $userId <br/>
     * @param $auth <br/>
     */
    public function updateUserConnectAuth($userId, $type, $auth, $status, $access) {
        $sqlComm = "update t_dolphin_user_profile_connect set auth=:_auth, status=:_status, access=:_access where user_id=:_user_id and user_type=:_user_type";
		$sqlData = array('_auth' => $auth, '_status' => $status, '_access' => $access, '_user_id' => $userId, '_user_type' => $type);
		$result = DBUserHelper::getConn()->write($sqlComm, $sqlData);
        return $result;
    }    

    /**  
     * 更新设置
     * @param unknown_type $userId <br/>
     * @param unknown_type $type <br/>
     * @param unknown_type $settings <br/>
     */
    public function updateUserConnectSettings($userId, $type, $settings) {
        if (empty($userId) || empty($type)) {
            return FALSE;
        }    
        $sqlComm = "update t_dolphin_user_profile_connect set
            sync_goods={$settings['sync_goods']},
            sync_collect={$settings['sync_collect']},
            sync_like={$settings['sync_like']},
            sync_ask={$settings['sync_ask']},
            sync_answer={$settings['sync_answer']},
            sync_medal={$settings['sync_medal']}
            where user_id=:_user_id and user_type=:_type";
		$sqlData = array('_user_id' => $userId, '_type' => $type);
		$result = DBUserHelper::getConn()->write($sqlComm, $sqlData);
        return $result;
    }    

    /** 
     * 更新用户设置
     * @param $userId integer 用户编号
     * @param $type integer 互联类型 
     * @param $setting array 设置信息
     */
    public function updateSettings($userId, $type, array $settings) {
        if (empty($userId) || empty($type) || empty($settings)) {
            return FALSE;
        }   
        $sqlComm = "UPDATE t_dolphin_user_profile_connect SET
            sync_goods=:_sync_goods,
            sync_collect=:_sync_collect,
            sync_like=:_sync_like,
            sync_ask=:_sync_ask,
            sync_answer=:_sync_answer,
            sync_medal=:_sync_medal
            WHERE user_id=:_user_id AND user_type=:_type";
        $sqlData = array(
            '_sync_goods' => $settings['sync_goods'],
            '_sync_collect' => $settings['sync_collect'],
            '_sync_like' => $settings['sync_like'],
            '_sync_ask' => $settings['sync_ask'],
            '_sync_answer' => $settings['sync_answer'],
            '_sync_medal' => $settings['sync_medal'],
            '_user_id' => $userId,
			'_type' => $type,
        );  
        $result = DBUserHelper::getConn()->write($sqlComm, $sqlData);
        return $result;
    }

	/**
	 * 插入用户互联表信息t_dolphin_user_profile_connect
	 * @author Chen Hailong
	 * @param int $userId
	 * @param int $userType
	 *		1: renren, 3: weibo, 4: qzone, 5: baidu, 6: taobao, 7: wangyi,
	 *		8: txweibo, 10: douban
	 * @param string $auth ;openId
	 * @param string $access ;accessToken
	 * @return TRUE or FLASE
	 */
    function insertUserConnectInfo($userId, $userType, $auth, $access = '') {
		if (empty($userId) || empty($userType)) {
			return FALSE;
		}
        $sqlComm = "INSERT INTO t_dolphin_user_profile_connect" .
            "(user_id, user_type, status, auth, access) VALUES " .
            "(:_user_id, :_user_type, 0, :auth, :access)";
        $insert_succ = FALSE;
        $sqlData['auth'] = $auth;
        $sqlData['_user_id'] = $userId;
        $sqlData['_user_type'] = $userType;
        $sqlData['access'] = $access;
		$insertSucc = DBUserHelper::getConn()->write($sqlComm, $sqlData);
		return $insertSucc;
    } 

	/** 
     * 判断当前登录用户是否是美丽说QQ空间的粉丝
	 * @author yishuliu@meilishuo.com
	 * @param $userId int <br/>
	 *  1 代表是Qzone互联用户并且不是粉丝，需要弹窗 <br/>
	 *  0 代表非Qzone互联用户或者是美丽说空间粉丝 <br/>
     */       
	public function checkQzoneIsFans($userId) {

        $token = RedisUserConnectHelper::getUserToken('qzone', $userId);
		$openId = RedisUserConnectHelper::getUserAuth('qzone', $userId);

		//$request = new \Snake\Libs\Base\HttpRequest();
		//$cookie = $request->COOKIE;
		//UserSession::updateSessionData($_ZSESSION, $cookie);
        if (empty($token)) {
            return 0;
        }   
        $qc = new QzoneClient(QZONE_ID, QZONE_KEY, $token, $openId);
        if (empty($qc)) {
            return 0;
        }   
        $ret = $qc->check_page_fans();
        if (!empty($ret['isfans'])) {
            return 0;
        }   
        elseif (isset($ret['isfans']) && $ret['isfans'] == 0) {
			return 1;
        }   
        return;
	}   

	/**  
     * sina OAuth2.0 网站接入 微博登录方式
	 *
     * @author yishuliu@meilishuo.com <br/>
     * @param int $userId <br/>
	 * @param array(WB_AKEY, WB_SKEY) <br/>
	 * @return multi_type $wbc <br/>
     */
    public static function getWeiboClient($userId = 0, $keyInfo = array(WB_AKEY, WB_SKEY), $weibo_access_keys = array()) {
        //暂需同时维护OAuth1.0和OAuth2.0两种方式，SaeTOAuth是OAuth2.0,
        //weibooauth是OAuth1.0
        $session_key = 'weibo_access_keys';
        if ($keyInfo[0] == 3619418532) {
            $session_key = 'weibo_access_keys_for_app';
        }    
		if (!empty($weibo_access_keys)) {
			$keys = $weibo_access_keys;
		}
        else {
            if (empty($userId)) {
                return FALSE;
            }    
            else {
                $accessArr = RedisUserConnectHelper::getUserToken('weibo', $userId);
                //accessArr为一个字符串，2.0时只截取access_token,
                //1.0时需截取oauth_token， oauth_token_secret
                if (!empty($accessArr)) {
                    $tokens = explode(',', $accessArr);
                    if (strpos($tokens[0], '2.00') === 0) { //Oauth2.0方式
                    $keys['access_token'] = $tokens[0];
                    }    
                    else { //Oauth 1.0
                        $keys = array(
                            'oauth_token' => $tokens[0],
							'oauth_token_secret' => $tokens[1]
                        );
                    }
                }
            }
        }
        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
        if (isset($keys['access_token'])) {
            $wbc = new SaeTClient($aKey, $sKey, $keys['access_token']);
			return $wbc;
        }
		else {
			return FALSE;
		}
        /*if (isset($keys['oauth_token'])) {
            importer('corelib.weibooauth');
            $wbc = new WeiboClient($aKey, $sKey, $keys['oauth_token'], $keys['oauth_token_secret']);
            return $wbc;
        }*/
        return FALSE;
    }

	/**
     * 从新浪微博获取当前用户信息
     */
    public static function getWeiboInfo($keyInfo = array(WB_AKEY, WB_SKEY), $weibo_access_keys = NULL) {
        $wbc = self::getWeiboClient(0, $keyInfo, $weibo_access_keys);
        if ($wbc === FALSE) {
            return FALSE;
        }
        $ret = $wbc->verify_credentials();
        if (isset($ret['error'])) {
            return FALSE;
        }
        else {
			$uid = $ret['uid'];
			$userInfo = $wbc->show_user($uid);
			if (isset($userInfo['error'])) {
				$logHandle = new \Snake\Libs\Base\SnakeLog('get_weibo_info_api', 'normal');
				$logHandle->w_log(print_r($userInfo, TRUE));
				return FALSE;
			}
            return $userInfo;
        }
    }


	/**
     * 新浪OAuth v2.0 
     * Web应用的验证授权(Authorization Code)
     */
    public static function weiboAuth($callbackUrl, $keyInfo = array(WB_AKEY, WB_SKEY), $fromMobile = FALSE, $requestCode) {
        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
        if (!isset($requestCode)) { //$_REQUEST['code'])) {
            //第一次调用，去新浪登录
            $o = new SaeTOAuth($aKey, $sKey);
            $aurl = $o->getAuthorizeURL($callbackUrl, 'code', $fromMobile);
            //Utilities::headerToUrl($aurl);
			$result = array();
			$result['redirectUrl'] = $aurl;
			$result['result'] = FALSE;
			$result['flag'] = 1;
            return $result;
        }
        else {
            //从新浪微博返回
            $keys = array();
            $keys['code'] = $requestCode; //$_REQUEST['code'];
            $keys['redirect_uri'] = $callbackUrl;
            $o = new SaeTOAuth($aKey, $sKey);
            $last_key = $o->getAccessToken('code', $keys);
            if (is_array($last_key)) {
                $last_key['ctime'] = $_SERVER['REQUEST_TIME'];
            }
            else {
                $logHandle = new SnakeLog( "sinaWeiboOAuth2", "normal" );
                $logHandle->w_log(print_r($last_key, true));
                return FALSE;
            }
			$result = array();
			if ($aKey != 3619418532) {
				$result['weibo_access_keys'] = $last_key;
            }
            else {
				$result['weibo_access_keys_for_app'] = $last_key;
            }
			$result['result'] = TRUE;
            return $result;
        }
    }
	
	public static function getWangyiClient($userId = 0, $keyInfo = array(WY_AKEY_NEW, WY_SKEY_NEW), $access_token = NULL) {
        if (isset($access_token)) {
            $key = $access_token;
        }    
        else {
            if (empty($userId)) {
                return FALSE;
            }    
            else {
                //从数据库读取
                $param['user_id'] =  $userId;
                $param['user_type'] = 7; 
                $connInfo = self::getInstance()->getUserConnect($userId, "wangyi");
                if (empty($connInfo) || empty($connInfo[0]['access'])) {
                    return FALSE;
                }    
                else {
                    //$accessArr = explode(',', $connInfo[0]['access']);
                    /*$keys = array(
                        'oauth_token' => $accessArr[0],
                        'oauth_token_secret' => $accessArr[1]
                    );*/
					$key = $connInfo[0];
                }    
            }    
        }    
        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
        $wbc = new WYClient($aKey, $sKey, $key);
        return $wbc;
    }    

	/**    
     * wangyi OAuth1.0方式授权
     * @param string $callbackUrl 跳转回的URL
     * @param array  $keyInfo array(APPID, APPKEY)
     * @param string santorini_mm
     */
	public static function wangyiAuth($callbackUrl, $keyInfo = array(WY_AKEY, WY_SKEY), $santor) {
		//$request = new \Snake\Libs\Base\HttpRequest();
		//$COOKIE = $request->COOKIE; 
		$cacheObj = Memcache::instance();
		$cacheKey = 'wangyi_code' . ':' . $santor;
		//$cacheKey = 'wangyi' . ':' . $COOKIE['santorini_mm'];

        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];

        $o = new WYOAuth($aKey, $sKey);
        $keys = $o->getRequestToken();
		//将得到的tx_request_keys存入memcache以换取access_token
        $cacheObj->set($cacheKey, $keys, 600);
        $aurl = $o->getAuthorizeURL($keys['oauth_token'], $callbackUrl);
		//Utilities::headerToUrl($aurl);
		$result = array();
		$result['result'] = FALSE;
		$result['redirectUrl'] = $aurl;
        return $result;
    }

	public static function wangyiAccess($keyInfo = array(WY_AKEY, WY_SKEY), $santor) {
		//$request = new \Snake\Libs\Base\HttpRequest();
		//$COOKIE = $request->COOKIE; 
		$cacheObj = Memcache::instance();
		$cacheKey = 'wangyi_code' . ':' . $santor;
		//$cacheKey = 'wangyi' . ':' . $COOKIE['santorini_mm'];

		$keys = $cacheObj->get($cacheKey);
        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
        $o = new WYOAuth($aKey, $sKey, $keys['oauth_token'], $keys['oauth_token_secret']);
        $last_key = $o->getAccessToken() ;
		$result = array();
        $result['163_access_keys'] = $last_key;
		$result['result'] = TRUE;
		return $result;
    }

	/**    
     * 网易验证
	 * @version OAuth2.0
     * @param string $callbackUrl 跳转回的URL
     * @param array  $keyInfo array(APPID, APPKEY)
     *
     */
    public static function wangyiAuth2($callbackUrl, $keyInfo = array(WY_AKEY_NEW, WY_SKEY_NEW), $requestCode, $state) {
        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
        if (!isset($requestCode)) { 
            $o = new WYOAuth2($aKey, $sKey);
            $aurl = $o->getAuthorizeURL($callbackUrl, 'code', $state);
			$result = array();
			$result['redirectUrl'] = $aurl;
			$result['flag'] = 1;
			$result['result'] = FALSE;
            return $result;
        }
        else {
            $keys = array();
            $keys['code'] = $requestCode;
            $keys['redirect_uri'] = $callbackUrl;
            $o = new WYOAuth2($aKey, $sKey);
            $last_key = $o->getAccessToken('code', $keys);
            if (is_array($last_key)) {
                $last_key['ctime'] = $_SERVER['REQUEST_TIME'];
            }
            else {
                $logHandle = new SnakeLog( "wangyiOAuth2", "normal" );
                $logHandle->w_log(print_r($last_key, true));
                return FALSE;
            }
			$result = array();
			$result['163_access_keys'] = $last_key;
			$result['result'] = TRUE;
            return $result;
        }
    }

	public static function getWangyiInfo($keyInfo = array(WY_AKEY_NEW, WY_SKEY_NEW), $access_token = NULL, $refresh_token = NULL) {
        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
        $wbc = new WYClient($aKey, $sKey, $access_token, NULL);
        if ($wbc === FALSE) {
            return FALSE;
        }
        $ret = $wbc->user_show();
        return $ret;
    }

	public static function getQzoneInfo($access_token = NULL, $openId = NULL, $keyInfo = array(QZONE_ID, QZONE_KEY)) {
        if (empty($access_token) || empty($openId)){
            return FALSE;
        }    
        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
        $userInfo = array();
        $qc = self::getQzoneClient($aKey, $sKey, $access_token, $openId);
        if (!empty($qc)) {
            $userInfo = $qc->get_user_info();
			if ($userInfo['ret'] !== 0) {
				$logHandle = new \Snake\Libs\Base\SnakeLog('qzone_info_api', 'normal');
				$logHandle->w_log(print_r($userInfo, TRUE));
				return FALSE;
			}
        }    
        return $userInfo;
    }    

    public static function getQzoneClient($aKey, $sKey, $access_token, $openId) {
        if (empty($aKey) || empty($sKey) || empty($access_token) || empty($openId)) {
            return FALSE;
        }    
        $qc = new QzoneClient($aKey, $sKey, $access_token, $openId);
        return $qc; 
    }    

	/**    
     * baidu OAuth2.0方式授权
     * @param string $callbackUrl 跳转回的URL
     * @param array  $keyInfo array(APPID, APPKEY)
     * @param array  $params 包括oauth_code,santorini_mm等信息
     */
	public static function baiduAuth($callback, $keyInfo = array(BAIDU_AKEY, BAIDU_SKEY), $params = array()) {
        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
		$result = array();
		if (!empty($params['code']) && !empty($params['state'])) {
			//echo "if\n";
			$cacheObj = Memcache::instance();
			$cacheKey = 'baidu_code' . ':' . $params['santorini_mm'];
        	$cacheObj->set($cacheKey, $params, 600);
			//$answer = $cacheObj->get($cacheKey);
		}
        $baidu = new Baidu($aKey, $sKey, new BaiduSessionStore($aKey), $params['santorini_mm'], $callback);
        if (!empty($params['code'])) {
        	$access_token = $baidu->getAccessToken(TRUE);
            $user_profile = $baidu->api('passport/users/getInfo', array('fields' => 'userid,username,sex,birthday,realname,portrait'));

            //激活百度网盘功能
            $baiduPCSHelper = new BaiduPCS(array('access_token' => $access_token));
            $baiduPCSHelper->set_ssl(true);
            $dir = '/apps/美丽说/';     
     
            $netdisk = $baiduPCSHelper->list_file($dir);
			
            if (!empty($user_profile)) {
				$result['baiduInfo'] = $user_profile;
				$result['baiduInfo']['access_token'] = $access_token;
				$result['result'] = TRUE;
                return $result;
            }    
            //elseif (empty($_GET['again'])) {
				//Utilities::headerToUrl(BASE_URL . 'connect/auth/baidu?again=1');
            //}    
            return FALSE;
        } else {
        	$url = $baidu->getLoginUrl(array('response_type' => 'code', 'redirect_uri' => $callback));
			//print_r($url);die;
			//echo "else\n";
			$result = array();
			$result['result'] = FALSE;
			$result['flag'] = 1;
			$result['redirectUrl'] = $url;
        	return $result;
        }    
    }

	public static function getBaiduInfo($keyInfo = array(BAIDU_AKEY, BAIDU_SKEY)) {
        $baidu = new Baidu($keyInfo[0], $keyInfo[1], new BaiduSessionStore($keyInfo[0]));
        $access_token = $baidu->getAccessToken();
        if ($access_token) {
            return $baidu->api('passport/users/getInfo', array('fields' => 'userid,username,sex,birthday,realname'));
        }
        return FALSE;
    }

	public static function qzoneAuth($callbackUrl, $state, $keyInfo = array(QZONE_ID, QZONE_KEY), $scope = 'default', $ip = '', $display = '') {
        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
        $o = new QzoneOauth($aKey, $sKey, NULL, NULL, $ip);
        $authorUrl = $o->getAuthorizeUrl($callbackUrl, $response_type = 'code', $scope, $state, $display);
		$result = array();
		$result['redirectUrl'] = $authorUrl;
		$result['flag'] = 1;
		$result['result'] = FALSE;
		return $result;
		//Utilities::headerToUrl($authorUrl);
    }

	/**    
     * qzoneOAuth2.0方式授权
     * @param string $callbackUrl 跳转回的URL
     * @param array  $keyInfo array(APPID, APPKEY)
     *
     */
    public static function qzoneAccess($callbackUrl, $keyInfo = array(QZONE_ID, QZONE_KEY), $authCode = NULL, $ip = '') {
        if (empty($authCode)) {
            return FALSE;
        }

        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
        $type = 'code';
        $keys = array();
        $keys['code'] = $authCode;
		$params = array();
        $params['state'] = md5(uniqid(rand(), TRUE));
        $o = new QzoneOauth($aKey, $sKey, NULL, NULL, $ip);
        $accessKeys = $o->getAccessToken($callbackUrl, $type, $keys, $params['state']);
		/*
		 * <code>
		 *    Array (
         * 	      [access_token] => C77B688321C4B99A1F4314C0F56E4470
         *        [expires_in] => 7776000
         *    ) 
		 * </code>
		 */
        $params['qzone_access_keys'] = $accessKeys;
        $openIdKeys = $o->getOpenId();
		/*
		 * <code>
		 *    Array (
         * 	      [client_id] => 100210915
         *        [openid] => B310D52746854C14D0B713DE73A8F678
         *    ) 
		 * </code>
		 */
        $params['qzone_openid'] = $openIdKeys;
		$params['result'] = TRUE;
        return $params;
	}

	/**    
     * renrenOAuth方式授权第一步得到request_token
     * @param string $callbackUrl 跳转回的URL
     * @param array  $keyInfo array(APPID, APPKEY)
     *
     */
	public static function renrenAuth($callbackUrl, $state, $keyInfo = array(RENREN_API_KEY, RENREN_SECRET), $display = '', $ip = NULL) {
        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
        $o = new RenrenOauth($aKey, $sKey, $ip);
        $authorUrl = $o->getAuthorizeURL($callbackUrl, $response_type = 'code', 'default', $state, $display);
		//Utilities::headerToUrl($authorUrl);
		$result = array();
		$result['redirectUrl'] = $authorUrl;
		$result['flag'] = 1;
		$result['result'] = FALSE;
		return $result;
    }    

	/**    
     * renrenOAuth方式授权第二步用request_token换取access_token
     * @param string $callbackUrl 跳转回的URL
     * @param array  $keyInfo array(APPID, APPKEY)
     *
     */
    public static function renrenAccess($callbackUrl, $keyInfo = array(RENREN_API_KEY, RENREN_SECRET), $authCode = NULL, $ip = NULL) {
        if (empty($authCode)) {
            return FALSE;
        }
        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
        $type = 'code';
        $keys = array();
        $keys['redirect_uri'] = $callbackUrl;
        $keys['code'] = $authCode;
        $o = new RenrenOauth($aKey, $sKey, $ip);
        $accessKeys = $o->getAccessToken($type, $keys);
        $params['renren_access_keys'] = $accessKeys;
		$params['result'] = TRUE;
        return $params;
    }    

	/**    
     * 腾讯微博OAuth2.0方式授权
     * @param string $callbackUrl 跳转回的URL
     * @param array  $keyInfo array(APPID, APPKEY)
     *
     */
	public static function txweiboAuth2($callbackUrl, $keyInfo = array(TX_AKEY, TX_SKEY), $params = array()) {
        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
        if (!isset($params['code'])) {            //请求code
            TX_OAuth2::init($aKey, $sKey, $params['ip']);
            $aurl = TX_OAuth2::getAuthorizeURL($callbackUrl, 'code', FALSE);
			//Utilities::headerToUrl($aurl);
			$result = array();
			$result['redirectUrl'] = $aurl;
			$result['flag'] = 1;
			$result['result'] = FALSE;
            return $result;
        }
        else {          //请求accesstoken
            $code = $params['code'];
            $openid = isset($params['openid']) ? $params['openid'] : 0;
            $openkey = isset($params['openkey']) ? $params['openkey'] : 0;
            //获取授权token
            TX_OAuth2::init($aKey, $sKey, $params['ip']);
            $url = TX_OAuth2::getAccessToken($code, $callbackUrl);
            $r = TX_OAuth2::request($url);
            parse_str($r, $out);

			$result = array();
            //存储授权数据
            if ($out['access_token']) {
                $result['tx_access_keys']['access_token'] = $out['access_token'];
                $result['tx_access_keys']['expire_in'] = $out['expires_in'];
                $result['tx_access_keys']['code'] = $code;
                $result['tx_access_keys']['openid'] = $openid;
                $result['tx_access_keys']['openkey'] = $openkey;
                $r = TX_OAuth2::checkOAuthValid();
            }
			$result['result'] = TRUE;
			return $result;
		}
	}

    /**
     * 从腾讯微博获取的当前用户信息
     */
	public static function getTxweiboInfo($keyInfo = array(TX_AKEY, TX_SKEY), $txweibo_access_token = NULL, $openId = NULL, $ip = NULL) {
		//$twbc = self::getTXweiboClient(0, array(TX_AKEY, TX_SKEY), $txweibo_access_keys);
		//$ret = $twbc->getinfo($txweibo_access_keys, $openId);
		TX_OAuth2::init(TX_AKEY, TX_SKEY, $ip);
		$ret = TX_OAuth2::api('user/info', array(), 'GET', FALSE, $txweibo_access_token, $openId);
		$ret = (array)json_decode($ret);
        if ($ret['errcode'] != 0) {
            return FALSE;
        }
        else{
            return $ret;
        }
    }

	/**    
     * 淘宝验证
     * @param string $callbackUrl 跳转回的URL
     * @param array  $keyInfo array(APPID, APPKEY)
     *
     */
	public static function taobaoAuth($callback, $keyInfo = array(TAOBAO_APPKEY_AUTH, TAOBAO_APPSECRET_AUTH), $params = array()) {
        //spl_autoload_register("__autoload");
        //实例化TopClient类
        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
        $c = new TopClient($aKey, $sKey);
        if (!empty($params['code']) && !empty($params['state']) && $params['state'] == 1) { 
            $accessToken = $c->getAccessToken($params['code'], $callback);
            $userInfo = $c->getUserInfo($accessToken);
            $logHandle = new \Snake\Libs\Base\SnakeLog("taobaoError", "normal");
            $logHandle->w_log (print_r($_REQUEST, true));
            $logHandle->w_log (print_r($userInfo, true));
            $userObj = $userInfo->user;
			
            if (!empty($userObj)) {
                if (!isset($userObj->sex)) {
                    $userObj->sex = 'f'; 
                }    
                //$_SESSION['taobaoInfo']['userInfo'] = $userObj;
                //$_SESSION['taobaoInfo']['access_token'] = $accessToken;
				$result = array();
				$result['result'] = TRUE;
				$result['taobaoInfo']['access_token'] = $accessToken;
				$result['taobaoInfo']['userInfo'] = $userObj;
                return $result;
            }
            else {
                //unset($_SESSION['taobaoInfo']);
				$result['result'] = FALSE;
				$result['redirectUrl'] = 'logon';
            }
            return $result;
        }
        else {
            $url = TAOBAO_USER_AUTHORIZE_AUTH . '&redirect_uri=' . urlencode($callback);
			$result = array();
			$result['result'] = FALSE;
			$result['flag'] = 1;
			$result['redirectUrl'] = $url;
			return $result;
			//Utilities::headerToUrl($url);
        }
        return $result;
    }

	/**    
     * 豆瓣验证
     * @param string $callbackUrl 跳转回的URL
     * @param array  $keyInfo array(APPID, APPKEY)
     *
     */
    public static function doubanAuth($callbackUrl, $keyInfo = array(DOUBAN_AKEY, DOUBAN_SKEY), $params = array()) {
		$cacheObj = Memcache::instance();
		$cacheKey = 'douban_code:' . $params['santorini_mm'];

        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
        $client = new DouBanPublic($aKey, $sKey);
        if (!isset($params['oauth_token'])) {
            //第一次调用，去豆瓣登录
            $requestToken = $client->getRequestToken();
            $key = $requestToken['oauth_token'];
            $secret = $requestToken['oauth_token_secret'];

			//将得到的douban request_token存入memcache以换取access_token
            $cacheObj->set($cacheKey, $requestToken, 3600);

            $authurl = $client->getAuthorizationUrl($key, $secret, $callbackUrl);
            $authurl .= '&p=1';
			//Utilities::headerToUrl($authurl);
			$result = array();
			$result['redirectUrl'] = $authurl;
			$result['result'] = FALSE;
            return $result;
        }     
        else {
            //第二次调用，获取access token
			$requestToken = $cacheObj->get($cacheKey);
			$result = array();
            $keys = $client->getAccessToken($requestToken['oauth_token'], $requestToken['oauth_token_secret']);
            $result['douban_access_keys'] = $keys;
			$result['result'] = TRUE;
			return $result;
        }    
    }    

	/**    
     * 豆瓣验证
	 * @version OAuth2.0
     * @param string $callbackUrl 跳转回的URL
     * @param array  $keyInfo array(APPID, APPKEY)
     *
     */
    public static function doubanAuth2($callbackUrl, $keyInfo = array(DOUBAN_AKEY_NEW, DOUBAN_SKEY_NEW), $requestCode, $state) {
        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
        if (!isset($requestCode)) { 
            $o = new DouBanOAuth2($aKey, $sKey);
            $aurl = $o->getAuthorizeURL($callbackUrl, 'code', $state);
			$result = array();
			$result['redirectUrl'] = $aurl;
			$result['flag'] = 1;
			$result['result'] = FALSE;
            return $result;
        }
        else {
            $keys = array();
            $keys['code'] = $requestCode;
            $keys['redirect_uri'] = $callbackUrl;
            $o = new DouBanOAuth2($aKey, $sKey);
            $last_key = $o->getAccessToken('code', $keys);
            if (is_array($last_key)) {
                $last_key['ctime'] = $_SERVER['REQUEST_TIME'];
            }
            else {
                $logHandle = new SnakeLog( "doubanOAuth2", "normal" );
                $logHandle->w_log(print_r($last_key, true));
                return FALSE;
            }
			$result = array();
			$result['douban_access_keys'] = $last_key;
			$result['result'] = TRUE;
            return $result;
        }
    }

    /**   
     * 从豆瓣获取当前用户信息
     */
    public static function getDoubanInfo($keyInfo = array(DOUBAN_AKEY_NEW, DOUBAN_SKEY_NEW), $douban_access_keys = array()) {
        $aKey = $keyInfo[0];
        $sKey = $keyInfo[1];
		
        $access_token = $douban_access_keys['access_token'];
        //$secret = $douban_access_keys['oauth_token_secret'];
        $user_id = $douban_access_keys['douban_user_id'];

        $wbc = new DouBanClient2($aKey, $sKey, $access_token);
        if ($wbc === FALSE) {
            return FALSE;
        }    

        //$wbc->programmaticLogin($key, $secret);
        $result = $wbc->getCurrentPeopleInfo($user_id);

        return empty($result) ? '' : $result;
    }    

	/**
	 * 得到微博粉丝信息
	 */
	public static function getUserWeiboFans($userId) {
		$token = RedisUserConnectHelper::getUserToken('weibo', $userId);
		$auth = RedisUserConnectHelper::getUserAuth('weibo', $userId);
		$tokens = explode(',', $token);
		$access_token = $tokens[0];
		$keyInfo = array(WB_AKEY, WB_SKEY);
		$aKey = $keyInfo[0];
		$sKey = $keyInfo[1];
		$wbc = new SaeTClient($aKey, $sKey, $access_token);
		if (empty($wbc)) {
			return FALSE;
		}
		$result = array();
		$response = array();
		$result = $wbc->followers($auth);
		$userInfo = $result['users'];
		if (empty($userInfo)) return false;
		foreach ($userInfo as $item => $value) {
			$response[$item]['id'] = $userInfo[$item]['id'];
			$response[$item]['nickname'] = $userInfo[$item]['screen_name'];
			$response[$item]['avatar'] = $userInfo[$item]['profile_image_url'];
			$response[$item]['gender'] = $userInfo[$item]['gender'];
		}
		return $response;
	}

	//TODO 腾讯微博不兼容1.0 2.0授权不稳定
	public static function getUserTxweiboFans($userId, $ip = NULL) {
		$token = RedisUserConnectHelper::getUserToken('txweibo', $userId);
		$auth = RedisUserConnectHelper::getUserAuth('txweibo', $userId);
		$tokens = explode(',', $token);
		$access_token = $tokens[0];
		$openId = $tokens[1];
		$keyInfo = array(TX_AKEY, TX_SKEY);
		$aKey = $keyInfo[0];
		$sKey = $keyInfo[1];
		//var_dump($access_token, $openId);die;

        TX_OAuth2::init(TX_AKEY, TX_SKEY, $ip);
        $ret = TX_OAuth2::api('friends/fanslist_s', array(), 'GET', FALSE, $access_token, $openId);
        $ret = (array)json_decode($ret);
		//print_r($ret);die;
        if ($ret['errcode'] != 0) {
            return FALSE;
        }
		$data = $ret['data']->info;
		$response = array();
		foreach ($data as $item => $value) {
			$response[$item]['name'] = $data[$item]->name;
			$response[$item]['nickname'] = $data[$item]->nick;
			$response[$item]['avatar'] = isset($data[$item]->head) ? $data[$item]->head . '/50' : NULL; //腾讯微博头像要加/30, /50
		}
		return $response;
	}

	/**
	 * 得到微博互相关注的用户信息
	 */
	public static function getUserWeiboBilateral($userId) {
		$token = RedisUserConnectHelper::getUserToken('weibo', $userId);
		$auth = RedisUserConnectHelper::getUserAuth('weibo', $userId);
		$tokens = explode(',', $token);
		$access_token = $tokens[0];
		$keyInfo = array(WB_AKEY, WB_SKEY);
		$aKey = $keyInfo[0];
		$sKey = $keyInfo[1];
		$wbc = new SaeTClient($aKey, $sKey, $access_token);
		if (empty($wbc)) {
			return FALSE;
		}
		$result = array();
		$response = array();
		$result = $wbc->bilateral($auth);
		$userInfo = $result['users'];
		if (empty($userInfo)) return false;
		foreach ($userInfo as $item => $value) {
			$response[$item]['id'] = $userInfo[$item]['id'];
			$response[$item]['nickname'] = $userInfo[$item]['screen_name'];
			$response[$item]['avatar'] = $userInfo[$item]['profile_image_url'];
			$response[$item]['gender'] = $userInfo[$item]['gender'];
		}
		return $response;
	}

	public function getWeiboFansNum($userId = 1740155) {
		$token = RedisUserConnectHelper::getUserToken('weibo', $userId);
		$auth = RedisUserConnectHelper::getUserAuth('weibo', $userId);
		$tokens = explode(',', $token);
		$access_token = $tokens[0];
		$keyInfo = array(WB_AKEY, WB_SKEY);
		$aKey = $keyInfo[0];
		$sKey = $keyInfo[1];
		$wbc = new SaeTClient($aKey, $sKey, $access_token);
		if (empty($wbc)) {
			return FALSE;
		}
		$result = array();
		$response = array();
		$result = $wbc->followers($auth);
		$nums = !empty($result['total_number']) ? $result['total_number'] : 4112000;
		return $nums;
	}

	public function getTxweiboFansNum($userId = 4229478, $ip = NULL) {
		$token = RedisUserConnectHelper::getUserToken('txweibo', $userId);
		$auth = RedisUserConnectHelper::getUserAuth('txweibo', $userId);
		$tokens = explode(',', $token);
		$access_token = $tokens[0];
		$openId = $tokens[1];
		$keyInfo = array(TX_AKEY, TX_SKEY);
		$aKey = $keyInfo[0];
		$sKey = $keyInfo[1];
		//var_dump($access_token, $openId);die;

        TX_OAuth2::init(TX_AKEY, TX_SKEY, $ip);
        $ret = TX_OAuth2::api('user/info', array(), 'GET', FALSE, $access_token, $openId);
        $ret = (array)json_decode($ret);
		//print_r($ret);die;
        if ($ret['errcode'] != 0) {
            return FALSE;
        }
		$data = $ret['data']->fansnum;
		return $data;
	}

	public function getQzoneFansNum() {
		$cacheObj = Memcache::instance();
		$cacheKey = 'qzone:fans:num:snake';
		$result = $cacheObj->get($cacheKey);
		
		$result = !empty($result) ? $result : 21110000;
		return $result;
	}

	public function getTotalFansNum() {
		$cacheObj = Memcache::instance();
		$cacheKey = 'total_fans_num';
		$totalNum = $cacheObj->get($cacheKey);
		if (empty($totalNum)) {
			$weiboFansNum = 4123000; //$this->getWeiboFansNum();
			$txWeiboFansNum = 8010000;
			$qzoneFansNum = $this->getQzoneFansNum();
			$nums = $weiboFansNum + $txWeiboFansNum + $qzoneFansNum;
			$totalNum = (int) ceil($nums/10000);
			$cacheObj->set($cacheKey, $totalNum, 86400);
		}
		return $totalNum;
	}
}
