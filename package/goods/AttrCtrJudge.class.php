<?php

namespace Snake\Package\Goods;

Use Snake\Package\Goods\Helper\RedisAttrClickHelper; 
/**
 * 判断属性页是不是采用click等特殊排序
 *
 * @author Xuan Zheng
 * @package 宝库
 */

class AttrCtrJudge {

	/**
	 * 判断是否是符合属性页click展示的条件
	 *
	 * 现阶段只有新用户一个条件
	 *
	 * @return TRUE
	 */
	public static function isAttrCtr($aid = 0) {
		$sessionId = $_COOKIE['MEILISHUO_GLOBAL_KEY'];
		if (substr($sessionId, -15, 6) != date("ymd")) {
			return FALSE;
		}
		$num = RedisAttrClickHelper::getNumInRedis($aid);
		if (empty($num)) {
			return FALSE;
		}
		return TRUE;	
	}

}

