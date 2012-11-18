<?php

namespace Snake\Package\Goods;

/**
 * 
 * 海报推位置调整类
 *
 * 一个白名单alpha版, 通过调用取得调整后推
 *
 * @author Xuan Zheng <xuanzheng@meilishuo.com>
 * @package 宝库
 */

class AdjustTwitterPosition {

	static $yifuTidAdjust = array(
		'5' => array(
		   	479438437, 481541865, 479427826, 477773346,
			480569359, 481635836, 483911204, 478831805,
			477635654, 480264586, 483925314, 483924446,
			483947516, 481115746, 481076188, 482337935,
			482243915, 482237050, 482235568, 482188486,
		)
	);

	public function adjustTidsInCata($tids = array(), $request) {
		$cataId = $request->getCataId();
		$offset = $request->getOffset();
	   	$pageSize = $request->getPageSize();

//		$date = date("Y-m-d");
//		if (!in_array($date,array('2012-11-21', '2012-09-22', '2012-09-23'))) {
//			return $tids;	
//		}
		if ($cataId != 2000000000000 || empty($tids)) {
			return $tids;
		}
		$start = $offset * $pageSize;
		$end = ($offset + 1) * $pageSize - 1;
		$position = array_keys(self::$yifuTidAdjust);
		foreach ($position as $p) {
			if ($p < $start || $p > $end) {
				continue;
			}
			$st = $p % $pageSize - 1;
			$tid = $this->getRandTid($p);
			foreach ($tids as $k => $v) {
				if ($v == $tid) {
					unset($tids[$k]);	
				}
			}
			$partOne = array_slice($tids, 0, $st);	
			$partTwo = array_slice($tids, $st, $pageSize);	
			if (empty($tid)) {
				continue;	
			}
			$tids = array_merge($partOne, array($tid), $partTwo);
		}
		return $tids;
	}

	private function getRandTid($positionKey) {
		$count = count(self::$yifuTidAdjust[$positionKey]);
		$rand = rand(0, $count - 1);
		$tid = self::$yifuTidAdjust[$positionKey][$rand];
		$cacheHelper = new AdjustTwitterPositionCache($tid, $positionKey);
		$showNum = $cacheHelper->getCache();
		$showNum += 1;
		$cacheHelper->setCache($showNum);
		return $tid;
	}

	public function getTidsShowNum() {
		$tid = 0;
		$cacheHelper = new AdjustTwitterPositionCache($tid);
		$echo = '';
		foreach (self::$yifuTidAdjust as $p => $tids) {
			$echo .= "{$p}位置:\n";
			foreach ($tids as $tid) {
				$cacheHelper->setTid($tid);
				$cacheHelper->setP($p);
				$showNum = $cacheHelper->getCache();
				$echo .= "{$tid} : {$showNum}\n";
			}
		}
		return $echo;	
	}
	
	static public function adjustTidSetCacheJudge($offset, $pageSize, $cid = 0) {
		if ($cid != 2000000000000) {
			return FALSE;
		}
		$positions = array_keys(self::$yifuTidAdjust);
		$start = $offset * $pageSize;
		$end = ($offset + 1) * $pageSize;
		foreach ($positions as $p) {
			if ( $p >= $start && $p <= $end) {
				return TRUE;
			}
		}
		return FALSE;	
	}



}
