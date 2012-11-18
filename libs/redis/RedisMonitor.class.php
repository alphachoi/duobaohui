<?php
namespace Snake\Libs\Redis;

class RedisMonitor {

	private static $monitor = NULL;

	private $queries = array();

	private $overLook = TRUE;

	public static function getMonitor() {
		is_null(self::$monitor) && self::$monitor = new self();
		return self::$monitor;
	}

	public function __construct() {
	}

	public function shutDownMonitor() {
		$this->overLook = FALSE;
	}

	public function finish($method, $args, $message) {

		if (empty($this->overLook)) {
			return FALSE;
		}

		$this->queries[] = array(
			'command' => "{$method} " . implode(' ', $args),
		    'message' => $message,
		);

	}

	public function dump() {
		return $this->queries;
	}

}
