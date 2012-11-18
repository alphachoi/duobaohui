<?php
namespace Snake\Package\User;

Use \Snake\Package\User\LogOn;

/**
 * 参数中的password是加密后的
 */
class LogOnOB implements \Snake\Libs\Interfaces\Iobserver {

	public function __construct() {

	}

	public function onChanged($sender, $args) {
		$params = array();
		$from = 'register';
		if ($sender == 'Register_actionconnect' 
			|| $sender == 'Register_action' ) {
			$params = array(
				'email_address' => $args['email'],
				'password' => $args['password'],
				'request' => $args['request'],
			);	
		}
		else {
			$params = array(
				'email_address' => $args['info']['email'],
				'password' => $args['info']['password'],
				'request' => $args['request']
			);
			$from = 'activate';
		}
		$result = $this->_userLogOn($params, $from);
		return $result;
	}

	private function _userLogOn($params, $from = 'register') {
		$logOnHelper = new LogOn();
		$result = $logOnHelper->userLogOn($params, $from);
		if ($result['status'] > 0) {
			return TRUE;
		}
		return FALSE;

	}


}
