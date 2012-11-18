<?php
namespace Snake\Package\Goods\Helper;

class RedisEx extends \Snake\Libs\Redis\Redis {

	protected static $lastConnection = array();


	protected static function getRedis($key = NULL) {
		static $config = NULL;
		is_null($config) && $config = \Snake\Libs\Base\Config::load('Redis');

		$count = count($config->servers);
		$server_id = is_null($key) ? 0 : (hexdec(substr(md5($key), 0, 2)) % $count);

		if (!isset(self::$connections[$server_id])) {
			$host = $config->servers[$server_id]['host'];
			$port = $config->servers[$server_id]['port'];
			self::$connections[$server_id] = self::connect($host, $port);
		}
		return self::$connections[$server_id];
	}

	public static function getLastConnection() {
		if (empty(self::$lastConnection)) {
			return FALSE;	
		}
		return self::$lastConnection;
	}

	public static function __callStatic($method, $args) {
		//$endArg = end($args);
		foreach($args as $key => $arg) {
			if (is_array($arg) && isset($arg['lastConnection'])) {
				$lastConnection = $arg['lastConnection'];
				unset($args[$key]);
			}
		}
		$class = get_called_class();
		$prefix = $class::getPrefix();
		$configSwither = $class::getConfig();

		$name = $args[0];
		$key = "{$prefix}:{$name}";
		$args[0] = $key;

		if (strtolower($method) == "rename") {
			$args[1] = "{$prefix}:{$args[1]}";
		}

		try {
			if (empty($configSwither)) {
				if (!empty($lastConnection)) {
					if ($method == 'rename') {
						var_dump($args);	
					}
					$result = call_user_func_array(array($lastConnection, $method), $args);
				}
				else {
					self::$lastConnection = $class::getRedis($key);
					$result = call_user_func_array(array(self::$lastConnection, $method), $args);
				}
			}
			else {
				$result = call_user_func_array(array($class::getLogRedis($key), $method), $args);
			}
		}
		catch (\RedisException $e) {
			$redis_monitor = RedisMonitor::getMonitor();
			$redis_monitor->finish($method, $args, $e->getMessage());
			$result = NULL;
		}

		return $result;
	}


}
