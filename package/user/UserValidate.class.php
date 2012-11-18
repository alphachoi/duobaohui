<?php
namespace Snake\Package\User;

USE \Snake\libs\Cache\Memcache AS Memcache;

class UserValidate {
	public function __construct() {

	}

	/**
	 * 验证码验证
	 *
	 */
	public function ValidateCaptcha($captcha, $santorini) {
		if (empty($captcha) || empty($santorini)) {
			return FALSE;
		}
	    $cacheHelper = Memcache::instance();
		$cacheKey = "Register:Captcha:{$santorini}";
		$captchaCode = $cacheHelper->get($cacheKey);	
		if (strtolower($captcha) != strtolower($captchaCode)) {
			return FALSE;
		}
		return TRUE;
	}

	public function ClearCaptcha($santorini) {
		if (empty($santorini)) {
			return FALSE;
		}
		$cacheHelper = Memcache::instance();
		$cacheKey = "Register:Captcha:{$santorini}";
		$cacheHelper->delete($cacheKey);
		return TRUE;
	}
}
