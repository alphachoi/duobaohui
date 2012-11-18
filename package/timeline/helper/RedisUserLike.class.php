<?php
namespace Snake\Package\Timeline\Helper;

/**
 * 用户喜欢
 * type zSet
 * key UserLike:$user_id
 */
class RedisUserLike extends \Snake\Libs\Redis\Redis {

	const SIZE = 50;

	static $prefix = 'UserLike';

	/**
	 * 按score降序返回喜欢数据
	 * @param $userId int 用户编号
	 * @param $start int 开始时间
	 * @param $end int 结束时间
	 * @param $options array 参数(withscores/limit)
	 *
	 */
	public static function getLikes($userId, $start, $end, $options = array()) {
		if (empty($userId)) {
			return FALSE;
		}
		return self::zRevRangeByScore($userId, $end, $start, $options);
	}

	/**
	 * 添加喜欢
	 * @param $userId int 用户编号
	 * @param $tId int 推编号（新推）
	 * @param $score 喜欢时间
	 *
	 */
	public static function addLikes($userId, $tId, $score = -1) {
		if (empty($userId) || empty($tId) || empty($score)) {
			return FALSE;
		}
		$score == -1 && $score = time();
		return self::zAdd($userId, $score, $tId);
	}

	/**
	 * 修剪喜欢数据，使之保持一个常量值
	 * @param $userId int 用户编号
	 *
	 */
	public static function trimLikes($userId) {
		if (empty($userId)) {
			return FALSE;
		}
		$size = self::zSize($userId) - self::SIZE - 1;
		return self::zRemRangeByRank($userId, 0, $size);
	}
}
