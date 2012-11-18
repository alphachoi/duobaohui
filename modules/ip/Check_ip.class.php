<?php
namespace Snake\Modules\Ip;

use \Snake\Package\Ip\IpCheck;

class Check_ip extends \Snake\Libs\Controller {
	
	private $ip = NULL;
	public $mode = NULL;


	public function run() {
		$this->_init();
		$ipHelper = new IpCheck($this->ip, "HANGZHOU");
		$ipHelper->setValue();
		$ip = $ipHelper->getValue();
		$this->view = $ip;
		return TRUE;
	}

	private function _init() {
		$this->ip = $this->request->REQUEST['ip'];
		$this->mode = 0;
		return TRUE;
	}

}


