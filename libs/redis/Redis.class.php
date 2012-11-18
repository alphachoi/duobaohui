<?php
namespace Snake\Libs\Redis;

class Redis {

	/**
	 * Holds initialized Redis connections.
	 *
	 * @var array
	 */
	protected static $connections = array();
	protected static $writeHandle = NULL;

	protected static $logConnections = array();

	/**
	 * By default the prefix of Redis key is the same as class name. But it
	 * can be specified manually.
	 *
	 * @var string
	 */
	protected static $prefix = NULL;
	protected static $xsync = NULL;

	/**
	 * the server_id of the Redis Cluster 
	 *
	 * @var int
	 */

	/**
	 * Initialize a Redis connection.
	 * @TODO: support master/slave
	 */
	protected static function connect($host, $port, $timeout = 5) {
		$redis = new \Redis();
		$redis->connect($host, $port, $timeout);
		return $redis;
	}

	/**
	 * Get an initialized Redis connection according to the key.
	 */
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

	/**
	 * Get the write connect to the nginx proxy.
	 */
	protected static function getWriteHandle() {
		if(!isset(self::$writeHandle)) {
			self::$writeHandle = new RedisWrite();
		}
		return self::$writeHandle;
	}	

	protected static function getLogRedis($key = NULL) {
		static $logConfig = NULL;
		is_null($logConfig) && $logConfig = \Snake\Libs\Base\Config::load('LogRedis');

		$count = count($logConfig->servers);
		$server_id = is_null($key) ? 0 : (crc32($key) % $count);
		$server_id = abs($server_id);
		if (!isset(self::$logConnections[$server_id])) {
			$host = $logConfig->servers[$server_id]['host'];
			$port = $logConfig->servers[$server_id]['port'];
			self::$logConnections[$server_id] = self::connect($host, $port);
		}
		return self::$logConnections[$server_id];
	}

	protected static function getPrefix() {
		$class = get_called_class();
		if (!is_null($class::$prefix)) {
			return $class::$prefix;
		}
		return get_called_class();
	}

	protected static function getSync() { 
		$class = get_called_class();
		if (is_null($class::$xsync)) {
			return TRUE;
		}
		return FALSE;
	}

