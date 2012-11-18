<?php
namespace Snake\Modules\User;

use \Snake\package\user\LogOn360;
use \Snake\package\user\LogOn;

class Log_on_360 extends \Snake\Libs\Controller {

	private $emailAddress = NULL;
	private $postParams = NULL;


	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$logOnHelper = new LogOn();
		$response = $logOnHelper->userLogOn($this->postParams, '360');
		//$logOnHelper = new LogOn360();
		//$response = $logOnHelper->userLogOn($this->postParams, 1);
		$this->view = $response;
		return;
	}

	private function _init() {
		$this->postParams['email_address'] = $this->request->REQUEST['username'];
		$this->postParams['password'] = $this->request->REQUEST['token'];
		$this->postParams['request'] = $this->request;
		return TRUE;
	}

}
