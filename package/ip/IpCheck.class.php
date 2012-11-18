<?php
namespace Snake\Package\Ip;

Use Snake\Package\Goods\Abtest;
Use Snake\Package\Ip\Ip;

class IpCheck {

	private $ip = NULL;
	private $placeName = "";
	private $ipHelper = NULL;
	private $isban = NULL;

	public function __construct($ip, $placeName, $mode = 0) {
		$this->ip = $ip;
		$this->placeName = $placeName;
	}

	public function setValue() {
		$isban = $this->_isAbtest();
		$this->isban = 1;
		if ($isban == FALSE) {
			$this->isban = 0;
		}
		return TRUE;
	}

	public function getValue() {
		return $this->isban;
	}

	private function _isAbtest() {
		$ipobj = Ip::instance();
		$ipobj->setIp($this->ip);
		if (!$ipobj->checkIpByCityName($this->placeName)) {
			return FALSE;	
		}
		/*if (parent::abtest()) {
			return TRUE;
		}*/
		return TRUE;
	}
	
}
