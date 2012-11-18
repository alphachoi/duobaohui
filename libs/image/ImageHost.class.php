<?php
namespace Snake\Libs\Image;

class ImageHost {

	private static $singleton = NULL;

	/**
	 * Singleton.
	 */
    public static function instance() {
		is_null(self::$singleton) && self::$singleton = new self();
		return self::$singleton;
	}


	private $config = NULL;

	/**
	 * Constructor.
	 */
    private function __construct() {
		$this->config = \Snake\Libs\Base\Config::load('ImageHost')->imageHost;
    }

    public function getconfig() {
        return $this->config;
    }

	public function getImageHost() {
		return array_rand($this->config);
	}

}
