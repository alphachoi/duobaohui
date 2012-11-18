<?php
namespace Snake\Modules\Register;

USE \Snake\Package\User\User AS User;
USE \Snake\libs\Cache\Memcache  AS Memcache;
USE \Snake\Package\User\UserFormat AS UserFormat;
USE \Snake\Package\User\UserValidate AS UserValidate;

/**
 * 数据验证
 *
 */
class Register_validate extends \Snake\Libs\Controller {
	
	private $rules = array(
		'email',
		'nickname',
		'captcha',
	);
	private $santorini = '';
	private $currentRule = '';
	private $currentData = '';
	private $errMsg = 0;

	const EMAIL_EXIST = 1; //'邮箱已经存在'; 
	const NICKNAME_EXIST = 2; //'用户名已经存在'; 
	const EMAIL_FORMAT_ERROR = 3; //'邮箱格式错误'; 
	const NICKNAME_FORMAT_ERROR = 4; //'支持中英文、数字、下划线，限长10个汉字。';
	const MASK_WORD = 5; //屏蔽词;
	const CAPTCHA_ERROR = 6; //验证码
	
	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		if ($this->currentRule == 'captcha') { 
			$this->isCaptchaError();
		}
		else {
			$this->isFormatError();
			$param[$this->currentRule] = $this->currentData;
			empty($this->errMsg) && $this->isExist($param);
		}
		$this->view = $this->errMsg;
		return TRUE;
	}

	private function _init() {
		$this->santorini = $this->request->COOKIE[DEFAULT_SESSION_NAME];
		if (!empty($this->santorini) &&
			!empty($this->request->REQUEST['rule']) && 
			(!empty($this->request->REQUEST['data']) || (int) $this->request->REQUEST['data'] === 0) && 
			in_array($this->request->REQUEST['rule'], $this->rules)) {
				$this->currentRule = $this->request->REQUEST['rule'];
				$this->currentData = $this->request->REQUEST['data'];
				return TRUE;
		}
		$this->setError(400, 20125, 'parameter empty');
		return FALSE;
	}

	/**
	 * 邮箱/呢称格式
	 *
	 */
	private function isFormatError() {
		$formatObj = new UserFormat();
		if ($this->currentRule == 'email') {
			if ($formatObj->emailFormat($this->currentData) === FALSE) {
				$this->errMsg = self::EMAIL_FORMAT_ERROR; 	
			}
		}
		elseif ($this->currentRule == 'nickname') {
			if ($formatObj->nicknameFormat($this->currentData) === FALSE) {
				$this->errMsg = self::NICKNAME_FORMAT_ERROR;
			}
			elseif ($formatObj->setMaskRule('DFA_register') && $formatObj->maskwordFormat($this->currentData) === FALSE) {
				$this->errMsg = self::MASK_WORD;
			}
		}
	}

	/**
	 * 邮箱/呢称是否存在
	 *
	 */
	private function isExist($param) {
		$user = new User();
		$result = $user->getUserProfile($param, "count(*) AS num");
		if ($result[0]['num'] > 0) {
			if ($this->currentRule == 'email') {
				$this->errMsg = self::EMAIL_EXIST;
			}	
			elseif ($this->currentRule == 'nickname') {
				$this->errMsg = self::NICKNAME_EXIST;
			}
		}
	}

	/**
	 * 验证码是否正确
	 *
	 */
	private function isCaptchaError() {
		$data = $this->currentData;
		$santorini = $this->santorini;
        $validate = new UserValidate($data, $santorini);
        if ($validate->ValidateCaptcha($data, $santorini) === FALSE) {
			$this->errMsg = self::CAPTCHA_ERROR;	
        }
	}
}
