<?php
namespace Snake\Package\Spam\Helper;

use \Snake\libs\Cache\Memcache;

class CacheIpJail {

	protected static $prefix = 'spam:ip:';

	public static function checkSpam($ip) {
		$cache = Memcache::instance();
		$key = self::$prefix . $ip . ":level";
		$level = $cache->get($key);
		if (!empty($level)) {
			if ($level > 0) {
				return FALSE;
			}
		}
		return TRUE;
	}

}
