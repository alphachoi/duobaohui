<?php
namespace Snake\Package\User\Helper;

class RedisUserOauth extends \Snake\Libs\Redis\Redis {
	protected static $prefix = 'UserOauth';

	public static function updateUserIdWithAuth($type, $auth, $uid) {
        $name = $type . $auth;
        return self::set($name, $uid);
    }   

    public static function getUserIdFromAuth($type, $auth) {
        $name = $type . $auth;
        return self::get($name);
    }   

    public static function removeUserIdFromAuth($type, $auth) {
        $name = $type . $auth;
        return self::del($name);
    }   
}
