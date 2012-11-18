<?php
namespace Snake\Package\Goods\Helper;
/**
 * Guang页面redis中tids相关
 * @author xuanzheng@meilishuo.com
 * score => twitterId, member => twitterId
 */
//Use \Snake\Libs\Redis\Redis;

class RedisNewestHelper extends \Snake\Libs\Redis\Redis {
	static $prefix = 'GoodsHotWhiteList';
	const redisKey = 'HOTWHITE';

	/**
	 * 获取逛宝贝newest，redis中twitter的数目
	 * @author zhengxuan <xuanzheng@meilishuo.com>
	 * @access public
	 * @return int twitter的数目
	 */
	public static function getNumInRedisByScore($score) {
		return parent::zCount(self::redisKey, '-inf', $score);
	}

	public static function getNumInRedis() {
		return parent::zCount(self::redisKey, '-inf', '+inf');
	}

	/**
	 * 获取逛宝贝popular，redis中想要的 获取逛宝贝popular，redis中twitter
	 */
	public static function getTwittersInRedis($offset, $limit) {
		$start = $offset * $limit;
		$stop = $start + $limit - 1;
		return parent::zRevRange(self::redisKey, $start, $stop);
	}

	public static function getTwitterIdsInRedisByFirstTid($tid) {
		return parent::zRevRangeByScore(self::redisKey, $tid, '-inf');
	}

	public static function getTwittersChange($twitterId){
		return parent::zRevRank(self::redisKey, $twitterId);
	}

}
