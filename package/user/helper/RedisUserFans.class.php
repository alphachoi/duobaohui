<?php
/**
 * 用户粉丝
 * type zSet 
 * key UserFans:$user_id
 *
 */
namespace Snake\Package\User\Helper;

class RedisUserFans extends \Snake\Libs\Redis\Redis {
	
	protected static $prefix = 'UserFans';

	/**
	 * 获取用户粉丝
	 */
	public static function getFans($user_id, $order = 'DESC', $start = 0, $limit = 0, $withscore = FALSE) {
		if (empty($user_id)) {
			return FALSE;
		}
		if (empty($limit) || $limit > 200000) {
			$limit = 100000;
		}
		$end = $start + $limit - 1;
		if ($order == 'DESC') {
			return self::zRevRange($user_id, $start, $end, $withscore);
		}
		else {
			return self::zRange($user_id, $start, $end, $withscore); 
		}
	}

	/**
	 * 添加用户粉丝
	 * @param 用户编号
	 * @param 粉丝编号
	 * @param Unix时间
	 *
	 */
	public static function addFans($user_id, $fan_id, $score = -1) {
		if (empty($user_id) || empty($fan_id) || empty($score)) {
			return FALSE;		
		}
		$score == -1 && $score = $_SERVER['REQUEST_TIME'];
		self::zAdd($user_id, $score, $fan_id);
	}

	/**
	 * 移除用户粉丝
	 * @param $user_id 用户编号
	 * @param $fan_id 粉丝编号
	 */
	public static function removeFans($user_id, $fan_id) {
		if (empty($user_id) || empty($fan_id)) {
			return FALSE;
		}	
		self::zDelete($user_id, $fan_id);
	}

	/**
	 * 获取用户粉丝数 
	 * @param user_id 用户编号
	 * @param min scoreMin
	 * @param max scoreMax
	 */
	public static function getFansNumber($user_id, $min = NULL, $max = NULL) {
		if (empty($user_id)) {
			return FALSE;
		}
		is_null($min) && $min = '-inf';
		is_null($max) && $max = '+inf';
		return self::zCount($user_id, $min, $max);
	}



	/**
	 * 判断粉丝是否存在 
	 * @param $user_id 用户编号
	 * @pram $userId 用户编号
     */
    public static function getFansCount($userId) {
        if (empty($userId)) {
            return FALSE;
        }   
        return self::zCard($userId);
    }  

	public static function isFans($user_id, $fan_id) {
		if (empty($user_id) || empty($fan_id)) {
			return FALSE;
		}
		$score = self::zScore($user_id, $fan_id);	
		return $score !== FALSE;
	}
}

