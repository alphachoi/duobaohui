<?php
namespace Snake\Package\User\Helper;

class RedisUserConnectHelper extends \Snake\Libs\Redis\Redis {
	protected static $prefix = 'UserConnect';

	public static function updateUserToken($type, $uid, $ttl = TRUE, $access_token) {
        $name = $type . $uid . 'token';
        if (TRUE === $ttl) {
            return self::set($name, $access_token);
        }   
        else {
            return self::setEx($name, $ttl, $access_token);
        }   
    }   

    public static function updateUserrefToken($type, $uid, $ttl, $refresh_token) {
        $name = $type . $uid . 'refreshtoken';
        return self::setEx($name, $ttl, $access_token);
    }   

	public static function getUserToken($type, $uid) {
		if (empty($uid)) {
			return FALSE;
		}
		$name = $type . $uid . 'token';
		return self::get($name);
	}

	public static function updateUserAuth($type, $uid, $auth) {
        $name = $type . $uid . 'auth';
        return self::set($name, $auth);
    }   

    public static function getUserAuth($type, $uid) {
		if (empty($uid)) {
			return FALSE;
		}
        $name = $type . $uid . 'auth';
        return self::get($name);
    }   

    public static function delUserAuth($type, $uid) {
        $name = $type . $uid . 'auth';
        return self::del($name);
    }   

    public static function delUserToken($type, $uid) {
        $name = $type . $uid . 'token';
        return self::del($name);
    }

    public static function setUserSetting($type, $uid, $settings) {
		if (empty($uid)) {
			return FALSE;
		}
        $name = $type . $uid . 'settings';
        return self::set($name, $settings);
    }

    public static function getUserSetting($type, $uid) {
		if (empty($uid)) {
			return FALSE;
		}
        $name = $type . $uid . 'settings';
        return self::get($name);
    }

    public static function delUserSetting($type, $uid) {
		if (empty($uid)) {
			return FALSE;
		}
        $name = $type . $uid . 'settings';
        return self::del($name);
    }
}
