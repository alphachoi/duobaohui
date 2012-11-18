<?php

namespace Snake\Package\Qzone\Special;

use \Snake\Package\Qzone\Special\DBSpecialOfferWear;
use \Snake\Package\Qzone\QzoneApp;
use \Snake\Package\Twitter\Twitter;
Use \Snake\Package\Goods\Goods;
Use \Snake\Package\Url\Url;
Use \Snake\Libs\Base\Utilities;
Use \Snake\Package\Twitterstat\TwitterStat;


class User {

	private $userInfo = array();
	private $qzoneHelper = NULL;

	public function __construct($params, $time) {
		$this->_setUserInfo($params, $time);
	}

	public function getUser() {
		return $this->userInfo;
	}
	
	private function _setUserInfo ($params, $time) {
		$qzoneHelper = new QzoneApp($params['app_id'], $params['app_key']);
		if ($time == 0) {
			$userInfo = $qzoneHelper->getUserInfoFirstTime($params['open_id'], $params['open_key'], $params['cookie'], $params['name']);
		}
		else {
			$userInfo = $qzoneHelper->getUserInfoSecondTime($params['cookie'], $params['name']);
		}
		$this->userInfo = $userInfo;
		return TRUE;
	}

}
