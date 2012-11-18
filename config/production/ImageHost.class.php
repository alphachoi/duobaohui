<?php
namespace Snake\Config\Production;

class ImageHost extends \Snake\Libs\Base\Config {

	protected function __construct() {
		$this->imageHost = array(
			'http://imgtest.meiliworks.com',
			'http://imgtest.meiliworks.com',
			'http://imgtest-dl.meiliworks.com',
			'http://imgtest-dl.meiliworks.com',
			'http://imgtest-lx.meilishuo.net',
			'http://imgtest-lx.meilishuo.net',
			'http://imgst-dl.meilishuo.net',
			'http://imgtest-lx.meilishuo.net',
			'http://imgst-dl.meiliworks.com',
			'http://img-tx.meilishuo.net'
		);
	}
}
