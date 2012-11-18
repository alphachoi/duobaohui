<?php
namespace Snake\Modules\Register;

USE \Snake\Package\User\User AS User;
USE \Snake\Package\Edm\SendEdm  AS SendEdm;
USE \Snake\Libs\Cache\Memcache AS Memcache;

class Register_sendemail extends \Snake\Libs\Controller implements \Snake\Libs\Interfaces\Iobservable {

	private $email = '';
	private $observers = array();

	//暂没有应用
	const EMAIL_SUCCESS = TRUE;
	const EMAIL_HAS_SEND = TRUE;
	const EMAIL_NOT_EXISTS = TRUE;

	const RESEND_TIME = 900;

	public function run() {
		if (!$this->_init()) {
			return FALSE;	
		}

		$cache = Memcache::instance();
		$cacheKey = 'users:resend' . $this->email;
		$check = $cache->get($cacheKey);
		if (!empty($check)) {
			$this->view = self::EMAIL_HAS_SEND;		
			return TRUE;
		}
		
		$user = new User();
		$param['email'] = $this->email;
		$fields = array('nickname', 'email', 'active_code', 'is_actived');
		$userInfo = $user->getUserProfile($param, $fields, TRUE);

		if (empty($userInfo)) {
			$this->view = self::EMAIL_NOT_EXISTS;
			return TRUE;
		}

		$obs = new SendEdm();
		$params = array(
			'nickname' => $userInfo[0]['nickname'],
			'email' => $userInfo[0]['email'],
			'activatecode' => $userInfo[0]['active_code'],
			'is_actived' => $userInfo[0]['is_actived'],
		);
		if ($params['is_actived'] == -1) {
			$sender = 'Register_verify';
		}
		else {
			$sender = 'Register_action';
		}
		$obs->onChanged($sender, $params);
		$cache->set($cacheKey, $this->email, self::RESEND_TIME);
		$this->view = self::EMAIL_SUCCESS;	
		return TRUE;
	}

	private function _init() {
		$this->email = trim($this->request->REQUEST['email']);
		if (empty($this->email)) {
			$this->setError(400, 40002, 'empty email');
			return FALSE;
		}
		return TRUE;
	}

    public function addObserver($observer) {
        $this->observers[] = $observer;
    }
}
