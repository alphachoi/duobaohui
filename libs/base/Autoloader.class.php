<?php
namespace Snake\Libs\Base;

class Autoloader {

	/**
	 * Singleton.
	 */
	public static function get($root_path_setting) {
		static $singleton = NULL;
		is_null($singleton) && $singleton = new self($root_path_setting);
		return $singleton;
	}

	/**
	 * Store the base path of IO library.
	 *
	 * @var string
	 */
	private $root_path_setting = array();

	/**
	 * Constructor method. Identify IO's base path and set user specified
	 * path settings. Also register self's load() method.
	 */
	private function __construct($root_path_setting) {
		// path of IO library
		$this->root_path_setting = $root_path_setting;
		spl_autoload_register(array($this, 'autoload'));
	}

	/**
	 * Get file path of source file.
	 */
	private function getFilepath($class_name) {
		// class name contains namespaces
		$pieces = explode('\\', $class_name);

		if (isset($this->root_path_setting[strtolower($pieces[0])])) {
			$root_path = $this->root_path_setting[strtolower($pieces[0])];
			// get rid of the leading root namespace
			array_shift($pieces);
		}
		else {
			$root_path = ROOT_PATH;
		}

		$class_name = array_pop($pieces);
		$base_path = $root_path . DIRECTORY_SEPARATOR . strtolower(implode(DIRECTORY_SEPARATOR, $pieces)); 
		$type = strpos($class_name, 'i') === 0 ? 'interface' : 'class';
		return $base_path . "/{$class_name}.{$type}.php";
	}

	/**
	 * The method that actually triggers require_once().
	 */
	private function autoload($class_name) {
		$filepath = $this->getFilepath($class_name);
		if (file_exists($filepath)) {
			require_once($filepath);
		}
	}
}
