<?php
namespace Snake\Package\User\Helper;

Use Snake\Libs\Cache\Memcache AS Memcache;

class CacheUserHelper extends Memcache {
	protected static $prefix = "user:login_status:";
	public static function 	getUserOnline($uid) {
		$status = Memcache::instance()->get(self::$prefix . $uid);
		return $status;
	}

}
