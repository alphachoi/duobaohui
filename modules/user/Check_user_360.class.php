<?php
namespace Snake\Modules\User;

use \Snake\package\user\LogOn360;

class Check_user_360 extends \Snake\Libs\Controller {

	private $emailAddress = NULL;
	private $postParams = NULL;


	public function run() {
		if (!$this->_init()) {
			return FALSE;
		}
		$logOnHelper = new LogOn360();
		$response = $logOnHelper->userLogOn($this->postParams, 1);
		$this->view = $response;
		return;
	}

	private function _init() {
		if (empty($this->request->REQUEST['username'])) { 
			$this->view = array(
				'code' => '401',
				'msg' => '用户名为空'
			);
			//$this->setError(400, 400104, 'uncorrect user name!');
			return FALSE;
		}
		$this->postParams['user_name'] = $this->request->REQUEST['username'];
		if (empty($this->request->REQUEST['password'])) {
			$this->view = array(
				'code' => '401',
				'msg' => '密码错误'
			);
			//$this->setError(200, 400105, 'password is empty!');
			return FALSE;
		}
		$this->postParams['password'] = $this->request->REQUEST['password'];
		return TRUE;
	}

}
