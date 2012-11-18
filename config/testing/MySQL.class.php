<?php
namespace Snake\Config\Testing;

class MySQL extends \Snake\Libs\Base\Config {

	protected function __construct() {
		$this->dolphin = $this->_dolphin();
		$this->shark = $this->_shark();
		$this->whale = $this->_whale();
		$this->seal = $this->_seal();
	}

	/**
	 * dolphin database.
	 */
	private function _dolphin() {
		$config = array();
		$config['MASTER']    = array('HOST' => '192.168.1.198', 'PORT' => '3306', 'USER' => 'root', 'PASS' => 'LRo4LVcvxJFSk', 'DB' => 'dolphin');
		$config['SLAVES'][0] = array('HOST' => '192.168.1.198',	'PORT' => '3306', 'USER' => 'root', 'PASS' => 'LRo4LVcvxJFSk', 'DB' => 'dolphin');
		$config['SLAVES'][1] = array('HOST' => '192.168.1.198',	'PORT' => '3306', 'USER' => 'root', 'PASS' => 'LRo4LVcvxJFSk', 'DB' => 'dolphin');
		return $config;
	}

	private function _shark() {
		$config = array();
		$config['MASTER']    = array('HOST' => '192.168.1.198', 'PORT' => '3306', 'USER' => 'shark', 'PASS' => 'shark', 'DB' => 'shark');
		$config['SLAVES'][0] = array('HOST' => '192.168.1.198', 'PORT' => '3306', 'USER' => 'shark', 'PASS' => 'shark', 'DB' => 'shark');
		return $config;
	}

	private function _whale() {
		$config = array();
		$config['MASTER']    = array('HOST' => '192.168.1.198', 'PORT' => '3306', 'USER' => 'root', 'PASS' => 'LRo4LVcvxJFSk', 'DB' => 'whale');
		$config['SLAVES'][0] = array('HOST' => '192.168.1.198', 'PORT' => '3306', 'USER' => 'root', 'PASS' => 'LRo4LVcvxJFSk', 'DB' => 'whale');
		return $config;
	}
	private function _seal() {
		$config = array();
		$config['MASTER']    = array('HOST' => '192.168.1.198', 'PORT' => '3306', 'USER' => 'root', 'PASS' => 'LRo4LVcvxJFSk', 'DB' => 'whale');
		$config['SLAVES'][0] = array('HOST' => '192.168.1.198', 'PORT' => '3306', 'USER' => 'root', 'PASS' => 'LRo4LVcvxJFSk', 'DB' => 'whale');
		return $config;
	}
}
