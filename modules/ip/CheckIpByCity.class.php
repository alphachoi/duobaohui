<?php
namespace Snake\Modules\Ip;

use \Snake\libs\Cache\Memcache AS Memcache;
use \Snake\Package\Group\Groups AS Groups;

class Check_ip extends \Snake\Libs\Controller {
	
	private $ip = NULL;
	private $mode = NULL;


	public function run() {
		$this->_init();


	}

	private function _init() {
		$ip = 11111;
		$mode = 0;
		if (isset($this->request->REQUEST['mode'])) {
			$this->mode = 1;
		}
		return TRUE;
	}

}


