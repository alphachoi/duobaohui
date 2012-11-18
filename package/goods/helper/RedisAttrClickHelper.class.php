<?php
namespace Snake\Package\Goods\Helper;

/**
 * twitter id <=> attr 的redis相关
 *
 * 某类人群的属性页面的推是按点击数排序的，需要插入redis
 * 
 * @author Xuan Zheng
 * @package 宝库
 */
class RedisAttrClickHelper extends \Snake\Libs\Redis\Redis {

	static $prefix = 'AttrClickTwitter';
	
	/**
	 * prefix of redis key
	 * @const AttrClickTwitter_
	 */
	const redisKey = 'AttrClick_id:';

	/**
	 * 获取redis key
	 * @param int attr id (属性id)
	 * @return string rediskey 
	 */
	public static function getRedisKey($aid) {
		if(empty($aid)) {
			return FALSE;	
		}
		return  self::redisKey . "{$aid}";
	}

	/**
	 * 获取twitter ids
	 * @params int aid
	 * @params int offset
	 * @params int limit
	 * @return array tids
	 */
	public static function getTwittersInRedis($aid, $offset, $limit) {
		$redisKey = self::getRedisKey($aid);
		$tidJson = parent::get($redisKey);
		$tidJsonObj = json_decode($tidJson);
		$tids = $tidJsonObj->tids;
		if (!is_array($tids)) {
			return array();	
		}
		$tt =	array_slice($tids, $offset, $limit);
		return $tt;
		
	}

	/**
	 * 获取redis中twitter的数目
	 * @param int aid
	 * @return int 
	 */
	public static function getNumInRedis($aid) {
		$redisKey = self::getRedisKey($aid);
		$tidJson = parent::get($redisKey);
		$tidsObj = json_decode($tidJson);
		$tids = $tidsObj->tids;
		$num = count($tids);
		return $num;
	}


	/**
	 * 将twitter id 插入redis
	 * @param array twitter ids
	 * @param int attr id (属性id)
	 * @return boolean
	 */
	public static function insertTwitters($tids, $aid) {
		if( count($tids) <= 0) {
			$content = "ERROR : {$aid}数量为" . count($tids) . "不够,不更新\r\n";
			CallMeHelper::sendEmail("ERROR", $content);
			return FALSE;
		}
		$redisKey = self::getRedisKey($aid);
		if (empty($redisKey)) {
			return FALSE;
		}
		$num = count($tids);
		$oldJson = parent::get($redisKey);
		$oldTidsObj = json_decode($oldJson);
		$oldTids = $oldTidsObj->tids;
		$oldNum = count($oldTids);


		$log = new \Snake\Libs\Base\SnakeLog('attr_click_redis_log', "normal");
		if ($num <= $oldNum / 2) {
			$content = "ERROR : {$redisKey}现有{$oldNum},{$num}条推太少无法插入\r\n";
			CallMeHelper::sendEmail("ERROR", $content);
			$log->w_log($content);	
		}
		else {
			$json = json_encode(array('tids'=>$tids));
			parent::set($redisKey, $json);

			$content = "OK : {$redisKey}: {$oldNum} => {$num} update over\r\n";
			CallMeHelper::sendEmail("OK", $content);
			$log->w_log($content);
		}
		return TRUE;
	}
}
