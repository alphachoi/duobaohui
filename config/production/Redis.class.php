<?php
namespace Snake\Config\Production;

class Redis extends \Snake\Libs\Base\Config {

	public function __construct() {
		$this->servers = array(
				array('host' => '127.0.0.1', 'port' => '6379'),
			);
	}
}
