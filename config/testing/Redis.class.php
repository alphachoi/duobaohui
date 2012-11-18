<?php
namespace Snake\Config\Testing;

class Redis extends \Snake\Libs\Base\Config {

	public function __construct() {
		$this->servers = array(
			array('host' => '192.168.1.198', 'port' => '6379'),
			array('host' => '192.168.1.198', 'port' => '6380'),
		);
	}
}
