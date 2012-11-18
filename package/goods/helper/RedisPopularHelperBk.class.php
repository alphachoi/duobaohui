<?php
namespace Snake\Package\Goods\Helper;
/**
 * 逛宝贝 热榜 popular 的redis相关的描述文件
 * @author zhengxuan <xuanzheng@durain.meilishuo.com>
 */
Use \Snake\Libs\Redis\Redis;
Use Snake\Libs\Cache\Memcache;

/**
 * RedisPopularHelper
 * popular的redis相关
 * @author zhengxuan <xuanzheng@durian.meilishuo.com>
 */

class RedisPopularHelperBk extends \Snake\Libs\Redis\Redis {

	static $prefix = 'TwitterRankSet';
	const redisKey = 'twitterRankSet_';
	const cpcLandingRedisKey = 'twitterCpcLandingRankSet_';


	/**
	 * 获取逛宝贝popular，redis中twitter的数目
	 * @author zhengxuan <xuanzheng@meilishuo.com>
	 * @access public
	 * @return int twitter的数目
	 */
	public static function getNumInRedis($keyName) {
		//return parent::zCount($keyName, '-inf', '+inf');
		$tidJson = parent::get($keyName);
		$tidsObj = json_decode($tidJson);
		$tids = $tidsObj->tids;
		$num = count($tids);
		return $num;
	}

	/**
	 * 取得所对应的redis key
	 * @author zhengxuan <xuanzheng@meilishuo.com>
	 */
	public static function getRedisKey($key) {
		$totalName = self::redisKey . $key;
		return $totalName;
	} 

	/**
	 * 取得所对应的redis test  key
	 * @author zhengxuan <xuanzheng@meilishuo.com>
	 */
	public static function getCpcLandingRedisKey($key) {
		return self::cpcLandingRedisKey . $key;
	}

	/**
	 * 获取逛宝贝popular，redis中想要的 获取逛宝贝popular，redis中twitter
	 */
	public static function getTwittersInRedis($keyName, $offset, $limit) {
		$start = $offset * $limit;
		//$stop = $start + $limit - 1;
		//return parent::zRevRange(self::redisKey, $start, $stop);
		//return parent::zRevRange( $keyName, $start, $stop);
		$tidJson = parent::get($keyName);
		$tidJsonObj = json_decode($tidJson);
		$tids = $tidJsonObj->tids;
		if (!is_array($tids)) {
			return array();	
		}
		return array_slice($tids, $start, $limit);

	}

	public static function insertIntoRedis($type, $newTwitters, $forCpc = 'FALSE') {

		if( count( $newTwitters) < 600 ) {
			CallMeHelper::sendEmail("from pop24's twitter script", "ERROR : 数量不够，无法更新");
			return false;
		}
		$log = new \Snake\Libs\Base\SnakeLog('pop24_twitter_in_redis', "normal");
		if ($forCpc) {
			$twitterRankKey = self::getCpcLandingRedisKey($type);
		}
		else {
			$twitterRankKey = self::getRedisKey($type);
		}
		$toJson = array('tids' => $newTwitters);
		$newTwittersJson = json_encode($toJson);
		parent::set($twitterRankKey, $newTwittersJson);
		$log->w_log("{$twitterRankKey} 插入到redis ". count( $newTwitters) . " 条");
		echo "{$twitterRankKey} 插入到redis ". count( $newTwitters) . " 条\n";
		if (empty($forCpc)) {
			$cacheHelper = new CachePopularHelper();
			$cacheHelper->setCache($newTwitters, $type);
		}
		return TRUE;
	}
}


