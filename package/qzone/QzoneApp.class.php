<?php

namespace Snake\Package\Qzone;

use \Snake\Package\Qzone\DBQzoneActivity;
use \Snake\Package\Session\UserSession;
use \Snake\Package\Welfare\SideBar;
use \Snake\Package\Oauth\Qq_sdk\OpenApiV3Client;
use \Snake\libs\Cache\Memcache;
use \Snake\Libs\Base\SnakeLog;

class QzoneApp {
	
	private $appId = NULL;
	private $appKey = NULL;
	private $logHelper = NULL;
	private $pf = 'qzone';
	private $qq = 1379986183;

	public function __construct($appId, $appKey) {
		if(empty($appId)) {
			return FALSE;
		}
		$this->appId = $appId;
		$this->appKey = $appKey;
		$this->logHelper = new SnakeLog('qzone_api', 'normal');
		return TRUE;
	}
	public function getUserInfoFirstTime($openId, $openKey, $cookie, $cacheKey) {
		$openParams = array(
			'open_id' => $openId,
			'open_key' => $openKey
		);
		$this->_setUserInfoByCookie($openParams, $cookie, $cacheKey);
		$firstTime = 1;
		$userInfo = $this->_getUserInfoFromQQ($openId, $openKey, $firstTime);
		return $userInfo;
		
	}

	public function getUserInfoSecondTime($cookie, $cacheKey) {
		$openParams = $this->_getUserInfoByCookie($cookie, $cacheKey);
		//$this->_setCookieForOpenId($openParams, $cookie);
		$userInfo = $this->_getUserInfoFromQQ($openParams['open_id'], $openParams['open_key']);
		return $userInfo;
	}

	private function _getUserInfoByCookie($cookie, $cacheKey) {
		$cacheKey = $cookie['SEASHELL'] . ":" . $cacheKey;
		$cacheHelper = Memcache::instance();
		$result = $cacheHelper->get($cacheKey);
		return $result;
	}

	private function _setUserInfoByCookie($params, $cookie, $cacheKey) {
		$cacheKey = $cookie['SEASHELL'] . ':' . $cacheKey;
        $cacheHelper = Memcache::instance();
        $cacheHelper->set($cacheKey, $params, 3600*24);
		//$userInfo = $this->_getUserInfoFromQQ($params['open_id'], $params['open_key']);
		return TRUE;
	}

	private function _getUserInfoFromQQ($openId, $openKey, $firstTime = 0) {
		$cacheHelper = Memcache::instance();
		$cacheKey =	"QzoneActivity:UserInfo:" . $openId;
		$result = $cacheHelper->get($cacheKey);
		if (empty($result) || !empty($firstTime)) {
			$clientHelper = new OpenApiV3Client($this->appId, $this->appKey);
			$isFans = $clientHelper->is_fans($openId, $openKey, $this->pf, $this->qq);
			if ($isFans['ret'] != 0) {
				$str = "erro is:\n" . print_r($isFans, TRUE);
				$this->logHelper->w_log($str);
			}
			$isFans = $isFans['is_fans'];
			$userInfo = $clientHelper->get_user_info($openId, $openKey, $this->pf);
			$userInfo['openId'] = $openId;
			$userInfo['openKey'] = $openKey;
			$result = array(
				'isFans' => $isFans,
				'userInfo' => $userInfo
			);
			$cacheHelper->set($cacheKey, $result, 3600 * 24);
		}
		return $result;
	}

}
