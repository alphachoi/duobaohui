<?php
namespace Snake\Config\Testing;

class ImageHost extends \Snake\Libs\Base\Config {

	protected function __construct() {
		$this->imageHost = array(
			'http://imgst-office.meilishuo.com',
		);
	}
}