	private static function verifyMethod($method) {
		$writeMethods = array(
			'hdel' => TRUE, 'hincrby' => TRUE, 'hset' => TRUE, 'hsetnx' => TRUE,
			'del' => TRUE, 'expireat' => TRUE, 'expire' => TRUE, 'persist' => TRUE,
			'linsert' => TRUE, 'lpop' => TRUE, 'lpush' => TRUE, 'lpushx' => TRUE, 'lrem' => TRUE, 'lset' => TRUE, 'ltrim' => TRUE, 'rpop' => TRUE, 'rpush' => TRUE, 'rpushx' => TRUE,
			'sadd' => TRUE, 'spop' => TRUE, 'srem' => TRUE,
			'zadd' => TRUE, 'zincrby' => TRUE, 'zrem' => TRUE, 'zremrangebyrank' => TRUE,
			'append' => TRUE, 'decrby' => TRUE, 'decr' => TRUE, 'incrby' => TRUE, 'incr' => TRUE, 'setbit' => TRUE, 'setex' => TRUE, 'set' => TRUE, 'setnx' => TRUE,
		);
		if (isset($writeMethods[$method]) && !empty($writeMethods[$method])) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	protected static function getConfig() {
		$class = get_called_class();
		if (!empty($class::$config)) {
			return $class::$config;
		}
		return FALSE;
	}

	public static function __callStatic($method, $args) {
        $method = strtolower($method);
		$class = get_called_class();
		$prefix = $class::getPrefix();
		$name = $args[0];
		$key = "{$prefix}:{$name}";
		$args[0] = $key;
		if (self::verifyMethod($method)) {
			unset($args[0]);
			$sync = $class::getSync();
			$result = $class::getWriteHandle()->request($method, $key, $args, $sync);
			return $result;
		}
		else {
			try {
				$result = call_user_func_array(array($class::getRedis($key), $method), $args);
			}
			catch (\RedisException $e) {
				$redis_monitor = RedisMonitor::getMonitor();
				$redis_monitor->finish($method, $args, $e->getMessage());
				$result = NULL;
			}
		}
		return $result;
	}

	//////////////////////////////////////////////////
    public static function delete($name) {
		$class = get_called_class();
		$prefix = $class::getPrefix();
		$key = "{$prefix}:{$name}";
		return $class::getWriteHandle()->request('del', $key);
	}

    public static function hMset($name, $values = array()) {
		$class = get_called_class();
		$prefix = $class::getPrefix();
		$key = "{$prefix}:{$name}";
		$params = array();
		foreach ($values as $key => $value) {
			$params[] = $key . '#' . $value;
		}
		$args = array(1 => implode(",", $params));
		$result = $class::getWriteHandle()->request('hmset', $key, $args);
		return $result;
    }

	public static function rPush($name, $value) {
		$class = get_called_class();
		$prefix = $class::getPrefix();
		$configSwither = $class::getConfig();
		$key = "{$prefix}:{$name}";
		if (empty($configSwither)) {
			$args = array(
				1 => $value
			);
			$result = $class::getWriteHandle()->request('rpush', $key, $args);
		}
		else {
			try {
				$result = $class::getLogRedis($key)->rpush($key, $value);
			}
			catch (\RedisException $e) {
				$result = NULL;
			}
		}
		return $result;
	}

	public static function del($name) {
		$class = get_called_class();
		$prefix = $class::getPrefix();
		$configSwither = $class::getConfig();
		$key = "{$prefix}:{$name}";
		if (empty($configSwither)) {
			$result = $class::getWriteHandle()->request('del', $key);
		}
		else {
			try {
				$result = $class::getLogRedis($key)->del($key);
			}
			catch (\RedisException $e) {
				$result = NULL;
			}
		}
		return $result;
	}

    public static function rPop($name) {
		$class = get_called_class();
		$prefix = $class::getPrefix();
		$key = "{$prefix}:{$name}";
		$result = $class::getWriteHandle()->request('rpop', $key);
        return json_decode($result, TRUE);
    }

    public static function lPop($name) {
		$class = get_called_class();
		$prefix = $class::getPrefix();
		$key = "{$prefix}:{$name}";
		$result = $class::getWriteHandle()->request('lpop', $key);
        return json_decode($result, TRUE);
    }

	public static function lRemove($name, $value, $count = 0) {
		$class = get_called_class();
		$prefix = $class::getPrefix();
		$key = "{$prefix}:{$name}";
		$args = array(
			1 => $count,
			2 => $value
		);
		return $class::getWriteHandle()->request('lrem', $key, $args);
	}

	/*
	 * multi only support one key because of the hash cluster
	 * a multi(PIPELINE) block is simply transmitted faster to the server
	 * this write_multi is only for write commands
	 */
	public static function multi($name, $values = array()) {
		$class = get_called_class();
		$prefix = $class::getPrefix();
		$key = "{$prefix}:{$name}";
		$params = array();
		foreach ($values as $value) {
			$params[] = implode("#", $value);
		}
		$args = array(1 => implode(",", $params));
		$result = $class::getWriteHandle()->request('multi', $key, $args);
	}

}

class RedisWrite {

	private static $host = NULL; 
    static $ci = NULL;

	public function __construct() {
		static $config;
		is_null($config) && $config = \Snake\Libs\Base\Config::load('Redis');
		self::$host = $config->writeHost;
        is_null(self::$ci) && self::$ci = curl_init();
	}

	public function request($method, $key, $args = array()) {
		$params = array(
			'method=' . $method,
			'key=' . $key,
		);
		foreach ($args as $key => $value) {
			$pkey = "arg" . $key;
			$params[] = $pkey . "=" . $value;
		}
		$body = join($params, "&");

        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: 60";
		$header[] = "Expect:";
		curl_setopt(self::$ci, CURLOPT_CONNECTTIMEOUT, 1); 
		curl_setopt(self::$ci, CURLOPT_TIMEOUT, 1); 
		curl_setopt(self::$ci, CURLOPT_HEADER, FALSE);
		curl_setopt(self::$ci, CURLOPT_URL, self::$host);
        curl_setopt(self::$ci, CURLOPT_HTTPHEADER, $header);
		curl_setopt(self::$ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt(self::$ci, CURLOPT_POST, TRUE);
		curl_setopt(self::$ci, CURLOPT_POSTFIELDS, $body);

		$ret = curl_exec(self::$ci);
		if (curl_errno(self::$ci)) {
			$info = curl_getinfo(self::$ci);
			if ($info['http_code'] != 100) {
				$redis_error_log = new \Snake\Libs\Base\SnakeLog("redis_error_log", "normal");
				$str = $body . "\nerror with " . json_encode($info);
				$redis_error_log->w_log($str);
			}
		}
		return $ret;
	}
}
