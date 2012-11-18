<?php
namespace Snake\Package\Goods\Helper;

Use \Snake\Libs\Cache\Memcache;
Use \Snake\Package\Manufactory\Poster;
Use \Snake\Package\Goods\Tag;
Use \Snake\Package\Goods\FirstFrameRule;


/**
 * 热榜cache的相关操作
 *
 * @author Wei Wang 
 * @author Xuan Zheng
 * @package 宝库
 */


class CachePopularHelper {
	const isShowClose = 0;
	const isShowLike = 1;

	public function setCache($tids = array(), $type = '') {
		if (empty($tids) || empty($type)) {
			return FALSE;
		}	
		$totalNum = count($tids);
        switch ($type) {
            case 'pop24': 
                $typeChange = 'hot'; break;
            case 'pop7': 
                $typeChange = 'popular'; break;
            default :
                return FALSE;
        }

		$tids20 = array();

		$i = 1;
		foreach ($tids as $tid) {
			$tidsTmp[] = $tid;
			if (($i % 20) == 0) {
				$tids20[] = $tidsTmp;
				$tidsTmp = array();
			}
			$i++;
		}

		$cacheHelper = Memcache::instance();

		foreach ($tids20 as $k => $t) {
			if ($k > 60) {
				break;
			}
			$posterObj = new Poster();
			$posterObj->isShowLike(self::isShowLike);
			$posterObj->isShowClose(self::isShowClose);
			$posterObj->setVariables($t, 0);
			$posterObj->parallelPart2(0);
			$posterObj->parallel(0);
			$poster	= $posterObj->getPoster();

			//硬规则
			$rule = new FirstFrameRule($poster, $k); 
			$poster = $rule->firstFrameAdjust();

			if (($k % 6) == 0) {
				$poster = Tag::addTagWzz($poster, 0, $k % 6);
			}
			if (empty($poster)) {
				continue;
			}
			$responsePosterData = array('tInfo' => $poster, 'totalNum' => $totalNum);
			$cacheKeyForPosters = "all_{$k}_20_0_{$typeChange}_weight____01";
			$cacheKeyForPosters2 = "all_{$k}_20_0_{$typeChange}_weight____011";
            //echo "{$cacheKeyForPosters}    ";
            $cacheKeyForPosters = md5($cacheKeyForPosters);
            $cacheKeyForPosters = "CacheKey:Attribute_poster:{$cacheKeyForPosters}";
			$cacheHelper->set($cacheKeyForPosters, $responsePosterData, 1200);
			$cacheHelper->set($cacheKeyForPosters2, $responsePosterData, 1200);
            //echo $cacheKeyForPosters;echo "\n";
		}
		return TRUE;
	}
}

