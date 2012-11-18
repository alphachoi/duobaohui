<?php
namespace Snake\Config\Testing;

class Memcache extends \Snake\Libs\Base\Config {

	protected function __construct() {
		$this->pools = array(
			array('host' => '192.168.1.198', 'port' => 11211),
			array('host' => '192.168.60.1', 'port' => 11211),
		);
	}
}
