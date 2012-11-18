<?php
namespace Snake\Package\User\Helper;

class RedisUserFollow extends \Snake\Libs\Redis\Redis {
	protected static $prefix = 'UserFollow';

    /** 
     * 获取用户关注
     *
     */
    public static function getFollow($userId, $order = 'DESC', $start = 0, $limit = 0, $withscore = FALSE) {
        if (empty($userId)) {
            return FALSE;
        }   
		$end = $start + $limit - 1;
        if ($order == 'DESC') {
            return self::zRevRange($userId, $start, $end, $withScore);
        }   
        else {
            return self::zRange($userId, $start, $end, $withScore);
        }   
    }   

    /** 
     * 添加用户关注
     * @param 用户编号 <br/>
     * @param 关注人编号 <br/>
     * @param Unix时间 <br/>
     *
     * @return Long 1 if the element is added. 0 otherwise.
     */
    public static function addFollow($userId, $followId, $score = -1) {
        if (empty($userId) || empty($followId)) {
            return FALSE;    
        }   
        $score == -1 && $score = time();
        return self::zAdd($userId, $score, $followId);
    }  

    /**
     * 移除关注用户
     * @param 用户编号
     * @param 关注人编号
     *
     * @return LONG 1 on success, 0 on failure.
     */
    public static function removeFollow($userId, $followId) {
        if (empty($userId) || empty($followId)) {
            return FALSE;
        }
        return self::zDelete($userId, $followId);
    }

     /** 
     * 获取用户关注数(获取总数不要用)
     * @param user_id 用户编号
     * @param min scoreMin
     * @param max scoreMax
     */
    public static function getFollowNumber($userId, $min = NULL, $max = NULL) {
        if (empty($userId)) {
            return FALSE;
        }
		is_null($min) && $min = '-inf';
		is_null($max) && $max = '+inf';
        return self::zCount($userId, $min, $max);
    }

	/**
	 * 获取用户关注总数
	 * @param $userId 用户编号
	 */
	public static function getFollowCount($userId) {
		if (empty($userId)) {
			return FALSE;
		}	
		return self::zCard($userId);
	}

    /** 
     * 判断是否关注某人 
     * @param $userId 用户编号
     * @param $followId 关注人编号
     *
     */
    public static function isFollowed($userId, $followId) {
        if (empty($userId) || empty($followId)) {
            return FALSE;
        }
        $score = self::zScore($userId, $followId);
        return $score !== FALSE;
    }


}

