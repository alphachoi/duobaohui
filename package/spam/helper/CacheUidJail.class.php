<?php
namespace Snake\Package\Spam\Helper;

use \Snake\libs\Cache\Memcache;

class cacheUidJail {

	protected static $prefix = 'spam:uid:';

	public static function checkSpam($uid) {
		$cache = Memcache::instance();
		$key = self::$prefix . $uid . ":level";
		$level = $cache->get($key);
		if (!empty($level)) {
			if ($level > 0) {
				return FALSE;
			}
		}
		return TRUE;
	}

}
