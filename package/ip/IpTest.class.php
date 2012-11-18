<?php
namespace Snake\Package\Ip;

Use Snake\Package\Goods\Abtest;
Use Snake\Package\Ip\Ip;

class IpTest extends Abtest {

	const SYMBOL = "ip";

	private $placeName = '';
	private $ip = '';

	public function setPlace($placeName = '') {
		$this->placeName = $placeName;	
		return TRUE;	
	}
	public function setIp($ip = '') {
		$this->ip = $ip;
		return TRUE;
	}

	public	function isAbtest() {
		$ipobj = Ip::instance();
		$ipobj->setIp($this->ip);
		if (!$ipobj->checkIpByPlace($this->placeName)) {
			return FALSE;	
		}
		if (parent::abtest()) {
			return TRUE;
		}
		return FALSE;
	}
	



}
