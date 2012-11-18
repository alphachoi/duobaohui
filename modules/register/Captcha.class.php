<?php
namespace Snake\Modules\Register;

use \Snake\Libs\Base\Captcha AS CaptchaCode;
use \Snake\libs\Cache\Memcache AS Memcache;


class Captcha extends \Snake\Libs\Controller {
	
	private $santorini = '';

	public function run() {
		$this->_init();
		$captcha = new CaptchaCode();
		$captcha->SetCaptchaCode();
		$captcha->SetCaptchaImageWidthAndHeight(90, 36);
		$captcha->setCaptchaBackground('captchaBackground.jpg');
		$captcha->SetCaptchaLevel(3);
		$captcha->setCaptchaFont('angelina.ttf');

		$captchaCode = $captcha->GetCaptchaCode(); 
		$cacheHelper = Memcache::instance(); 
		$cacheKey = "Register:Captcha:{$this->santorini}";
		$cacheHelper->set($cacheKey, $captchaCode, 2700);

        $log = new \Snake\Libs\Base\SnakeLog('captcha', 'normal');
		$log->w_log(print_r(array('captcha', 'cacheKey' => $cacheKey, 'santorini' => $this->santorini, 'captchaCode' => $captchaCode), true));

		$this->view = $captcha->OutCheckImage();	
		return TRUE;
	}

	private function _init() {
		$cookie_key = DEFAULT_SESSION_NAME;
		if ($this->userSession != 'captcha_register') {
			$this->setError(400, 400150, 'cannot connect');
			return FALSE;
		}
		$session_id = $this->request->COOKIE[$cookie_key];
		$this->santorini = $session_id;	
		return TRUE;
	}
}
