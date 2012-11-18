<?php
namespace Snake\Modules\Setting;

USE \Snake\Package\User\Helper\RedisUserConnectHelper AS RedisUserConnect;
USE \Snake\Package\User\Helper\RedisUserOauth AS RedisUserOauth;
USE \Snake\Package\User\UserConnect AS UserConnect;

/**
 * 同步账号
 * 互联后
 */
class Setting_syncbind extends \Snake\Libs\Controller {
	
	private $outsites = array(
		'weibo',
		'qzone',
	);

	private $auth = '';
	private $token = '';
	private $ttl = TRUE;
	private $typeNum = 4; //qzone
	private $santorini = '';

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		//互联授权后的数据
		$info = $this->getAuthInfo();
		if (!empty($info)) {
			$this->auth = $info['auth'];
			$this->access = $info['access'];
			$this->ttl = $info['ttl'];
			$result = $this->SyncBind();
			
			$this->view = array(
				'url' => '/setting/setting_sync',
				'result' => $result,
			);
		}
		else {
			$this->setError(400, 40150, 'connect error');
			return FALSE;
		}
	}

	private function getAuthInfo() {
		$cacheHelper = Memcache::instance();
		if ($this->typeNum == 4) {
			$cacheKey = 'QzoneAuth:qzone:' . $this->santorini;
			$info = $cacheHelper->get($cacheKey);
			$auth = $info['qzone_openid']['openid'];
			$access = $info['qzone_access_keys']['access_token'];
			$ttl = $info['qzone_access_keys']['expires_in'];
		}
		elseif ($this->typeNum == 3) {
			$cacheKey = 'WeiboAuth:weibo:' . $this->santorini;
			$info = $cacheHelper->get($cacheKey);
			$auth = $info['weibo_access_keys']['uid'];
			$access = $info['weibo_access_keys']['access_token'];
			$ttl = $info['weibo_access_keys']['expires_in'];
		}
		return array(
			'auth' => $auth,
			'access' => $access,
			'ttl' => $ttl,
		);
	}

	private function _init() {
		if (!$this->setUserId()) {
			return FALSE;	
		}		
		if (!$this->setType()) {
			return FALSE;
		}
		if (!$this->setCookie()) {
			return FALSE;
		}
		return TRUE;
	}

	private function setCookie() {
		if (empty($this->request->COOKIE['santorini_mm'])) {
			$this->setError(400, 40150, 'empty santorini');
			return FALSE;
		}
		$this->santorini = $this->request->COOKIE['santorini_mm'];
		return TRUE;
	}

	private function setUserId() {
        if (empty($this->userSession['user_id'])) {
            $this->setError(400, 40150, 'empty user_id');
            return FALSE;
        }   
        $this->userId = $this->userSession['user_id'];
        return TRUE;
    } 

	private function setType() {
		if (empty($this->request->REQUEST['type'])) {
			$this->setError(400, 40150, 'empty type');
			return FALSE;
		}
		if (!in_array($this->request->REQUEST['type'], $this->outsites)) {
			$this->setError(400, 40150, 'type illegal');
			return FALSE;
		}
		$this->type = $this->request->REQUEST['type'];
		if ($this->type == 'weibo') {
			$this->typeNum = 3;	
		}
		return TRUE;
	}

	private function syncBind() {
		$userAuth = RedisUserConnect::getUserAuth($this->type, $this->userId);
        $userId = RedisUserOauth::getUserIdFromAuth($this->type, $this->auth);
        if (!empty($userAuth) && !empty($userId)) {
			
            $param = array('user_type' => $this->typeNum, 'auth' => $this->auth);
            $meiliUserExist = UserConnect::getInstance()->getUserConnectFromDB($param, 'user_id');
            if (isset($meiliUserExist[0]['user_id']) && $meiliUserExist[0]['user_id'] != 0) {
				UserConnect::getInstance()->updateUserConnectAuth($this->userId,  $this->typeNum, $this->auth, 1, $this->token);
            }   
            else {
				UserConnect::getInstance()->insertUserConnectInfo($this->userId, $this->typeNum, $this->auth, $this->token);
            }

			//更新UserConnect:settings
            $settingSina = array('sync_goods' => 1); 
            RedisUserConnect::setUserSetting($this->type, $this->userId, json_encode($settingSina));
			//更新UserConnect和UserOauth
			$connectFactory = new ConnectFactory();
			$connectFactory->UpdateToken($this->type, $this->userId, $this->token, $this->auth, $this->ttl);
            return TRUE;
        }   
        else {
			//已经绑定过
            return FALSE;
        }   
	}
}
