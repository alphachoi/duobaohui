<?php
namespace Snake\Libs\Cache;
class Memcache {

	private static $singleton = NULL;

	/**
	 * Singleton.
	 */
    public static function instance() {
		is_null(self::$singleton) && self::$singleton = new self();
		return self::$singleton;
	}


	private $config = NULL;
	private $engine = NULL;

	/**
	 * Connection pools.
	 *
	 * @var array
	 */
	private $pools = array();


	/**
	 * Constructor.
	 */
    private function __construct() {
		$inite = FALSE;
		if (class_exists('\\Memcached')) {
			$this->engine = 'Memcached';
			$inite = TRUE;
		}
		else if (class_exists('\\Memcache')) {
			$this->engine = 'Memcache';
		}
		if (is_null($this->engine)) {
			throw new \Exception("Neither \"Memcached\" nor \"Memcache\" class can be found.");
			die();
		}

		$this->config = \Snake\Libs\Base\Config::load('Memcache');
		$class = "\\{$this->engine}";
		$this->pools = new $class();
		foreach ($this->config->pools AS $server) {
			/*if ($inite == FALSE) {
				$this->pools->connect($server['host'], $server['port']);
				$inite = TRUE;
				continue;
			}*/
			$this->pools->addServer($server['host'], $server['port']);
		}
    }

    public function getconfig() {
        print_r($this->config);
    }

	/**
	 * Delete an item.
	 *
	 * @param string $key The key to be deleted.
	 * @param int $time The amount of time the server will wait to delete
	 * the item.
	 *
	 * @return Returns TRUE on success or FALSE on failure.
	 */
	public function delete($key, $time = 0) {
		$result = TRUE;
		$result = $result && $this->pools->delete($key, $time);
		return $result;
	}

	/**
	 * Store an item.
	 *
	 * @param string $key The key under which to store the value.
	 * @param mixed $value The value to store.
	 * @param int $expiration The expiration time, defaults to 0.
	 *
	 * @return Returns TRUE on success or FALSE on failure.
	 */
	public function set($key, $value, $expiration = 0) {
		$result = TRUE;
		switch ($this->engine) {
			case 'Memcache':
                if (empty($key) || is_array($key)) {
                    $logHandle = new \Snake\Libs\Base\SnakeLog('error_memcache', 'normal');
                    $logHandle->w_log(print_r(debug_backtrace(), TRUE));
                }
				$result = $result && $this->pools->set($key, $value, MEMCACHE_COMPRESSED, $expiration);
				break;

			case 'Memcached':
			default:
				$result = $result && $this->pools->set($key, $value, $expiration);
				break;
		}
		return $result;
	}

	/**
	 * Retrieve an item.
	 *
	 * @param string $key The key of the item to retrieve.
	 *
	 * @return Returns the value stored in the cache or FALSE otherwise.
	 */
	public function get($key) {
		$result = FALSE;
		$result = $this->pools->get($key);
		return $result;
	}

	/**
	 * Increment numeric item's value.
	 */
	public function increment($key, $offset = 1) {
		$result = FALSE;
		$result = $this->pools->increment($key, $offset);
		return $result;
	}

	/**
	 * Decrement numeric item's value.
	 */
	public function decrement($key, $offset = 1) {
		$result = FALSE;
		$result = $this->pools->decrement($key, $offset);
		return $result;
	}

	/**
	 * Retrieve multiple items.
	 */
    public function getMulti($keys) {
		switch ($this->engine) {
			case 'Memcached':
				return $this->pools->getMulti($keys);

			case 'Memcache':
				$values = array();
				foreach ($keys as $key) {
					$val = $this->pools->get($key);
					if ($val !== FALSE) {
						$values[$key] = $val;
					}
				}
				return $values;
        }
    }

	/**
	 * Store multiple items.
	 */
    public function setMulti($items, $expiration = 0) {
		switch ($this->engine) {
			case 'Memcached':
				$result = TRUE;
				$result = $result && $this->pools->setMulti($items, $expiration);
				return $result;

			case 'Memcache':
				$result = TRUE;
				foreach ($items AS $k => $v) {
					$result = $result && $this->pools->set($k, $v, MEMCACHE_COMPRESSED, $expiration);
				}
		}	
		return $result;
    }
}
?>
