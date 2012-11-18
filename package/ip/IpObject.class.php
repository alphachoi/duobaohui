<?php
namespace Snake\Package\Ip;
Use Snake\Package\Ip\ShortIp;

class IpObject extends \Snake\Package\Base\DomainObject {

    public function __construct($ip = array()) {
		$this->row = $ip;
	}

	public function getRow() {
		return $this->row;
	}

	public function getCountry() {
		//$province = mb_substr($this->row['Country'], 0, 2, 'UTF-8');
		//$country = mb_substr($this->row['city'], 0, 2, 'UTF-8');
		return $this->row['city'];
	}
}
