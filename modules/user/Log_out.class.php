<?php
namespace Snake\Modules\User;

use \Snake\package\user\LogOut;
use \Snake\package\user\UserValidate;

class Log_out extends \Snake\Libs\Controller {
	
	private $userId = NULL;

	public function run() {
		$this->view = array(
			'redirect' => 'welcome'
		); 
		if (!$this->_init()) {
			return FALSE;
		}
		$logOutHelper = new LogOut($this->userId);
		$logOutHelper->userLogOut($this->request->COOKIE);
		$cacheHelper = \Snake\libs\Cache\Memcache::instance();
		$cacheKey = $this->request->COOKIE["santorini_mm"];
		$session = $cacheHelper->get($cacheKey);
		if (!empty($session)) {
			$logHelper = new \Snake\Libs\Base\SnakeLog('hz_session', 'normal');
			$logHelper->w_log(print_R($session, TRUE));
			$session = $cacheHelper->delete($cacheKey);
		}
		return TRUE;
	}

	private function _init() {
		if (empty($this->userSession['user_id'])) {
			return FALSE;
		}
		$this->userId = $this->userSession['user_id'];
		return TRUE;
	}

}

