<?php
namespace Snake\Modules\User;

Use \Snake\libs\Cache\Memcache;

class Logon_validate extends \Snake\Libs\Controller {

	public function run() {
		$this->view = $this->_checkLogOnTimes();
		return TRUE;
	}

	private function _checkLogOnTimes() {
		$cacheHelper = Memcache::instance();
		$cacheKey = "LOGON_ERROR_TIMES" . $this->request->COOKIE[DEFAULT_SESSION_NAME];
		$logonTimes = $cacheHelper->get($cacheKey);
		if (empty($logonTimes)) {
			return FALSE;
		}
		if ($logonTimes < 2) {
			return FALSE;
		}
		return TRUE;
	}
}


