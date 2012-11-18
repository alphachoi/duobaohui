<?php
namespace Snake\Modules\User;

use \Snake\package\user\LogOn360;
use \Snake\package\user\LogOn;

class Redirect_360 extends \Snake\Libs\Controller {

	private $emailAddress = NULL;
	private $postParams = NULL;


	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$logOnHelper = new LogOn360();
		$response = $logOnHelper->userLogOn($this->postParams, 2);
		//$logOnHelper = new LogOn360();
		//$response = $logOnHelper->userLogOn($this->postParams, 1);
		$this->view = $response;
		return;
	}

	private function _init() {
		$this->postParams['user_name'] = $this->request->REQUEST['username'];
		$this->postParams['password'] = $this->request->REQUEST['password'];
		$this->postParams['request'] = $this->request;
		return TRUE;
	}

}
