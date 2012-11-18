<?php
namespace Snake\Libs\Base;

abstract class Config {

	public static function load($name) {
		if (is_null(self::$config_namespace)) {
			throw new \Exception("Config namespace is not set yet.");
		}

		static $configs;

		if (!isset($configs[$name])) {
			$class = self::$config_namespace . "\\{$name}";
			$config = new $class();
			$configs[$name] = $config;
		}

		return $configs[$name];
	}

	/**
	 * Set the namespace of all config classes.
	 */
	public static function setConfigNamespace($namespace) {
		self::$config_namespace = $namespace;
	}

	private static $config_namespace = NULL;

	////////////////////////////////////////

	abstract protected function __construct();

	private $config;

	public function __get($name) {
		return $this->config[$name];
	}

	public function __set($name, $value) {
		$this->config[$name] = $value;
	}

	public function getConfig() {
		return $this->config;
	}
}
