<?php
namespace Snake\Config\Production;

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

		$config['MASTER'] = array(
			'HOST' => 'localhost',
			'PORT' => '3306',
			'USER' => 'root',
			'PASS' => 'root',
			'DB'   => 'duobao',
		);
		$config['SLAVES'][] = $config['MASTER'];
		return $config;
	}



	private function _shark() {

		return $this->_dolphin();
	}

	private function _whale() {
		$config = array();

		$config['MASTER'] = array(
			'HOST' => '172.16.0.47',
			'PORT' => '3306',
			'USER' => 'meiliwork',
			'PASS' => 'Tqs2nHFn4pvgw',
			'DB'   => 'whale',
		);

		$config['SLAVES'][] = array(
			'HOST' => '172.16.0.46',
			'PORT' => '3306',
			'USER' => 'dbreader',
			'PASS' => 'wearefashions',
			'DB'   => 'whale',
		);

		$config['SLAVES'][] = array(
			'HOST' => '172.16.0.47',
			'PORT' => '3306',
			'USER' => 'dbreader',
			'PASS' => 'wearefashions',
			'DB'   => 'whale',
		);

		return $config;
	}
	
	function _seal() {
		$config = array();

		$config['MASTER'] = array(
			'HOST' => '172.16.0.137',
			'PORT' => '3306',
			'USER' => 'meiliwork',
			'PASS' => 'Tqs2nHFn4pvgw',
			'DB'   => 'seal',
		);

		$config['SLAVES'][] = array(
			'HOST' => '172.16.0.137',
			'PORT' => '3306',
			'USER' => 'dbreader',
			'PASS' => 'wearefashions',
			'DB'   => 'seal',
		);

        return $config;
	}
}
