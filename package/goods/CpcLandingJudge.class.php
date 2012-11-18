<?php
namespace Snake\Package\Goods;

class CpcLandingJudge {

	static $firstLanding = FALSE;
	
	static public function isCpcLanding() {
		return FALSE;
		//return !empty($_COOKIE['MEILISHUO_BOB_LANDING_Q']) || self::$firstLanding;
	}

	static public function setCpcLanding($offset) {
		if (!empty($offset)) {
			return FALSE;	 	
		}
		self::$firstLanding = TRUE;
		return setcookie('MEILISHUO_BOB_LANDING_Q', time(), time() + 60 * 30, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
	}

}
