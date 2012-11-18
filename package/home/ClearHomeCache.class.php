<?php
namespace Snake\Package\Home;

use \Snake\libs\Cache\Memcache AS Memcache;

class ClearHomeCache {

	public function __construct() {

	}
	public function clearUserHomePageCache($user_id) {
        $cacheObj = Memcache::instance();

		//package/home/HomePoster
        $cacheKeyNum = 'USER:TIMELINE:' . $user_id;
        $cacheKey = 'HOME_LAST_TID_' . $user_id;
        $cacheObj->delete($cacheKeyNum);
        $cacheObj->delete($cacheKey);

		//modules/home/home_poster
        $cacheKeyNum = 'CacheKey:Home_poster_tids:' . $user_id;
        $cacheObj->delete($cacheKeyNum);

		//modules/home/home_recommends
        $cacheKey = 'CacheKey:Home_recommends_groupInfo:' . $user_id;
        $cacheObj->delete($cacheKey);
	}
}
