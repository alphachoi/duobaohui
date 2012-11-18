<?php 
namespace Snake\Package\Msg\Helper;
/**
 * QQ渠道页卡模型通知
 *
 **/
class RedisWeiboPush extends \Snake\Libs\Redis\Redis {

	static $prefix = "WeiboPush";
	const SIZE = 30;
	const READSIZE = 10;
	
	public static function setWeiboPush($type, $params = array()) {
		if (empty($type) || empty($params) || !is_array($params)) {
			return FALSE;	
		}
		$params = json_encode($params);
		$params = base64_encode(serialize($params));
		self::lpush($type, $params);
		self::ltrim($type, 0, self::SIZE);
	}

	public static function getWeiboPush($type) {
		if (empty($type)) {
			return FALSE;
		}
		$notices = self::lRange($type, 0, self::READSIZE);
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
}

