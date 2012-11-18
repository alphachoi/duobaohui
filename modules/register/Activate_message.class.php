<?php
namespace Snake\Modules\Register;

/**
 * 返回邮箱URL
 *
 */
class Activate_message extends \Snake\Libs\Controller {
	
	private $email = '';
	private $emailUrl = '';

	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$this->emailUrl = $this->emailUrl();
		$this->view = array(
			'email' => $this->email,
			'emailUrl' => $this->emailUrl,
		);

		return TRUE;
	}

	private function _init() {
		if (empty($this->request->REQUEST['email'])) {
			$this->setError(400, 40150, 'empty email');
			return FALSE;
		}	
		$this->email = trim($this->request->REQUEST['email']);
		return TRUE;
	}

	private function emailUrl() {
		$info = explode('@', $this->email);
        $emailDomain = '@' . $info[1];
        if (strpos($emailDomain, 'gmail')) {
            return 'http://mail.google.com';
        }    
        if (strpos($emailDomain, '126.com')) {
            return 'http://mail.126.com';
        }    
        if (strpos($emailDomain, '163.com')) {
            return 'http://mail.163.com';
        }    
        if (strpos($emailDomain, 'sina.com')) {
            return 'http://mail.sina.com.cn';
        }    
        if (strpos($emailDomain, 'sohu.com')) {
            return 'http://mail.sohu.com';
        }    
        if (strpos($emailDomain, 'yahoo')) {
            return 'http://mail.cn.yahoo.com';
        }    
        if (strpos($emailDomain, 'hotmail.com')) {
            return 'https://login.live.com/login.srf';
        }    
        if (strpos($emailDomain, 'yeah.net')) {
            return 'http://www.yeah.net';
        }    
        if (strpos($emailDomain, 'tom.com')) {
            return 'http://mail.tom.com';
        }    
        if (strpos($emailDomain, 'qq.com')) {
            return 'http://mail.qq.com';
        }
        return FALSE;
	}
}
