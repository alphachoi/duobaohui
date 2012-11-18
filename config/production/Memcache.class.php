<?php
namespace Snake\Config\Production;

class Memcache extends \Snake\Libs\Base\Config {

	protected function __construct() {
		$this->pools = array(
			array('host' => '127.0.0.1' , 'port' => '11211'),
		/*
			array('host' => '172.16.0.12' , 'port' => '11211'),
			array('host' => '172.16.0.21' , 'port' => '11211'),
			array('host' => '172.16.0.21' , 'port' => '11222'),
			array('host' => '172.16.0.32' , 'port' => '11211'),
			array('host' => '172.16.0.77' , 'port' => '11211'),
			array('host' => '172.16.0.95' , 'port' => '11211'),
			array('host' => '172.16.0.118' , 'port' => '11211'),
			array('host' => '172.16.0.118' , 'port' => '11233'),
			array('host' => '172.16.0.130' , 'port' => '11233'),
			*/
		);
	}
}
