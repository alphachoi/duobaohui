<?php
namespace Snake\Package\Commerceapp\Helper;

class MedalStatistic extends \Snake\Libs\Redis\Redis {

	protected static $prefix = 'MedalStatistic';

	/**
	 * 获取商业活动tid
	 *
	 */
	public static function getCommerceTids($type, $offset = 0, $limit = 0) {
		if (empty($type)) {
			return FALSE;
		}	
		return self::lRange($type, $offset, $offset + $limit - 1);	
	}
}
