<?php
namespace Snake\Package\Msg\Helper;

class RedisUserRepinNotice extends \Snake\Libs\Redis\Redis {
	static $prefix = "UserRepinNotice";

    const SIZE = 30;
    const READSIZE = 10;


    public static function setNotice($uid, $params = array()) {
		if (empty($params) || empty($uid) || !is_array($params)) {
			$log = new \Snake\Libs\Base\SnakeLog('gc_snake_notice', 'normal');
			$log->w_log(print_r(array('user_id' => $uid, 'params' => $params), true));
			return FALSE;
		}
        $params = json_encode($params);
		$params = base64_encode(serialize($params));
        self::lPush($uid, $params);
        self::lTrim($uid, 0, self::SIZE);
    }   

    public static function getNotice($uid) {
        if (empty($uid)) {
            return FALSE;
        }   
        $notices = self::lRange($uid, 0, self::READSIZE - 1);
		if (empty($notices)) {
			return array();
		}
        $notices_array = array();
        foreach($notices as $key => $notice) {
			$value = unserialize(base64_decode($notice));
			if (empty($value)) {
				$value = $notice;
			}
            $notices_array[$key] = json_decode($value, TRUE);  
        }   

        return $notices_array;
    }  

	public static function existNotice($uid) {
		if (empty($uid)) {
			return FALSE;
		}
		return self::exists($uid);
	}
}
