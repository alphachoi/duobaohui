<?php
namespace Snake\Package\Spam\Helper;

use \Snake\libs\Cache\Memcache;

class cacheSidJail {

	protected static $prefix = 'spam:sessid:';

	public static function checkSpam($sessid) {
		$cache = Memcache::instance();
		$key = self::$prefix . $sessid . ":level";
		$level = $cache->get($key);
		if (!empty($level)) {
			if ($level > 0) {
				return FALSE;
			}
		}
		return TRUE;
	}

}
