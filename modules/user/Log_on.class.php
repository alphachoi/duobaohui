<?php
namespace Snake\Modules\User;

use \Snake\package\user\LogOn;
use \Snake\package\user\UserValidate;
Use \Snake\libs\Cache\Memcache;

class Log_on extends \Snake\Libs\Controller {

	private $emailAddress = NULL;
	private $postParams = NULL;
	private $checkCode = NULL;
	private $santorini = NULL;
	const ENTER_ERROR = -1;
	const AUDITING = -3;
	const CAPTCHA_ERROR = 6; 

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$logOnHelper = new LogOn();
		$response = $logOnHelper->userLogOn($this->postParams);
		if (!empty($this->checkCode) && $response['status'] == 6) {
			$response['status'] = -1;
		}
		$this->view = $response;
		return;
	}

	private function _init() {
		preg_match("^[-a-zA-Z0-9_.]+@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$^", $this->request->REQUEST['email'], $match);
		if(!$this->_checkCode()) {
			return FALSE;
		}

		if (/*empty($this->request->REQUEST['email']) || */empty($this->request->REQUEST['email'])) { 
			$this->setError(400, 40111, 'uncorrect email address!');
			return FALSE;
		}
		$this->postParams['email_address'] = $this->request->REQUEST['email'];
		if (empty($this->request->REQUEST['password'])) {
			$this->setError(400, 40112, 'password is empty!');
			return FALSE;
		}
		$this->postParams['password'] = $this->request->REQUEST['password'];
		$this->postParams['request'] = $this->request;
		$this->postParams['save_state'] = isset($this->request->REQUEST['save_state']) ? $this->request->REQUEST['save_state'] : 0;
		$this->postParams['type'] = isset($this->request->REQUEST['type']) ? $this->request->REQUEST['type'] : 0;
		$this->postParams['wbid'] = isset($this->request->REQUEST['wbid']) ? $this->request->REQUEST['wbid'] : 0;
		$this->postParams['redirect'] = isset($this->request->REQUEST['r']) ? $this->request->REQUEST['r'] : "";
		$pos = strpos($this->request->refer, 'frm=tuanaliyun');
		if (!empty($pos)) {
			$url = explode('&r=', $this->request->refer);
			$this->postParams['redirect'] = $url[1];
		}
		return TRUE;
	}

	private function _validateCaptcha($checkCode, $santorini) {
		$data = $checkCode;
		$santorini = $santorini;
        $validateHelper = new UserValidate($data, $santorini);
        if ($validateHelper->ValidateCaptcha($data, $santorini) === FALSE) {
			return FALSE;
        }
		return TRUE;
	}

	private function _checkCode() {
		$cacheHelper = Memcache::instance();
		$cacheKey = "LOGON_ERROR_TIMES" . $this->request->COOKIE[DEFAULT_SESSION_NAME];
		$logonTimes = $cacheHelper->get($cacheKey);
		if ($logonTimes <= 2) {
			return TRUE;
		}
		$this->checkCode = $this->request->REQUEST['checkcode'];
		$this->santorini = $this->request->COOKIE[DEFAULT_SESSION_NAME];
		if ($this->_validateCaptcha($this->checkCode, $this->santorini) == FALSE) {
			$this->view = array(
				'status' => self::CAPTCHA_ERROR
			);
			return FALSE;
		}
		return TRUE;
	}

}
