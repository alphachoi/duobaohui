<?php
namespace Snake\Package\User;

Use \Snake\Package\Session\UserSession;

class LogOut {

	private $userId = NULL;
	private $redirect = array();
	
	public function __construct($userId) {
		$this->userId = $userId;
		$redirect = array(
			'redirect' => MEILISHUO_URL . '/welcome'
		);
		return TRUE;
	}

	public function userLogOut($cookie) {
		if (empty($this->userId)) {
			return $this->redirect;
		}
		$sessionId = $cookie['santorini_mm'];
		UserSession::destroy_session($sessionId, $this->userId);
		self::removeCookie($cookie);
		if (isset($request->REQUEST['r']) && !empty($request->REQUEST['r'])) {
			$this->redirect = array(
				'redirect' => $request->REQUEST['r']
			);
		}
		return $this->redirect;
	}

	public static function removeCookie($cookie) {
		setcookie("CHANNEL_FROM", '',time() - 3600, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
		setcookie('MEILISHUO_REFER', 'default', 0, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
		setcookie('MEILISHUO_MM', FALSE, time() - 3600, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
		setcookie('santorini_mm', FALSE, time() - 3600, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
		if ($cookie['MEILISHUO_BAIDU']) {
			setcookie("MEILISHUO_BAIDU", '',time() - 3600, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
			setcookie("MEILISHUO_BAIDU_SIGN", '',time() - 3600, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);
		}    
		if ($cookie['channel_from_321']) {
			setcookie('channel_from_321','', time() - 3600, DEFAULT_COOKIEPATH, DEFAULT_COOKIEDOMAIN);  
		}
	}
}
