<?php

namespace Snake\Package\Timeline\Outbox;


/**
 * 杂志社发件箱
 * type : list
 * key  : GroupPosterOutbox:$gid
 *
 */
class RedisGroupPosterOutbox extends \Snake\Libs\Redis\Redis {
	
	protected static $prefix = 'GroupPosterOutbox';
	const SIZE = 300;
	

	/**
	 * 获取杂志社发件箱推的数量
	 * @param $gid 杂志社编号
	 *
	 */
	public static function getSize($gid) {
		if (empty($gid)) {
			return FALSE;
		}	
		return self::lSize($gid);
	}	

	/**
	 * 获取杂志社发件箱的一段tid
	 * @param $gid int 杂志社编号
	 * @param $offset int 偏移量,默认为零
	 * @param $limit int 获取tid的数量,默认为零(取全部)
	 *
	 */
	public static function getRangePoster($gid, $offset = 0, $limit = 0) {
		if (empty($gid)) {
			return FALSE;
		}
		$end = $offset + $limit - 1;
		return self::lRange($gid, $offset, $end);
	}

	/**
	 * 返回发件箱的第一个远素
	 * @param $gid int 杂志社编号
	 *
	 */
	public static function getFirstTid($gid) {
		if (empty($gid)) {
			return FALSE;
		}
		return self::lGet($gid, 0);
	}
}
